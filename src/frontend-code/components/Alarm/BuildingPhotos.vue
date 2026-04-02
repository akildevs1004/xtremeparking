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
            <EditCustomerBuildingPhotos
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
    <!-- <div v-if="!loading && building_photos.length == 0" class="text-center">
      No Photos Available
    </div> -->

    <v-row>
      <v-col
        ><!-- Email: {{ customer ? customer.user?.email : "---" }}  --></v-col
      >
      <v-col
        v-if="!isMapviewOnly && isEditable"
        class="text-center"
        style="max-width: 150px"
      >
        <v-btn
          :loading="loading"
          color="primary"
          @click="newPhoto()"
          dense
          x-small
        >
          + Photo
        </v-btn>
      </v-col>

      <v-col cols="12" style="padding-top: 0px; margin-top: 7px">
        <v-tabs v-model="tab" right show-arrows class="tabswidthalignment">
          <v-tabs-slider></v-tabs-slider>

          <v-tab href="#tabAddress"
            ><v-icon size="18" color="red">mdi-map-marker</v-icon>Address</v-tab
          >
          <v-tab
            v-for="(item, index) in building_photos"
            :key="'photo' + item.id"
            :href="'#tab' + item.id"
            ><v-icon size="18" color="yellow">mdi-image</v-icon
            >{{ caps(item.title) }}</v-tab
          >
          <v-tabs-items v-model="tab">
            <v-tab-item
              v-for="(item, index) in building_photos"
              :key="'photo2' + item.id"
              :value="'tab' + item.id"
              ><v-row>
                <v-col cols="12" class="text-left">
                  <v-row>
                    <v-col cols="8">
                      <!-- <h3>{{ caps(item.title) }}</h3> -->
                    </v-col>
                    <v-col cols="4" class="text-right"
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
                              <v-icon color="secondary" small>
                                mdi-pencil
                              </v-icon>
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
                    <v-col>
                      <img
                        width="100%"
                        max-width="100%"
                        height="500px"
                        :src="item.picture || '/no-business_profile.png'"
                        contain
                        style="object-fit: contain; max-height: 500px"
                      />
                    </v-col>
                  </v-row>
                </v-col>
              </v-row>
            </v-tab-item>
            <v-tab-item value="tabAddress">
              <NewCustomerPhotopage
                name="NewCustomerPhotopage"
                id="NewCustomerPhotopage"
                key="NewCustomerPhotopage"
                v-if="customer"
                :customer_id="customer_id"
                :customer="customer"
                @closeDialog="closeDialog"
                :isMapviewOnly="isMapviewOnly"
                :isEditable="isEditable"
            /></v-tab-item>
          </v-tabs-items>
        </v-tabs>
      </v-col>

      <!-- <v-col
        v-for="(item, index) in building_photos"
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
import EditCustomerBuildingPhotos from "./EditCustomerBuildingPhotos.vue";
import NewCustomerPhotopage from "../../components/Alarm/NewCustomer.vue";
export default {
  components: { EditCustomerBuildingPhotos, NewCustomerPhotopage },
  props: ["customer_id", "customer", "isMapviewOnly", "isEditable"],
  data: () => ({
    tab: 0,
    editItem: null,
    key: 1,
    building_photos: [],
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
          .delete(`customers_building_photos/${id}`)

          .then(({ data }) => {
            this.color = "background";
            this.errors = [];
            this.snackbar = true;
            this.response = "Photo Details are deleted successfully";

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
        .get(`customers_building_photos`, {
          params: {
            company_id: this.$auth.user.company_id,
            customer_id: this.customer_id,
          },
        })
        .then(({ data }) => {
          this.building_photos = data.data;
          if (this.building_photos[0]) this.tab = this.building_photos[0].id;
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
