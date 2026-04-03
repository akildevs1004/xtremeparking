<template>
  <div>
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row>
      <v-col cols="8" class="pt-8 pb-0 pl-10 text-left" dense hide-details
        >Total {{ plottings ? checkIsSensorAddedAnyPhoto() : "0" }} Mapping(s)

        <span style="color: red"> {{ this.response }}</span>
      </v-col>
      <v-col cols="4" class="pt-6 pb-0" style="float: right">
        <v-select
          outlined
          hide-details
          dense
          @change="changePhoto()"
          :items="photos"
          item-value="id"
          item-text="title"
          v-model="selectedPhotoId"
        ></v-select>
      </v-col>
    </v-row>
    <v-row style="margin: auto">
      <v-col cols="12" style="position: relative" v-if="item">
        <img
          style="margin: auto; width: 100%"
          :src="item.picture"
          :width="IMG_PLOTTING_WIDTH"
          :height="IMG_PLOTTING_HEIGHT"
        />
        <span v-if="!loading">
          <div
            v-for="(plotting, index) in plottings"
            :key="index"
            style="position: absolute; text-align: center"
            :style="{ top: plotting.top, left: plotting.left }"
          >
            <v-icon
              v-if="plotting.alarm_event?.length"
              class="alarm-red-to-green"
              :title="plotting.alarm_event[0].alarm_type + ' Aalrm ON'"
              size="40"
              >mdi-circle</v-icon
            ><v-icon v-else color="green" size="40" title="Aalrm OFF"
              >mdi-circle</v-icon
            >
            <div style="background: black; color: #fff">
              {{ plotting.label }}
            </div>
          </div>
        </span>

        <div>
          Note: Check Customers Photo Mapping page incase any sensor is missing
        </div>
      </v-col>
    </v-row>
  </div>
</template>
<script>
export default {
  props: ["photos", "alarm_id"],
  data() {
    return {
      snackbar: false,
      response: "",
      dialog: false,
      loading: false,
      device_ids: [],
      devices: [],
      sensors: [],
      plottings: [],
      draggingIndex: null,

      existingPlottings: [],
      IMG_PLOTTING_WIDTH: "500px",
      IMG_PLOTTING_HEIGHT: "500px",

      selectedPhotoId: 0,
      item: null,
      alarm: null,
    };
  },
  async created() {
    //await this.getDevices();
    if (this.photos[0]) {
      this.item = this.photos[0];
      this.selectedPhotoId = this.item.id;
      this.getExistingPlottings();
      if (process) {
        this.IMG_PLOTTING_WIDTH = process?.env?.IMG_PLOTTING_WIDTH;
        this.IMG_PLOTTING_HEIGHT = process?.env?.IMG_PLOTTING_HEIGHT;
      }
    }
  },
  methods: {
    changePhoto() {
      this.response = "";
      this.item = this.photos.filter((e) => e.id == this.selectedPhotoId)[0];

      this.getExistingPlottings();
    },
    getDeviceName(device_id) {
      return this.devices.find((e) => e.id == device_id).name || "";
    },
    async getDevices() {
      let config = {
        params: {
          company_id: this.$auth.user.company_id,
          customer_id: this.item.customer_id,
        },
      };
      let { data } = await this.$axios.get(`device-list`, config);

      this.devices = [
        // { id: ``, name: "Select Device" },
        ...data,
      ];
    },
    getAlarmInfo() {
      let config = {
        params: {
          alarm_id: this.alarm_id,
        },
      };
      this.$axios.get(`alarm_event_by_id`, config).then((data) => {
        this.alarm = data.data;
        let alarmCounter = 0;
        if (this.alarm && this.plottings) {
          this.plottings.forEach((element) => {
            if (
              element.top != "-500px" &&
              element.zone_data &&
              this.alarm.zone == element.zone_data.zone_code &&
              this.alarm.area == element.zone_data.area_code
            ) {
              alarmCounter++;
              element.alarm_event = [];
              element.alarm_event[0] = this.alarm;
            }
          });

          if (alarmCounter == 0) {
            this.snackbar = true;
            this.response = "No Alarm Found";
          } else {
            this.response = alarmCounter + " Alarms";
          }
        }
      });
    },
    async getExistingPlottings() {
      this.loading = true;
      this.plottings = [];
      let config = {
        params: {
          customer_building_picture_id: this.item.id,
        },
      };
      this.$axios.get(`plotting`, config).then((data) => {
        this.existingPlottings = data.data.plottings;
        this.plottings = data.data.plottings;
        this.loading = false;
        if (this.alarm_id) this.getAlarmInfo();
      });
    },

    checkIsSensorAddedAnyPhoto() {
      let matchCount = 0;

      this.plottings.forEach((sensor) => {
        if (sensor.top !== "-500px") {
          matchCount++;
        }
      });

      return matchCount;
    },
    // async getSensors(device_ids) {
    //   await this.getExistingPlottings();

    //   let config = {
    //     params: { device_ids },
    //   };
    //   let { data: sensors } = await this.$axios.get("sensor-list", config);

    //   this.plottings = sensors.map((sensor) => {
    //     let plotting = this.existingPlottings.find(
    //       (e) => e.sensor_id == sensor.id
    //     );
    //     return (
    //       plotting || {
    //         sensor_id: sensor.id,
    //         device_id: sensor.device_id,
    //         label: sensor.label,
    //         top: "-500px",
    //         left: "-500px",
    //       }
    //     );
    //   });
    // },
    // dragStart(event, index) {
    //   this.draggingIndex = index;
    // },
    // allowDrop(event) {
    //   event.preventDefault();
    // },
    // async onDrop(event) {
    //   const dropZoneRect = event.currentTarget.getBoundingClientRect();
    //   const offsetX = event.clientX - dropZoneRect.left;
    //   const offsetY = event.clientY - dropZoneRect.top;

    //   this.plottings[this.draggingIndex].left = `${offsetX}px`;
    //   this.plottings[this.draggingIndex].top = `${offsetY}px`;

    //   this.draggingIndex = null;

    //   await this.submit();
    // },

    // async submit() {
    //   try {
    //     let { data } = await this.$axios.post(`plotting`, {
    //       customer_building_picture_id: this.item.id,
    //       plottings: this.plottings,
    //     });
    //     console.log(data);
    //   } catch (error) {
    //     console.log(error);
    //   }
    // },

    getRelaventImage(label) {
      return this.$utils.getRelaventImage(label);
    },
  },
};
</script>
