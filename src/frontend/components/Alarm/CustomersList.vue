<template>
  <div v-if="can(`customers_view`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog
      v-model="dialogViewCustomer2"
      fullscreen
      scrollable
      style="overflow: visible"
    >
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense>View Customer Information</span>
          <v-spacer></v-spacer>
          <v-icon
            @click="
              dialogViewCustomer2 = false;
              closeCustomerDialog();
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text
          style="padding-left: 10px; background-color: #fff; margin-top: 10px"
        >
          <AlarmCustomerTabsView2
            :key="key"
            @closeCustomerDialog="closeCustomerDialog"
            :_id="viewCustomerId"
            :selectedCustomer="selectedCustomer"
            :isPopup="true"
            :isEditable="false"
        /></v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog
      v-model="dialogViewCustomer"
      width="1250px"
      style="overflow: visible"
    >
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense>View Customer Information</span>
          <v-spacer></v-spacer>
          <v-icon
            @click="
              dialogViewCustomer = false;
              closeCustomerDialog();
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text
          style="padding-left: 10px; margin-top: 10px"
          class="background"
        >
          <AlarmCustomerTabsView
            :key="key"
            @closeCustomerDialog="closeCustomerDialog"
            :_id="viewCustomerId"
            :selectedCustomer="selectedCustomer"
            :isPopup="true"
            :isEditable="false"
        /></v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog
      v-model="dialogEditCustomer"
      width="1250px"
      style="overflow: visible"
    >
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense>Update Customer Information</span>
          <v-spacer></v-spacer>
          <v-icon
            @click="
              dialogEditCustomer = false;
              closeCustomerDialog();
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text class="background mt-3"
          ><AlarmCustomerTabsView
            :key="key"
            @closeCustomerDialog="closeCustomerDialog"
            :_id="viewCustomerId"
            :selectedCustomer="selectedCustomer"
            :isPopup="true"
            :isEditable="true"
        /></v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="newCustomerDialog" max-width="900px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense> New Customer</span>
          <v-spacer></v-spacer>
          <v-icon @click="newCustomerDialog = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <AlarmNewCustomer
            name="AlarmNewCustomerCustomersList"
            :key="key"
            @closeDialog="getDataFromApi()"
            :isEditable="true"
          />
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog
      v-model="dialogAssignSecurity"
      width="300px"
      style="overflow: visible"
    >
      <v-card :key="key">
        <v-card-title dark class="popup_background_noviolet">
          <span dense style="color: black3333"> Assign Operator</span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="
              dialogAssignSecurity = false;
              closeCustomerDialog();
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <v-select
            v-model="security_id"
            label="Select Operator"
            height="20"
            class="employee-schedule-search-box11111111 pt-8"
            outlined
            dense
            :items="securityList"
            item-text="full_name"
            item-value="id"
            clearable
            @change="showCustomerOTPbox()"
          >
          </v-select>

          <v-text-field
            v-if="showCustomerOTP"
            label="Customer Secret Code"
            dense
            small
            outlined
            type="number"
            v-model="customerContactPIN"
            hide-details
          ></v-text-field>
          <div style="color: red">{{ error_messages }}</div>
          <v-col cols="12" class="text-center pt-5"
            ><v-btn
              dense
              class="primary"
              @click="updateCustomerSecurityId()"
              small
              >Submit</v-btn
            ></v-col
          >
        </v-card-text>
      </v-card>
    </v-dialog>

    <div elevation="0" v-if="graphs === true">
      <CompCustomersDashboardStatistics
        name="CompCustomersDashboardStatistics1"
        :key="222"
        style="max-width: 99%"
        :setIntervalLoopstatus="setIntervalLoopstatus"
      />
    </div>

    <v-card elevation="0" class="mt-0">
      <v-toolbar v-if="!eventFilter" class="mb-2" dense flat>
        <v-toolbar-title> <span style=""> Customers</span></v-toolbar-title>

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

        <v-spacer></v-spacer>
        <span style="padding-top: 26px; padding-right: 14px; max-width: 250px">
          <v-select
            v-model="filterSecuritymapped"
            label="Operators"
            height="20"
            class="employee-schedule-search-box"
            style="padding-top: 7px"
            outlined
            dense
            :items="[{ full_name: 'All', id: '' }, ...securityList]"
            item-text="full_name"
            item-value="id"
            @change="getDataFromApi()"
            clearable
          >
          </v-select>
        </span>
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
          v-if="!eventFilter && can(`customers_create`)"
          title="New Customer"
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
                  height: 45px;
                  min-height: 45px;
                  width: 45px;
                  max-width: 45px;
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
              <small style="font-size: 12px">
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
          <small title="Start Date " style="font-size: 12px">
            {{ $dateFormat.format_date_month_name_year(item.start_date) }}
          </small>
        </template>

        <template v-slot:item.building_type="{ item }">
          <div>
            {{ getBuildingTypeName(item.building_type_id) }}
          </div>
          <!-- <small style="font-size: 12px; color: #6c7184">
                {{ item.landmark }}
              </small> -->
        </template>

        <template v-slot:item.security="{ item }">
          <div v-if="item.mappedsecurity">
            {{ item.mappedsecurity.security_info.first_name }}
            {{ item.mappedsecurity.security_info.last_name }}
          </div>
          <div v-else>---</div>
        </template>

        <template v-slot:item.burglary="{ item }">
          <div
            v-if="$dateFormat.verifyDeviceSensorName('Intruder', item.devices)"
          >
            <img
              title="Burglary"
              style="width: 30px; float: left"
              src="/device-icons/burglary.png"
            />
          </div>
          <div v-else>---</div>
        </template>
        <template v-slot:item.temperature="{ item }">
          <div
            v-if="
              $dateFormat.verifyDeviceSensorName('Temperature', item.devices)
            "
          >
            <img
              title="Temperature"
              style="width: 30px; float: left"
              src="/device-icons/temperature.png"
            />
          </div>
          <div v-else>---</div>
        </template>
        <template v-slot:item.medical="{ item }">
          <div
            v-if="$dateFormat.verifyDeviceSensorName('Medical', item.devices)"
          >
            <img
              title="Medical"
              style="width: 30px; float: left"
              src="/device-icons/medical.png"
            />
          </div>
          <div v-else>---</div>
        </template>
        <template v-slot:item.fire="{ item }">
          <div v-if="$dateFormat.verifyDeviceSensorName('Fire', item.devices)">
            <img
              title="Fire"
              style="width: 30px; float: left"
              src="/device-icons/fire.png"
            />
          </div>
          <div v-else>---</div>
        </template>
        <template v-slot:item.water="{ item }">
          <div v-if="$dateFormat.verifyDeviceSensorName('Water', item.devices)">
            <img
              title="Water"
              style="width: 30px; float: left"
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
              <v-list-item
                v-if="can('customers_view')"
                @click="viewCustomerItem(item)"
              >
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-eye </v-icon>
                  View
                </v-list-item-title>
              </v-list-item>
              <v-list-item
                v-if="can('customers_edit')"
                @click="editCustomerItem(item)"
              >
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-pencil </v-icon>
                  Edit
                </v-list-item-title>
              </v-list-item>
              <!-- <v-list-item v-if="can('customers_view')" @click="viewItem(item)">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-eye </v-icon>
                  Popup
                </v-list-item-title> </v-list-item
              ><v-list-item
                v-if="can('customers_edit')"
                @click="editItem(item)"
              >
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-pencil </v-icon>
                  Edit
                </v-list-item-title>
              </v-list-item> -->
              <!-- <v-list-item
                    v-if="can('device_notification_contnet_view')"
                    @click="viewItem2(item)"
                  >
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="secondary" small> mdi-eye </v-icon>
                      View
                    </v-list-item-title>
                  </v-list-item> -->
              <v-list-item
                v-if="can('customers_edit')"
                @click="changeSecurity(item)"
              >
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small>mdi-account-tie </v-icon>
                  Operator
                </v-list-item-title>
              </v-list-item>
              <v-list-item
                v-if="can('customers_delete')"
                @click="deleteCustomer(item)"
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
  </div>
  <NoAccess v-else />
</template>

<script>
import AlarmNewCustomer from "../../components/Alarm/NewCustomer.vue";
import AlarmCustomerView from "../../components/Alarm/ViewCustomer.vue";
import AlarmCustomerTabsView from "../../components/Alarm/AlarmCustomerTabsView.vue";
import AlarmCustomerTabsView2 from "../../components/Alarm/AlarmCustomerTabsView2.vue";

import CompCustomersDashboardStatistics from "./CompCustomersDashboardStatistics.vue";
export default {
  props: ["eventFilter", "graphs"],
  components: {
    AlarmNewCustomer,
    AlarmCustomerView,
    CompCustomersDashboardStatistics,
    AlarmCustomerTabsView2,
  },
  data: () => ({
    dialogViewCustomer2: false,
    browserHeight: 900,
    setIntervalLoopstatus: true,
    error_messages: "",
    showCustomerOTP: false,
    customerContactPIN: "",
    security_id: null,
    dialogAssignSecurity: false,
    filterSecuritymapped: "",
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
    dialogEditCustomer: false,

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
        text: "Customer",
        value: "building_name",
      },
      {
        text: "Customer Type",
        value: "building_type",
      },
      {
        text: "Operator",
        value: "security",
      },
      // {
      //   text: "Type",
      //   value: "building_type",
      // },

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
    securityList: [],
    selectedCustomer: null,
  }),
  computed: {},
  async mounted() {
    this.tableHeight = window.innerHeight - 320;
    window.addEventListener("resize", () => {
      this.tableHeight = window.innerHeight - 320;
    });
    this.getDataFromApi();
  },
  async created() {
    try {
      if (window) this.browserHeight = window.innerHeight;
    } catch (e) {}
    this.loading = true;

    this.getSecurityList();
    this.getBuildingTypes();

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
    isDisplayGraphs() {
      if (this.graphs) {
        return this.graphs;
      } else return true;
    },
    getSecurityList() {
      let options = { params: { company_id: this.$auth.user.company_id } };
      this.$axios.get("security-dropdownlist", options).then(({ data }) => {
        this.securityList = data;
      });
    },
    getBuildingTypeName(id) {
      let filter = this.buildingTypes.filter(
        (buildingType) => buildingType.id == id
      );

      if (filter[0]) return filter[0].name;
      else return "---";
    },

    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

    showCustomerOTPbox() {
      this.error_messages = "";
      if (this.security_id == 1) {
        this.showCustomerOTP = true;
      } else {
        this.showCustomerOTP = false;
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
      this.dialogEditCustomer = false;
      this.setIntervalLoopstatus = true;
      this.getDataFromApi();
    },
    getBuildingTypes() {
      // if (this.$store.state.storeAlarmControlPanel?.BuildingTypes) {
      //   this.buildingTypes =
      //     this.$store.state.storeAlarmControlPanel.BuildingTypes;
      // }

      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      this.$axios.get("building_types", options).then((data) => {
        this.buildingTypes = data.data;
      });
    },
    editItem(item) {
      this.setIntervalLoopstatus = false;
      this.viewCustomerId = item.id;
      this.key += 1;
      this.selectedCustomer = item;
      this.dialogEditCustomer = true;
    },
    viewItem(item) {
      this.selectedCustomer = item;
      this.setIntervalLoopstatus = false;
      this.viewCustomerId = item.id;
      this.key += 1;
      this.dialogViewCustomer = true;
    },

    editCustomerItem(item) {
      this.$router.push("/alarm/editcustomer/" + item.id);
    },
    viewCustomerItem(item) {
      this.$router.push("/alarm/customer/" + item.id);
      // this.selectedCustomer = item;
      // this.setIntervalLoopstatus = false;
      // this.viewCustomerId = item.id;
      // this.key += 1;
      // this.dialogViewCustomer2 = true;
    },
    viewItem2(item) {
      this.$router.push("/alarm/view-customer/" + item.id);
    },
    deleteCustomer(item) {
      if (confirm("Are you want to Delete Customer?"))
        if (
          confirm(
            "This will permanently delete the customer’s devices and account. Proceed? "
          )
        ) {
          let options = {
            params: {
              company_id: this.$auth.user.company_id,
              customer_name: item.building_name,
              customer_id: item.id,
            },
          };

          this.$axios.post("delete_customer", options.params).then((data) => {
            this.snackbar = true;
            this.response = "Customer Details are Deleted permanently";
            this.getDataFromApi();
          });
        }
    },
    updateCustomerSecurityId() {
      if (this.showCustomerOTP) {
        this.error_messages = "";
        if (this.customerContactPIN == "") {
          this.error_messages = "Customer Verification is required";
          return false;
        } else {
          if (
            this.selectedCustomer.primary_contact.alarm_stop_pin ==
              this.customerContactPIN ||
            this.selectedCustomer.secondary_contact.alarm_stop_pin ==
              this.customerContactPIN
          ) {
            this.error_messages = "";
          } else {
            this.error_messages = "Secret Code is not a valid";

            return false;
          }
        }
      }

      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          security_id: this.security_id,
          customer_id: this.selectedCustomer.id,
        },
      };

      this.$axios
        .post("security_customers_single_update", options.params)
        .then((data) => {
          this.getDataFromApi();
          this.dialogAssignSecurity = false;
          this.snackbar = true;
          this.response = "Customer Details are updated successfully";
        });
    },
    changeSecurity(customer) {
      this.security_id = null;
      if (customer.mappedsecurity)
        this.security_id = customer.mappedsecurity.security_id;
      this.selectedCustomer = customer;
      this.customerContactPIN = "";
      this.error_messages = "";
      this.key += 1;
      this.dialogAssignSecurity = true;
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    getDataFromApi(url = "", filter_column = "", filter_value = "") {
      //if (this.isBackendRequestOpen) return false;
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
          eventFilter: this.eventFilter,
          filterSecuritymapped: this.filterSecuritymapped,
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
