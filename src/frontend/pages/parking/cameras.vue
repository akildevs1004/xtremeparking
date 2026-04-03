<template>
  <NoAccess v-if="!can('cameras_view')" />
  <div v-else>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="DialogPreview" max-width="1200px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense>
            Live Preview - {{ selectedCamera ? selectedCamera.name : "" }}
          </span>
          <v-spacer></v-spacer>
          <v-icon @click="closePreview()" outlined>mdi mdi-close-circle</v-icon>
        </v-card-title>

        <v-card-text>

          <RtspLiveCameraPlayer v-if="selectedCamera" :key="'focus-' + selectedWsPort" class="nvr-focus-player"
            :title="selectedCamera.name" :wsPort="selectedWsPort" :wsHost="NODE_SERVER_IP" />
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- CREATE/EDIT/VIEW DIALOG -->
    <v-dialog v-model="newCameraDialog" max-width="800px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense> {{ editId ? "Update" : "New" }} Camera</span>
          <v-spacer></v-spacer>
          <v-icon @click="newCameraDialog = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <ParkingEditCamera :key="key" :editId="editId" :item="item" :editable="editable"
            @closeDialog="closeCameraDialog" />
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-card elevation="0" class="mt-0" :style="'height:' + (browserHeight - 20) + 'px'">
      <v-toolbar dense flat>
        <v-toolbar-title>
          <span> Cameras List </span>
        </v-toolbar-title>

        <v-btn title="Reload" dense class="ma-0 px-0" x-small :ripple="false" @click="getDataFromApi" text>
          <v-icon class="ml-2" dark>mdi mdi-reload</v-icon>
        </v-btn>

        <v-spacer></v-spacer>

        <div>
          <v-row>
            <v-col style="max-width:250px">
              <v-text-field height="20" @click:clear="commonSearch = ''; getDataFromApi()"
                class="employee-schedule-search-box" @input="getDataFromApi()" v-model="commonSearch"
                label="Search (min 3)" dense outlined type="text" append-icon="mdi-magnify" clearable hide-details />
            </v-col>

            <v-col style="max-width:40px">
              <v-btn v-if="can('cameras_create')" title="Add Camera" x-small :ripple="false" text @click="addItem()">
                <v-icon class="">mdi mdi-plus-circle</v-icon>
              </v-btn>
            </v-col>
          </v-row>
        </div>
      </v-toolbar>

      <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
        {{ snackText }}
        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
        </template>
      </v-snackbar>

      <v-data-table dense :headers="headers" :items="data" :loading="loading" :options.sync="options"
        :footer-props="{ itemsPerPageOptions: [10, 50, 100, 500, 1000] }" class="elevation-1"
        :server-items-length="totalRowsCount" fixed-header :height="tableHeight" :disable-sort="true">
        <template v-slot:item.sno="{ item }">
          {{
            currentPage
              ? (currentPage - 1) * perPage + (cumulativeIndex + data.indexOf(item))
              : ""
          }}
        </template>

        <template v-slot:item.rtsp_url="{ item }">
          <div style="max-width:520px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"
            :title="item.rtsp_url">
            {{ item.rtsp_url || "---" }}
          </div>
        </template>

        <template v-slot:item.node_server_ip="{ item }">
          {{ item.node_server_ip || "---" }}
        </template>

        <template v-slot:item.preview="{ item }">
          <v-btn x-small text color="primary" class="success" @click="openPreview(item)">
            <v-icon left small>mdi-play-circle</v-icon>
            Preview
          </v-btn>
        </template>



        <template v-slot:item.options="{ item }">
          <v-menu bottom left>
            <template v-slot:activator="{ on, attrs }">
              <v-btn dark-2 icon v-bind="attrs" v-on="on">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </template>
            <v-list width="140" dense>
              <v-list-item @click="viewItem(item)">
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

              <v-list-item @click="deleteItem(item)">
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
import ParkingEditCamera from "../../components/Parking/ParkingEditCamera.vue";
import RtspLiveCameraPlayer from "@/components/Parking/RtspLiveCameraPlayer.vue";


export default {
  components: { ParkingEditCamera, RtspLiveCameraPlayer },

  data: () => ({

    DialogPreview: false,
    selectedCamera: null,
    selectedWsPort: null,
    NODE_SERVER_IP: null,
    editId: null,
    item: null,
    editable: false,
    key: 1,

    commonSearch: "",
    perPage: 10,
    cumulativeIndex: 1,
    currentPage: 1,

    tableHeight: 750,
    browserHeight: 700,

    newCameraDialog: false,
    totalRowsCount: 0,

    snack: false,
    snackColor: "",
    snackText: "",

    endpoint: "cameraslist", // if your baseURL already includes /api
    payload: {},
    loading: true,

    data: [],
    headers: [
      { text: "#", value: "sno" },
      { text: "Name", value: "name" },
      { text: "Camera RTSP URL", value: "rtsp_url" },
      { text: "Streaming Server IP", value: "node_server_ip" },
      { text: "Preview", value: "preview" },



      { text: "Options", value: "options" },
    ],

    options: { page: 1, itemsPerPage: 10 },
    snackbar: false,
    response: "",
    isBackendRequestOpen: false,
  }),

  mounted() {
    this.tableHeight = window.innerHeight - 270;
    window.addEventListener("resize", () => {
      this.tableHeight = window.innerHeight - 270;
    });

    this.getDataFromApi();
  },

  created() {
    this.loading = true;
    try {
      if (window) this.browserHeight = window.innerHeight - 70;
    } catch (e) { }
  },

  watch: {
    DialogPreview(v) {
      if (!v) this.closePreview();
    },

    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },

  methods: {
    openPreview(item) {
      this.selectedCamera = item;

      // Host (your component expects NODE_SERVER_IP)
      // If each camera row has node_server_ip, use it:
      this.NODE_SERVER_IP = item.node_server_ip;

      // Port (choose ONE method)

      // Method A (best): store ws_port in DB and return it in API:
      // this.selectedWsPort = item.ws_port;

      // Method B (if your node uses base+id mapping):
      this.selectedWsPort = 9991 + Number(item.id || 0);

      this.DialogPreview = true;
    },

    closePreview() {
      this.DialogPreview = false;

      // reset so player disconnects
      this.selectedCamera = null;
      this.selectedWsPort = null;
      this.NODE_SERVER_IP = null;
    },

    can(per) {
      return this.$pagePermission.can(per, this);
    },

    closeCameraDialog() {
      this.newCameraDialog = false;
      this.getDataFromApi();
    },

    addItem() {
      this.editId = null;
      this.editable = true;
      this.key += 1;
      this.item = null;
      this.newCameraDialog = true;
    },

    viewItem(item) {
      this.editId = item.id;
      this.editable = false;
      this.key += 1;
      this.item = item;
      this.newCameraDialog = true;
    },

    editItem(item) {
      this.editable = true;
      this.editId = item.id;
      this.key += 1;
      this.item = item;
      this.newCameraDialog = true;
    },

    deleteItem(item) {
      if (confirm("Are you sure want to delete ?")) {
        this.loading = true;

        // IMPORTANT: same style as your members page
        this.$axios.delete(`${this.endpoint}/${item.id}`).then(({ data }) => {
          this.snackbar = true;
          this.response = "Camera Deleted Successfully";
          this.getDataFromApi();
          this.loading = false;
        }).catch((e) => {
          this.loading = false;
          this.snackbar = true;
          this.response = e?.response?.data?.message || "Delete failed";
        });
      }
    },

    getDataFromApi(url = "", filter_column = "", filter_value = "") {
      if (this.isBackendRequestOpen) return false;
      this.isBackendRequestOpen = true;

      url = this.endpoint;

      const { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";

      this.loading = true;
      this.currentPage = page;
      this.perPage = itemsPerPage;

      const payloadOptions = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          common_search: this.commonSearch,
          ...this.payload,
        },
      };

      if (filter_column != "") payloadOptions.params[filter_column] = filter_value;

      try {
        this.$axios.get(url, payloadOptions).then(({ data }) => {
          this.isBackendRequestOpen = false;
          this.data = data.data || [];
          this.totalRowsCount = data.total || 0;
          this.loading = false;
        }).catch((e) => {
          this.isBackendRequestOpen = false;
          this.loading = false;
          this.snackbar = true;
          this.response = e?.response?.data?.message || "Failed to load cameras";
        });
      } catch (e) {
        this.isBackendRequestOpen = false;
        this.loading = false;
      }
    },
  },
};
</script>
