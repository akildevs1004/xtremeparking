<template>
  <div>
    <div class="text-center ma-0">
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
          <span style="color: black111"> Photo </span>
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
            <AlarmEditPhotos
              :key="key"
              :customer_id="customer_id"
              @closeDialog="closeDialog"
              :editId="editId"
              :item="editItem"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <div
      v-if="building_pictures.length == 0"
      class="text-center"
      style="height: 50px"
    >
      No Photos Available
    </div>
    <v-row>
      <v-col cols="12" class="text-right" v-if="isEditable">
        <v-btn
          v-if="!isMapviewOnly"
          :loading="loading"
          color="primary"
          @click="resetAllSensors()"
          dense
          x-small
        >
          Clear/Reset Plotting
        </v-btn>
        <v-btn
          v-if="!isMapviewOnly"
          :loading="loading"
          color="primary"
          @click="newPhoto()"
          dense
          x-small
        >
          + Photo
        </v-btn>
      </v-col>

      <v-col cols="12" style="margin-top: -10px">
        <v-tabs
          :key="tabkey"
          right
          class="customerEmergencyContactTabs"
          show-arrows
        >
          <v-tab
            @click="reloadTab()"
            v-for="(item, index) in building_pictures"
            :key="'sensor' + item.id"
            >{{ caps(item.title) }}</v-tab
          >

          <v-tab-item
            style="border: 1px solid #443f6f; padding: 25px"
            v-for="(item, index) in building_pictures"
            :key="'sensorpicture' + item.id"
          >
            <v-row style="padding: 0px">
              <v-col cols="12" style="padding: 0px">
                <CompSensorPlotting
                  :name="'sensorPlotting' + item.id"
                  :key="keytabitem"
                  :item="item"
                />
                <!-- <v-img
                  @dblclick="viewPhoto(item)"
                  :src="
                    item.picture ? item.picture : '/no-business_profile.png'
                  "
                  aspect-ratio="1"
                  class="grey222 lighten-2"
                  style="max-width: 100%; margin: auto; height: 500px"
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
                </v-img> -->
              </v-col>
            </v-row>
            <v-row
              style="position: absolute; right: -9px; top: -30px"
              v-if="isEditable"
            >
              <v-col cols="12" class="text-right"
                ><v-menu v-if="!isMapviewOnly" bottom left>
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

                    <v-list-item @click="editPhoto(item)">
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-pencil </v-icon>
                        Edit
                      </v-list-item-title>
                    </v-list-item>

                    <!-- <v-list-item>
                      <v-list-item-title style="cursor: pointer">
                        <AlarmSensorPlotting :key="key" :item="item" />
                      </v-list-item-title>
                    </v-list-item> -->

                    <v-list-item
                      v-if="isEditable"
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
          </v-tab-item>
        </v-tabs>
      </v-col>

      <!-- <v-col
        v-for="(item, index) in building_pictures"
        :key="'photo3' + index"
        cols="4"
        class="mt-1"
        style="line-height: 35px; border-right: #ddd 0px solid"
      >
        <v-card class="elevation-1 pl-1">
          <v-row>
            <v-col cols="12" class="text-left">
              <v-row>
                <v-col cols="10"
                  ><h3>{{ caps(item.title) }}</h3></v-col
                >
                <v-col cols="2" class="text-right"
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
                        v-if="can('device_notification_contnet_view')"
                      >
                        <v-list-item-title style="cursor: pointer">
                          <AlarmSensorPlotting :key="key" :item="item" />
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

              <v-img
                @dblclick="viewPhoto(item)"
                :src="item.picture ? item.picture : '/no-business_profile.png'"
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
            </v-col>
          </v-row>
        </v-card>
      </v-col> -->
    </v-row>
  </div>
</template>

<script>
import AlarmEditPhotos from "../../components/Alarm/EditPhotos.vue";
import CompSensorPlotting from "./CompSensorPlotting.vue";

export default {
  components: { AlarmEditPhotos, CompSensorPlotting },
  props: ["customer_id", "isMapviewOnly", "isEditable"],
  data: () => ({
    keytabitem: 1,
    tabkey: 1,
    editItem: null,
    key: 1,
    building_pictures: [],
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
    reloadTab() {
      this.keytabitem++;
    },
    async resetAllSensors() {
      if (confirm("Are you sure you want to reset")) {
        this.loading = true;
        let config = {
          params: {
            company_id: this.$auth.user.company_id,

            customer_building_picture_id: this.building_pictures.map(
              (item) => item.id
            ),
          },
        };
        let { data } = await this.$axios.post(
          `reset_plotting_all`,
          config.params
        );
        this.getDatafromapi();
        this.tabkey++;
        this.loading = false;
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
          .delete(`customers_building_picture/${id}`)

          .then(({ data }) => {
            this.color = "background";
            this.errors = [];
            this.snackbar = true;
            this.response = "Photo Details are deleted successfully";
            this.getDatafromapi();
            this.$emit("closeDialog");
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
        .get(`customers_building_picture`, {
          params: {
            company_id: this.$auth.user.company_id,
            customer_id: this.customer_id,
          },
        })
        .then(({ data }) => {
          this.building_pictures = data.data;
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
