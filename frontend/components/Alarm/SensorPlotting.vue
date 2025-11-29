<template>
  <div class="text-centers">
    <v-dialog v-model="dialog" width="1000">
      <template v-slot:activator="{ on, attrs }">
        <div v-bind="attrs" v-on="on">
          <v-icon color="secondary" small> mdi-leak </v-icon>
          Sensor Plotting
        </div>
      </template>

      <v-card>
        <div class="text-center">
          <v-snackbar
            v-model="snackbar"
            top="top"
            color="primary"
            elevation="24"
          >
            {{ response }}
          </v-snackbar>
        </div>
        <v-card-title dense class="popup_background_noviolet">
          <span class="black--text"
            >Sensor Plotting - {{ item?.title || "---" }}
          </span>
          <v-spacer></v-spacer>
          <v-icon color="black" @click="dialog = false">mdi-close</v-icon>
        </v-card-title>

        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="6"
                ><v-row
                  ><v-col>
                    <v-col
                      cols="12"
                      style="position: relative"
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
                            draggable="true"
                            @dragstart="dragStart($event, index)"
                            style="width: 40px"
                            :src="getRelaventImage(plotting.label)"
                          ></v-img>
                        </div>
                      </span>
                    </v-col>

                    <v-col
                      cols="12"
                      style="
                        position: relative;
                        height: 50px;
                        border: 1px solid red;
                      "
                      class="dropzone"
                      @drop="deleteOnDrop"
                      @dragover="allowDrop"
                      >DELETE ZONE - Drag Icon here to delete
                    </v-col></v-col
                  ></v-row
                ></v-col
              >

              <v-col cols="6">
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
                  <v-col cols="12" v-if="device_ids.length == 0"
                    >Select Devices List</v-col
                  >
                  <v-col
                    class="ma-1"
                    cols="12"
                    v-for="(device_id, index) in device_ids"
                    :key="index"
                  >
                    <v-row no-gutters>
                      <v-col
                        cols="12"
                        style="border-radius: 3px"
                        class="py-2 px-3"
                      >
                        Device: {{ getDeviceName(device_id) }}

                        <div
                          v-for="(plotting, plotIndex) in plottings"
                          :key="plotIndex"
                        >
                          <div
                            v-if="device_id == plotting.device_id"
                            style="color: green"
                          >
                            <v-img
                              :title="plotting.label"
                              v-if="checkIsSensorAddedAnyPhoto(plotting) == 0"
                              draggable="true"
                              @dragstart="dragStart($event, plotIndex)"
                              style="width: 40px; float: left; margin: 10px"
                              :src="getRelaventImage(plotting.label)"
                            ></v-img>

                            <v-img
                              v-else
                              :title="plotting.label"
                              disabled="true"
                              draggable="false"
                              style="
                                width: 40px;
                                float: left;
                                margin: 10px;
                                filter: grayscale(100%);
                              "
                              :src="getRelaventImage(plotting.label)"
                            ></v-img>
                          </div>
                        </div>
                      </v-col>
                    </v-row>
                  </v-col>
                </v-row>

                <v-row>
                  <v-col cols="12" class="text-right align-right"
                    ><v-btn dense small color="primary" @click="submit(true)"
                      >Save and close</v-btn
                    ></v-col
                  >
                </v-row>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
export default {
  props: ["item"],
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
      buildingPhotosPlottings: [],

      existingPlottings: [],
      IMG_PLOTTING_WIDTH: process?.env?.IMG_PLOTTING_WIDTH || "800px",
      IMG_PLOTTING_HEIGHT: process?.env?.IMG_PLOTTING_HEIGHT || "800px",
    };
  },
  watch: {
    async dialog() {
      await this.getDevices();
      await this.getExistingPlottings();
      if (process) {
        this.IMG_PLOTTING_WIDTH = process?.env?.IMG_PLOTTING_WIDTH;
        this.IMG_PLOTTING_HEIGHT = process?.env?.IMG_PLOTTING_HEIGHT;
      }
    },
  },
  async created() {
    await this.getDevices();
    await this.getExistingPlottings();
    if (process) {
      this.IMG_PLOTTING_WIDTH = process?.env?.IMG_PLOTTING_WIDTH;
      this.IMG_PLOTTING_HEIGHT = process?.env?.IMG_PLOTTING_HEIGHT;
    }
  },
  methods: {
    getPlotIndex(sensorId) {
      let result = this.plottings.findIndex((e) => e.device_id == sensorId);

      return result;
    },
    getDeviceName(device_id) {
      return this.devices.find((e) => e.id == device_id).name || "";
    },

    checkIsSensorAddedAnyPhoto(verifyPlotting) {
      let matchCount = 0;

      this.buildingPhotosPlottings.forEach((building) => {
        building.photo_plottings.forEach((plotting) => {
          const sensors = plotting.plottings;
          sensors.forEach((sensor) => {
            if (
              sensor.device_id == verifyPlotting.device_id &&
              sensor.sensor_id == verifyPlotting.sensor_id &&
              sensor.top !== "-500px"
            ) {
              matchCount++;
            }
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
      await this.devices.forEach((element) => {
        this.device_ids.push(element.id);
      });
      console.log(this.device_ids);

      await this.getSensors(this.device_ids);
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
      await this.getExistingPlottings();
      if (process) {
        this.IMG_PLOTTING_WIDTH = process?.env?.IMG_PLOTTING_WIDTH;
        this.IMG_PLOTTING_HEIGHT = process?.env?.IMG_PLOTTING_HEIGHT;
      }
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
        await this.getExistingPlottings();
        if (process) {
          this.IMG_PLOTTING_WIDTH = process?.env?.IMG_PLOTTING_WIDTH;
          this.IMG_PLOTTING_HEIGHT = process?.env?.IMG_PLOTTING_HEIGHT;
        }
      }
    },

    async submit(status = true) {
      try {
        let { data } = await this.$axios.post(`plotting`, {
          customer_building_picture_id: this.item.id,
          plottings: this.plottings,
        });

        this.snackbar = true;
        this.response = "Plotting Details are updated successfully";
        if (status) this.dialog = false;
      } catch (error) {
        console.log(error);
      }
    },

    getRelaventImage(label) {
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
