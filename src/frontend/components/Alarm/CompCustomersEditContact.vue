<template>
  <div>
    <div class="text-center ma-2">
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
              :src="
                editItem ? editItem.profile_picture : '/no-business_profile.png'
              "
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

    <v-dialog v-model="showMap" width="800px">
      <v-card>
        <v-card-title
          style="color: black3333"
          dense
          class="popup_background_noviolet"
        >
          <span style="">Move Marker to change Location </span>
          <v-spacer></v-spacer>
          <v-icon @click="showMap = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text style="padding-left: 0px; padding-right: 0px">
          <CompGoogleMapSelectLocation
            v-if="showMap"
            @triggerAddress="updateMapAddressDetails"
            @closePopup="showMap = false"
            :customer_latitude="payload_primary?.latitude"
            :customer_longitude="payload_primary?.longitude"
            :contact_id="1"
        /></v-card-text>
      </v-card>
    </v-dialog>
    <v-row>
      <v-col cols="12">
        <v-card class="elevation-0 p-2" style="padding: 5px">
          <v-row class="pt-0">
            <v-col cols="4" dense>
              <div class="text-center mt-0" style="height: 220px">
                <v-img
                  style="
                    height: auto;
                    min-height: 100px;
                    width: 150px;
                    border-radius: 50%;
                    margin: 0 auto;
                  "
                  @dblclick="viewPhoto(payload_primary)"
                  :src="primary_previewImage || '/no-business_profile.png'"
                ></v-img>
                <v-btn
                  v-if="!isMapviewOnly && isEditable"
                  class="mt-2"
                  style="width: 50%"
                  small
                  @click="onpick_primary_attachment"
                  >{{ !primary_previewImage ? "Upload" : "Change" }}
                  <v-icon right dark color="primary">mdi-cloud-upload</v-icon>
                </v-btn>

                <input
                  required
                  type="file"
                  @change="primary_attachment"
                  style="display: none"
                  accept="image/*"
                  ref="primary_attachment_input"
                />

                <span
                  v-if="primary_errors && primary_errors.logo"
                  class="text-danger mt-2"
                  >{{ primary_errors.logo[0] }}</span
                >
              </div>

              <div>
                <CompGoogleMapLatLan
                  :latitude="payload_primary.latitude"
                  :longitude="payload_primary.longitude"
                  :title="
                    payload_primary.first_name + ' ' + payload_primary.last_name
                  "
                  :contact_id="payload_primary.id"
                  :key="mapkey"
                  mapheight="200px"
                />
                <br />
                <div class="text-center">
                  <v-btn
                    v-if="isEditable || isMapviewOnly"
                    dense
                    small
                    color="primary"
                    @click="changeAddresss()"
                    ><v-icon>mdi-map-marker-radius</v-icon>Update Address Using
                    Map</v-btn
                  >
                </div>
              </div>
            </v-col>
            <v-col cols="8"
              ><v-row
                v-if="
                  (contact_id &&
                    payload_primary.address_type != 'primary' &&
                    payload_primary.address_type != 'secondary') ||
                  !contact_id
                "
                ><v-col cols="6" dense>
                  <v-combobox
                    label="Contact  Type"
                    :items="addressTypes"
                    v-model="payload_primary.address_type"
                    dense
                    outlined
                    hide-details
                    small
                    :disabled="!isEditable"
                    :readonly="isMapviewOnly"
                  ></v-combobox
                ></v-col>
                <v-col cols="6" dense></v-col>
              </v-row>
              <v-row>
                <v-col cols="6" dense>
                  <v-text-field
                    :readonly="isMapviewOnly"
                    label="First Name"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="payload_primary.first_name"
                    hide-details
                    :disabled="!isEditable"
                  ></v-text-field>
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    :readonly="isMapviewOnly"
                    label="Last Name"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="payload_primary.last_name"
                    hide-details
                    :disabled="!isEditable"
                  ></v-text-field>
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    :readonly="isMapviewOnly"
                    label="Phone 1"
                    dense
                    maxlength="15"
                    small
                    outlined
                    type="text"
                    v-model="payload_primary.phone1"
                    hide-details
                    :disabled="!isEditable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.phone1"
                    class="text-danger mt-2"
                    >{{ primary_errors.phone1[0] }}</span
                  >
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    :readonly="isMapviewOnly"
                    label="Phone 2"
                    dense
                    maxlength="15"
                    small
                    outlined
                    type="text"
                    v-model="payload_primary.phone2"
                    hide-details
                    :disabled="!isEditable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.phone2"
                    class="text-danger mt-2"
                    >{{ primary_errors.phone2[0] }}</span
                  >
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    :readonly="isMapviewOnly"
                    label="Office Phone"
                    dense
                    maxlength="15"
                    small
                    outlined
                    type="text"
                    v-model="payload_primary.office_phone"
                    hide-details
                    :disabled="!isEditable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.office_phone"
                    class="text-danger mt-2"
                    >{{ primary_errors.office_phone[0] }}</span
                  >
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    :readonly="isMapviewOnly"
                    label="Email"
                    dense
                    small
                    outlined
                    type="email"
                    v-model="payload_primary.email"
                    hide-details
                    :disabled="!isEditable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.email"
                    class="text-danger mt-2"
                    >{{ primary_errors.email[0] }}</span
                  >
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    :readonly="isMapviewOnly"
                    label="Whatsapp"
                    maxlength="15"
                    dense
                    small
                    outlined
                    type="whatsapp"
                    v-model="payload_primary.whatsapp"
                    hide-details
                    :disabled="!isEditable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.whatsapp"
                    class="text-danger mt-2"
                    >{{ primary_errors.whatsapp[0] }}</span
                  >
                </v-col>
                <v-col
                  cols="6"
                  dense
                  v-if="
                    contact?.address_type == 'primary' ||
                    contact?.address_type == 'secondary'
                  "
                >
                  <v-text-field
                    :readonly="isMapviewOnly"
                    label="Alarm STOP Pin"
                    dense
                    small
                    max="6"
                    outlined
                    type="password"
                    max-length="6"
                    v-model="payload_primary.alarm_stop_pin"
                    hide-details
                    :disabled="!isEditable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.alarm_stop_pin"
                    class="text-danger mt-2"
                    >{{ primary_errors.alarm_stop_pin[0] }}</span
                  > </v-col
                ><v-col cols="6" dense>
                  <v-text-field
                    readonly
                    label="Working Hours"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="payload_primary.working_hours"
                    hide-details
                    :disabled="!isEditable"
                    :readonly="isMapviewOnly"
                  ></v-text-field>
                </v-col>
                <v-col
                  :cols="
                    contact?.address_type == 'primary' ||
                    contact?.address_type == 'secondary'
                      ? 6
                      : 12
                  "
                  dense
                >
                  <v-text-field
                    label="Distance From Building"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="payload_primary.distance"
                    hide-details
                    :disabled="!isEditable"
                    :readonly="isMapviewOnly"
                  ></v-text-field>
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    label="Latitude"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="payload_primary.latitude"
                    hide-details
                    :disabled="!isEditable"
                    :readonly="isMapviewOnly"
                  ></v-text-field>
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    label="Longitude"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="payload_primary.longitude"
                    hide-details
                    :disabled="!isEditable"
                    :readonly="isMapviewOnly"
                  ></v-text-field>
                </v-col>

                <v-col cols="12">
                  <v-text-field
                    outlined
                    label="Important Notes"
                    value=""
                    dense
                    small
                    hide-details
                    v-model="payload_primary.notes"
                    :disabled="!isEditable"
                    :readonly="isMapviewOnly"
                  ></v-text-field>
                </v-col> </v-row
            ></v-col>
          </v-row>
          <v-row>
            <v-col cols="10" class="text-right">
              <v-btn
                small
                v-if="
                  payload_primary.address_type != 'primary' &&
                  payload_primary.address_type != 'secondary' &&
                  !isMapviewOnly &&
                  isEditable &&
                  payload_primary.id
                "
                :loading="loading"
                color="error"
                @click="deleteContact(payload_primary)"
              >
                Delete
              </v-btn></v-col
            ><v-col cols="2" class="text-right">
              <v-btn
                small
                v-if="!isMapviewOnly && isEditable"
                :loading="loading"
                color="primary"
                @click="submit_primary"
              >
                Submit
              </v-btn></v-col
            >
          </v-row>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import CompGoogleMapLatLan from "./CompGoogleMapLatLan.vue";
import CompGoogleMapSelectLocation from "./CompGoogleMapSelectLocation.vue";

export default {
  components: { CompGoogleMapLatLan, CompGoogleMapSelectLocation },
  props: [
    "customer_id",
    "customer_contacts",
    "customer",
    "isMapviewOnly",
    "isEditable",
    "contact",
    "contact_id",
  ],
  data: () => ({
    mapkey: 1,
    showMap: false,
    editItem: null,
    dialogViewPhotos: false,
    show1: false,
    buildingTypes: [],
    branchesList: [],
    startDateMenuOpen: "",
    endDateMenuOpen: "",
    preloader: false,
    loading: false,
    primary_upload: {
      name: "",
    },
    secondary_upload: {
      name: "",
    },
    building_upload: {
      name: "",
    },
    start_date: "",
    end_date: "",
    payload_primary: {
      alarm_stop_pin: "",
      //   first_name: "",
      //   last_name: "",
      //   phone1: "",
      //   phone2: "",
      //   office_phone: "",
      //   email: "",
      //   whatsapp: "",
    },
    payload_secondary: {
      first_name: "",
      last_name: "",
      phone1: "",
      phone2: "",
      office_phone: "",
      email: "",
      whatsapp: "",
    },
    payload_building: {
      attachment: "",

      phone1: "",
      phone2: "",
      office_phone: "",
      email: "",
      whatsapp: "",
    },
    e1: 1,
    primary_errors: [],
    primary_previewImage: null,
    secondary_errors: [],
    secondary_previewImage: null,
    building_errors: [],
    building_previewImage: null,
    response: "",
    snackbar: false,
    errors: [],
    addressTypes: [],
  }),
  created() {
    this.preloader = false;
    // this.getBranchesList();
    if (this.$store.state.storeAlarmControlPanel?.AddressTypes) {
      this.addressTypes = this.$store.state.storeAlarmControlPanel.AddressTypes;

      this.addressTypes = this.addressTypes.map((item) => item.name);

      if (this.customer_contacts) {
        let addressTypesAlreadyAddeed = this.customer_contacts.map((item) =>
          item.address_type.toLowerCase()
        );

        this.addressTypes = this.addressTypes.filter(
          (e) => !addressTypesAlreadyAddeed.includes(e.toLowerCase())
        );
      }
    }
    if (this.customer && this.contact) {
      this.payload_primary = this.contact;
      this.primary_previewImage = this.contact.profile_picture;
    }

    return;
    if (this.customer) {
      this.payload_primary = this.customer.primary_contact;
      this.primary_previewImage = this.payload_primary.profile_picture;

      this.payload_secondary = this.customer.secondary_contact;
      this.secondary_previewImage = this.payload_secondary.profile_picture;

      // this.payload_primary = this.customer_contacts.filter(
      //   (el) => el.address_type == "primary"
      // );
      // if (this.payload_primary[0]) {
      //   this.payload_primary = this.payload_primary[0];
      //   this.primary_previewImage = this.payload_primary.profile_picture;
      // }
      // //secondary
      // this.payload_secondary = this.customer_contacts.filter(
      //   (el) => el.address_type == "secondary"
      // );
      // if (this.payload_secondary[0]) {
      //   this.payload_secondary = this.payload_secondary[0];
      //   this.secondary_previewImage = this.payload_secondary.profile_picture;
      // }
      //building
      // this.payload_building = this.customer_contacts.filter(
      //   (el) => el.address_type == "building"
      // );
      // if (this.payload_building[0]) {
      //   this.payload_building = this.payload_building[0];
      //   this.building_previewImage = this.payload_building.profile_picture;
      // }
    }
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    changeAddresss() {
      this.showMap = true;
    },
    updateMapAddressDetails(latitude, longitude) {
      this.payload_primary.latitude = latitude;
      this.payload_primary.longitude = longitude;
      this.mapkey++;
      this.showMap = false;
    },
    deleteContact(contact) {
      if (confirm("Are you sure you wish to delete ?")) {
        if (contact.id > 0) {
          this.$axios
            .delete(`delete_customer_contact`, {
              params: { contact_id: contact.id, customer_id: this.customer_id },
            })
            .then(({ data }) => {
              this.color = "background";
              this.primary_errors = [];
              this.snackbar = true;
              this.response = data.message;

              this.$emit("callrefreshData");
            });
        }
      }
    },

    //primary
    onpick_primary_attachment() {
      this.$refs.primary_attachment_input.click();
    },
    primary_attachment(e) {
      this.primary_upload.name = e.target.files[0] || "";

      let input = this.$refs.primary_attachment_input;
      let file = input.files;
      //console.log(file);
      if (file[0] && file[0].size > 1024 * 1024) {
        e.preventDefault();
        this.primary_errors["logo"] = [
          "File too big (> 1MB). Upload less than 1MB",
        ];
        return;
      }

      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
          this.primary_previewImage = e.target.result;
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);
      }
    },
    viewPhoto(item) {
      this.editItem = item;
      this.dialogViewPhotos = true;
    },
    async submit_primary() {
      let customer = new FormData();

      for (const key in this.payload_primary) {
        if (
          this.payload_primary[key] !== "" &&
          this.payload_primary[key] !== "null"
        )
          customer.append(key, this.payload_primary[key]);
      }
      customer.append("company_id", this.$auth.user.company_id);
      customer.append("customer_id", this.customer_id);
      if (this.contact?.id) customer.append("editId", this.contact.id);

      if (this.primary_upload.name) {
        customer.append("profile_picture", this.primary_upload.name);
      }

      try {
        const { data } = await this.$axios.post(
          "/customers_component_contact_update",
          customer
        );

        if (!data.status) {
          this.primary_errors = data.primary_errors || [];
          this.color = "red";
          this.snackbar = true;
          this.response = data.message;
          return false;
        } else {
          this.color = "background";
          this.primary_errors = [];
          this.snackbar = true;
          this.response = data.message;

          this.$emit("callrefreshData");
          return true;
        }
      } catch (e) {
        if (e.response?.data) {
          this.primary_errors = e.response.data.errors;
          this.color = "red";
        }
        return false;
      }
    },
  },
};
</script>
