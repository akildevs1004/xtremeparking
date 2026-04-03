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
          <v-row class="pt-0">
            <v-col cols="6" dense>
              <v-autocomplete
                @change="loadZonesContent()"
                label="Device"
                :items="devicesList"
                item-text="name"
                item-value="serial_number"
                v-model="payload_primary.serial_number"
                dense
                outlined
                hide-details
                small
              ></v-autocomplete>
              <span
                v-if="errors && errors.serial_number"
                class="text-danger mt-2"
                >{{ errors.serial_number[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense v-if="zonesList.length > 0">
              <v-autocomplete
                label="Sensor/Zone"
                :items="zonesList"
                item-text="sensor_name"
                item-value="sensor_name"
                v-model="payload_primary.zone_name"
                dense
                outlined
                hide-details
                small
              ></v-autocomplete>
              <span
                v-if="errors && errors.zone_name"
                class="text-danger mt-2"
                >{{ errors.zone_name[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Receiver Name"
                dense
                small
                outlined
                type="text"
                v-model="payload_primary.name"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.name"
                class="text-danger mt-2"
                >{{ primary_errors.name[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Mobile Number"
                dense
                small
                outlined
                type="text"
                v-model="payload_primary.mobile_number"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.mobile_number"
                class="text-danger mt-2"
                >{{ primary_errors.mobile_number[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Email"
                dense
                small
                outlined
                type="email"
                v-model="payload_primary.email"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.email"
                class="text-danger mt-2"
                >{{ primary_errors.email[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Whatsapp Number"
                dense
                small
                outlined
                type="text"
                v-model="payload_primary.whatsapp_number"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.whatsapp_number"
                class="text-danger mt-2"
                >{{ primary_errors.whatsapp_number[0] }}</span
              >
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" class="text-right">
              <!-- <v-btn
                small
                :loading="loading"
                color="primary"
                @click="$emit('closeDialog')"
              >
                Close
              </v-btn> -->
              <v-btn
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
  props: ["editItem", "editId", "customer_id"],
  data: () => ({
    show1: false,
    devicesList: [],
    zonesList: [],
    //branchesList: [],
    startDateMenuOpen: "",
    endDateMenuOpen: "",
    preloader: false,
    loading: false,
    primary_upload: {
      name: "",
    },

    start_date: "",
    end_date: "",
    payload_primary: {},

    e1: 1,
    primary_errors: [],

    response: "",
    snackbar: false,
    errors: [],
    selectedItem: null,
    items: ["Apple", "Banana", "Orange"],
  }),
  created() {
    this.payload_primary = {};
    this.preloader = false;
    this.getDevicesList();
    // this.getBranchesList();

    if (this.$store.state.storeAlarmControlPanel?.AddressTypes) {
      this.addressTypes = this.$store.state.storeAlarmControlPanel.AddressTypes;
    }

    if (this.editItem) {
      this.payload_primary = {};
      this.payload_primary.editId = this.editItem.id;
      this.payload_primary.serial_number = this.editItem.serial_number;
      this.payload_primary.zone_name = this.editItem.zone_name;
      this.payload_primary.name = this.editItem.name;
      this.payload_primary.email = this.editItem.email;
      this.payload_primary.mobile_number = this.editItem.mobile_number;
      this.payload_primary.whatsapp_number = this.editItem.whatsapp_number;
      this.payload_primary.customer_id = this.editItem.customer_id;
      setTimeout(() => {
        this.loadZonesContent();
      }, 1000 * 2);
    }
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    loadZonesContent() {
      let deviceFilter = this.devicesList.filter(
        (e) => e.serial_number == this.payload_primary.serial_number
      );

      if (deviceFilter[0]?.sensorzones?.length > 0)
        this.zonesList = deviceFilter[0].sensorzones;
      else this.zonesList = [];
    },

    getDevicesList() {
      this.$axios
        .get(`device-list`, {
          params: {
            company_id: this.$auth.user.company_id,
            customer_id: this.customer_id,
          },
        })
        .then(({ data }) => {
          this.devicesList = data;
        });
    },

    submit_primary() {
      this.payload_primary.company_id = this.$auth.user.company_id;
      this.payload_primary.customer_id = this.customer_id;

      if (this.editId) {
        this.$axios
          .put("/automation/" + this.editId, this.payload_primary)
          .then(({ data }) => {
            this.loading = false;
            this.$emit("closeDialog");
            if (!data.status) {
              this.primary_errors = data.errors;
              return;
            }

            this.snackbar = data.status;
            this.response = data.message;
          })
          .catch((e) => console.log(e));
      } else {
        this.$axios
          .post("/automation", this.payload_primary)
          .then(({ data }) => {
            this.loading = false;

            if (!data.status) {
              this.primary_errors = data.errors;
              return;
            }
            this.snackbar = data.status;
            this.response = data.message;
            this.$emit("closeDialog");
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
