<template>
  <div v-if="can(`device_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ deviceResponse }}
      </v-snackbar>
    </div>
    <v-form ref="form" v-model="valid" lazy-validation>
      <v-row class="ma-1">
        <!-- <v-col md="6">
          <v-text-field
            class="pb-0"
            hide-details
            v-model="payload.serial_number"
            outlined
            dense
            label="Serial Number"
          ></v-text-field>
          <span v-if="errors && errors.serial_number" class="error--text"
            >{{ errors.serial_number[0] }}
          </span>
        </v-col> --><v-col md="6">
          <v-autocomplete :disabled="editDevice && payload.serial_number != ''" :items="master_serial_numbers"
            @change="loadDeviceDetails(payload.serial_number)" item-text="serial_number" item-value="serial_number"
            class="pb-0" hide-details v-model="payload.serial_number" outlined dense
            label="Serial Number"></v-autocomplete>
          <span v-if="errors && errors.serial_number" class="error--text">{{ errors.serial_number[0] }}
          </span>
        </v-col>
        <v-col md="6">
          <v-autocomplete class="pb-0" hide-details v-model="payload.customer_id" outlined dense label="Customer Name"
            :items="customersList" item-value="id" item-text="building_name" clearable
            :disabled="checkDisable()"></v-autocomplete>
          <span v-if="errors && errors.customer_id" class="error--text">{{ errors.customer_id[0] }}
          </span>
        </v-col>

        <v-col md="6">
          <v-text-field hide-details v-model="payload.name" outlined dense label="Device Name"></v-text-field>
          <span v-if="errors && errors.name" class="error--text pa-0 ma-0">{{ errors.name[0] }}
          </span>
        </v-col>

        <v-col md="6">
          <v-text-field class="pb-0" hide-details v-model="payload.location" outlined dense
            label="Device location"></v-text-field>
          <span v-if="errors && errors.location" class="error--text">{{ errors.location[0] }}
          </span>
        </v-col>
        <v-col md="6">
          <v-autocomplete class="pb-0" hide-details v-model="payload.utc_time_zone" outlined dense
            label="Time Zone(Ex:UTC+)" :items="getTimezones()" item-value="key" item-text="text"></v-autocomplete>
          <span v-if="errors && errors.utc_time_zone" class="error--text">{{ errors.utc_time_zone[0] }}
          </span>
        </v-col>
        <v-col md="6">
          <v-select disabled outlined dense class="pb-0" hide-details v-model="payload.model_number"
            :items="deviceModels" label="Model Number"></v-select>
          <!-- <v-text-field></v-text-field> -->
          <span v-if="errors && errors.model_number" class="error--text">{{ errors.model_number[0] }}
          </span>
        </v-col>

        <!-- <v-col md="6">
          <v-select
            class="pb-0"
            hide-details
            v-model="payload.wired"
            placeholder="Wired/Wireless"
            outlined
            dense
            label="Wired/Wireless"
            :items="['Wired', 'Wireless']"
          ></v-select>
          <span v-if="errors && errors.wired" class="error--text"
            >{{ errors.wired[0] }}
          </span>
        </v-col> -->
        <!-- <v-col md="6">
          <v-select
            class="pb-0"
            hide-details
            v-model="payload.alarm_delay_minutes"
            outlined
            dense
            label="Alarm Delay (Minutes)"
            :items="oneTOsixty"
          ></v-select>
          <span v-if="errors && errors.alarm_delay_minutes" class="error--text"
            >{{ errors.alarm_delay_minutes[0] }}
          </span>
        </v-col> -->

        <!-- <v-col md="6">
          <v-select
            disabled
            class="pb-0"
            hide-details
            v-model="payload.device_type"
            outlined
            dense
            label="Device Type"
            :items="deviceTypes"
            item-value="id"
            item-text="name"
          ></v-select>
          <span v-if="errors && errors.device_type" class="error--text"
            >{{ errors.device_type[0] }}
          </span>
        </v-col> -->
        <!-- <v-col
          md="6"
          v-if="
            payload.device_type == 'all' ||
            payload.device_type == 'Attendance' ||
            payload.device_type == 'Access Control'
          "
        >
          <v-select
            class="pb-0"
            hide-details
            v-model="payload.function"
            outlined
            dense
            label="Attendance Function"
            :items="[
              { id: 'auto', name: 'Auto' },
              { id: 'In', name: 'In' },
              { id: 'Out', name: 'Out' },
              { id: 'manual', name: 'Manual Entry' },
            ]"
            item-value="id"
            item-text="name"
          ></v-select>
          <span v-if="errors && errors.function" class="error--text"
            >{{ errors.function[0] }}
          </span>
        </v-col> -->
        <!-- <v-col
          md="6"
          v-if="
            payload.device_model == 'XT-CPANEL' ||
            payload.device_type == 'all' ||
            payload.device_type == 'Temperature' ||
            payload.device_type == 'Control Panel'
          "
        >
          <v-text-field
            class="pb-0"
            hide-details
            v-model="payload.threshold_temperature"
            outlined
            dense
            label="Threshold Temperature"
          ></v-text-field>
          <span
            v-if="errors && errors.threshold_temperature"
            class="error--text"
            >{{ errors.threshold_temperature[0] }}
          </span>
        </v-col> -->
      </v-row>
    </v-form>

    <v-row>
      <v-col cols="8" style="font-size: 14px">
        <div style="color: red">{{ response }}</div>
        <!-- Note:Multiple Zones can add to Control Panel Type -->
      </v-col>
      <v-col cols="4">
        <div class="text-right">
          <v-btn :loading="loading" small color="primary" @click="store_device">
            Submit
          </v-btn>
        </div>
      </v-col>
    </v-row>
  </div>
  <NoAccess v-else />
</template>
<script>
import timeZones from "../../defaults/utc_time_zones.json";

export default {
  components: {},
  props: ["editDevice", "customer_id"],
  data: () => ({
    master_serial_numbers: [],
    deviceTypes: [],
    deviceModels: [],
    oneTOsixty: [],
    deviceCAMVIISettings: { voice_volume: 0 },
    DialogDeviceMegviiSettings: false,
    valid: false,
    rules: [(value) => (value || "").length <= 10 || "Max 10 characters"],
    device_model: [
      (v) => !!v || "Device Name  is required",
      (value) => (value || "").length <= 20 || "Max 20 characters",
    ],

    menu_password: [
      (v) => !!v || "Password is required",
      (value) => (value || "").length <= 8 || "Max 8 characters",
    ],
    sdk_message: "",
    verificationModeItems: [
      { name: "Face", value: "face" },
      { name: "Face Or Card", value: "face_or_card" },
      { name: "Face and Card", value: "face_and_card" },
      {
        name: "Face and Password",
        value: "face_and_pass",
      },
    ],
    DialogDeviceSettings: false,
    deviceSettings: { maker: {} },
    to_menu_filter: false,
    to_menu_filter: "",
    to_date_filter: "",
    visitorUploadedDevicesInfo: [],
    loadingDeviceData: false,
    visitor_status_list: [],
    inputFindDeviceUserId: "",
    popupDeviceId: null,
    popupDeviceName: null,
    uploadedUserInfoDialog: false,
    dialogAccessSettings: false,
    popup_device_id: "",
    editDialog: false,
    showFilters: false,
    filters: {
      branch_id: "",
    },
    isFilter: false,
    totalRowsCount: 0,
    datatable_search_textbox: "",
    filter_employeeid: "",
    snack: false,
    snackColor: "",
    snackText: "",
    timeZones,
    payload: {
      name: "",
      device_type: "",
      device_id: "",
      model_number: "",
      status_id: "",
      company_id: "",
      location: "",
      short_name: "",
      alarm_delay_minutes: 0,
      ip: "",
      function: "auto",
      port: "",
      camera_save_images: false,
      threshold_temperature: "",
    },
    Model: "Device",
    pagination: {
      current: 1,
      total: 0,
      per_page: 100,
    },
    options: {},
    endpoint: "device",
    search: "",
    snackbar: false,
    dialog: false,
    data: [],
    loading: false,
    total: 0,
    deviceResponse: "",
    headers: [
      { text: "#", value: "sno" },
      { text: "Device", value: "device" },
      { text: "Sensor", value: "sensor" },
      { text: "Location", value: "location" },

      { text: "Zone", value: "zone" },
      { text: "Alarm Type", value: "alarm_type" },

      { text: "Delay(Min)", value: "delay" },
      // { text: "24 Hrs", value: "hrs_24" },
      { text: "Temperature", value: "threshold_temperature" },

      { text: "Status", value: "status" },
      { text: "Alarm", value: "alarm" },
      { text: "Options", value: "options" },
    ],

    response: "",
    errors: [],
    customersList: [],
    device_statusses: [],
    branches: [],
    branchesList: [],
    isCompany: true,
    timeZoneOptions: [],
    editedItem: null,
    downloadProfileLink: null,
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New device" : "Edit device";
    },
  },

  watch: {
    options: {
      handler() {
        //this.getDataFromApi();
      },
      deep: true,
    },
    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
    },
  },
  mounted() {
    // setTimeout(() => {
    //   this.updateDevicesHealth();
    // }, 1000 5);
    // setInterval(() => {
    //   if (this.$route.name == "device") {
    //     this.getDataFromApi();
    //   }
    // }, 1000 60);
  },
  async created() {
    for (let index = 0; index <= 60; index++) {
      this.oneTOsixty.push(index);
    }
    this.payload = {
      name: "",
      device_type: "",
      device_id: "",
      model_number: "",
      status_id: "",
      company_id: "",
      location: "",
      short_name: "",
      alarm_delay_minutes: 0,
      ip: "",
      function: "auto",
      port: "",
      camera_save_images: false,
      threshold_temperature: "",
    };
    if (this.editDevice) {
      // this.payload = this.editDevice;
      this.payload.name = this.editDevice.name;
      this.payload.location = this.editDevice.location;
      this.payload.utc_time_zone = this.editDevice.utc_time_zone;
      this.payload.model_number = this.editDevice.model_number;
      this.payload.serial_number = this.editDevice.serial_number;
      this.payload.wired = this.editDevice.wired;
      this.payload.alarm_delay_minutes = this.editDevice.alarm_delay_minutes;
      this.payload.device_type = this.editDevice.device_type;
      this.payload.function = this.editDevice.function;
      this.payload.threshold_temperature =
        this.editDevice.threshold_temperature;
      if (this.editDevice.customer_id)
        this.payload.customer_id = this.editDevice.customer_id;
    }

    if (this.$store.state.storeAlarmControlPanel?.DeviceTypes) {
      this.deviceTypes = this.$store.state.storeAlarmControlPanel.DeviceTypes;
    }
    if (this.$store.state.storeAlarmControlPanel?.DeviceModels) {
      this.deviceModels = this.$store.state.storeAlarmControlPanel.DeviceModels;
    }
    let options = {
      params: {
        company_id: this.$auth.user.company_id,
      },
    };
    this.getCustomersList();
    this.$axios.get("get_customer_new_serial_numbers", options).then((data) => {
      this.master_serial_numbers = data.data;

      if (this.editDevice)
        this.master_serial_numbers = [
          {
            model_number: this.editDevice.model_number,
            device_type: this.editDevice.device_type,
            serial_number: this.editDevice.serial_number,
          },
          ...this.master_serial_numbers,
        ];
    });
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    loadDeviceDetails(serial_number) {
      let device = this.master_serial_numbers.find(
        (e) => e.serial_number == serial_number
      );
      if (device) {
        this.payload.device_type = device.device_type;
        this.payload.model_number = device.model_number;
      }
    },
    checkDisable() {
      if (this.$auth.user.user_type == "company") {
        return false;
      }
      return false;
    },
    store_device() {
      this.errors = [];
      let id = -1;
      if (this.editDevice) id = this.editDevice.id;
      //let company_id = console.log(this.payload);

      this.payload.company_id = this.$auth.user.company_id;
      this.payload.id = id;
      this.payload.ip = "0.0.0.0";
      this.payload.port = "0000";
      if (this.customer_id) this.payload.customer_id = this.customer_id;

      this.payload.old_serial_number = this.editDevice
        ? this.editDevice.serial_number
        : null;

      delete this.payload.status;
      delete this.payload.company;
      delete this.payload.company_branch;

      let payload = this.payload;

      this.loading = true;
      if (!this.editDevice) {
        this.$axios
          .post(`/device`, payload)
          .then(({ data }) => {
            this.loading = false;
            if (!data.status) {
              this.errors = [];
              if (data.errors) this.errors = data.errors;

              this.snackbar = true;
              if (data.message != "") {
                this.deviceResponse = data.message;
                this.response = data.message;
                //this.$emit("closeDialog");
              } else {
                this.deviceResponse = "Some fields are missing";
                this.response = "Some fields are missing";
              }
            } else if (data.status == "device_api_error") {
              this.errors = [];
              this.snackbar = true;
              this.deviceResponse =
                "Check the Device information. There are errors.";

              this.response = "Check the Device information. There are errors.";
            } else {
              this.snackbar = true;
              this.deviceResponse = "Device details are  Created successfully";
              this.response = "Device details are  Created successfully";
              //this.getDataFromApi();
              this.editDialog = false;

              this.$emit("closeDialog");
            }
          })
          .catch((e) => console.log(e));
      } else {
        this.$axios
          .put(`/device/${id}`, payload)
          .then(({ data }) => {
            this.loading = false;
            if (!data.status) {
              this.errors = data.errors;
              this.snackbar = true;
              this.deviceResponse = data.message;
              this.response = data.message;
            } else if (data.status == "device_api_error") {
              this.errors = [];
              this.snackbar = true;
              this.deviceResponse =
                "Check the Device information. There are errors.";
              this.response = "Check the Device information. There are errors.";
            } else {
              this.snackbar = true;
              this.deviceResponse = "Device details are  updated successfully";
              this.response = "Device details are updated successfully";

              this.editDialog = false;
              this.$emit("closeDialog");
            }
          })
          .catch((e) => console.log(e));
      }
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
              this.snackbar = data.status;
              this.response = data.message;
              this.$emit("closeDialog");
            }
          })
          .catch((err) => console.log(err));
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
  },
};
</script>
