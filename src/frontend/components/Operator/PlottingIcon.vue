<template>
  <div
    v-if="plotting.top != '-500px' && plotting.left != '-500px'"
    class="plotting-icon"
    :style="plottingStyle"
  >
    <v-row>
      <v-col style="padding: 0px">
        <v-img
          :title="plotting.sensorTypeName"
          disabled="true"
          draggable="false"
          style="width: 40px"
          :src="plotting.sensorImage"
        ></v-img>
      </v-col>
      <v-col style="padding: 0px; margin: auto; text-align: left">
        <v-icon
          v-if="isAlarmOn"
          class="alarm-red-to-green"
          :title="alarm?.alarm_type + ' Alarm ON'"
          size="20"
        >
          mdi-circle
        </v-icon>
        <v-icon
          v-else-if="isAlarmOff"
          style="color: red"
          :title="alarm?.alarm_type + ' Alarm OFF'"
          size="20"
        >
          mdi-circle
        </v-icon>
        <v-icon v-else color="green" size="20" title="Alarm OFF">
          mdi-circle
        </v-icon>
      </v-col>
    </v-row>
    <v-row>
      <v-col style="padding: 0px">
        <div class="plotting-label" style="margin-top: 2px">
          <!-- {{ plotting.label }} -->
          {{ plotting.sensorTypeName }}
        </div></v-col
      >
    </v-row>
  </div>
</template>

<script>
export default {
  props: {
    plotting: Object,
    pictureId: Number,
    alarm: Object,
  },
  data: () => ({
    tab: "",
    imageLoaded: {},
    IMG_PLOTTING_WIDTH: process?.env?.IMG_PLOTTING_WIDTH || "650px",
    IMG_PLOTTING_HEIGHT: process?.env?.IMG_PLOTTING_HEIGHT || "500px",
    loadingImagesstatus: false,
    keyPlottings: 1,
  }),
  computed: {
    plottingStyle() {
      return {
        top: this.adjustHeightPosition(
          this.plotting.top,
          this.pictureId,
          this.plotting
        ),
        left: this.adjustWidthPosition(this.plotting.left, this.pictureId),
      };
    },
    isAlarmOn() {
      return (
        this.plotting.sensor_id == this.alarm?.sensor_zone_id &&
        this.alarm?.alarm_status == 1
      );
      return (
        this.plotting.zone_data &&
        this.plotting.zone_data.area_code == this.alarm.area &&
        this.plotting.zone_data.zone_code == this.alarm.zone &&
        this.alarm?.alarm_status == 1
      );
    },
    isAlarmOff() {
      return (
        this.plotting.sensor_id == this.alarm?.sensor_zone_id &&
        this.alarm?.alarm_status == 0
      );
      return (
        this.plotting.zone_data &&
        this.plotting.zone_data.area_code == this.alarm.area &&
        this.plotting.zone_data.zone_code == this.alarm.zone &&
        this.alarm?.alarm_status == 0
      );
    },
  },
  methods: {
    adjustHeightPosition(heightInPx, id, plotting) {
      const imageElement = document.getElementById("plotting" + id);

      if (imageElement) {
        const imageHeight = imageElement.clientHeight; //-50;

        let heightRatio =
          imageHeight / parseInt(this.IMG_PLOTTING_HEIGHT.replace("PX", ""));

        let finalHeightposition = heightInPx.replace("px", "") * heightRatio;

        /* const imageHeight = imageElement.clientHeight - 50;

        let heightRatio =
          imageHeight / parseInt(this.IMG_PLOTTING_HEIGHT.replace("PX", ""));

        let finalHeightposition = heightInPx.replace("px", "") * heightRatio;*/

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
      return false;
    },
    adjustWidthPosition(widthInPx, id) {
      const imageElement = document.getElementById("plotting" + id);
      if (imageElement) {
        const imageWidth = imageElement.clientWidth; // - 50;

        //console.log("imageElement.clientWidth", imageElement.clientWidth);

        let widthRatio = imageWidth / this.IMG_PLOTTING_WIDTH.replace("PX", "");

        let finalWidthposition = widthInPx.replace("px", "") * widthRatio;

        return finalWidthposition + "px";
      }

      return false;
    },
    onImageLoad(imageId) {
      this.$set(this.imageLoaded, imageId, true);
    },
  },
};
</script>

<style scoped>
.plotting-icon {
  position: absolute;
  text-align: center;
}
.plotting-label {
  background: #787878;
  color: #fff;
  font-size: 10px;
  line-height: 13px;
  padding: 1px;
}
</style>
