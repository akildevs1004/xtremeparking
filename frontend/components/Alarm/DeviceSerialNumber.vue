<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row>
      <v-col md="12" sm="12" cols="12" dense>
        <v-row>
          <!-- <v-col cols="4">
              <div class="text-center mt-0">
                <v-img
                  style="
                    height: auto;
                    min-height: 200px;
                    width: 200px;
                    border-radius: 10%;
                    margin: 0 auto;
                  "
                  :src="primary_previewImage || '/no-business_profile.png'"
                ></v-img>
                <v-btn
                  class="mt-2"
                  style="width: 50%"
                  small
                  @click="onpick_primary_attachment"
                  >{{ !editId ? "Upload" : "Change" }}
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
            </v-col> -->

          <v-col cols="12" style="padding: 0px">
            <v-row class="pt-0" style="padding: 0px">
              <v-col md="12">
                <v-text-field hide-details v-model="payload_serial_number.name" outlined dense
                  label="Device Name"></v-text-field>
                <span v-if="primary_errors && primary_errors.name" class="error--text pa-0 ma-0">{{
                  primary_errors.name[0] }}
                </span>
              </v-col>

              <v-col md="12">
                <v-text-field class="pb-0" hide-details v-model="payload_serial_number.location" outlined dense
                  label="Device location"></v-text-field>
                <span v-if="primary_errors && primary_errors.location" class="error--text">{{ primary_errors.location[0]
                  }}
                </span>
              </v-col>



              <!-- <v-col md="12">
                <v-text-field class="pb-0" hide-details v-model="payload_serial_number.camera1" outlined dense
                  label="Entry Camera Code"></v-text-field>
                <span v-if="primary_errors && primary_errors.camera1" class="error--text">{{
                  primary_errors.camera_entry_code[0]
                  }}
                </span>
              </v-col>
              <v-col md="12">
                <v-text-field class="pb-0" hide-details v-model="payload_serial_number.camera2" outlined dense
                  label="Exit Camera Code"></v-text-field>
                <span v-if="primary_errors && primary_errors.camera2" class="error--text">{{
                  primary_errors.camera_entry_code[0]
                  }}
                </span>
              </v-col> -->

              <v-col cols="12" dense>
                <v-select class="pb-0" :readonly="!editable" :filled="!editable" hide-details
                  v-model="payload_serial_number.function" outlined dense label="Device Entry/Exit" :items="deviceTypes"
                  item-value="value" item-text="name"></v-select>


                <span v-if="errors && errors.function" class="error--text mt-2">{{ errors.function[0] }}</span>
              </v-col>

              <v-col cols="12" dense>
                <v-text-field label="Serial Number(ESP32 ID)" dense small outlined clearable autocomplete="off"
                  v-model="payload_serial_number.serial_number" hide-details :readonly="!editable"
                  :filled="!editable"></v-text-field>

                <span v-if="primary_errors && primary_errors.serial_number" class="text-danger mt-2">{{
                  primary_errors.serial_number[0] }}</span>
              </v-col>
              <v-col cols="12" dense>
                <v-text-field label="Parking Camera In Name" dense small outlined clearable autocomplete="off"
                  v-model="payload_serial_number.camera_in_name" hide-details :readonly="!editable"
                  :filled="!editable"></v-text-field>

                <span v-if="primary_errors && primary_errors.camera_in_name" class="text-danger mt-2">{{
                  primary_errors.camera_in_name[0] }}</span>
              </v-col>
              <v-col cols="12" dense>
                <v-text-field label="Parking Camera Out Name" dense small outlined clearable autocomplete="off"
                  v-model="payload_serial_number.camera_out_name" hide-details :readonly="!editable"
                  :filled="!editable"></v-text-field>

                <span v-if="primary_errors && primary_errors.camera_out_name" class="text-danger mt-2">{{
                  primary_errors.camera_out_name[0] }}</span>
              </v-col>
              <v-col md="12">
                <v-autocomplete class="pb-0" hide-details v-model="payload_serial_number.utc_time_zone" outlined dense
                  label="Time Zone(Ex:UTC+)" :items="getTimezones()" item-value="key" item-text="text"></v-autocomplete>
                <span v-if="primary_errors && primary_errors.utc_time_zone" class="error--text">{{
                  primary_errors.utc_time_zone[0] }}
                </span>
              </v-col>
              <!-- <v-col cols="12" dense>
                <span>Gates for Entry and Exit?</span>

                <v-radio-group class="radiogroup1" style="  " v-model="payload_serial_number.gates_count">
                  <v-radio label="2 seperate gates for Entry and Exit" value="2" style="font-size: 10px"></v-radio>
                  <v-radio label="only  1 Gate for Entry and Exit" value="1"></v-radio>

                </v-radio-group>
              </v-col> -->
            </v-row>
          </v-col>
        </v-row>

        <v-row>
          <v-col cols="12" v-if="response" style="font-size: 14px">
            <div style="color: red">{{ response }}</div>
          </v-col>
          <v-col cols="12" class="text-center">
            <v-btn v-if="editable" small :loading="loading" color="primary" @click="submit_primary">
              Submit
            </v-btn></v-col>
        </v-row>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import timeZones from "../../defaults/utc_time_zones.json";

export default {
  props: ["customer_id", "editId", "item", "editable"],
  data: () => ({
    timeZones,
    customersList: [],

    deviceTypes: [],
    deviceModels: [],
    show1: false,
    contactTypes: [],
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
    payload_serial_number: {
      device_description: "",
      model_number: "",
      function: "",
      serial_number: "",


      picture: "",
      company_id: 0,
    },
    new_serial_number: "",

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
    selectedItem: null,
    items: ["Apple", "Banana", "Orange"],
    web_login_access: 1,
    companiesList: [],
  }),
  created() {
    this.primary_previewImage = null;
    this.payload_serial_number = { function: "all", };
    this.preloader = false;
    // this.getBranchesList();
    // this.$axios
    //   .get("functions", {
    //     params: {},
    //   })
    //   .then((data) => {
    //     if (data) this.deviceTypes = data.data;
    //   });

    this.deviceTypes = [{ "name": "Entry", "value": "in" }, { "name": "Exit", "value": "out" }, { "name": "Entry and Exit", "value": "auto" }]
    this.$axios
      .get("device_models", {
        params: {},
      })
      .then((data2) => {
        if (data2) this.deviceModels = data2.data;
      });
    this.$axios
      .get("company/list", {
        params: {},
      })
      .then((data2) => {
        if (data2) this.companiesList = data2.data;
      });

    // setTimeout(() => {

    if (this.editId != "" && this.item) {
      this.payload_serial_number.name = this.item.name;
      this.payload_serial_number.location = this.item.location;
      this.payload_serial_number.utc_time_zone = this.item.utc_time_zone;
      this.payload_serial_number.customer_id = this.item.customer_id;

      this.payload_serial_number.editId = this.editId;
      this.payload_serial_number.company_id = this.$auth.user.company_id;
      this.payload_serial_number.serial_number = this.item.serial_number;
      this.payload_serial_number.function = this.item.function;

      this.payload_serial_number.camera_in_name = this.item.camera_in_name;
      this.payload_serial_number.camera_out_name = this.item.camera_out_name;






    }

    this.getCustomersList();
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    getCustomersList() {
      this.payloadOptions = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      try {
        this.$axios
          .get("customers-all", this.payloadOptions)
          .then(({ data }) => {
            this.customersList = data;
          });
      } catch (e) { }
    },
    getTimezones() {
      return Object.keys(this.timeZones).map((key) => ({
        offset: this.timeZones[key].offset,
        time_zone: this.timeZones[key].time_zone,
        key: key,
        text:
          this.timeZones[key].time_zone +
          " - " +
          key +
          " - " +
          this.timeZones[key].offset,
      }));
    },
    generateNewToken() {
      let length = 10;
      const characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
      let token = "XTG"; // Set the prefix here

      for (let i = 0; i < length - 2; i++) {
        const randomIndex = Math.floor(Math.random() * characters.length);
        token += characters.charAt(randomIndex);
      }
      this.new_serial_number = token;
      //this.payload_serial_number.serial_number = token;
    },
    changeStatus(status) {
      if (this.editable) this.web_login_access = status;
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

    submit_primary() {
      let customer = new FormData();

      for (const key in this.payload_serial_number) {
        if (this.payload_serial_number[key] != "")
          customer.append(key, this.payload_serial_number[key]);
      }
      // customer.append("new_serial_number", this.new_serial_number);

      // if (this.primary_upload.name) {
      //   customer.append("attachment", this.primary_upload.name);
      // }

      customer.append("company_id", this.$auth.user.company_id);
      // if (this.editAddressType != "") customer.append("editAddressType", true);

      if (this.editId) {
        customer.append("editId", this.editId);
      }


      this.$axios
        .post("/device", customer)
        .then(({ data }) => {
          //this.loading = false;

          if (!data.status) {
            this.primary_errors = [];
            if (data.errors) this.primary_errors = data.errors;
            this.color = "red";

            this.snackbar = true;
            this.response = data.message;

            for (let error in data.errors) {
              if (data.errors.hasOwnProperty(error)) {
                this.response = data.errors[error][0];
              }
            }
          } else {
            this.color = "background";
            this.primary_errors = [];
            this.snackbar = true;
            this.response = data.message;
            setTimeout(() => {
              this.$emit("closeDialog");
            }, 1000);
          }
        })
        .catch((e) => {
          if (e.response && e.response.data) {
            this.primary_errors = e.response.data.errors;
            this.color = "red";
            this.snackbar = true;
            this.response = e.response.data.message;
            for (let error in e.response.data.errors) {
              if (e.response.data.errors.hasOwnProperty(error)) {
                this.response = e.response.data.errors[error][0];
              }
            }
          }
        });
    },
  },
};
</script>
