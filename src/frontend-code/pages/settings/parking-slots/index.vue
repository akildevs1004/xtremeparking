<template>
  <NoAccess v-if="!can('operators_view')" />
  <div v-else>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-dialog v-model="newProductDialog" max-width="300px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense> {{ editId ? "Update" : "New" }} Parking Slot</span>
          <v-spacer></v-spacer>
          <v-icon @click="newProductDialog = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <v-row>
            <v-col md="12" sm="12" cols="12" dense>
              <v-card class="elevation-0 p-2" style="padding: 5px">
                <v-row>
                  <v-col cols="12">
                    <v-row class="pt-0">
                      <v-col cols="12" dense>
                        <v-text-field
                          label="Floor Number"
                          dense
                          small
                          outlined
                          type="text"
                          v-model="payload.floor_no"
                          hide-details
                          :readonly="!editable"
                          :filled="!editable"
                        ></v-text-field>
                        <span
                          v-if="primary_errors && primary_errors.floor_no"
                          class="text-danger mt-2"
                          >{{ primary_errors.floor_no[0] }}</span
                        >
                      </v-col>
                      <v-col v-if="!editId" cols="12" dense>
                        <v-text-field
                          label="Start Number"
                          dense
                          small
                          outlined
                          type="number"
                          v-model="payload.start_number"
                          hide-details
                          :readonly="!editable"
                          :filled="!editable"
                        ></v-text-field>
                        <span
                          v-if="primary_errors && primary_errors.start_number"
                          class="text-danger mt-2"
                          >{{ primary_errors.start_number[0] }}</span
                        >
                      </v-col>
                      <v-col v-if="!editId" cols="12" dense>
                        <v-text-field
                          label="End Number"
                          dense
                          small
                          outlined
                          type="number"
                          v-model="payload.end_number"
                          hide-details
                          :readonly="!editable"
                          :filled="!editable"
                        ></v-text-field>
                        <span
                          v-if="primary_errors && primary_errors.end_number"
                          class="text-danger mt-2"
                          >{{ primary_errors.end_number[0] }}</span
                        >
                      </v-col>

                      <v-col v-if="editId" cols="12" dense>
                        <v-text-field
                          label="Slot Number"
                          dense
                          small
                          outlined
                          type="number"
                          v-model="payload.slot_number"
                          hide-details
                          :readonly="!editable"
                          :filled="!editable"
                        ></v-text-field>
                        <span
                          v-if="primary_errors && primary_errors.slot_number"
                          class="text-danger mt-2"
                          >{{ primary_errors.slot_number[0] }}</span
                        >
                      </v-col>
                    </v-row>
                  </v-col>
                </v-row>

                <v-row>
                  <v-col cols="12" class="text-right">
                    <v-btn
                      v-if="editable"
                      small
                      :loading="loading"
                      color="primary"
                      @click="submit"
                    >
                      Submit
                    </v-btn></v-col
                  >
                </v-row>
              </v-card>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-card elevation="0" class="mt-0">
      <v-toolbar class="mb-2" dense flat>
        <v-toolbar-title> <span> Parking Slots</span></v-toolbar-title>
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
        <span style="width: 180px; margin-right: 8px">
          <v-select
            class="employee-schedule-search-box"
            style="
              padding-top: 7px;
              z-index: 999;
              min-width: 100%;
              width: 200px;
            "
            height="25px"
            outlined
            v-model="filters.floor_no"
            dense
            :items="floorList"
            item-text="name"
            item-value="id"
            hide-details
            @change="getDataFromApi"
          ></v-select>
        </span>
        <span style="width: 180px"
          ><v-text-field
            style="padding-top: 7px"
            height="20"
            class="employee-schedule-search-box"
            @input="onSearchInput"
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
          v-if="can('operators_create')"
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

        <!-- default rendering for floor_no and slot_number with modal-based edit -->
        <template v-slot:item.floor_no="{ item }">
          <span>{{ item.floor_no }}</span>
        </template>

        <template v-slot:item.slot_number="{ item }">
          <span>{{ item.slot_number }}</span>
        </template>

        <template v-slot:item.options="{ item }">
          <v-menu bottom left>
            <template v-slot:activator="{ on, attrs }">
              <v-btn dark-2 icon v-bind="attrs" v-on="on">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </template>
            <v-list width="120" dense>
              <v-list-item v-if="can('operators_view')" @click="viewItem(item)">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-eye </v-icon>
                  View
                </v-list-item-title>
              </v-list-item>

              <v-list-item @click="editItem(item)" v-if="can('operators_edit')">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-pencil </v-icon>
                  Edit
                </v-list-item-title>
              </v-list-item>
              <v-list-item
                v-if="can('operators_delete')"
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
  </div>
</template>

<script>
export default {
  data: () => ({
    dialogSecurityCustomers: false,
    editId: null,
    item: null,
    editable: false,
    key: 1,
    viewCustomerId: null,
    commonSearch: "",
    searchTimeout: null,
    perPage: 10,
    cumulativeIndex: 1,
    currentPage: 1,
    branchesList: [],
    floorList: [],
    isCompany: true,
    tableHeight: 750,
    id: "",

    newProductDialog: false,
    dialogViewCustomer: false,
    totalRowsCount: 0,

    snack: false,
    snackColor: "",
    snackText: "",
    departments: [],
    primary_errors: [],
    Model: "Log",
    security_id: null,
    endpoint: "parking-slots",

    filters: {
      floor_no: null,
      status: "",
    },
    payload: {
      floor_no: "",
      start_number: 0,
      end_number: 0,
    },
    loading: true,
    browserHeight: 700,

    data: [],
    headers: [
      {
        text: "#",
        value: "sno",
      },
      {
        text: "Floor No",
        value: "floor_no",
      },

      {
        text: "Slot Number",
        value: "slot_number",
      },
      // {
      //   text: "Status",
      //   value: "status",
      // },
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
    this.tableHeight = window.innerHeight - 230;
    window.addEventListener("resize", () => {
      this.tableHeight = window.innerHeight - 230;
    });

    this.getDataFromApi();

    this.loadFloorList();
  },
  created() {
    this._id = 4; //this.$route.params.id;
    this.loading = true;

    if (this.$auth.user.branch_id) {
      this.branch_id = this.$auth.user.branch_id;
      this.isCompany = false;
      return;
    }
    try {
      if (window) this.browserHeight = window.innerHeight - 70;
    } catch (e) {}
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
    loadFloorList() {
      this.$axios
        .get("parking-slots-floors", {
          params: {
            company_id: this.$auth.user.company_id,
          },
        })
        .then(({ data }) => {
          this.floorList = [{ id: null, name: "All Floors" }, ...data];
        })
        .catch((e) => {
          console.log("Floor load error", e);
        });
    },
    onSearchInput() {
      clearTimeout(this.searchTimeout);

      this.searchTimeout = setTimeout(() => {
        // optional min 3 characters, but allow empty search also
        if (this.commonSearch.length >= 3 || this.commonSearch.length === 0) {
          this.options.page = 1;
          this.getDataFromApi();
        }
      }, 500); // 500ms debounce
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

    closeProductDialog() {
      this.newProductDialog = false;
      this.dialogSecurityCustomers = false;
      this.getDataFromApi();
      this.loadFloorList();
    },

    addItem() {
      this.editId = null;
      this.editable = true;
      this.key += 1;
      this.item = null;
      this.viewCustomerId = null;
      this.payload = {
        floor_no: "",
        start_number: 0,
        end_number: 0,
      };
      this.newProductDialog = true;
    },
    viewItem(item) {
      this.editId = item.id;
      this.editable = false;
      this.viewCustomerId = item.id;
      this.key += 1;
      this.item = item;
      this.newProductDialog = true;
    },
    // viewItem2(item) {
    //   this.$router.push("/alarm/view-customer/" + item.id);
    // },
    editItem(item) {
      this.editable = true;
      this.editId = item.id;
      this.key += 1;
      this.item = item;

      this.payload = {
        floor_no: item.floor_no || "",
        slot_number: item.slot_number || 0,
      };
      this.newProductDialog = true;
    },

    getDataFromApi(filter_column = "", filter_value = "") {
      if (this.isBackendRequestOpen) return false;
      this.isBackendRequestOpen = true;

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
          ...this.filters,
        },
      };
      if (filter_column != "")
        this.payloadOptions.params[filter_column] = filter_value;
      this.loading = true;

      this.currentPage = page;
      this.perPage = itemsPerPage;
      try {
        this.$axios.get(this.endpoint, this.payloadOptions).then(({ data }) => {
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
    submit() {
      let payload;
      let method;
      let url;

      if (this.editId) {
        // Update uses slot_number in backend
        payload = {
          floor_no: this.payload.floor_no,
          slot_number: this.payload.slot_number,
        };
        method = "put";
        url = `/parking-slots/${this.editId}`;
      } else {
        // Store remains unchanged
        payload = {
          floor_no: this.payload.floor_no,
          start_number: this.payload.start_number,
          end_number: this.payload.end_number,
        };
        method = "post";
        url = "parking-slots";
      }

      this.$axios[method](url, payload)
        .then(({ data }) => {
          if (!data.status) {
            this.primary_errors = [];
            if (data.errors) this.primary_errors = data.errors;
            this.color = "red";

            this.snackbar = true;
            this.response = data.message;
          } else {
            this.color = "background";
            this.primary_errors = [];
            this.snackbar = true;
            this.response = data.message;
            this.closeProductDialog();
          }
        })
        .catch((e) => {
          if (e.response && e.response.data.errors) {
            this.primary_errors = e.response.data.errors;
            this.color = "red";

            this.snackbar = true;
            this.response = e.response.data.message;
          }
        });
    },
    deleteItem(item) {
      if (confirm("Are you sure want to delete  ?")) {
        this.loading = true;
        this.$axios.delete(`/parking-slots/${item.id}`).then(({ data }) => {
          this.snackbar = true;
          this.response = "Product Service Deleted Successfully";
          this.getDataFromApi();
          this.loading = false;
        });
      }
    },
  },
};
</script>

<style scoped>
/deep/ input[type="number"] {
  -webkit-appearance: none;
  -moz-appearance: textfield;
}
/deep/ input[type="number"]::-webkit-outer-spin-button,
/deep/ input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>
