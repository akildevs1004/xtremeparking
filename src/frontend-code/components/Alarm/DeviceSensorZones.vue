<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-dialog v-model="dialogNewZone" max-width="400px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense style="color: black3333">
            {{ editId ? "Update" : "New" }} Sensor</span
          >
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="dialogNewZone = false"
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text v-if="editDevice">
          <EditDeviceZone
            :key="key"
            :editId="editId"
            :item="item"
            :editable="editable"
            :deviceId="device_id"
            :device="editDevice"
            @closeDialog="closeSecurityDialog"
          />
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-row>
      <v-col class="mt-0 pt-0">
        <v-card elevation="0" class="mt-0">
          <v-toolbar class="mb-0 background" dense flat>
            <v-toolbar-title> <span> Sensors List </span></v-toolbar-title>
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
            <span style="width: 180px">
              <!-- <v-text-field
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
            > -->
            </span>
            <!-- <span style="color: black3333"
              >
              Max Sensors Allowed :
              {{
                invoicePackageData?.device_product_service?.sensor_count ||
                "---"
              }}</span
            > -->
            <!-- {{ invoicePackageData?.customerTotalSensors || 0 }}
            {{ invoicePackageData?.device_product_service?.sensor_count || 0 }}
            {{ invoicePackageData }}   -->

            <div style="color: red" v-if="!invoicePackageData && !loading">
              Customer Invoice package is not avaialbe.
            </div>
            <div v-else-if="isEditable">
              <v-btn
                v-if="
                  invoicePackageData &&
                  invoicePackageData.device_product_service &&
                  invoicePackageData.customerTotalSensors <
                    invoicePackageData.device_product_service.sensor_count
                "
                title="Change Request"
                x-small
                :ripple="false"
                text
                @click="addItem()"
              >
                <v-icon class="">mdi mdi-plus-circle</v-icon>
              </v-btn>
              <div v-else-if="!loading" style="color: red">
                Max Sensor count Reached or Package is not Active
                <v-icon class="" color="red">mdi mdi-plus-circle</v-icon>
              </div>
            </div>
            <div v-else>No</div>
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
              v-slot:item.first_name="{ item, index }"
              style="width: 300px"
            >
              <v-row no-gutters>
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
                      border-radius: 50%;
                      height: 50px;
                      width: 50px;
                      max-width: 50px;
                    "
                    :src="
                      item.picture ? item.picture : '/no-business_profile.png'
                    "
                  >
                  </v-img>
                </v-col>
                <v-col style="padding: 10px">
                  <div style="font-size: 13px">
                    {{
                      item.first_name
                        ? item.first_name + " " + item.last_name
                        : ""
                    }}
                  </div>
                </v-col>
              </v-row>
            </template>
            <!-- <template v-slot:item.customers="{ item }">
              {{ item.customers_assigned?.length || "0" }}
            </template>
            <template v-slot:item.contact_number="{ item }">
              {{ item.contact_number }}
            </template> -->
            <template v-slot:item.sensor_type="{ item }">
              {{ item.sensor_type || "---" }}
            </template>
            <!-- <template v-slot:item.email="{ item }">
              {{ item.user?.email || "---" }} </template
            > -->
            <template v-slot:item.area_code="{ item }">
              {{
                item.area_code == ""
                  ? "Default"
                  : getAreaName(item.area_code) ?? "Default"
              }}
            </template>
            <!-- <template v-slot:item.hours24="{ item }">
              <img
                v-if="item.hours24 == 1"
                title="Active"
                style="width: 30px"
                src="/on.png"
              />
              <img
                v-else-if="item.hours24 == 0"
                title="Inactive"
                style="width: 30px"
                src="/off.png"
              />
            </template> -->
            <template v-slot:item.options="{ item }">
              <v-menu bottom left>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn dark-2 icon v-bind="attrs" v-on="on">
                    <v-icon>mdi-dots-vertical</v-icon>
                  </v-btn>
                </template>
                <v-list width="120" dense>
                  <v-list-item @click="editItem(item)" v-if="isEditable">
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="secondary" small> mdi-pencil </v-icon>
                      Edit
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item v-if="isEditable" @click="deleteItem(item)">
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
import EditDeviceZone from "../../components/Alarm/EditDeviceZone.vue";
import AlarmCustomerView from "../../components/Alarm/ViewCustomer.vue";
import SecurityCustomersList from "../../components/Alarm/SecurityCustomersList.vue";

export default {
  props: ["employeeId", "editDevice", "isEditable", "invoicePackageData"],
  components: {
    EditDeviceZone,
    AlarmCustomerView,
    SecurityCustomersList,
  },
  data: () => ({
    dialogSecurityCustomers: false,
    editId: null,
    item: null,
    editable: false,
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
    device_id: null,
    dialogNewZone: false,
    dialogViewCustomer: false,
    totalRowsCount: 0,

    snack: false,
    snackColor: "",
    snackText: "",
    departments: [],
    Model: "Log",
    security_id: null,
    endpoint: "security",
    payload: {},
    loading: false,
    data: [],
    headers: [
      {
        text: "#",
        value: "sno",
      },
      {
        text: "Zone Number",
        value: "zone_code",
      },
      {
        text: "Zone Name",
        value: "location",
      },
      {
        text: "Zone Type",
        value: "sensor_name",
      },
      {
        text: "Sensor Type",
        value: "sensor_type",
      },
      {
        text: "Area",
        value: "area_code",
      },
      {
        text: "Wired/Wireless",
        value: "wired",
      },
      // {
      //   text: "24 Hours",
      //   value: "hours24",
      // },
      {
        text: "Options",
        value: "options",
      },
    ],
    areaList: [
      { id: "01", name: "Area 1" },
      { id: "02", name: "Area 2" },
      { id: "03", name: "Area 3" },
      { id: "04", name: "Area 4" },
      { id: "05", name: "Area 5" },
      { id: "06", name: "Area 6" },
      { id: "07", name: "Area 7" },
      { id: "08", name: "Area 8" },

      { id: "09", name: "Area 9" },
      { id: "10", name: "Area 10" },
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
    options: { perPage: 10 },
    errors: [],
    snackbar: false,
    branchesList: [],
    branch_id: "",

    responseStatusColor: "",
    response: "",
    buildingTypes: [],
    _id: null,
    perPage: 10,
    isBackendRequestOpen: false,
  }),
  computed: {},
  mounted() {
    this.tableHeight = window.innerHeight - 270;
    window.addEventListener("resize", () => {
      this.tableHeight = window.innerHeight - 270;
    });

    this.getBuildingTypes();
    this.getDataFromApi();
  },
  created() {
    console.log("invoicePackageData", this.invoicePackageData);

    this._id = 4; //this.$route.params.id;

    if (this.$auth.user.branch_id) {
      this.branch_id = this.$auth.user.branch_id;
      this.isCompany = false;
      return;
    }
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
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    getAreaName(area_code) {
      return (
        this.areaList.find((areaName) => areaName.id == area_code)?.name ||
        "---"
      );
    },
    viewCustomers(item) {
      this.security_id = item.id;
      this.item = item;
      this.key += 1;
      this.dialogSecurityCustomers = true;
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
    closeSecurityDialog() {
      this.dialogNewZone = false;
      this.dialogSecurityCustomers = false;
      this.getDataFromApi();
    },
    getBuildingTypes() {
      if (this.$store.state.storeAlarmControlPanel?.BuildingTypes) {
        this.buildingTypes =
          this.$store.state.storeAlarmControlPanel.BuildingTypes;
      }
    },
    addItem() {
      this.editId = null;
      this.editable = true;
      this.key += 1;
      this.item = null;
      this.device_id = this.editDevice.id;
      this.viewCustomerId = null;
      this.dialogNewZone = true;
    },
    viewItem(item) {
      this.editId = item.id;
      this.editable = false;
      this.viewCustomerId = item.id;
      this.key += 1;
      this.item = item;
      this.dialogNewZone = true;
    },
    // viewItem2(item) {
    //   this.$router.push("/alarm/view-customer/" + item.id);
    // },
    editItem(item) {
      console.log("item", item);

      this.editId = item.id;
      this.key += 1;
      this.item = item;

      this.device_id = this.editDevice.id;
      this.dialogNewZone = true;
      this.editable = true;
    },

    deleteItem(item) {
      if (confirm("Are you sure want to delete  ?")) {
        this.loading = true;
        let options = {
          params: {
            company_id: this.$auth.user.company_id,
            id: item.id,
          },
        };

        this.$axios.delete(`delete_device_zone`, options).then(({ data }) => {
          this.snackbar = true;
          this.response = "Sensor Deleted Successfully";

          this.loading = false;
          this.getDataFromApi();
        });
      }
    },

    can(per) {
      return this.$pagePermission.can(per, this);
    },

    getDataFromApi(url = "", filter_column = "", filter_value = "") {
      if (this.loading) return false;

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
          deviceId: this.editDevice.id,
        },
      };
      if (filter_column != "")
        this.payloadOptions.params[filter_column] = filter_value;
      this.loading = true;

      // this.currentPage = page;
      // this.perPage = itemsPerPage;
      this.currentPage = page ?? 1;
      this.perPage = itemsPerPage ?? 10;

      try {
        this.$axios
          .get("get_device_zones", this.payloadOptions)
          .then(({ data }) => {
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
