<template>
  <div>
    <v-row
      ><v-col>
        <v-carousel
          v-model="currentSlideUpdated"
          v-if="customer?.photos"
          hide-delimiter-background
          show-arrows-on-hover
          @change="keyPlottings++"
          hide-arrows-on-hover
          hide-delimiters
          hide-arrows
          class="floorplancarousel"
          :height="
            browserHeight > 700 ? browserHeight - 210 : browserHeight - 30
          "
        >
          <v-carousel-item
            v-for="(item, index) in customer.photos"
            :key="'imageplotting' + item.id"
            class="floorplanimage"
          >
            <div style="text-align: center">
              <v-chip
                color="#203864"
                style="color: #fff; margin-bottom: 2px"
                label
                >{{ index + 1 }}: {{ item.title }}</v-chip
              >
              <div
                :style="
                  'margin: auto;position: relative;width:' + IMG_PLOTTING_WIDTH
                "
              >
                <img
                  :id="
                    'plotting' +
                      item.photo_plottings[0]?.customer_building_picture_id ?? 0
                  "
                  class="photo-img"
                  :src="item.picture"
                  :width="IMG_PLOTTING_WIDTH"
                  :height="IMG_PLOTTING_HEIGHT"
                  @load="
                    onImageLoad(
                      item.photo_plottings[0]?.customer_building_picture_id ?? 0
                    )
                  "
                />
                <!-- <img
              :id="
                'plotting' +
                  item.photo_plottings[0]?.customer_building_picture_id ?? 0
              "
              class="photo-img"
              :src="item.picture"
              :style="
                'width: 100%; height: ' + parseInt(browserHeight - 220) + 'px'
              "
              @load="
                onImageLoad(
                  item.photo_plottings[0]?.customer_building_picture_id ?? 0
                )
              "
            /> -->

                <span
                  v-if="
                    item.photo_plottings[0] &&
                    imageLoaded[
                      item.photo_plottings[0]?.customer_building_picture_id ?? 0
                    ]
                  "
                >
                  <PlottingIcon
                    v-if="plottingStatus && item.photo_plottings[0]"
                    v-for="(plotting, idx) in item.photo_plottings[0].plottings"
                    :key="'plotting' + idx"
                    :plotting="plotting"
                    :picture-id="
                      item.photo_plottings[0]?.customer_building_picture_id ?? 0
                    "
                    :alarm="alarm"
                  />
                </span>
              </div>
            </div>
          </v-carousel-item>
        </v-carousel>
      </v-col>
    </v-row>
    <v-row style="margin-top: 0px" v-if="browserHeight - 210 > 550">
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
            v-for="(item, index) in customer.photos"
            :key="index"
            @click="changeSlideImage(index)"
          >
            <div
              style="height: 100px; width: 150px; margin: auto"
              :class="{ 'active-thumbnail': index === currentSlideUpdated }"
            >
              <v-img
                :src="item.picture"
                :alt="item.title"
                contain
                width="100px"
                max-height="80px"
                height="80px"
                style="max-height: 80px; margin: auto"
              ></v-img>
            </div>
            <label style="font-size: 10px">{{ item.title }}</label>
          </div>
        </div>
      </v-col>
    </v-row>
    <!-- <v-row>
      <v-col style="width: 600px; overflow: scroll">
        <v-row justify="center" dense>
          <v-col
            v-for="(item, index) in customer.photos"
            :key="'thumb-' + index"
            style="max-width: 150px; text-align: center"
          >
            <div
              style="height: 100px; width: 150px; margin: auto"
              :class="{ 'active-thumbnail': index === currentSlideUpdated }"
            >
              <v-img
                :src="item.picture"
                :alt="item.title"
                contain
                width="140px"
                max-height="100px"
                height="100px"
                style="max-height: 100px"
                @click="currentSlideUpdated = index"
              ></v-img>
            </div>
            <label style="font-size: 10px">{{ item.title }}</label>
          </v-col>
        </v-row>
      </v-col>
    </v-row> -->
  </div>
</template>

<script>
import PlottingIcon from "./PlottingIcon.vue";
export default {
  components: { PlottingIcon },
  props: ["customer", "alarm", "browserHeight"],
  data: () => ({
    tab: "",
    currentSlideUpdated: 0,
    imageLoaded: {},

    loadingImagesstatus: false,
    keyPlottings: 1,
    IMG_PLOTTING_WIDTH: process?.env?.IMG_PLOTTING_WIDTH || "650px",
    IMG_PLOTTING_HEIGHT: process?.env?.IMG_PLOTTING_HEIGHT || "500px",
    sensorTypesImages: [],
    plottingStatus: false,
  }),
  computed: {},
  mounted() {
    setTimeout(() => {
      if (this.customer)
        this.customer.photos.forEach((photo) => {
          if (photo.photo_plottings[0])
            this.updatePlottingswithSensorTypeImage(
              photo.photo_plottings[0].plottings
            );
        });

      this.plottingStatus = true;
    }, 1000 * 3);
  },
  created() {
    this.getSensorTypesImages();
  },
  watch: {},
  methods: {
    changeSlideImage(index) {
      this.currentSlideUpdated = index;
    },
    onImageLoad(imageId) {
      this.$set(this.imageLoaded, imageId, true);
    },
    getSensorTypesImages() {
      this.$axios
        .get("device_sensor_types_dropdown", {
          params: {
            //company_id: this.$auth.user.company_id,
          },
        })
        .then(({ data }) => {
          this.sensorTypesImages = data;
        });
    },
    updatePlottingswithSensorTypeImage(plottings) {
      //console.log(this.customer.devices);

      const allDevicesSensorTypeZones = this.customer.devices.flatMap(
        (device) => device.sensorzones
      );

      plottings.forEach((element) => {
        // Find the matching sensor zone
        const element2 = allDevicesSensorTypeZones.find(
          (zone) => zone.id == element.sensor_id
        );

        // Default values
        element.sensorImage = "/sensor_type_icons/" + "other_sensor.png";

        // element.sensorImage =
        //   process.env.BACKEND_URL2 + "/sensor_type_icons/" + "other_sensor.png";
        element.sensorTypeName = "Unknown";

        if (element2) {
          const find = this.sensorTypesImages.find(
            (e) => e.name == element2.sensor_type
          );

          element.sensorImage =
            "/sensor_type_icons/" + (find ? find.image : "other_sensor.png");
          // element.sensorImage =
          //   process.env.BACKEND_URL2 +
          //   "/sensor_type_icons/" +
          //   (find ? find.image : "other_sensor.png");
          element.sensorTypeName = element2.sensor_type;
        }
      });
    },
  },
};
</script>
