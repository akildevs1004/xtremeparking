<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row>
      <v-col class="text-right">
        <v-btn x-small title="New" color="primary" @click="addEarning()"
          >Add
        </v-btn>
      </v-col>
    </v-row>
    <v-row
      v-for="(d, index) in device?.sensorzones"
      :key="index"
      style="border: 0px solid black"
    >
      <v-col cols="2" style="width: 100px; padding: 4px">
        <!-- <v-text-field
          label="Sensor name"
          placeholder="Sensor name"
          hide-details
          outlined
          dense
          v-model.number="d.label"
        /> -->
        <v-select
          class="pb-0"
          hide-details
          v-model="d.sensor_name"
          outlined
          dense
          label="Sensor Type"
          :items="SensorTypes"
          item-value="id"
          item-text="name"
        ></v-select>
      </v-col>
      <v-col cols="2" style="padding: 4px">
        <v-select
          class="pb-0"
          hide-details
          v-model="d.wired"
          outlined
          dense
          label="Wired/Wireless"
          :items="['Wired', 'Wireless']"
        ></v-select>
      </v-col>

      <v-col cols="2" style="min-width: 60px; max-width: 110px; padding: 4px">
        <v-text-field
          label="Location"
          hide-details
          outlined
          dense
          v-model="d.location"
        />
      </v-col>
      <v-col cols="1" style="min-width: 50px; max-width: 90px; padding: 4px">
        <v-text-field
          type="text"
          label="Area"
          hide-details
          outlined
          dense
          v-model="d.area_code"
        />
      </v-col>
      <v-col cols="1" style="min-width: 100px; max-width: 100px; padding: 4px">
        <v-text-field
          type="text"
          label="Zone"
          hide-details
          outlined
          dense
          v-model="d.zone_code"
        />
      </v-col>
      <!-- <v-col cols="1" style="min-width: 110px; max-width: 110px; padding: 4px">
        <v-select
          class="pb-0"
          hide-details
          v-model="d.delay"
          outlined
          dense
          label="Delay(Minutes)"
          :items="oneTOsixty"
        ></v-select>
      </v-col>
      <v-col cols="1" style="min-width: 130px; max-width: 130px; padding: 4px">
        <v-select
          class="pb-0"
          hide-details
          v-model="d.hours24"
          outlined
          dense
          label="24 Hours"
          item-text="name"
          item-id="id"
          :items="[
            { id: 1, name: 'Yes' },
            { id: 0, name: 'No' },
          ]"
        ></v-select>
      </v-col> -->
      <v-col cols="1" style="min-width: 50px; max-width: 50px; padding: 4px">
        <!-- <v-icon
          v-if="index == device.zones.length - 1"
          style="color: black3333"
          outlined
          @click="addEarning"
          size="20"
          fab
          >mdi-plus-circle</v-icon
        > -->

        <v-icon
          v-if="device.sensorzones.length > 1"
          dark
          fab
          style="padding-top: 10px"
          outlined
          @click="removeEarningItem(index)"
          size="20"
          >mdi-delete</v-icon
        >
      </v-col>
    </v-row>

    <v-row class="pr-3">
      <v-col cols="10" class="text-right">
        <!-- <v-btn class="error" small @click="cancel">Cancel</v-btn> -->
      </v-col>
      <v-col cols="2" class="text-left">
        <v-btn class="primary" small @click="save_device_info">Save</v-btn>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  props: ["employeeId", "editDevice"],
  data() {
    return {
      deviceTypes: [],
      SensorTypes: [],
      displayEditform: false,
      loading: false,
      device_info: false,
      menu: false,
      date: false,
      snackbar: false,
      response: "",
      errors: [],
      zonesErrors: [],
      device: {
        sensorzones: [],
      },
      oneTOsixty: [],
    };
  },
  created() {
    for (let index = 0; index <= 60; index++) {
      this.oneTOsixty.push(index);
    }
    if (this.$store.state.storeAlarmControlPanel?.DeviceTypes) {
      this.deviceTypes = this.$store.state.storeAlarmControlPanel.DeviceTypes;
    }
    if (this.$store.state.storeAlarmControlPanel?.SensorTypes) {
      this.SensorTypes = this.$store.state.storeAlarmControlPanel.SensorTypes;
    }
    //this.addEarning();
    this.getInfo();
  },
  computed: {
    // net_salary() {
    //   const { zones } = this.device;
    //   const basic_salary = parseInt(this.device.basic_salary);
    //   if (zones && zones.length) {
    //     const reducer = (acc, cv) => acc + parseInt(cv.value);
    //     return `${basic_salary + zones.reduce(reducer, 0)}`;
    //   }
    //   return basic_salary;
    // },
  },
  methods: {
    displayEdit() {
      this.displayEditform = true;
    },
    cancel() {
      this.displayEditform = false;
    },
    removeEarningItem(index) {
      this.device.sensorzones.splice(index, 1);
    },
    addEarning() {
      let obj = {
        sensor_name: "",
        wired: "wired",
        location: "",
        area_code: "00",
        zone_code: "001",
        delay: 0,

        hours24: 0,
      };
      this.device.sensorzones.push(obj);
    },
    getInfo() {
      this.loading = true;
      //let zones = this.editDevice.sensorzones;
      if (this.editDevice) {
        this.editDevice.sensorzones.forEach((element) => {
          let obj = {
            sensor_name: element.sensor_name,
            wired: element.wired,
            location: element.location,
            area_code: element.area_code,
            zone_code: element.zone_code,
            delay: parseInt(element.delay),
            hours24: element.hours24,
          };
          this.device.sensorzones.push(obj);
        });
      } else {
        if (this.editDevice) {
          if (this.editDevice.sensorzones.length == 0) {
            this.addEarning();
          }
          this.editDevice.sensorzones = [];
        }
      }

      // this.device.sensorzones = zones;
      // console.log("this.device", this.device);
      // if (this.device?.sensorzones?.length == 0) {
      //   this.addEarning();
      // }
      // this.$axios
      //   .get(`device/${this.employeeId}`, {
      //     params: {
      //       company_id: this.$auth?.user?.company?.id,
      //     },
      //   })
      //   .then(({ data }) => {
      //     this.loading = false;
      //     this.device = data || { zones: [] };

      //     if (this.device.zones.length == 0) {
      //       this.addEarning();
      //     }
      //   });
    },
    can(item) {
      return true;
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

    save_device_info() {
      this.errors = [];

      let payload = {
        ...this.device,

        company_id: this.$auth?.user?.company?.id,
        customer_id: this.customer_id,
        device_id: this.editDevice?.id ?? null,
      };
      this.loading = true;
      this.$axios
        .post(`/device_zones_update`, payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.snackbar = true;
            this.response = data.message;
            //this.device = data.record || { zones: [] };
            //this.close_device_info();
            this.$emit("closeDialog");
          }
        })
        .catch((e) => {
          this.errors = [];
        });
    },
    close_device_info() {
      this.device_info = false;
      this.errors = [];
      this.cancel();
      this.$emit("close-popup");
    },
  },
};
</script>
