<template>
  <div v-if="can(`change_request`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-dialog v-model="newSecurityDialog" max-width="700px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense> {{ editId ? "Update" : "New" }} Operator Account</span>
          <v-spacer></v-spacer>
          <v-icon @click="newSecurityDialog = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <AlarmNewSecurity
            :key="key"
            :editId="editId"
            :item="item"
            :editable="editable"
            @closeDialog="closeSecurityDialog"
          />
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-row>
      <v-col>
        <v-card elevation="0" class="mt-2">
          <v-toolbar class="mb-2 white--text" color="white" dense flat>
            <v-toolbar-title>
              <span style="color: black111"> Operators</span></v-toolbar-title
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
              @click="addItem()"
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
                      border-radius: 10%;
                      height: auto;
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

            <template v-slot:item.contact_number="{ item }">
              {{ item.contact_number }}
            </template>
            <template v-slot:item.email="{ item }">
              {{ item.user?.email || "---" }}
            </template>
            <template v-slot:item.status="{ item }">
              <img
                v-if="item.user?.web_login_access == 1"
                title="Active"
                style="width: 60px"
                src="/on.png"
              />
              <img
                v-else-if="item.user?.web_login_access == 0"
                title="Inactive"
                style="width: 60px"
                src="/off.png"
              />
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
                    v-if="can('device_notification_contnet_view')"
                    @click="viewItem(item)"
                  >
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="secondary" small> mdi-eye </v-icon>
                      View
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="editItem(item)">
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="secondary" small> mdi-pencil </v-icon>
                      Edit
                    </v-list-item-title>
                  </v-list-item>
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
  <NoAccess v-else />
</template>

<script>
import AlarmNewSecurity from "../../components/Alarm/EditSecurity.vue";
import AlarmCustomerView from "../../components/Alarm/ViewCustomer.vue";

export default {
  layout: "technician",
  components: {
    AlarmNewSecurity,
    AlarmCustomerView,
  },
  data: () => ({
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

    newSecurityDialog: false,
    dialogViewCustomer: false,
    totalRowsCount: 0,

    snack: false,
    snackColor: "",
    snackText: "",
    departments: [],
    Model: "Log",
    endpoint: "security",
    payload: {},
    loading: true,
    data: [],
    headers: [
      {
        text: "#",
        value: "sno",
      },
      {
        text: "Name",
        value: "first_name",
      },
      {
        text: "Contact Number",
        value: "contact_number",
      },
      {
        text: "Email",
        value: "email",
      },

      {
        text: "Status",
        value: "status",
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
    options: { perPage: 10 },
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

    this.getBuildingTypes();
    this.getDataFromApi();
  },
  created() {
    this._id = 4; //this.$route.params.id;
    this.loading = true;

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
      this.newSecurityDialog = false;
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
      this.viewCustomerId = null;
      this.newSecurityDialog = true;
    },
    viewItem(item) {
      this.editId = item.id;
      this.editable = false;
      this.viewCustomerId = item.id;
      this.key += 1;
      this.item = item;
      this.newSecurityDialog = true;
    },
    // viewItem2(item) {
    //   this.$router.push("/alarm/view-customer/" + item.id);
    // },
    editItem(item) {
      this.editable = true;
      this.editId = item.id;
      this.key += 1;
      this.item = item;
      this.newSecurityDialog = true;
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

        this.$axios.delete(`security/${item.id}`).then(({ data }) => {
          this.snackbar = true;
          this.response = "Security Deleted Successfully";
          this.getDataFromApi();
          this.loading = false;
        });
      }
    },

    can(per) {
      return this.$pagePermission.can(per, this);
    },

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
