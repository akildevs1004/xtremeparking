<template>
  <div>
    <v-tabs
      height="25"
      center-active
      right
      class="customerEmergencyContactTabs1 customerEmergencyContactTabsBGcolor1222222"
    >
      <v-tab style="font-size: 12px; min-width: 50px !important">
        Google Map
      </v-tab>
      <v-tab style="font-size: 12px; min-width: 50px !important"> Camera</v-tab>
      <v-tab style="font-size: 12px; min-width: 50px !important"
        >Premises Photo</v-tab
      ><v-tab style="font-size: 12px; min-width: 50px !important"
        >Floor Plan</v-tab
      >
      <v-tab style="font-size: 12px; min-width: 50px !important">Address</v-tab>

      <v-tab style="font-size: 12px; min-width: 50px !important">System</v-tab>
      <v-tab
        v-if="$auth.user"
        style="font-size: 12px; min-width: 50px !important"
        >Events</v-tab
      >
      <v-tab-item>
        <CompGoogleMapLatLan
          v-if="customer"
          :alarm="alarm"
          :latitude="customer.latitude"
          :longitude="customer.longitude"
          :title="customer.building_name"
          :mapheight="parseInt(browserHeight - 25) + 'px'"
          :contact_id="customer.id"
          :key="keySelectedItem"
        />
      </v-tab-item>

      <v-tab-item>
        <v-row>
          <v-col>
            <div
              style="text-align: center; min-height: 600px; padding-top: 50px"
              v-if="customer?.cameras.length == 0"
            >
              No Cameras available
            </div>
            <!-- <iframe
              style="
                position: absolute;
                top: 0;
                left: 0;
                bottom: 0;
                right: 0;
                width: 100%;
                height: 100%;
                border: none;
              "
              src="https://rtmp.oxsai.com/player.html?url=rtmp://64.225.28.61/live/cam1"
            ></iframe> -->
            <v-carousel
              v-model="currentCameraSlide"
              :height="browserHeight - 150"
              hide-delimiter-background
              show-arrows-on-hover
              style="width: 100%"
            >
              <template
                v-for="(item, index) in customer.cameras"
                :name="item.id"
                style="width: 100%"
              >
                <v-carousel-item style="padding-top: 50px; width: 100%">
                  <v-chip color="#203864" style="color: #fff" label
                    >{{ index + 1 }}: {{ item.title }}</v-chip
                  >
                  <div style="width: 100%; margin: auto; text-align: center">
                    <iframe
                      :style="';  border: none;  min-width:750px'"
                      :width="browserWidth - 830"
                      :height="browserHeight - 250"
                      v-if="browserWidth && getCameraUrl(item) != ''"
                      :src="getCameraUrl(item)"
                    ></iframe>
                  </div>
                </v-carousel-item>
              </template>
            </v-carousel>
          </v-col>
        </v-row>
        <v-row>
          <v-col style="width: 600px; overflow: auto">
            <v-row justify="center" dense>
              <v-col
                class="thumbnail-wrapper"
                v-for="(item, index) in customer.cameras"
                :key="'thumb-' + index"
                style="max-width: 150px; text-align: center"
              >
                <div
                  @click="currentCameraSlide = index"
                  style="
                    height: 50px;
                    width: 50px;
                    margin: auto;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                  "
                  :class="{ 'active-thumbnail': index === currentCameraSlide }"
                >
                  <v-icon size="100">mdi-camera</v-icon>
                </div>
                <label style="font-size: 12px; text-align: center">{{
                  item.title
                }}</label>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-tab-item>

      <v-tab-item>
        <v-row>
          <v-col>
            <v-carousel
              v-model="currentSlide"
              v-if="customer"
              hide-delimiter-background
              hide-arrows-on-hover
              hide-delimiters
              hide-arrows
              :height="browserHeight - 210"
            >
              <v-carousel-item
                v-for="(item, index) in customer.profile_pictures"
                :key="i"
                style="margin: auto"
              >
                <v-img
                  :style="{
                    height: browserHeight - 230 + 'px',
                    width: browserWidth - 700 + 'px',
                    margin: auto,
                  }"
                  :src="item?.picture ? item.picture : '/no-profile-image.jpg'"
                  contain
                />
              </v-carousel-item>
            </v-carousel>
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <div
              style="
                max-width: 750px;
                width: 100%;
                overflow-x: auto;
                white-space: nowrap;
                margin: auto;
                text-align: center;
              "
            >
              <div
                style="
                  display: inline-block;
                  width: 140px;
                  text-align: center;
                  margin-right: 10px;
                "
                v-for="(item, index) in customer.profile_pictures"
                :key="index"
              >
                <div
                  style="height: 100px; width: 150px; margin: auto"
                  :class="{ 'active-thumbnail': index === currentSlide }"
                >
                  <v-img
                    :src="item.picture"
                    :alt="item.title"
                    contain
                    width="140px"
                    max-height="100px"
                    height="100px"
                    style="max-height: 100px"
                    @click="currentSlide = index"
                  ></v-img>
                </div>
                <label style="font-size: 10px">{{ item.title }}</label>
              </div>
            </div>
          </v-col>
        </v-row>
      </v-tab-item>

      <v-tab-item>
        <EventsBusinessTabFloorPlan
          v-if="customer"
          :alarm="alarm"
          :customer="customer"
          :browserHeight="browserHeight"
        />
      </v-tab-item>

      <v-tab-item
        ><EventsBusinessTab1Address :customer="customer"
      /></v-tab-item>

      <v-tab-item>
        <div style="padding-top: 20px">
          <table
            class="operatorcustomerTop1"
            style="width: 100%; line-height: 40px"
          >
            <tr>
              <td class="bold">#</td>
              <td class="bold">Zone Number</td>
              <td class="bold">Zone Name</td>
              <td class="bold">Zone Type</td>
              <td class="bold">Sensor Type</td>
              <td class="bold">Area</td>
              <td class="bold">Wired/Wireless</td>
            </tr>
            <tr v-for="(sensor, index) in device.sensorzones">
              <td>{{ index + 1 }}</td>
              <td>{{ sensor.zone_code }}</td>
              <td>{{ sensor.location }}</td>
              <td>{{ sensor.sensor_name }}</td>

              <td>{{ sensor.sensor_type }}</td>
              <td>
                {{
                  sensor.area_code == ""
                    ? "Default"
                    : getAreaName(sensor.area_code) ?? "Default"
                }}
              </td>
              <td>{{ sensor.wired }}</td>
            </tr>
          </table>
        </div>
      </v-tab-item>
      <v-tab-item>
        <AlarmCustomerEventsLog
          style="font-size: 12px !important"
          v-if="customer"
          :filter_customer_id="customer.id"
          name="PopupAlarmEventsCustoemrInfoAlamAllEventsPopup"
      /></v-tab-item>
    </v-tabs>
  </div>
</template>

<script>
import CompGoogleMapLatLan from "../Alarm/CompGoogleMapLatLan.vue";
import EventsBusinessTab1Address from "./EventsBusinessTab1Address.vue";
import AlarmCustomerEventsLog from "../Alarm/AlarmCustomerEventsLog.vue";
import EventsBusinessTabFloorPlan from "./EventsBusinessTabFloorPlan.vue";

import PlottingIcon from "./PlottingIcon.vue";
export default {
  components: {
    EventsBusinessTab1Address,
    AlarmCustomerEventsLog,
    PlottingIcon,
    CompGoogleMapLatLan,
    EventsBusinessTabFloorPlan,
  },
  props: ["customer", "alarm", "device", "browserHeight"],
  data: () => ({
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
    tab: "",
    currentSlide: 0,
    currentCameraSlide: 0,
    imageLoaded: {},
    i: null,
    auto: null,

    loadingImagesstatus: false,
    keyPlottings: 1,
    browserWidth: 1200,
    keySelectedItem: 1,
  }),
  computed: {},
  mounted() {
    setTimeout(() => {
      this.loadingImagesstatus = true;
    }, 1000 * 10);

    // setTimeout(() => {
    //   // Get all elements with the class .v-carousel__item and .v-carousel
    //   let carousels = document.querySelectorAll(".v-carousel__item");
    //   let carousels2 = document.querySelectorAll(".v-carousel");

    //   console.log("carousels", carousels);
    //   console.log("carousels2", carousels2);

    //   // Check if browserHeight is defined, otherwise get the window height
    //   let browserHeight =
    //     window.innerHeight || document.documentElement.clientHeight;

    //   // Loop through each .v-carousel__item element and set its height
    //   carousels.forEach((carousel) => {
    //     carousel.style.height = `${browserHeight - 200}px`; // Adding height style
    //   });

    //   // Loop through each .v-carousel element and set its height
    //   carousels2.forEach((carousel) => {
    //     carousel.style.height = `${browserHeight - 200}px`; // Adding height style
    //   });
    // }, 1000 * 10); // 5 seconds delay
  },
  created() {
    //if (this.customer) console.log("customer", this.customer);
    this.onResize();
    //if (window) window.addEventListener("resize", this.onResize);
  },
  // watch: {
  //   "alarm.alarm_status": function () {
  //     this.keySelectedItem++;

  //     console.log(this.keySelectedItem);
  //   },
  // },
  methods: {
    onResize() {
      try {
        if (window) this.browserWidth = window.innerWidth;
        // if (window) this.browserHeight = window.innerHeight;
      } catch (e) {}
    },
    getAreaName(area_code) {
      return (
        this.areaList.find((areaName) => areaName.id == area_code)?.name ||
        "---"
      );
    },
    getCameraUrl(item) {
      if (process) {
        return process?.env.CAMERA_RTMP_PLAYER + "?url=" + item.camera_url;
      } else {
        return "";
      }
    },
    // adjustHeightPosition(heightInPx, id, plotting) {
    //   const imageElement = document.getElementById("plotting" + id);
    //   console.log(imageElement?.clientHeight);
    //   if (imageElement) {
    //     const imageHeight = imageElement.clientHeight - 50;
    //     let heightRatio =
    //       imageHeight / parseInt(this.IMG_PLOTTING_HEIGHT.replace("PX", ""));
    //     let finalHeightposition = heightInPx.replace("px", "") * heightRatio;
    //     // let sensorCount = this.sensorPlottingCount[id]
    //     //   ? this.sensorPlottingCount[id] + 1
    //     //   : 0;
    //     // this.sensorPlottingCount[id] = sensorCount;
    //     // if (plotting.alarm_event) {
    //     //   this.sensorAlarmCount[id] = this.sensorAlarmCount[id]
    //     //     ? this.sensorAlarmCount[id] + 1
    //     //     : 0;
    //     // }
    //     return finalHeightposition + "px";
    //   }
    //   return false;
    // },
    // adjustWidthPosition(widthInPx, id) {
    //   const imageElement = document.getElementById("plotting" + id);
    //   if (imageElement) {
    //     const imageWidth = imageElement.clientWidth - 50;
    //     let widthRatio = imageWidth / this.IMG_PLOTTING_WIDTH.replace("PX", "");
    //     let finalWidthposition = widthInPx.replace("px", "") * widthRatio;
    //     return finalWidthposition + "px";
    //   }
    //   return false;
    // },
    // onImageLoad(imageId) {
    //   this.$set(this.imageLoaded, imageId, true);
    // },
  },
};
</script>
