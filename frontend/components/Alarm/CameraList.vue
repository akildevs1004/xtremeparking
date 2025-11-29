<template>
  <div>
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="dialogViewPhotos" width="60%">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span style="color: black111">
            {{ editItem ? editItem.title : "---" }}</span
          >
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="dialogViewPhotos = false"
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text>
          <v-container style="min-height: 100px">
            <v-img
              :src="editItem ? editItem.picture : '/no-business_profile.png'"
              aspect-ratio="1"
              class="grey222 lighten-2"
            >
              <template v-slot:placeholder>
                <v-row class="fill-height ma-0" align="center" justify="center">
                  <v-progress-circular
                    indeterminate
                    color="grey lighten-5"
                  ></v-progress-circular>
                </v-row>
              </template>
            </v-img>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogEditPhotos" width="600px">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span style="color: black111"> Camera </span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="
              closeDialog();
              dialogEditPhotos = false;
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text>
          <v-container style="min-height: 100px">
            <EditCustomerCamera
              :key="key"
              :customer_id="customer_id"
              @closeDialog="closeDialog"
              :editId="editId"
              :item="editItem"
              :building_cameras="building_cameras"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <div v-if="building_cameras.length == 0" class="text-center">
      No Camera Links Available
    </div>

    <v-row>
      <v-col>
        <!-- RTMP Server URL:<span class="bold">
          {{ cameraSelected ? cameraSelected.camera_url : "---" }}</span
        > -->
      </v-col>
      <v-col
        v-if="!isMapviewOnly && isEditable"
        class="text-right"
        style="max-width: 150px"
      >
        <v-btn
          :loading="loading"
          color="primary"
          @click="newPhoto()"
          dense
          x-small
        >
          + Camera
        </v-btn>
      </v-col>

      <v-col cols="12" style="padding-top: 0px; margin-top: 7px">
        <v-tabs v-model="tab" right show-arrows class="tabswidthalignment">
          <v-tabs-slider></v-tabs-slider>

          <v-tab
            v-for="(item, camindex) in building_cameras"
            :key="'camera' + item.id"
            :href="'#tab' + item.id"
            @click="selectCamera(item)"
            ><v-icon color="green" size="18">mdi-webcam</v-icon
            >{{ caps(item.title) }}</v-tab
          >
          <v-tabs-items v-model="tab">
            <v-tab-item
              v-for="(item, tabindex) in building_cameras"
              :key="'camera2' + item.id"
              :value="'tab' + item.id"
              ><v-row style="margin-top: -5px">
                <v-col cols="8">
                  <!-- <h3>{{ caps(item.title) }}</h3> -->
                </v-col>
                <v-col cols="4" class="text-right" style="margin-top: -10px"
                  ><v-menu bottom left v-if="!isMapviewOnly && isEditable">
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn
                        dark-2
                        icon
                        v-bind="attrs"
                        v-on="on"
                        style="
                          position: absolute;
                          color: black;
                          right: -7px;
                          z-index: 9999;
                        "
                      >
                        <v-icon>mdi-dots-vertical</v-icon>
                      </v-btn>
                    </template>
                    <v-list width="150" dense>
                      <!-- <v-list-item
                        v-if="can('device_notification_contnet_view')"
                        @click="viewPhoto(item)"
                      >
                        <v-list-item-title style="cursor: pointer">
                          <v-icon color="secondary" small> mdi-eye </v-icon>
                          View
                        </v-list-item-title>
                      </v-list-item> -->

                      <v-list-item
                        v-if="can('device_notification_contnet_view')"
                        @click="editPhoto(item)"
                      >
                        <v-list-item-title style="cursor: pointer">
                          <v-icon color="secondary" small> mdi-pencil </v-icon>
                          Edit
                        </v-list-item-title>
                      </v-list-item>

                      <v-list-item
                        v-if="can('device_notification_contnet_delete')"
                        @click="deletePhoto(item.id)"
                      >
                        <v-list-item-title style="cursor: pointer">
                          <v-icon color="error" small> mdi-delete </v-icon>
                          Delete
                        </v-list-item-title>
                      </v-list-item>
                    </v-list>
                  </v-menu></v-col
                >
              </v-row>

              <v-row>
                <v-col
                  cols="12"
                  class="text-center"
                  style="border: 1px solid #ddd"
                >
                  <iframe
                    v-if="getCameraUrl(item) != ''"
                    :src="getCameraUrl(item)"
                    width="100%"
                    style="border: 0; min-height: 510px; height: auto"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                  ></iframe>
                </v-col>
              </v-row>
            </v-tab-item>
          </v-tabs-items>
        </v-tabs>
      </v-col>

      <!-- <v-col
        v-for="(item, index) in building_cameras"
        :key="index"
        cols="4"
        class="mt-1"
        style="line-height: 35px; border-right: #ddd 0px solid"
      >
        <v-card class="elevation-1 pl-1">
          <v-row>
            <v-col cols="12" class="text-left">
              <v-row>
                <v-col cols="8"
                  ><h3>{{ caps(item.title) }}</h3></v-col
                >
                <v-col cols="4" class="text-right"
                  ><v-menu bottom left>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn dark-2 icon v-bind="attrs" v-on="on">
                        <v-icon>mdi-dots-vertical</v-icon>
                      </v-btn>
                    </template>
                    <v-list width="150" dense>
                      <v-list-item
                        v-if="can('device_notification_contnet_view')"
                        @click="viewPhoto(item)"
                      >
                        <v-list-item-title style="cursor: pointer">
                          <v-icon color="secondary" small> mdi-eye </v-icon>
                          View
                        </v-list-item-title>
                      </v-list-item>

                      <v-list-item
                        v-if="can('device_notification_contnet_view')"
                        @click="editPhoto(item)"
                      >
                        <v-list-item-title style="cursor: pointer">
                          <v-icon color="secondary" small> mdi-pencil </v-icon>
                          Edit
                        </v-list-item-title>
                      </v-list-item>

                      <v-list-item
                        v-if="can('device_notification_contnet_delete')"
                        @click="deletePhoto(item.id)"
                      >
                        <v-list-item-title style="cursor: pointer">
                          <v-icon color="error" small> mdi-delete </v-icon>
                          Delete
                        </v-list-item-title>
                      </v-list-item>
                    </v-list>
                  </v-menu></v-col
                >
              </v-row>
              <v-divider color="red" />
              <div>
                <v-img
                  @dblclick="viewPhoto(item)"
                  :src="
                    item.picture ? item.picture : '/no-business_profile.png'
                  "
                  aspect-ratio="1"
                  class="grey222 lighten-2"
                >
                  <template v-slot:placeholder>
                    <v-row
                      class="fill-height ma-0"
                      align="center"
                      justify="center"
                    >
                      <v-progress-circular
                        indeterminate
                        color="grey lighten-5"
                      ></v-progress-circular>
                    </v-row>
                  </template>
                </v-img>
              </div>
            </v-col>
          </v-row>
        </v-card>
      </v-col> -->
    </v-row>
  </div>
</template>

<script>
import EditCustomerCamera from "./EditCustomerCamera.vue";
import NewCustomerPhotopage from "../../components/Alarm/NewCustomer.vue";
export default {
  components: { EditCustomerCamera, NewCustomerPhotopage },
  props: ["customer_id", "customer", "isMapviewOnly", "isEditable"],
  data: () => ({
    cameraSelected: "",
    cameraID: "",
    cameraServerURL: "",
    tab: 0,
    editItem: null,
    key: 1,
    building_cameras: [],
    dialogEditPhotos: false,
    dialogGoogleMap: false,
    branchesList: [],
    startDateMenuOpen: "",
    endDateMenuOpen: "",
    preloader: false,
    loading: false,
    upload: {
      name: "",
    },
    start_date: "",
    end_date: "",
    editId: "",
    customer_payload: {
      branch_id: "",
      building_type_id: "",
      first_name: "",
      last_name: "",
      building_name: "",
      house_number: "",
      street_number: "",
      city: "",
      state: "",
      country: "",
      landmark: "",
      latitude: "",
      longitude: "",
      contact_number: "",
      start_date: "",
      end_date: "",
    },
    contact_payload: {
      name: "",
      number: "",
      position: "",
      whatsapp: "",
    },
    // location: "",
    geographic_payload: {
      location: "",
      lat: "",
      lon: "",
    },
    e1: 1,
    errors: [],
    previewImage: null,
    snackbar: false,
    response: "",
    dialogViewPhotos: false,
  }),
  created() {
    this.cameraServerURL = process.env.CAMERA_RTMP;
    this.preloader = false;
    this.getDatafromapi();
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    caps(str) {
      return str.replace(/\b\w/g, (c) => c.toUpperCase());
    },
    selectCamera(item) {
      this.cameraSelected = item;
    },
    getCameraUrl(item) {
      if (process) {
        return process?.env.CAMERA_RTMP_PLAYER + "?url=" + item.camera_url;
      } else {
        return "";
      }
    },
    newPhoto() {
      this.editId = "";

      this.editItem = null;
      this.key = this.key + 1;

      this.dialogEditPhotos = true;
    },
    viewPhoto(item) {
      this.editItem = item;
      this.dialogViewPhotos = true;
    },
    deletePhoto(id) {
      if (confirm("Are you sure  wish to delete ?")) {
        this.$axios
          .delete(`customers_building_cameras/${id}`)

          .then(({ data }) => {
            this.color = "background";
            this.errors = [];
            this.snackbar = true;
            this.response = "Camera Details are deleted successfully";

            this.$emit("closeDialog");
            this.getDatafromapi();
            //this.branch_id = this.$auth.user.branch_id || "";
          });
      }
    },
    editPhoto(item) {
      this.key = this.key + 1;
      this.editId = item.id;
      this.editItem = item;
      this.dialogEditPhotos = true;
    },
    getDatafromapi() {
      this.loading = true;
      this.$axios
        .get(`customers_building_cameras`, {
          params: {
            company_id: this.$auth.user.company_id,
            customer_id: this.customer_id,
          },
        })
        .then(({ data }) => {
          this.building_cameras = data.data;
          if (this.building_cameras[0]) {
            this.cameraID = this.building_cameras[0].id;
            this.tab = this.building_cameras[0].id;

            this.cameraSelected = this.building_cameras[0];
          }
          this.loading = false;
        });
    },
    closeDialog() {
      //this.key = this.key + 1;
      //this.$emit("closeDialog");
      this.dialogEditPhotos = false;
      this.getDatafromapi();
      // location.reload();
      //  this.dialogEditPhotos = false;
    },
  },
};
</script>
