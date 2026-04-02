<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row>
      <v-col md="12" sm="12" cols="12" dense>
        <v-card class="elevation-0 p-2" style="padding: 5px">
          <v-row>
            <v-col cols="12">
              <v-row class="pt-0">
                <v-col cols="12" dense>
                  <v-text-field
                    label="Name(ex: Kitchen, Hall, etc)"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="payload_security.location"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.location"
                    class="text-danger mt-2"
                    >{{ primary_errors.location[0] }}</span
                  >
                </v-col>
                <v-col cols="12" dense>
                  <v-text-field
                    label="Code / Zone Number(Ex: 001,002)"
                    dense
                    small
                    outlined
                    v-model="payload_security.zone_code"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.zone_code"
                    class="text-danger mt-2"
                    >{{ primary_errors.zone_code[0] }}</span
                  >
                </v-col>

                <v-col cols="12" dense>
                  <v-autocomplete
                    label="Sensor Type"
                    :items="sensorTypes"
                    dense
                    small
                    outlined
                    v-model="payload_security.sensor_type"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                    item-value="name"
                    item-text="name"
                  ></v-autocomplete>
                  <span
                    v-if="primary_errors && primary_errors.sensor_type"
                    class="text-danger mt-2"
                    >{{ primary_errors.sensor_type[0] }}</span
                  >
                </v-col>
                <!-- <v-col
                  cols="12"
                  dense
                  v-if="payload_security.sensor_type == 'Other'"
                >
                  <v-text-field
                    label="Other Sensor Type"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="payload_security.sensor_type_other"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                </v-col> -->
                <v-col cols="12" dense>
                  <v-autocomplete
                    :items="[...ZoneTypes, 'Other']"
                    label="Zone Type"
                    dense
                    small
                    outlined
                    v-model="payload_security.sensor_name"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-autocomplete>
                  <span
                    v-if="primary_errors && primary_errors.sensor_name"
                    class="text-danger mt-2"
                    >{{ primary_errors.sensor_name[0] }}</span
                  >
                </v-col>
                <v-col
                  cols="12"
                  dense
                  v-if="payload_security.sensor_name == 'Other'"
                >
                  <v-text-field
                    label="Other Zone Type"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="payload_security.sensor_name_other"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" dense>
                  <!-- <v-text-field
                    label="Area (Ex:  01, 02)"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload_security.area_code"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field> -->

                  <v-select
                    :items="[{ id: null, name: 'Default' }, ...areaList]"
                    item-value="id"
                    item-text="name"
                    label="Area(Default - Device Model  XT700)"
                    dense
                    small
                    outlined
                    v-model="payload_security.area_code"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-select>
                  <span
                    v-if="primary_errors && primary_errors.area_code"
                    class="text-danger mt-2"
                    >{{ primary_errors.area_code[0] }}</span
                  >
                </v-col>
                <v-col cols="12">
                  <v-select
                    class="pb-0"
                    hide-details
                    v-model="payload_security.wired"
                    outlined
                    dense
                    label="Wired/Wireless"
                    :items="['Wired', 'Wireless']"
                  ></v-select
                ></v-col>
                <!-- <v-col cols="12" dense>
                  <v-select
                    label="Is 24 Hour?"
                    dense
                    outlined
                    clearable
                    :items="[
                      { value: '0', text: 'No' },
                      { value: '1', text: 'Yes' },
                    ]"
                    item-text="text"
                    item-value="value"
                    v-model="payload_security.hours24"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-select>
                  <span
                    v-if="errors && errors.hours24"
                    class="text-danger mt-2"
                    >{{ errors.hours24[0] }}</span
                  >
                </v-col> -->
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
export default {
  props: ["customer_id", "editId", "editable", "item", "deviceId", "device"],
  data: () => ({
    show1: false,
    areaList: [
      { id: "01", name: "Area 1" },
      { id: "02", name: "Area 2" },
      { id: "03", name: "Area 3" },
      { id: "04", name: "Area 4" },
      { id: "05", name: "Area 5" },
      { id: "06", name: "Area 6" },
      { id: "07", name: "Area 7" },
      { id: "08", name: "Area 8" },

      { id: "09", name: "Area 9" },
      { id: "10", name: "Area 10" },
    ],
    ZoneTypes: [],
    sensorTypes: [],
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
    payload_security: {
      sensor_name: "Perimeter Zone",
      area_code: null,
      wired: "Wireless",
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
    selectedItem: null,
    items: ["Apple", "Banana", "Orange"],
    web_login_access: 1,
  }),
  created() {
    // this.payload_security = {};
    if (this.$store.state.storeAlarmControlPanel?.ZoneTypes) {
      this.ZoneTypes = this.$store.state.storeAlarmControlPanel.ZoneTypes;

      this.ZoneTypes = this.ZoneTypes.map((item) => item.name);
    }

    this.$axios
      .get("device_sensor_types_dropdown", {
        params: {
          // company_id: this.$auth.user.company_id,
        },
      })
      .then(({ data }) => {
        this.sensorTypes = data;
      });
    // setTimeout(() => {
    //console.log(this.editAddressType);
    if (this.editId != "" && this.item) {
      this.payload_security.editId = this.editId;
      this.payload_security.zone_code = this.item.zone_code;
      this.payload_security.location = this.item.location;
      this.payload_security.sensor_name = this.item.sensor_name;
      this.payload_security.area_code = this.item.area_code;
      this.payload_security.hours24 = this.item.hours24;
      this.payload_security.wired = this.item.wired;
      this.payload_security.sensor_type = this.item.sensor_type;
    }
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
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

      for (const key in this.payload_security) {
        if (this.payload_security[key] != "")
          if (this.payload_security[key] != null)
            customer.append(key, this.payload_security[key]);
      }

      // if (this.payload_security.sensor_type.toLowerCase() == "other") {
      //   this.payload_security.sensor_type =
      //     this.payload_security.sensor_type_other;
      // }
      if (this.payload_security.sensor_name.toLowerCase() == "other") {
        this.payload_security.sensor_name =
          this.payload_security.sensor_name_other;
      }

      customer.append("company_id", this.$auth.user.company_id);

      if (this.editId) {
        let options = {
          params: {
            company_id: this.$auth.user.company_id,
            ...this.payload_security,
            device_zone_id: this.editId,
            device_id: this.deviceId,
            serial_number: this.device.serial_number,
            customer_id: this.customer_id,
          },
        };

        this.$axios
          .post("/update_device_zone", options.params)
          .then(({ data }) => {
            //this.loading = false;

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
              setTimeout(() => {
                this.$emit("closeDialog");
              }, 1000);
            }
          })
          .catch((e) => {
            if (e.response.data.errors) {
              this.primary_errors = e.response.data.errors;
              this.color = "red";

              this.snackbar = true;
              this.response = e.response.data.message;
            }
          });
      } else {
        let options = {
          params: {
            company_id: this.$auth.user.company_id,
            ...this.payload_security,

            serial_number: this.device.serial_number,
            device_id: this.deviceId,
            customer_id: this.customer_id,
          },
        };
        this.$axios
          .post("/create_device_zone", options.params)
          .then(({ data }) => {
            //this.loading = false;

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
              setTimeout(() => {
                this.$emit("closeDialog");
              }, 1000);
            }
          })
          .catch((e) => {
            if (e.response.data.errors) {
              this.primary_errors = e.response.data.errors;
              this.color = "red";

              this.snackbar = true;
              this.response = e.response.data.message;
            }
          });
      }
    },
  },
};
</script>
