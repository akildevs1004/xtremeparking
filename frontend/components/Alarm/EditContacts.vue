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
    <v-row>
      <v-col cols="12">
        <v-tabs right class="customerEmergencyContactTabs" show-arrows>
          <v-tab>Primary Contact</v-tab>
          <v-tab>Secondary Contact</v-tab>

          <v-tab-item>
            <v-card class="elevation-0 p-2" style="padding: 5px">
              <v-row class="pt-5">
                <v-col cols="4" dense>
                  <div class="text-center mt-0">
                    <v-img
                      style="
                        height: auto;
                        min-height: 150px;
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
                      >{{ !primary_upload.name ? "Upload" : "Change" }}
                      <v-icon right dark color="primary"
                        >mdi-cloud-upload</v-icon
                      >
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
                </v-col>
                <v-col cols="8"
                  ><v-row>
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
                    <v-col cols="6" dense>
                      <v-text-field
                        :readonly="isMapviewOnly"
                        label="Alarm STOP Pin"
                        dense
                        small
                        outlined
                        type="number"
                        max-length="6"
                        v-model="payload_primary.alarm_stop_pin"
                        hide-details
                        :disabled="!isEditable"
                      ></v-text-field>
                      <span
                        v-if="primary_errors && primary_errors.alarm_stop_pin"
                        class="text-danger mt-2"
                        >{{ primary_errors.alarm_stop_pin[0] }}</span
                      >
                    </v-col>
                  </v-row></v-col
                >
              </v-row>
              <v-row>
                <v-col cols="12" class="text-right">
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
          </v-tab-item>

          <v-tab-item>
            <v-card class="elevation-0 p-2" style="padding: 5px">
              <v-row class="pt-5">
                <v-col cols="4" dense>
                  <div class="text-center mt-0">
                    <v-img
                      style="
                        height: auto;
                        min-height: 150px;
                        width: 150px;
                        border-radius: 50%;
                        margin: 0 auto;
                      "
                      @dblclick="viewPhoto(payload_secondary)"
                      :src="
                        secondary_previewImage || '/no-business_profile.png'
                      "
                    ></v-img>
                    <v-btn
                      v-if="!isMapviewOnly && isEditable"
                      class="mt-2"
                      style="width: 50%"
                      small
                      @click="onpick_secondary_attachment"
                      >{{ !secondary_upload.name ? "Upload" : "Change" }}
                      <v-icon right dark color="primary"
                        >mdi-cloud-upload</v-icon
                      >
                    </v-btn>

                    <input
                      required
                      type="file"
                      @change="secondary_attachment"
                      style="display: none"
                      accept="image/*"
                      ref="secondary_attachment_input"
                    />

                    <span
                      v-if="secondary_errors && secondary_errors.logo"
                      class="text-danger mt-2"
                      >{{ secondary_errors.logo[0] }}</span
                    >
                  </div>
                </v-col>
                <v-col cols="8"
                  ><v-row>
                    <v-col cols="6" dense>
                      <v-text-field
                        :readonly="isMapviewOnly"
                        label="First Name"
                        dense
                        small
                        outlined
                        type="text"
                        v-model="payload_secondary.first_name"
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
                        v-model="payload_secondary.last_name"
                        hide-details
                        :disabled="!isEditable"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="6" dense>
                      <v-text-field
                        :readonly="isMapviewOnly"
                        label="Phone 1"
                        dense
                        small
                        outlined
                        type="text"
                        v-model="payload_secondary.phone1"
                        hide-details
                        :disabled="!isEditable"
                      ></v-text-field>
                      <span
                        v-if="secondary_errors && secondary_errors.phone1"
                        class="text-danger mt-2"
                        >{{ secondary_errors.phone1[0] }}</span
                      >
                    </v-col>
                    <v-col cols="6" dense>
                      <v-text-field
                        :readonly="isMapviewOnly"
                        label="Phone 2"
                        dense
                        small
                        outlined
                        type="text"
                        v-model="payload_secondary.phone2"
                        hide-details
                        :disabled="!isEditable"
                      ></v-text-field>
                      <span
                        v-if="secondary_errors && secondary_errors.phone2"
                        class="text-danger mt-2"
                        >{{ secondary_errors.phone2[0] }}</span
                      >
                    </v-col>
                    <v-col cols="6" dense>
                      <v-text-field
                        :readonly="isMapviewOnly"
                        label="Office Phone"
                        dense
                        small
                        outlined
                        type="text"
                        v-model="payload_secondary.office_phone"
                        hide-details
                        :disabled="!isEditable"
                      ></v-text-field>
                      <span
                        v-if="secondary_errors && secondary_errors.office_phone"
                        class="text-danger mt-2"
                        >{{ secondary_errors.office_phone[0] }}</span
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
                        v-model="payload_secondary.email"
                        hide-details
                        :disabled="!isEditable"
                      ></v-text-field>
                      <span
                        v-if="secondary_errors && secondary_errors.email"
                        class="text-danger mt-2"
                        >{{ secondary_errors.email[0] }}</span
                      >
                    </v-col>
                    <v-col cols="6" dense>
                      <v-text-field
                        :readonly="isMapviewOnly"
                        label="Whatsapp"
                        dense
                        small
                        outlined
                        type="whatsapp"
                        v-model="payload_secondary.whatsapp"
                        hide-details
                        :disabled="!isEditable"
                      ></v-text-field>
                      <span
                        v-if="secondary_errors && secondary_errors.whatsapp"
                        class="text-danger mt-2"
                        >{{ secondary_errors.whatsapp[0] }}</span
                      >
                    </v-col>
                    <v-col cols="6" dense>
                      <v-text-field
                        :readonly="isMapviewOnly"
                        label="Alarm STOP Pin"
                        dense
                        small
                        outlined
                        type="number"
                        max-length="6"
                        v-model="payload_secondary.alarm_stop_pin"
                        hide-details
                        :disabled="!isEditable"
                      ></v-text-field>
                      <span
                        v-if="
                          secondary_errors && secondary_errors.alarm_stop_pin
                        "
                        class="text-danger mt-2"
                        >{{ secondary_errors.alarm_stop_pin[0] }}</span
                      >
                    </v-col>
                  </v-row></v-col
                ></v-row
              >
              <v-row>
                <v-col cols="12" class="text-right">
                  <v-btn
                    v-if="!isMapviewOnly && isEditable"
                    small
                    :loading="loading"
                    color="primary"
                    @click="submit_secondary"
                  >
                    Submit
                  </v-btn>
                </v-col>
              </v-row>
            </v-card>
          </v-tab-item></v-tabs
        >
      </v-col>

      <v-col cols="6" dense> </v-col>
      <v-col cols="6" dense style="border-left: 1px solid #ddd"> </v-col>
      <!-- <v-col md="4" sm="12" cols="12" dense>
        <v-card class="elevation-0 p-2" style="padding: 5px">
          <h3>Building Details</h3>
          <v-row class="pt-0">
            <v-col cols="6" dense>
              <div class="text-center mt-0">
                <v-img
                  style="
                    height: auto;
                    min-height: 150px;
                    width: 150px;
                    border-radius: 50%;
                    margin: 0 auto;
                  "
                  :src="building_previewImage || '/no-business_profile.png'"
                ></v-img>
                <v-btn
                  class="mt-2"
                  style="width: 100%"
                  small
                  @click="onpick_building_attachment"
                  >{{ !building_upload.name ? "Upload" : "Change" }}
                  <v-icon right dark color="primary">mdi-cloud-upload</v-icon>
                </v-btn>

                <input
                  required
                  type="file"
                  @change="building_attachment"
                  style="display: none"
                  accept="image/*"
                  ref="building_attachment_input"
                />

                <span
                  v-if="building_errors && building_errors.logo"
                  class="text-danger mt-2"
                  >{{ building_errors.logo[0] }}</span
                >
              </div>
            </v-col>

            <v-col cols="6" dense>
              <v-text-field
                label="Phone 1"
                dense
                small
                outlined
                type="text"
                v-model="payload_building.phone1"
                hide-details
              ></v-text-field>
              <span
                v-if="building_errors && building_errors.phone1"
                class="text-danger mt-2"
                >{{ building_errors.phone1[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Phone 2"
                dense
                small
                outlined
                type="text"
                v-model="payload_building.phone2"
                hide-details
              ></v-text-field>
              <span
                v-if="building_errors && building_errors.phone2"
                class="text-danger mt-2"
                >{{ building_errors.phone2[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Office Phone"
                dense
                small
                outlined
                type="text"
                v-model="payload_building.office_phone"
                hide-details
              ></v-text-field>
              <span
                v-if="building_errors && building_errors.office_phone"
                class="text-danger mt-2"
                >{{ building_errors.office_phone[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Email"
                dense
                small
                outlined
                type="email"
                v-model="payload_building.email"
                hide-details
              ></v-text-field>
              <span
                v-if="building_errors && building_errors.email"
                class="text-danger mt-2"
                >{{ building_errors.email[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Whatsapp"
                dense
                small
                outlined
                type="whatsapp"
                v-model="payload_building.whatsapp"
                hide-details
              ></v-text-field>
              <span
                v-if="building_errors && building_errors.whatsapp"
                class="text-danger mt-2"
                >{{ building_errors.whatsapp[0] }}</span
              >
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" class="text-right">
              <v-btn
                small
                :loading="loading"
                color="primary"
                @click="submit_building"
              >
                Submit
              </v-btn></v-col
            >
          </v-row>
        </v-card>
      </v-col> -->
    </v-row>
  </div>
</template>

<script>
export default {
  props: [
    "customer_id",
    "customer_contacts",
    "customer",
    "isMapviewOnly",
    "isEditable",
  ],
  data: () => ({
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
      first_name: "",
      last_name: "",
      phone1: "",
      phone2: "",
      office_phone: "",
      email: "",
      whatsapp: "",
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
  }),
  created() {
    this.preloader = false;
    // this.getBranchesList();

    if (this.customer && this.customer.primary_contact) {
      this.payload_primary = this.customer.primary_contact;
      this.primary_previewImage = this.payload_primary.profile_picture;
    }

    if (this.customer && this.customer.secondary_contact) {
      this.payload_secondary = this.customer.secondary_contact;
      this.secondary_previewImage = this.payload_secondary.profile_picture;
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
    getBranchesList() {
      this.$axios
        .get(`branches_list`, {
          params: {
            company_id: this.$auth.user.company_id,
          },
        })
        .then(({ data }) => {
          this.branchesList = data;
          this.payload_primary.branch_id = this.branchesList[0].id;
          //this.branch_id = this.$auth.user.branch_id || "";
        });
    },
    getBuildingTypes() {
      this.$axios
        .get(`building_types`, {
          params: {
            company_id: this.$auth.user.company_id,
          },
        })
        .then(({ data }) => {
          this.buildingTypes = data;
        });
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
      console.log(file);
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
        if (this.payload_primary[key] !== "")
          customer.append(key, this.payload_primary[key]);
      }
      customer.append("company_id", this.$auth.user.company_id);
      customer.append("customer_id", this.customer_id);

      if (this.primary_upload.name) {
        customer.append("profile_picture", this.primary_upload.name);
      }

      try {
        const { data } = await this.$axios.post(
          "/customers_primary_contact_update",
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
          this.response = "Customer Details Created successfully";
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
    //secondary
    onpick_secondary_attachment() {
      this.$refs.secondary_attachment_input.click();
    },
    secondary_attachment(e) {
      this.secondary_upload.name = e.target.files[1] || "";

      let input = this.$refs.secondary_attachment_input;
      let file = input.files;

      if (file[0] && file[0].size > 1024 * 1024) {
        e.preventDefault();
        this.secondary_errors["logo"] = [
          "File too big (> 1MB). Upload less than 1MB",
        ];
        return;
      }

      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
          this.secondary_previewImage = e.target.result;
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);
      }
    },
    async submit_secondary() {
      const primaryResult = await this.submit_primary();

      if (primaryResult) {
        let customer = new FormData();

        for (const key in this.payload_secondary) {
          if (this.payload_secondary[key] !== "")
            customer.append(key, this.payload_secondary[key]);
        }
        customer.append("company_id", this.$auth.user.company_id);
        customer.append("customer_id", this.customer_id);

        if (this.secondary_upload.name) {
          customer.append("profile_picture", this.secondary_upload.name);
        }

        try {
          const { data } = await this.$axios.post(
            "/customers_secondary_contact_update",
            customer
          );

          if (!data.status) {
            this.secondary_errors = data.secondary_errors || [];
            this.color = "red";
            this.snackbar = true;
            this.response = data.message;
            return false;
          } else {
            this.color = "background";
            this.secondary_errors = [];
            this.snackbar = true;
            this.response = "Customer Details Created successfully";
            this.$emit("closeDialog");
            return true;
          }
        } catch (e) {
          if (e.response?.data) {
            this.secondary_errors = e.response.data.errors;
            this.color = "red";
          }
          return false;
        }
      } else {
        return false;
      }
    },
    //building
    onpick_building_attachment() {
      this.$refs.building_attachment_input.click();
    },
    building_attachment(e) {
      this.building_upload.name = e.target.files[0] || "";

      let input = this.$refs.building_attachment_input;
      let file = input.files;
      //console.log(file);
      if (file[0] && file[0].size > 1024 * 1024) {
        e.preventDefault();
        this.building_errors["logo"] = [
          "File too big (> 1MB). Upload less than 1MB",
        ];
        return;
      }
      console.log(file);
      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
          this.building_previewImage = e.target.result;
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);
      }
    },
    async submit_building() {
      // const secondaryResult = await this.submit_secondary();
      // alert("secondaryResult", secondaryResult);
      // if (secondaryResult)
      {
        // this.submit_secondary();
        let customer = new FormData();

        for (const key in this.payload_building) {
          if (this.payload_building[key] != "")
            customer.append(key, this.payload_building[key]);
        }
        customer.append("company_id", this.$auth.user.company_id);
        customer.append("customer_id", this.customer_id);

        if (this.building_upload.name) {
          customer.append("profile_picture", this.building_upload.name);
        }

        this.$axios
          .post("/customers_building_contact_update", customer)
          .then(({ data }) => {
            //this.loading = false;

            if (!data.status) {
              this.building_errors = [];
              if (data.building_errors)
                this.building_errors = data.building_errors;
              this.color = "red";

              this.snackbar = true;
              this.response = data.message;
            } else {
              this.color = "background";
              this.building_errors = [];
              this.snackbar = true;
              this.response = "Customer Details Created successfully";

              this.$emit("closeDialog");
            }
          })
          .catch((e) => {
            if (e.response.data.primary_errors) {
              this.primary_errors = e.response.data.primary_errors;
              this.color = "red";

              // this.snackbar = true;
              // this.response = e.response.data.message;
            }
          });
      }
    },
    update() {
      let branch = new FormData();
      branch.append("company_id", this.$auth.user.company_id);
      branch.append("branch_name", this.branch.branch_name);
      branch.append("user_id", this.branch.user_id);

      branch.append("licence_number", this.branch.licence_number);
      branch.append(
        "licence_issue_by_department",
        this.branch.licence_issue_by_department
      );
      branch.append("licence_expiry", this.branch.licence_expiry);
      branch.append("lat", this.branch.lat);
      branch.append("lon", this.branch.lon);
      branch.append("address", this.branch.address);

      if (this.primary_upload.name) {
        branch.append("logo", this.primary_upload.name);
      }

      this.$axios
        .post(`/branch/${this.branch.id}`, branch)
        .then(({ data }) => {
          //this.loading = false;

          if (!data.status) {
            this.primary_errors = [];
            if (data.primary_errors) this.primary_errors = data.primary_errors;
            this.color = "red";

            this.snackbar = true;
            this.response = data.message;
          } else {
            this.primary_errors = [];
            this.snackbar = true;
            this.response = "Branch updated successfully";
            this.getDataFromApi();
            this.branchDialog = false;
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
