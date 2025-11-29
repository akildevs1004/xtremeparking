<template>
  <div v-if="can(`designation_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <div>
      <v-dialog
        persistent
        v-model="dialogFormSubdesignation"
        :fullscreen="false"
        width="300px"
      >
        <v-card>
          <v-card-title dense class="popup_background_noviolet">
            <span style="color: black111">New Sensor Type</span>
            <v-spacer></v-spacer>
            <v-icon
              style="color: black3333"
              @click="dialogFormSubdesignation = false"
              outlined
            >
              mdi mdi-close-circle
            </v-icon>
          </v-card-title>
          <v-card-text>
            <v-container>
              <v-row class="mt-2">
                <v-col cols="12">
                  <v-text-field
                    hide-details
                    v-model="New_sub_DesignationName"
                    placeholder="Sensor Name"
                    outlined
                    dense
                  ></v-text-field>
                  <span v-if="errors && errors.name" class="error--text">{{
                    errors.name[0]
                  }}</span>
                </v-col>
              </v-row>
            </v-container>
            <v-card-actions style="width: 100%">
              <v-spacer></v-spacer>
              <div class="text-right">
                <v-btn
                  style="float: right"
                  small
                  class="primary"
                  @click="saveSubDesignation"
                  >Save
                </v-btn>
              </div>
            </v-card-actions>
          </v-card-text>
        </v-card>
      </v-dialog>
      <v-dialog
        persistent
        v-model="dialogForm"
        :fullscreen="false"
        width="350px"
      >
        <v-card>
          <v-card-title dark class="popup_background_noviolet">
            <span style="color: black111">{{ formTitle }} Sensor Type</span>
            <v-spacer></v-spacer>
            <v-icon
              style="color: black3333"
              @click="dialogForm = false"
              outlined
            >
              mdi mdi-close-circle
            </v-icon>
          </v-card-title>
          <v-card-text>
            <v-container>
              <v-row class="">
                <v-col md="12" sm="12" cols="12" small dense class="pt-5">
                  <v-text-field
                    label="Name"
                    dense
                    outlined
                    :hide-details="!errors.name"
                    type="text"
                    v-model="editedItem.name"
                    :error="errors.name"
                    :error-messages="
                      errors && errors.name ? errors.name[0] : ''
                    "
                    placeholder="Name"
                  ></v-text-field>
                </v-col>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-col md="12" sm="12" cols="12" class="pa-0 text-right">
                    <!-- <v-btn small dark class="background" @click="close">
                          Cancel
                        </v-btn> -->
                    <v-btn small class="primary" @click="save">Save</v-btn>
                  </v-col>
                </v-card-actions>
              </v-row>
            </v-container>
          </v-card-text>
        </v-card>
      </v-dialog>
      <v-row>
        <v-col md="12" lg="12">
          <!-- <Back color="primary" /> -->

          <v-card class="mb-5 mt-2 rounded-md" elevation="0">
            <v-toolbar class="rounded-md" dense flat>
              <v-toolbar-title><span>Sensor Types List</span></v-toolbar-title>
              <!--
                <v-tooltip top color="primary">
                  <template v-slot:activator="{ on, attrs }"> -->
              <v-btn
                dense
                class="ma-0 px-0"
                x-small
                :ripple="false"
                text
                title="Reload"
                @click="clearFilters()"
              >
                <v-icon class="ml-2" dark>mdi mdi-reload</v-icon>
              </v-btn>
              <!-- </template>
                  <span>Reload</span>
                </v-tooltip> -->

              <!-- <v-tooltip top color="primary">
                  <template v-slot:activator="{ on, attrs }"> -->
              <!-- <v-btn
                x-small
                :ripple="false"
                text
                title="Filter"
                @click="toggleFilter"
              >
                <v-icon dark white>mdi-filter</v-icon>
              </v-btn> -->
              <!-- </template>
                  <span>Filter</span>
                </v-tooltip> -->

              <v-spacer></v-spacer>
              &nbsp;
              <v-btn
                text
                v-if="can(`sub_designation_create`)"
                @click="newSubDesignation"
                x-small
              >
                <v-icon class="">mdi mdi-plus-circle</v-icon>
              </v-btn>
            </v-toolbar>
            <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
              {{ snackText }}

              <template v-slot:action="{ attrs }">
                <v-btn v-bind="attrs" text @click="snack = false">
                  Close
                </v-btn>
              </template>
            </v-snackbar>
            <v-data-table
              dense
              :headers="headers_table"
              :items="data"
              model-value="data.id"
              :loading="loading"
              :options.sync="options"
              :footer-props="{
                itemsPerPageOptions: [10, 50, 100, 500, 1000],
              }"
              class="elevation-0"
              :server-items-length="totalRowsCount"
            >
              <template v-slot:item.options="{ item }">
                <v-menu bottom left>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn dark-2 icon v-bind="attrs" v-on="on">
                      <v-icon>mdi-dots-vertical</v-icon>
                    </v-btn>
                  </template>
                  <v-list width="120" dense>
                    <v-list-item
                      v-if="can(`designation_edit`)"
                      @click="editItem(item)"
                    >
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-pencil </v-icon>
                        Edit
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      v-if="can(`designation_delete`)"
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
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  layout: "master",
  data: () => ({
    show1: false,
    dialogFormDesignation: false,
    showFilters: false,
    options: {},
    filters: {},
    isFilter: false,
    generateLogsDialog: false,
    totalRowsCount: 0,
    new_Designation_name: "",
    designations: [],

    New_sub_DesignationName: "",
    Newdesignation_id: "",
    dialogFormSubdesignation: false,
    datatable_search_textbox: "",
    filter_employeeid: "",
    snack: false,
    snackColor: "",
    snackText: "",
    dialogForm: false,
    pagination: {
      current: 1,
      total: 0,
      per_page: 10,
    },
    Model: "Designations",
    options: {},
    endpoint: "alarm_sensor_types",
    search: "",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: false,
    total: 0,

    editedIndex: -1,
    editedItem: {
      name: "",
      email: "",
      password: "",
      password_confirmation: "",
    },
    defaultItem: {
      name: "",
      email: "",
      password: "",
      password_confirmation: "",
    },
    response: "",
    data: [],
    errors: [],
    headers_table: [
      {
        text: "Name",
        align: "left",
        sortable: false,
        value: "name",
      },
      { text: "Options", align: "left", sortable: false, value: "options" },
    ],
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New" : "Edit";
    },
  },

  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },

    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
    },
  },

  created() {
    this.loading = true;
    this.getDataFromApi();
  },

  methods: {
    datatable_cancel() {
      this.datatable_search_textbox = "";
    },
    datatable_open() {
      this.datatable_search_textbox = "";
    },
    datatable_close() {
      this.loading = false;
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    newItem() {
      this.dialogForm = true;
    },
    newSubDesignation() {
      this.dialogFormSubdesignation = true;
    },
    newDesignation() {
      this.dialogFormDesignation = true;
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    onPageChange() {
      this.getDataFromApi();
    },
    applyFilters() {
      this.getDataFromApi();
      this.from_menu_filter = false;
      this.to_menu_filter = false;
    },
    toggleFilter() {
      // this.filters = {};
      this.isFilter = !this.isFilter;
    },
    clearFilters() {
      this.filters = {};

      this.isFilter = false;
      this.getDataFromApi();
    },
    getDataFromApi(url = this.endpoint, filter_column = "", filter_value = "") {
      if (url == "") url = this.endpoint;
      this.loading = true;
      this.loading = true;

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
          //designation_ids: this.$auth.user.assignedDesignations,

          ...this.filters,
        },
      };
      if (filter_column != "") {
        this.payloadOptions.params[filter_column] = filter_value;
      }
      this.$axios.get(url, this.payloadOptions).then(({ data }) => {
        if (filter_column != "" && data.data.length == 0) {
          this.snack = true;
          this.snackColor = "error";
          this.snackText = "No Results Found";
          this.loading = false;
          return false;
        }
        this.data = data.data;
        this.designations = data.data;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;
        this.loading = false;
        this.totalRowsCount = data.total;
      });
    },
    searchIt(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${e}`);
      }
    },

    editItem(item) {
      this.editedIndex = this.data.indexOf(item);
      this.editedItem = Object.assign({}, item);
      //this.dialog = true;
      this.dialogForm = true;
    },
    gotoSubdesignations(item) {
      this.$router.push(`/sub-designation?id=${item.id}`);
    },
    delteteSelectedRecords() {
      let just_ids = this.ids.map((e) => e.id);
      confirm(
        "Are you sure you wish to delete selected records , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .post(`${this.endpoint}/delete/selected`, {
            ids: just_ids,
          })
          .then((res) => {
            if (!res.data.status) {
              this.errors = res.data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = res.data.status;
              this.ids = [];
              this.response = "Selected records has been deleted";
            }
          })
          .catch((err) => console.log(err));
    },

    deleteItem(item) {
      confirm(
        "Are you sure you wish to delete , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .delete("delete_sensor_type?id=" + item.id)
          .then(({ data }) => {
            const index = this.data.indexOf(item);
            this.data.splice(index, 1);
            this.snackbar = data.status;
            this.response = data.message;
          })
          .catch((err) => console.log(err));
    },

    close() {
      //this.dialog = false;
      this.dialogForm = false;
      this.dialogFormSubdesignation = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },
    saveSubDesignation() {
      let payload = {
        name: this.New_sub_DesignationName,

        company_id: this.$auth.user.company_id,
      };

      this.$axios
        .post("create_sensor_type", payload)
        .then(({ data }) => {
          if (!data.status) {
            if (data.errors) this.errors = data.errors;

            this.snackbar = true;
            this.response = data.message;
          } else {
            this.getDataFromApi();
            this.snackbar = data.status;
            this.response = data.message;
            this.close();
            this.errors = [];
            this.search = "";
            this.New_sub_DesignationName = "";
            this.Newdesignation_id = "";
            his.dialogFormSubdesignation = false;
          }
        })
        .catch((res) => console.log(res));
    },
    save() {
      let payload = {
        name: this.editedItem.name,
        id: this.editedItem.id,
      };
      //   if (this.editedIndex > -1) {
      //     this.$axios
      //       .put(this.endpoint + "/" + this.editedItem.id, payload)
      //       .then(({ data }) => {
      //         if (!data.status) {
      //           this.errors = data.errors;
      //         } else {
      //           this.getDataFromApi();
      //           this.snackbar = data.status;
      //           this.response = data.message;
      //           this.close();
      //           this.dialogForm = false;
      //         }
      //       })
      //       .catch((err) => console.log(err));
      //   } else {
      this.$axios
        .post("update_sensor_type", payload)
        .then(({ data }) => {
          this.errors = [];
          if (!data.status) {
            if (data.errors) this.errors = data.errors;

            this.snackbar = true;
            this.response = data.message;
          } else {
            this.getDataFromApi();
            this.snackbar = data.status;
            this.response = data.message;
            this.close();
            this.errors = [];
            this.search = "";
            this.dialogForm = false;
          }
        })
        .catch((res) => console.log(res));
      //  }
    },
  },
};
</script>
