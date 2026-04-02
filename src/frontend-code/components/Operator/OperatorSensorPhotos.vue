<template>
  <div>
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <div v-if="loading">Loading Sensor mapping....</div>

    <div
      v-if="!loading && photoPlottings.length == 0"
      style="padding-top: 15px"
    >
      No Sensor Mapping available
    </div>

    <v-row
      style="margin: auto; padding-top: 10px"
      :key="'operatorsensorphotos' + index + 20"
      v-for="(photo, index) in photoPlottings"
      v-if="isMounted"
    >
      <v-col cols="12" style="padding: 0px; position: relative" v-if="photo">
        <!-- <v-divider
          style="border: 1px solid #bdbdbd; margin-top: 5px"
        ></v-divider> -->
        <v-row>
          <v-col class="text-left">
            <h4>{{ index + 1 }}) {{ photo.photos.title }}</h4>
          </v-col>
          <v-col class="text-right">
            Total

            {{ sensorPlottingCount[photo.customer_building_picture_id] }}
            Mapping(s)
          </v-col>
        </v-row>
        <!-- <span style="color: red"> {{ sensorAlarmCount[photo.id] }}</span> -->
        <img
          :id="'plotting' + photo.customer_building_picture_id"
          style="
            margin: auto;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 10px;
          "
          :src="photo.photos.picture"
          :width="'400px'"
          :height="'300px'"
        />
        <span>
          <div
            v-for="(plotting, index) in photo.plottings"
            :key="'photoplottings' + index + 30"
            style="position: absolute; text-align: center"
            :style="{
              top: adjustHeightPosition(
                plotting.top,
                photo.customer_building_picture_id,
                plotting
              ),
              left: adjustWidthPosition(
                plotting.left,
                photo.customer_building_picture_id
              ),
            }"
          >
            <div v-if="plotting.top != '-500px' && plotting.left != '-500px'">
              <v-icon
                v-if="
                  plotting.zone_data &&
                  plotting.zone_data.area_code == alarm.area &&
                  plotting.zone_data.zone_code == alarm.zone &&
                  alarm.alarm_status == 1
                "
                class="alarm-red-to-green"
                :title="alarm.alarm_type + ' Aalrm ON'"
                size="20"
                >mdi-circle</v-icon
              >

              <v-icon
                v-else-if="
                  plotting.zone_data &&
                  plotting.zone_data.area_code == alarm.area &&
                  plotting.zone_data.zone_code == alarm.zone &&
                  alarm.alarm_status == 0
                "
                style="color: red"
                :title="alarm.alarm_type + ' Aalrm Off'"
                size="20"
                >mdi-circle</v-icon
              >

              <v-icon v-else color="green" size="20" title="Aalrm OFF"
                >mdi-circle</v-icon
              >
              <div
                style="
                  background: #787878;
                  color: #fff;
                  font-size: 10px;
                  line-height: 13px;
                  padding: 1px;
                "
              >
                {{ plotting.label }}
              </div>
            </div>
          </div>
        </span>

        <!-- <div>
          Note: Check Customers Photo Mapping page incase any sensor is missing
        </div> -->
      </v-col>
    </v-row>
  </div>
</template>
<script>
export default {
  props: ["customer_id", "alarm"],
  data() {
    return {
      isMounted: false,
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
      IMG_PLOTTING_WIDTH: process?.env?.IMG_PLOTTING_WIDTH || "500PX",
      IMG_PLOTTING_HEIGHT: process?.env?.IMG_PLOTTING_HEIGHT || "500PX",

      selectedPhotoId: 0,
      item: null,

      sensorPlottingCount: [],
      sensorAlarmCount: [],
      photoPlottings: [],
    };
  },
  mounted() {
    setTimeout(() => {
      this.isMounted = true;

      setTimeout(() => {
        this.isMounted = false;
        this.isMounted = true;
      }, 1000);
    }, 5000);
  },
  async created() {
    await this.getPlottingWithCustomerId();

    if (this.photoPlottings) {
      this.sensorPlottingCount = [];
      this.sensorAlarmCount = [];

      this.photoPlottings.forEach((element) => {
        this.checkIsSensorAddedAnyPhoto(element);
      });
    }
  },
  methods: {
    adjustHeightPosition(heightInPx, id, plotting) {
      const imageElement = document.getElementById("plotting" + id);
      if (imageElement) {
        const imageHeight = imageElement.clientHeight - 50;

        let heightRatio =
          imageHeight / parseInt(this.IMG_PLOTTING_HEIGHT.replace("PX", ""));

        let finalHeightposition = heightInPx.replace("px", "") * heightRatio;

        // let sensorCount = this.sensorPlottingCount[id]
        //   ? this.sensorPlottingCount[id] + 1
        //   : 0;

        // this.sensorPlottingCount[id] = sensorCount;

        // if (plotting.alarm_event) {
        //   this.sensorAlarmCount[id] = this.sensorAlarmCount[id]
        //     ? this.sensorAlarmCount[id] + 1
        //     : 0;
        // }

        return finalHeightposition + "px";
      }
    },
    adjustWidthPosition(widthInPx, id) {
      const imageElement = document.getElementById("plotting" + id);
      if (imageElement) {
        const imageWidth = imageElement.clientWidth - 50;

        let widthRatio = imageWidth / this.IMG_PLOTTING_WIDTH.replace("PX", "");

        let finalWidthposition = widthInPx.replace("px", "") * widthRatio;

        return finalWidthposition + "px";
      }
    },
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
    async getPlottingWithCustomerId() {
      this.loading = true;
      let config = {
        params: {
          company_id: this.$auth.user.company_id,
          customer_id: this.customer_id,
        },
      };
      this.$axios.get(`plotting_with_customer_id`, config).then(({ data }) => {
        this.photoPlottings = data;
        setTimeout(() => {
          this.loading = false;
        }, 1000 * 5);
        // if (data) this.loading = false;

        // if (this.photoPlottings.length > 0) {
        //   this.loading = false;
        // }
      });
    },
    async getExistingPlottings(item) {
      let plottings = [];
      let response = "";

      let config = {
        params: {
          customer_building_picture_id: item.id,
        },
      };
      let { data } = await this.$axios.get(`plotting`, config);

      if (data) {
        // this.existingPlottings = data.plottings;
        plottings = data.plottings;
      }

      let alarmEvents = plottings.filter(
        (plott) => plott.top != "-500px" && plott.alarm_event.length > 0
      );

      if (alarmEvents.length == 0) {
        this.snackbar = true;
        return (response = "No Alarm Found");
      } else {
        return alarmEvents.length + " Alarms";
      }
    },

    checkIsSensorAddedAnyPhoto(photo) {
      this.sensorPlottingCount[photo.customer_building_picture_id] = 0;
      this.sensorAlarmCount[photo.customer_building_picture_id] = 0;
      let matchCount = 0;
      let alarmCount = 0;

      photo.plottings.forEach((sensor) => {
        if (sensor.top == "-500px" && sensor.left == "-500px") {
        } else {
          matchCount++;
        }

        if (sensor.top != "-500px" && sensor.alarm_event.length > 0) {
          alarmCount++;
        }
      });
      this.sensorPlottingCount[photo.customer_building_picture_id] = matchCount;
      this.sensorAlarmCount[photo.customer_building_picture_id] = alarmCount;
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
