<template>
  <div class="text-centers" @click="hideContextMenu">
    <div class="text-center">
      <v-snackbar v-model="snackbar" :timeout="3000" centered elevation="24">
        {{ response }}

        <template v-slot:action="{ attrs }">
          <v-btn color="red" text v-bind="attrs" @click="snackbar = false">
            Close
          </v-btn>
        </template>
      </v-snackbar>
    </div>

    <v-row>
      <v-col
        v-if="IMG_PLOTTING_WIDTH"
        :style="'padding: 0px;max-width:' + IMG_PLOTTING_WIDTH + ''"
      >
        <v-card
          ><v-card-text elevation="3" style="border: 0px solid #ddd"
            ><v-row>
              <v-col
                cols="12"
                :style="'position: relative; padding: 0px; text-align: center;'"
                class="dropzone"
                @drop="onDrop"
                @dragover="allowDrop"
              >
                <!-- <v-img :src="item.picture" style="width: 100%; height: auto" /> -->

                <img
                  :src="item.picture"
                  :width="IMG_PLOTTING_WIDTH"
                  :height="IMG_PLOTTING_HEIGHT"
                />

                <span v-if="!loading">
                  <div
                    v-for="(plotting, index) in plottings"
                    :key="index"
                    style="position: absolute"
                    :style="{ top: plotting.top, left: plotting.left }"
                    draggable="false"
                  >
                    <!-- <v-icon v-if="plotting.alarm_event" class="alarm">
                            mdi-alarm-light
                          </v-icon> -->
                    <v-img
                      :title="plotting.sensorTypeName"
                      draggable="true"
                      @dragstart="dragStart($event, index)"
                      style="width: 40px"
                      :src="plotting.sensorImage"
                      @contextmenu.prevent="showContextMenu($event, index)"
                    ></v-img>

                    <v-card
                      :style="
                        'border: 0px solid black; color:black; ' +
                        'position:relative; left:-45px;z-index:9999'
                      "
                      class="context-menu"
                      v-if="menuVisible && selectedImageId == index"
                    >
                      <v-card-text elevation="2">
                        <v-btn
                          x-small
                          dense
                          color="primary"
                          @click="unmapSensor(index)"
                          primary
                          fill
                          >Delete Sensor</v-btn
                        >
                      </v-card-text>
                    </v-card>
                  </div>
                </span>
              </v-col>
              <!-- <v-col
                cols="12"
                style="position: relative; height: 50px; border: 1px solid red"
                class="dropzone"
                @drop="deleteOnDrop"
                @dragover="allowDrop"
                >DELETE ZONE - Drag Icon here to delete
              </v-col> -->
            </v-row></v-card-text
          ></v-card
        ></v-col
      >

      <v-col
        style="height: 500px; overflow: auto"
        class="sensorPlottingdevices"
      >
        <v-expansion-panels v-model="panelOpenList" multiple>
          <v-expansion-panel
            :key="'panelOpenList' + index"
            v-for="(device, index) in devices"
          >
            <v-expansion-panel-header
              style="background-color: rgb(32 56 100); color: #fff"
            >
              {{ device.name }}
            </v-expansion-panel-header>
            <v-expansion-panel-content class="sensorplotting">
              <div v-for="(plotting, plotIndex) in plottings" :key="plotIndex">
                <div
                  v-if="device.id == plotting.device_id"
                  style="color: green"
                >
                  <v-img
                    :title="plotting.sensorTypeName"
                    v-if="checkIsSensorAddedAnyPhoto(plotting) > 0"
                    :src="
                      getSensorTypeRelaventImage(
                        getDeviceCategory(device.id),
                        plotting
                      )
                    "
                    disabled="true"
                    draggable="false"
                    style="
                      width: 40px;
                      float: left;
                      margin: 5px;
                      filter: grayscale(100%);
                    "
                  ></v-img>
                  <v-img
                    :title="plotting.sensorTypeName"
                    v-else-if="plotting.sensorTypeName == null"
                    draggable="true"
                    @dragstart="dragStart($event, plotIndex)"
                    style="width: 40px; float: left; margin: 5px"
                    :src="
                      getSensorTypeRelaventImage(
                        getDeviceCategory(device.id),
                        plotting
                      )
                    "
                  ></v-img>
                  <v-img
                    :title="plotting.sensorTypeName"
                    v-else-if="checkIsSensorAddedAnyPhoto(plotting) == 0"
                    draggable="true"
                    @dragstart="dragStart($event, plotIndex)"
                    style="width: 40px; float: left; margin: 5px"
                    :src="plotting.sensorImage"
                  ></v-img>

                  <v-img
                    v-else
                    :title="plotting.sensorTypeName"
                    disabled="true"
                    draggable="false"
                    style="
                      width: 40px;
                      float: left;
                      margin: 5px;
                      filter: grayscale(100%);
                    "
                    :src="plotting.sensorImage"
                  ></v-img>
                </div>
              </div>
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
      </v-col>
      <!-- <v-col cols="12" class="text-right align-right">
        <v-btn dense small color="primary" @click="submit(true)"
          >Save</v-btn
        ></v-col
      > -->

      <!-- <v-col cols="6">
        <v-row no-gutters>
          <v-col cols="12">
            <v-autocomplete
              label="Select Device "
              multiple
              @change="getSensors(device_ids)"
              outlined
              dense
              :items="devices"
              item-value="id"
              item-text="name"
              v-model="device_ids"
            ></v-autocomplete>
          </v-col>
          <v-col cols="12" v-if="device_ids.length == 0">0 Devices</v-col>
          <v-col
            class="ma-1"
            cols="12"
            v-for="(device_id, index) in device_ids"
            :key="index"
          >
            <v-row no-gutters>
              <v-col
                cols="12"
                style="border: 1px solid #cdcccc; border-radius: 3px"
                class="py-2 px-3"
              >
                {{ index + 1 }} :{{ getDeviceCategory(device_id) }}

                <div
                  v-for="(plotting, plotIndex) in plottings"
                  :key="plotIndex"
                >
                  <div
                    v-if="device_id == plotting.device_id"
                    style="color: green"
                  >

                    <v-img
                      :title="plotting.sensorImage"
                      v-if="checkIsSensorAddedAnyPhoto(plotting) == 0"
                      draggable="true"
                      @dragstart="dragStart($event, plotIndex)"
                      style="width: 40px; float: left; margin: 10px"
                      :src="
                        getSensorTypeRelaventImage(
                          getDeviceCategory(device_id),
                          plotting
                        )
                      "
                    ></v-img>

                    <v-img
                      v-else
                      :title="plotting.sensorImage"
                      disabled="true"
                      draggable="false"
                      style="
                        width: 40px;
                        float: left;
                        margin: 10px;
                        filter: grayscale(100%);
                      "
                      :src="
                        getSensorTypeRelaventImage(
                          getDeviceCategory(device_id),
                          plotting
                        )
                      "
                    ></v-img>
                  </div>
                </div>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-col> -->
    </v-row>
  </div>
</template>
<script>
export default {
  props: ["item"],
  data() {
    return {
      menuVisible: false,
      menuX: 0,
      menuY: 0,
      snackbar: false,
      response: "",
      dialog: false,
      loading: false,
      device_ids: [],
      devices: [],
      sensors: [],
      plottings: [],
      draggingIndex: null,
      buildingPhotosPlottings: [],
      allPhotoPlottings: [],
      existingPlottings: [],
      IMG_PLOTTING_WIDTH: process?.env?.IMG_PLOTTING_WIDTH || "650px",
      IMG_PLOTTING_HEIGHT: process?.env?.IMG_PLOTTING_HEIGHT || "500px",

      panelOpenList: [],
      sensorTypesImages: [],
      selectedImageId: null,
    };
  },
  watch: {
    async dialog() {
      await this.getDevices();
      await this.getPlottingWithCustomerId();
      await this.getExistingPlottings();
      if (process) {
        this.IMG_PLOTTING_WIDTH = process?.env?.IMG_PLOTTING_WIDTH;
        this.IMG_PLOTTING_HEIGHT = process?.env?.IMG_PLOTTING_HEIGHT;
      }
    },
  },
  async created() {
    this.snackbar = true;
    this.response = "Sensor list is loading...Please wait....";
    await this.getSensorTypesImages();
    await this.getDevices();
    await this.getPlottingWithCustomerId();
    await this.getExistingPlottings();
    if (process) {
      this.IMG_PLOTTING_WIDTH = process?.env?.IMG_PLOTTING_WIDTH;
      this.IMG_PLOTTING_HEIGHT = process?.env?.IMG_PLOTTING_HEIGHT;
    }
    this.snackbar = false;
  },
  methods: {
    showContextMenu(event, selectedImageId) {
      this.menuX = event.clientX - 100;
      this.menuY = event.clientY - 100;
      this.menuVisible = true;
      this.selectedImageId = selectedImageId;
    },
    hideContextMenu() {
      this.menuVisible = false;
    },
    downloadImage() {
      const link = document.createElement("a");
      link.href = this.imageUrl;
      link.download = "image.jpg";
      link.click();
      this.menuVisible = false;
    },
    copyImageUrl() {
      navigator.clipboard.writeText(this.imageUrl);
      alert("Image URL copied!");
      this.menuVisible = false;
    },
    openInNewTab() {
      window.open(this.imageUrl, "_blank");
      this.menuVisible = false;
    },
    async getSensorTypesImages() {
      this.$axios
        .get("device_sensor_types_dropdown", {
          params: {
            // company_id: this.$auth.user.company_id,
          },
        })
        .then(({ data }) => {
          this.sensorTypesImages = data;
        });
    },
    getPlotIndex(sensorId) {
      let result = this.plottings.findIndex((e) => e.device_id == sensorId);

      return result;
    },
    getDeviceName(device_id) {
      return this.devices.find((e) => e.id == device_id)?.name || "";
    },
    getDeviceCategory(device_id) {
      return this.devices.find((e) => e.id == device_id)?.device_type || "---";
    },
    // checkIsSensorAddedAnyPhoto(verifyPlotting) {
    //   let matchCount = 0;

    //   this.buildingPhotosPlottings.forEach((building) => {
    //     building.photo_plottings.forEach((plotting) => {
    //       const sensors = plotting.plottings;
    //       sensors.forEach((sensor) => {
    //         if (
    //           sensor.sensor_id == verifyPlotting.sensor_id &&
    //           sensor.top !== "-500px"
    //         ) {
    //           matchCount++;
    //         }
    //       });
    //     });
    //   });

    //   return matchCount;
    // },
    checkIsSensorAddedAnyPhoto(verifyPlotting) {
      let matchCount = 0;
      //console.log(this.allPhotoPlottings);
      this.allPhotoPlottings.forEach((element) => {
        element.buildingPhotosPlottings.forEach((building) => {
          //console.log(building);

          building.photo_plottings.forEach((plotting) => {
            const sensors = plotting.plottings;
            sensors.forEach((sensor) => {
              if (
                sensor.top !== "-500px" &&
                sensor.device_id == verifyPlotting.device_id &&
                sensor.sensor_id == verifyPlotting.sensor_id
              ) {
                matchCount++;
              }
            });
          });
        });
      });

      return matchCount;
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

      this.device_ids = [];
      let i = 0;
      this.devices.forEach(async (element) => {
        this.device_ids.push(element.id);

        this.panelOpenList.push(i++);
      });

      await this.getSensors(this.device_ids);
    },
    async resetPlotting() {
      // let alarmEvents = this.plottings.filter((plott) => plott.top == "-500px");
      // this.plottings.forEach((element) => {
      //   console.log(element);
      // });
      // if (confirm("Are you sure you want to reset")) {
      //   this.loading = true;
      //   let config = {
      //     params: {
      //       company_id: this.$auth.user.company_id,
      //       customer_id: this.item.customer_id,
      //       customer_building_picture_id: this.item.id,
      //     },
      //   };
      //   let { data } = await this.$axios.post(`reset_plotting`, config.params);
      //   this.getExistingPlottings();
      //   this.loading = false;
      // }
    },
    async getPlottingWithCustomerId() {
      this.loading = true;
      let config = {
        params: {
          company_id: this.$auth.user.company_id,
          customer_id: this.item.customer_id,
        },
      };
      this.$axios.get(`plotting_with_customer_id`, config).then(({ data }) => {
        //console.log("data", data);

        this.allPhotoPlottings = data;
      });
    },
    async getExistingPlottings() {
      this.loading = true;
      let config = {
        params: {
          company_id: this.$auth.user.company_id,
          customer_id: this.item.customer_id,
          customer_building_picture_id: this.item.id,
        },
      };
      let { data } = await this.$axios.get(`plotting`, config);

      if (data) {
        this.existingPlottings = data.plottings;
        this.plottings = data.plottings;

        this.buildingPhotosPlottings = data.buildingPhotosPlottings;

        this.updatePlottingswithSensorTypeImage(this.plottings);
      }

      this.loading = false;
    },
    async getSensors(device_ids) {
      await this.getExistingPlottings();

      let config = {
        params: { device_ids },
      };
      let { data: sensors } = await this.$axios.get("sensor-list", config);

      this.sensors = sensors;
      //display on map
      this.plottings = sensors.map((sensor) => {
        let plotting = this.existingPlottings.find(
          (e) => e.sensor_id == sensor.id
        );
        return (
          plotting || {
            sensor_id: sensor.id,
            device_id: sensor.device_id,
            label: sensor.label,
            top: "-500px",
            left: "-500px",
          }
        );
      });
    },
    dragStart(event, index) {
      this.draggingIndex = index;
    },
    allowDrop(event) {
      event.preventDefault();
    },
    async onDrop(event) {
      const dropZoneRect = event.currentTarget.getBoundingClientRect();
      const offsetX = event.clientX - dropZoneRect.left;
      const offsetY = event.clientY - dropZoneRect.top;

      this.plottings[this.draggingIndex].left = `${offsetX}px`;
      this.plottings[this.draggingIndex].top = `${offsetY}px`;

      this.draggingIndex = null;

      await this.submit(false);
      await this.getDevices();
      await this.getPlottingWithCustomerId();
      await this.getExistingPlottings();
      if (process) {
        this.IMG_PLOTTING_WIDTH = process?.env?.IMG_PLOTTING_WIDTH;
        this.IMG_PLOTTING_HEIGHT = process?.env?.IMG_PLOTTING_HEIGHT;
      }
      this.snackbar = true;
      this.response = "Plotting Details are Sync position details";
    },
    async unmapSensor(draggingIndex) {
      try {
        if (confirm(`Are you sure you want to Unmap Sensor?`)) {
          // const dropZoneRect = event.currentTarget.getBoundingClientRect();
          // const offsetX = ""; // event.clientX - dropZoneRect.left;
          // const offsetY = event.clientY - dropZoneRect.top;

          this.plottings[draggingIndex].left = "-500px"; // `${offsetX}px`;
          this.plottings[draggingIndex].top = "-500px"; // `${offsetY}px`;

          this.draggingIndex = null;

          await this.submit(false);
          await this.getDevices();
          await this.getPlottingWithCustomerId();

          await this.getExistingPlottings();
          if (process) {
            this.IMG_PLOTTING_WIDTH = process?.env?.IMG_PLOTTING_WIDTH;
            this.IMG_PLOTTING_HEIGHT = process?.env?.IMG_PLOTTING_HEIGHT;
          }
          this.snackbar = true;
          this.response = "Unmap Sensor is updated successfully";
        }
      } catch (e) {}
    },
    async deleteOnDrop(event) {
      if (confirm(`Are you sure you want to delete`)) {
        const dropZoneRect = event.currentTarget.getBoundingClientRect();
        const offsetX = ""; // event.clientX - dropZoneRect.left;
        const offsetY = event.clientY - dropZoneRect.top;

        this.plottings[this.draggingIndex].left = "-500px"; // `${offsetX}px`;
        this.plottings[this.draggingIndex].top = "-500px"; // `${offsetY}px`;

        this.draggingIndex = null;

        await this.submit(false);
        await this.getDevices();
        await this.getPlottingWithCustomerId();

        await this.getExistingPlottings();
        if (process) {
          this.IMG_PLOTTING_WIDTH = process?.env?.IMG_PLOTTING_WIDTH;
          this.IMG_PLOTTING_HEIGHT = process?.env?.IMG_PLOTTING_HEIGHT;
        }
        this.snackbar = true;
        this.response = "Unmapping Sensor is Initialted....";
      }
    },

    async submit(status = true) {
      try {
        let { data } = await this.$axios.post(`plotting`, {
          customer_building_picture_id: this.item.id,
          plottings: this.plottings,
        });

        if (status) this.dialog = false;

        await this.getDevices();
        await this.getPlottingWithCustomerId();
        await this.getExistingPlottings();
        if (process) {
          this.IMG_PLOTTING_WIDTH = process?.env?.IMG_PLOTTING_WIDTH;
          this.IMG_PLOTTING_HEIGHT = process?.env?.IMG_PLOTTING_HEIGHT;
        }
        this.snackbar = true;
        this.response = "Plotting Details are updated successfully";
      } catch (error) {
        console.log(error);
      }
    },

    updatePlottingswithSensorTypeImage(plottings) {
      const allDevicesSensorTypeZones = this.devices.flatMap(
        (device) => device.sensorzones
      );

      plottings.forEach((element) => {
        // Find the matching sensor zone
        const element2 = allDevicesSensorTypeZones.find(
          (zone) => zone.id == element.sensor_id
        );

        // Default values
        // element.sensorImage =
        //   process.env.BACKEND_URL2 + "/sensor_type_icons/" + "other_sensor.png";

        element.sensorImage = "/sensor_type_icons/" + "other_sensor.png";
        element.sensorTypeName = "Unknown";

        if (element2) {
          const find = this.sensorTypesImages.find(
            (e) => e.name == element2.sensor_type
          );

          element.sensorImage =
            "/sensor_type_icons/" + (find ? find.image : "other_sensor.png");
          element.sensorTypeName = element2.sensor_type;
          // element.sensorImage =
          //   process.env.BACKEND_URL2 +
          //   "/sensor_type_icons/" +
          //   (find ? find.image : "other_sensor.png");
          // element.sensorTypeName = element2.sensor_type;
        }
      });
    },
    getSensorTypeRelaventImage(label, plottingObj) {
      for (const device of this.devices) {
        for (const element2 of device.sensorzones) {
          if (element2.id == plottingObj.sensor_id) {
            let find = this.sensorTypesImages.find(
              (e) => e.name == element2.sensor_type
            );

            return (
              "/sensor_type_icons/" + (find ? find.image : "other_sensor.png")
            );

            return (
              process.env.BACKEND_URL2 +
              "/sensor_type_icons/" +
              (find ? find.image : "other_sensor.png")
            );
          }
        }
      }

      return "/sensor_type_icons/" + "/other_sensor.png";
      return (
        process.env.BACKEND_URL2 + "/sensor_type_icons/" + "/other_sensor.png"
      ); // Default if no match

      // return this.$utils.getSensorTypeRelaventImage(
      //   this.sensorTypesImages,
      //   plottingObj.sensor_id
      // );
    },
    getRelaventImage(label, plottingObj) {
      return this.$utils.getRelaventImage(label);
      // let relaventImage = {
      //   Burglary: "/alarm-icons/burglary.png",
      //   Medical: "/alarm-icons/medical.png",
      //   Fire: "/alarm-icons/fire.png",
      //   Water: "/alarm-icons/water.png",
      //   Temperature: "/alarm-icons/temperature.png",
      //   Humidity: "/alarm-icons/humidity.png",
      // };
      // return relaventImage[label] ?? "Unknown";
    },
  },
};
</script>
