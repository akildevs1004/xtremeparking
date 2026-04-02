<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="purple" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="dialogViewCustomer" width="110%">
      <AlarmCustomerView
        :key="key"
        @closeCustomerDialog="closeCustomerDialog"
        :_id="viewCustomerId"
        :isPopup="true"
      />
    </v-dialog>
    <v-dialog v-model="newCustomerDialog" max-width="900px">
      <v-card>
        <v-card-title dark class="popup_background">
          <span dense> New Customer</span>
          <v-spacer></v-spacer>
          <v-icon @click="newCustomerDialog = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <AlarmNewCustomer
            name="AlarmNewCustomerSecurityCustomerslist"
            :key="key"
            @closeDialog="getDataFromApi()"
          />
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-row>
      <v-col>
        <v-card elevation="0" class="mt-2">
          <v-toolbar class="mb-2 white--text" color="white" dense flat>
            <v-toolbar-title>
              <span style="color: black111"> Customers</span></v-toolbar-title
            >
            <!-- <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }"> -->
            <v-btn
              title="Reload"
              dense
              class="ma-0 px-0"
              x-small
              :ripple="false"
              @click="getDataFromApi"
              text
            >
              <v-icon class="ml-2" dark>mdi mdi-reload</v-icon>
            </v-btn>
            <!-- </template>
                <span>Reload</span>
              </v-tooltip> -->

            <v-spacer></v-spacer>
            <span style="width: 180px"
              ><v-text-field
                style="padding-top: 7px"
                height="20"
                class="employee-schedule-search-box"
                @input="getDataFromApi()"
                v-model="commonSearch"
                label="Search (min 3)"
                dense
                outlined
                type="text"
                append-icon="mdi-magnify"
                clearable
                hide-details
              ></v-text-field
            ></span>

            <v-btn
              title="Change Request"
              x-small
              :ripple="false"
              text
              @click="
                key += 1;
                newCustomerDialog = true;
              "
            >
              <v-icon class="">mdi mdi-plus-circle</v-icon>
            </v-btn>
          </v-toolbar>

          <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
            {{ snackText }}

            <template v-slot:action="{ attrs }">
              <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
            </template>
          </v-snackbar>
          <v-data-table
            dense
            :headers="headers"
            :items="data"
            :loading="loading"
            :options.sync="options"
            :footer-props="{
              itemsPerPageOptions: [10, 50, 100, 500, 1000],
            }"
            class="elevation-1"
            :server-items-length="totalRowsCount"
            fixed-header
            :height="tableHeight"
            :disable-sort="true"
          >
            <template v-slot:item.sno="{ item, index }">
              {{
                currentPage
                  ? (currentPage - 1) * perPage +
                    (cumulativeIndex + data.indexOf(item))
                  : ""
              }}
            </template>
            <template
              v-slot:item.building_name="{ item, index }"
              style="width: 300px"
            >
              <v-row no-gutters @click="viewItem(item)">
                <v-col
                  style="
                    padding: 5px;
                    padding-left: 0px;
                    width: 50px;
                    max-width: 50px;
                  "
                >
                  <v-img
                    style="
                      border-radius: 10%;
                      height: auto;
                      width: 50px;
                      max-width: 50px;
                    "
                    :src="
                      item.profile_picture
                        ? item.profile_picture
                        : '/no-business_profile.png'
                    "
                  >
                  </v-img>
                </v-col>
                <v-col style="padding: 10px">
                  <div style="font-size: 13px">
                    {{ item.building_name || "" }}
                  </div>
                  <small style="font-size: 12px; color: #6c7184">
                    {{ item.house_number }}, {{ item.street_number }},
                    {{ item.area }}, {{ item.city }}
                  </small>
                </v-col>
              </v-row>
            </template>
            <template v-slot:item.created_date="{ item }">
              <div
                :title="
                  getExpiryDatesCountColor(item.end_date) == 'red'
                    ? 'Expired'
                    : getExpiryDatesCountColor(item.end_date) == 'orange'
                    ? 'Expire in 30 days'
                    : 'End Date'
                "
                :style="'color:' + getExpiryDatesCountColor(item.end_date)"
              >
                {{ $dateFormat.format_date_month_name_year(item.end_date) }}
              </div>
              <small
                title="Start Date "
                style="font-size: 12px; color: #6c7184"
              >
                {{ $dateFormat.format_date_month_name_year(item.start_date) }}
              </small>
            </template>
            <template v-slot:item.building_type="{ item }">
              <div>
                {{ getBuildingTypeName(item.building_type_id) }}
                <!-- {{
                  buildingTypes[item.building_type_id]
                    ? buildingTypes[item.building_type_id].name
                    : "---"
                }} -->
              </div>
              <small style="font-size: 12px; color: #6c7184">
                {{ item.landmark }}
              </small>
            </template>

            <template v-slot:item.burglary="{ item }">
              <div
                v-if="
                  $dateFormat.verifyDeviceSensorName('Burglary', item.devices)
                "
              >
                <img
                  title="Burglary"
                  style="width: 23px; float: left"
                  src="/device-icons/burglary.png"
                />
              </div>
              <div v-else>---</div>
            </template>
            <template v-slot:item.temperature="{ item }">
              <div
                v-if="
                  $dateFormat.verifyDeviceSensorName(
                    'Temperature',
                    item.devices
                  )
                "
              >
                <img
                  title="Burglary"
                  style="width: 23px; float: left"
                  src="/device-icons/temperature.png"
                />
              </div>
              <div v-else>---</div>
            </template>
            <template v-slot:item.medical="{ item }">
              <div
                v-if="
                  $dateFormat.verifyDeviceSensorName('Medical', item.devices)
                "
              >
                <img
                  title="Burglary"
                  style="width: 23px; float: left"
                  src="/device-icons/medical.png"
                />
              </div>
              <div v-else>---</div>
            </template>
            <template v-slot:item.fire="{ item }">
              <div
                v-if="$dateFormat.verifyDeviceSensorName('Fire', item.devices)"
              >
                <img
                  title="Burglary"
                  style="width: 23px; float: left"
                  src="/device-icons/fire.png"
                />
              </div>
              <div v-else>---</div>
            </template>
            <template v-slot:item.water="{ item }">
              <div
                v-if="$dateFormat.verifyDeviceSensorName('Water', item.devices)"
              >
                <img
                  title="Burglary"
                  style="width: 23px; float: left"
                  src="/device-icons/water.png"
                />
              </div>
              <div v-else>---</div>
            </template>
            <template v-slot:item.area="{ item }">
              {{ item.house_number }}, {{ item.street_number }}{{ item.area
              }}{{ item.city }}
            </template>
            <template v-slot:item.options="{ item }">
              <v-menu bottom left>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn dark-2 icon v-bind="attrs" v-on="on">
                    <v-icon>mdi-dots-vertical</v-icon>
                  </v-btn>
                </template>
                <v-list width="120" dense>
                  <!-- <v-list-item
                    v-if="can('device_notification_contnet_view')"
                    @click="viewItem(item)"
                  >
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="secondary" small> mdi-eye </v-icon>
                      Popup
                    </v-list-item-title>
                  </v-list-item> -->
                  <v-list-item @click="viewItem2(item)">
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="secondary" small> mdi-eye </v-icon>
                      View
                    </v-list-item-title>
                  </v-list-item>
                  <!-- <v-list-item
                    v-if="can('device_notification_contnet_edit')"
                    @click="editItem(item)"
                  >
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="secondary" small> mdi-pencil </v-icon>
                      Edit
                    </v-list-item-title>
                  </v-list-item> -->
                  <v-list-item
                    v-if="can('device_notification_contnet_delete')"
                    @click="deleteItem(item)"
                  >
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="error" small> mdi-delete </v-icon>
                      Delete
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import AlarmNewCustomer from "../../components/Alarm/NewCustomer.vue";
import AlarmCustomerView from "../../components/Alarm/ViewCustomer.vue";

export default {
  layout: "technician",
  components: {
    AlarmNewCustomer,
    AlarmCustomerView,
  },
  data: () => ({
    key: 1,
    viewCustomerId: null,
    commonSearch: "",
    perPage: 10,
    cumulativeIndex: 1,
    currentPage: 1,
    branchesList: [],
    isCompany: true,
    tableHeight: 750,
    id: "",

    newCustomerDialog: false,
    dialogViewCustomer: false,
    totalRowsCount: 0,

    snack: false,
    snackColor: "",
    snackText: "",
    departments: [],
    Model: "Log",
    endpoint: "customers",
    payload: {},
    loading: true,
    data: [
      {
        company_id: 1,
        data: {
          device_name: "Tet",
          zone: "Tet",
          area: "Tet",
          created_datetime: "Tet",
          alarm_type: "Tet",
        },
      },
      {
        company_id: 2,
        data: {
          device_name: "Tet",
          zone: "Tet",
          area: "Tet",
          created_datetime: "Tet",
          alarm_type: "Tet",
        },
      },
      {
        company_id: 4,
        data: {
          device_name: "Tet",
          zone: "Tet",
          area: "Tet",
          created_datetime: "Tet",
          alarm_type: "Tet",
        },
      },
      {
        company_id: 1,
        data: {
          name: "Tet",
          name: "Tet",
          name: "Tet",
          name: "Tet",
          name: "Tet",
        },
      },
    ],
    headers: [
      {
        text: "#",
        value: "sno",
      },
      {
        text: "Customer Name",
        value: "building_name",
      },
      {
        text: "Type",
        value: "building_type",
      },

      // {
      //   text: "Location",
      //   value: "area",
      // },
      {
        text: "Account Expiry",
        value: "created_date",
      },
      {
        text: "Customer",
        value: "primary_contact.first_name",
      },
      {
        text: "Burglary",
        value: "burglary",
      },
      {
        text: "Medical",
        value: "medical",
      },
      {
        text: "Fire",
        value: "fire",
      },
      {
        text: "Water",
        value: "water",
      },
      {
        text: "Temp",
        value: "temperature",
      },
      {
        text: "Options",
        value: "options",
      },
    ],
    ids: [],

    data: [],
    devices: [],
    total: 0,
    pagination: {
      current: 1,
      total: 0,
      itemsPerPage: 1000,
    },
    payloadOptions: {},
    options: {
      current: 1,
      total: 0,
      itemsPerPage: 10,
    },
    errors: [],
    snackbar: false,
    branchesList: [],
    branch_id: "",

    responseStatusColor: "",
    response: "",
    buildingTypes: [],
    _id: null,
    isBackendRequestOpen: false,
  }),
  computed: {},
  mounted() {
    this.tableHeight = window.innerHeight - 270;
    window.addEventListener("resize", () => {
      this.tableHeight = window.innerHeight - 270;
    });
    this.getDataFromApi();
    this.getBuildingTypes();
  },
  created() {
    this._id = 4; //this.$route.params.id;
    this.loading = true;

    if (this.$auth.user.branch_id) {
      this.branch_id = this.$auth.user.branch_id;
      this.isCompany = false;
      return;
    }

    // let branch_header = [
    //   {
    //     text: "Branch",
    //     value: "branch",
    //   },
    // ];

    // this.headers.splice(2, 0, ...branch_header);

    // try {
    //   const { data } = this.$axios.get(`branches_list`, {
    //     params: {
    //       per_page: 100,
    //       company_id: this.$auth.user.company_id,
    //     },
    //   });
    //   this.branchesList = data;
    // } catch (error) {
    //   // Handle the error
    //   console.error("Error fetching branch list", error);
    // }
  },
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  methods: {
    getBuildingTypeName(id) {
      let filter = this.buildingTypes.filter(
        (buildingType) => buildingType.id == id
      );

      if (filter[0]) return filter[0].name;
      else return "---";
    },
    // updateStatus(item) {
    //   this.$axios
    //     .post("update-change-request/" + item.id, item)
    //     .then(({ data }) => {
    //       this.snackbar = true;
    //       this.responseStatusColor = "green";
    //       this.response = "Request has been updated succussfully.";
    //       setTimeout(() => {
    //         this.snackbar = false;
    //         this.response = "";
    //         this.responseStatusColor = "";
    //       }, 2000);
    //     })
    //     .catch(({ response }) => {
    //       if (!response) {
    //         return false;
    //       }
    //       let { status, data, statusText } = response;
    //       this.response = status == 422 ? data.message : statusText;
    //       this.responseStatusColor = "red";
    //     });
    // },
    // getRelatedColor(item) {
    //   let colors = {
    //     P: "purple",
    //     R: "red",
    //     A: "green",
    //   };
    //   return colors[item.status];
    // },
    // handleDatesFilter(dates) {
    //   if (dates.length > 1) {
    //     this.getDataFromApi(this.endpoint, "dates", dates);
    //   }
    // },

    // applyFilters(item, columnValue) {
    //   this.getDataFromApi(item, columnValue);
    //   this.from_menu_filter = false;
    //   this.to_menu_filter = false;
    // },
    // toggleFilter() {
    //   // this.filters = {};
    //   this.isFilter = !this.isFilter;
    // },
    // clearFilters() {
    //   this.filters = {};

    //   this.isFilter = false;
    //   this.getDataFromApi();
    // },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    getExpiryDatesCountColor(date) {
      const today = new Date();

      const targetDate = new Date(date);

      const diffTime = targetDate - today;

      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      if (diffDays < 0) {
        return "red";
      } else if (diffDays <= 30) {
        return "orange";
      }
    },
    closeCustomerDialog() {
      this.dialogViewCustomer = false;
    },
    getBuildingTypes() {
      if (this.$store.state.storeAlarmControlPanel?.BuildingTypes) {
        this.buildingTypes =
          this.$store.state.storeAlarmControlPanel.BuildingTypes;
      }
      // this.$axios
      //   .get(`building_types`, {
      //     params: {
      //       company_id: this.$auth.user.company_id,
      //     },
      //   })
      //   .then(({ data }) => {
      //     this.buildingTypes = data;
      //   });
    },
    viewItem(item) {
      this.viewCustomerId = item.id;
      this.key += 1;
      this.dialogViewCustomer = true;
    },
    viewItem2(item) {
      this.$router.push("/alarm/view-customer/" + item.id);
    },
    // getDate() {
    //   const date = new Date();
    //   const year = date.getFullYear();
    //   const month = (date.getMonth() + 1).toString().padStart(2, "0");
    //   const day = date.getDate().toString().padStart(2, "0");
    //   return `${year}-${month}-${day}`;
    // },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    // getDataFromApi(item = "", filter_value = "") {
    //   this.filters = {};
    //   if (
    //     filter_value != "" &&
    //     filter_value.length <= 2 &&
    //     item.field_type == "text"
    //   ) {
    //     this.snack = true;
    //     this.snackColor = "error";
    //     this.snackText = "Minimum 3 Characters to filter ";
    //     this.loading = false;
    //     return false;
    //   }
    //   this.getDataFromApi(this.endpoint, item.value, filter_value);
    // },
    getDataFromApi(url = "", filter_column = "", filter_value = "") {
      if (this.isBackendRequestOpen) return false;
      this.isBackendRequestOpen = true;

      url = this.endpoint;

      this.newCustomerDialog = false;
      const { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";

      this.payloadOptions = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          company_id: this.$auth.user.company_id,
          common_search: this.commonSearch,
          // branch_id: this.branch_id,
          ...this.payload,
        },
      };
      if (filter_column != "")
        this.payloadOptions.params[filter_column] = filter_value;
      this.loading = true;

      this.currentPage = page;
      this.perPage = itemsPerPage;
      try {
        this.$axios.get(url, this.payloadOptions).then(({ data }) => {
          this.isBackendRequestOpen = false;
          this.data = data.data;
          this.total = data.total;
          this.loading = false;
          this.totalRowsCount = data.total;
        });
      } catch (e) {
        console.log(e);
        this.loading = false;
      }
    },
  },
};
</script>
