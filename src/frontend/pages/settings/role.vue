<template>
  <NoAccess v-if="!$pagePermission.can('roles_view', this)" />
  <div v-else>
    <v-dialog v-model="dialogNewRole" width="800">
      <!-- <AssetsIconClose left="790" @click="closeDialog" /> -->

      <v-card
        ><v-card-title dark class="popup_background_noviolet">
          <span dense style="color: black3333">
            {{ formTitle }} {{ Model }} and Permissions</span
          >
          <v-spacer></v-spacer>
          <v-icon style="color: black3333" @click="closeDialog()" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <!-- <v-alert dense flat class="grey2222 lighten-3">
          {{ formTitle }} {{ Model }} and Permissions
          <v-spacer></v-spacer>
        </v-alert> -->
        <v-card-text>
          <v-row class="pt-2">
            <v-col cols="5">
              <v-text-field
                hide-details
                label="Role Name"
                outlined
                dense
                v-model="editedItem.name"
              ></v-text-field>
              <span v-if="errors && errors.name" class="error--text">
                {{ errors.name[0] }}</span
              >
            </v-col>
            <v-col cols="5">
              <v-text-field
                hide-details
                label="Role Description"
                outlined
                dense
                v-model="editedItem.description"
              ></v-text-field>
              <span v-if="errors && errors.description" class="error--text">
                {{ errors.description[0] }}</span
              >
            </v-col>
            <v-col cols="2" class="text-right">
              <v-btn
                color="primary"
                v-if="
                  $pagePermission.can('roles_edit', this) ||
                  $pagePermission.can('roles_create', this)
                "
                fill
                small
                @click="save"
                >Save</v-btn
              >
            </v-col>

            <v-col cols="12">
              <Permissions
                :menuList="menuList"
                :defaultPermissionsIds="
                  editedIndex === -1 ? [] : permission_pages
                "
                :permissions="permissions"
                @selectedPermissions="handleSelectedPermissions"
              />
            </v-col>
            <v-col cols="6" class="text-right">
              <span v-if="errors && errors.permission_pages" class="red--text">
                {{ errors.permission_pages[0] }}
              </span>
              <span v-if="errors && errors.name" class="error--text">
                {{ errors.name[0] }}</span
              >
              <span v-if="errors && errors.description" class="error--text">
                {{ errors.description[0] }}</span
              >

              Note: User Role permissions will reflect next login onwards
            </v-col>
            <v-col class="text-right">
              <v-btn color="primary" fill small @click="save">Save</v-btn>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>

    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-card class="mb-5 rounded-md" elevation="0">
      <v-toolbar class="rounded-md" dense flat>
        <span> Roles</span>
        <v-tooltip top color="primary">
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              dense
              class="ma-0 px-0"
              x-small
              :ripple="false"
              text
              v-bind="attrs"
              v-on="on"
              @click="getDataFromApi()"
            >
              <v-icon class="ml-2" dark>mdi mdi-reload</v-icon>
            </v-btn>
          </template>
          <span>Reload</span>
        </v-tooltip>
        <v-spacer></v-spacer>
        <v-toolbar-items
          ><v-col>
            <v-btn
              v-if="$pagePermission.can('roles_create', this)"
              dark
              dense
              color="blue"
              small
              @click="createDefaultRoles()"
            >
              <v-icon color="white" small> mdi-plus </v-icon> Default Roles
            </v-btn></v-col
          >
          <v-col>
            <v-btn
              v-if="$pagePermission.can('roles_create', this)"
              dark
              dense
              color="blue"
              small
              @click="dispalyNewDialog()"
            >
              <v-icon color="white" small> mdi-plus </v-icon>
              Role
            </v-btn>
          </v-col>
        </v-toolbar-items>
      </v-toolbar>

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
        <template v-slot:top> </template>

        <template v-slot:item.action="{ item }">
          <v-menu bottom left>
            <template v-slot:activator="{ on, attrs }">
              <v-btn dark-2 icon v-bind="attrs" v-on="on">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </template>
            <v-list dense>
              <v-list-item @click="editItem(item)" v-if="can('roles_edit')">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small class="mr-2">
                    mdi-pencil </v-icon
                  >Edit
                </v-list-item-title>
              </v-list-item>
              <v-list-item v-if="can('roles_delete')" @click="deleteItem(item)">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="error" small>
                    {{ item.role === "customer" ? "" : "mdi-delete" }} </v-icon
                  >Delete
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </template>
        <template v-slot:no-data>
          <!-- <v-btn color="background" @click="initialize">Reset</v-btn> -->
        </template>
      </v-data-table>
    </v-card>
  </div>
  <NoAccess v-else />
</template>
<script>
import Permissions from "../../components/Roles/Permissions.vue";

export default {
  components: { Permissions },
  data: () => ({
    compKey: 1,
    dialogNewRole: false,
    options: {},
    Model: "Role",
    endpoint: "role",
    search: "",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: false,
    total: 0,
    tableHeight: 500,
    headers: [
      {
        text: "Role",
        align: "left",
        sortable: true,
        key: "name",
        value: "name",
      },
      {
        text: "Description",
        align: "left",
        sortable: true,
        key: "description",
        value: "description",
      },
      {
        text: "Created",
        align: "left",
        sortable: true,
        key: "updated_at",
        value: "updated_at",
      },
      { text: "Actions", align: "center", value: "action", sortable: false },
    ],
    editedIndex: -1,
    editedItem: { name: "", description: "" },
    defaultItem: { name: "", description: "" },
    response: "",
    data: [],
    errors: [],

    permission_pages: [],
    permissions: [],
    formTitle: "New",
    editPermissionId: "",
    menuList: [],
  }),

  computed: {},
  mounted() {
    this.tableHeight = window.innerHeight - 200;
    window.addEventListener("resize", () => {
      this.tableHeight = window.innerHeight - 200;
    });
  },
  watch: {
    editedIndex(val) {
      this.formTitle = val === -1 ? "New" : "Edit";
    },
    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
    },
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  created() {
    this.loading = true;
    this.getPageRolesData();
    //permissions
    this.getPermissions();
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    closeDialog() {
      this.dialogNewRole = false;
      ++this.compKey;
    },
    handleSelectedPermissions(e) {
      this.permission_pages = e;
    },
    dispalyNewDialog() {
      this.errors = [];
      this.editedItem = { name: "", description: "" };
      this.editedIndex = -1;
      this.formTitle = "New";
      this.dialogNewRole = true;
      this.permission_pages = [];
    },

    getDataFromApi(url = this.endpoint) {
      this.loading = true;

      const { page, itemsPerPage } = this.options;

      let options = {
        params: {
          per_page: itemsPerPage,
          company_id: this.$auth.user.company.id,
          role_type: "employee",
        },
      };

      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.data = data.data;
        this.total = data.total;
        this.loading = false;
      });
    },
    getPageRolesData(url = this.endpoint) {
      this.loading = true;

      const { page, itemsPerPage } = this.options;

      let options = {
        params: {
          per_page: itemsPerPage,
          company_id: this.$auth.user.company.id,
        },
      };

      this.$axios.get(`get_page_roles_menu_data`, options).then(({ data }) => {
        this.menuList = data;
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
      this.errors = [];
      this.editedIndex = this.data.indexOf(item);
      this.editedItem = Object.assign({}, item);
      //this.dialog = true;
      this.dialogNewRole = true;

      this.loading = true;

      const { page, itemsPerPage } = this.options;

      let options = {
        params: {
          per_page: itemsPerPage,
          company_id: this.$auth.user.company.id,
          role_id: item.id,
        },
      };

      this.$axios.get("role_get_permission_pages", options).then(({ data }) => {
        this.loading = false;
        this.permission_pages = data;
      });
    },
    createDefaultRoles() {
      confirm("Are you sure you wish to Create Default Roles?") &&
        this.$axios
          .post(`create_default_roles`, {
            company_id: this.$auth.user.company.id,
          })
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;

              this.response = data.message;
            }

            this.getDataFromApi();
          })
          .catch((err) => console.log(err));
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
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
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
          .delete(this.endpoint + "/" + item.id)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.deletePermission();

              this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
            }
          })
          .catch((err) => console.log(err));
    },

    close() {
      this.dialog = false;
      this.dialogNewRole = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },
    save() {
      let payload = {
        name: this.editedItem.name,
        description: this.editedItem.description,

        company_id: this.$auth.user.company.id,
      };
      if (this.editedIndex > -1) {
        this.$axios
          .put(this.endpoint + "/" + this.editedItem.id, payload)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              if (this.editPermissionId == "") {
                this.savePermisions(this.editedItem.id);
              } else {
                this.updatePermission(this.editedItem.id);
              }

              this.getDataFromApi();
              // const index = this.data.findIndex(
              //   (item) => item.id == this.editedItem.id
              // );
              // this.data.splice(index, 1, {
              //   id: this.editedItem.id,
              //   name: this.editedItem.name,
              // });
              this.snackbar = data.status;
              this.response = data.message;
              this.dialogNewRole = false;
              this.close();
            }
          })
          .catch((err) => console.log(err));
      } else {
        this.$axios
          .post(this.endpoint, payload)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.savePermisions(data.record.id);
              this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
              this.close();
              this.errors = [];
              this.search = "";
            }
          })
          .catch((res) => console.log(res));
      }
    },
    save_old() {
      let payload = {
        name: this.editedItem.name,
        description: this.editedItem.description,

        company_id: this.$auth.user.company.id,
      };
      if (this.editedIndex > -1) {
        this.$axios
          .put(this.endpoint + "/" + this.editedItem.id, payload)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              if (this.editPermissionId == "") {
                this.savePermisions(this.editedItem.id);
              } else {
                this.updatePermission(this.editedItem.id);
              }

              this.getDataFromApi();
              // const index = this.data.findIndex(
              //   (item) => item.id == this.editedItem.id
              // );
              // this.data.splice(index, 1, {
              //   id: this.editedItem.id,
              //   name: this.editedItem.name,
              // });
              this.snackbar = data.status;
              this.response = data.message;
              this.dialogNewRole = false;
              this.close();
            }
          })
          .catch((err) => console.log(err));
      } else {
        this.$axios
          .post(this.endpoint, payload)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.savePermisions(data.record.id);
              this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
              this.close();
              this.errors = [];
              this.search = "";
            }
          })
          .catch((res) => console.log(res));
      }
    },
    //permissions
    deletePermission(id) {
      this.$axios
        .delete(this.endpoint + "/" + id)
        .then(({ data }) => {
          this.snackbar = data.status;
          this.response = data.message;
        })
        .catch((err) => console.log(err));
    },
    updatePermission(role_id) {
      //alert(this.editPermissionId);
      console.log(this.editPermissionId);
      let payload = {
        role_id: role_id,
        permission_pages: this.permission_pages,
      };
      this.$axios
        .put("assign-permission/" + this.editPermissionId, payload)
        .then(({ data }) => {
          if (!data.status) {
            this.errors = data.errors;
            return;
          }
          this.response = "Permissions has been assigned";
          this.snackbar = true;
          //setTimeout(() => this.$router.push("/assign_permission"), 2000);
        });
    },

    getPermissions(url = "dropDownList") {
      this.$axios
        .get(url)
        .then(({ data }) => {
          this.permissions = data.data;
        })
        .catch((err) => console.log(err));
    },
    capsTitle(val) {
      if (val == "gst") {
        val = val.toUpperCase();
        return val;
      }
      let res = val;
      let r = res.replace(/[^a-z]/g, " ");
      let title = r.replace(/\b\w/g, (c) => c.toUpperCase());
      return title;
    },

    savePermisions(role_id) {
      this.errors = [];
      let payload = {
        role_id: role_id, //this.role_id,
        permission_pages: this.permission_pages,
        company_id: this.$auth.user.company.id,
      };

      this.$axios
        .post("role_update_permission_pages", payload)
        .then(({ data }) => {
          if (!data.status) {
            this.errors = data.errors;
            return;
          }
          this.msg = "Permissions has been assigned";
          this.snackbar = true;
          //setTimeout(() => this.$router.push("/assign_permission"), 1000);
        });
    },
  },
};
</script>
