<template>
  <NoAccess v-if="!$pagePermission.can('dashboard_view', this)" />
  <div v-else-if="!isMobileDevice">


    <div class="text-center">

      <v-snackbar centered color="secondary" elevation="24">
        {{ response }}


      </v-snackbar>
    </div>
    <v-dialog v-model="dialogParkingFull" width="500px">
      <v-card style="padding:0px!important">
        <v-card-title dense class="popup_background" style="background-color: red!important;padding:0px!important">
          Parking is Full
          <v-spacer></v-spacer>
          <v-btn icon @click="dialogParkingFull = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-img src="/parking-full.png" cover></v-img> </v-card-text></v-card>

    </v-dialog>
    <v-dialog v-model="snackbar" max-width="700px">
      <v-card>
        <v-card-title dense class="popup_background"
          :style="vehicleStatusEntryExit == 'exit' ? 'background-color: red!important;' : ''">
          Information - {{ $utils.caps(vehicleStatusEntryExit) }}
          <v-spacer></v-spacer>
          <v-btn icon @click=" snackbar = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text style="margin:auto">
          <div class="pa-2">



            <v-icon color="green" v-if="gatePassStatus">mdi-check-circle</v-icon>
            <v-icon color="red" v-else>mdi-alpha-x-circle</v-icon>

            {{ response }}
          </div>
          <div>
            <v-img v-if="vehicleGustNoEntryImage" :src="vehicleGustNoEntryImage" cover width="500px;"></v-img>
          </div>

        </v-card-text></v-card>

    </v-dialog>
    <!-- <v-dialog v-model="dialogImagePreview" max-width="600px">
      <v-card>
        <v-card-title dense class="popup_background">
          Image Preview
          <v-spacer></v-spacer>
          <v-btn icon @click="dialogImagePreview = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-img :src="dialogImageUrl" @click:outside="dialogImagePreview = false"></v-img>
        </v-card-text></v-card>

    </v-dialog> -->
    <v-row class="dashboard">
      <v-col style="max-width: 16.66%">

        <AudioSoundPlay v-if="activeAudio" :notificationsMenuItemsCount="mqttNewMessage ? 1 : 0" />
        <v-card elevation="2" style="height:80px; border-radius: 0.5rem"
          class="blue-border-bottom custom-card"><v-card-text><v-row style="height: 80px">
              <v-col class="d-flex1 justify-center" style="margin: auto; line-height: 20px; text-align: center">
                <div class="blue-text" style="font-size: 40px">{{ statisstics ? statisstics.totalRooms : 0 }}
                </div>
                <br />
                <div style="font-size: 16px">Car Wash Rooms</div>
              </v-col>
              <v-col class="d-flex justify-right" style="max-width: 100px">
                <div style="" class="image-box blue-border">
                  <v-icon color="blue">mdi-car</v-icon>
                </div>
              </v-col>
            </v-row></v-card-text></v-card>
      </v-col>
      <v-col style="max-width: 16.66%">
        <v-card elevation="2" style="height:80px; border-radius: 0.5rem"
          class="red-border-bottom custom-card"><v-card-text><v-row style="height: 80px">
              <v-col class="d-flex1 justify-center" style="margin: auto; line-height: 20px; text-align: center">
                <div class="red-text" style="font-size: 40px">{{ statisstics ? statisstics.occupiedRooms : 0 }}</div>
                <br />
                <div style="font-size: 16px">Occupied</div>
              </v-col>
              <v-col class="d-flex justify-right" style="max-width: 100px">
                <div style="" class="image-box red-border">
                  <v-icon color="red">mdi-car</v-icon>
                </div>
              </v-col>
            </v-row></v-card-text></v-card>
      </v-col>
      <v-col style="max-width: 16.66%">
        <v-card elevation="2" style="height: 80px; border-radius: 0.5rem"
          class="green-border-bottom custom-card"><v-card-text><v-row style="height: 80px">
              <v-col class="d-flex1 justify-center" style="margin: auto; line-height: 20px; text-align: center">
                <div class="green-text" style="font-size: 40px">{{ statisstics ? statisstics.availableRooms : 0 }}
                </div>
                <br />
                <div style="font-size: 16px">Empty</div>
              </v-col>
              <v-col class="d-flex justify-right" style="max-width: 100px">
                <div style="" class="image-box green-border">
                  <v-icon color="green">mdi-car</v-icon>
                </div>
              </v-col>
            </v-row></v-card-text></v-card>
      </v-col>
      <v-col style="max-width: 16.66%">
        <v-card elevation="2" style="height: 80px; border-radius: 0.5rem"
          class="yellow-border-bottom custom-card"><v-card-text><v-row style="height: 80px">
              <v-col class="d-flex1 justify-center" style="margin: auto; line-height: 20px; text-align: center">
                <div class="yellow-text" style="font-size: 40px">{{ statisstics ? statisstics.todayVehiclesCount : 0 }}
                </div>
                <br />
                <div style="font-size: 16px">Today Washing Count</div>
              </v-col>
              <v-col class="d-flex justify-right" style="max-width: 100px">
                <div style="" class="image-box yellow-border">
                  <v-icon color="yellow">mdi-car</v-icon>
                </div>
              </v-col>
            </v-row></v-card-text></v-card>
      </v-col><v-col style="max-width: 16.66%">
        <v-card elevation="2" style="height: 80px; border-radius: 0.5rem"
          class="purple-border-bottom custom-card"><v-card-text><v-row style="height: 80px">
              <v-col class="d-flex1 justify-center" style="margin: auto; line-height: 20px; text-align: center">
                <div class="purple-text" style="font-size: 40px">{{ statisstics ? statisstics.lessThanOneHour : 0
                }}
                </div>
                <br />
                <div style="font-size: 16px">Less than 1 Hour</div>
              </v-col>
              <v-col class="d-flex justify-right" style="max-width: 100px">
                <div style="" class="image-box purple-border">
                  <v-icon color="purple">mdi-clock</v-icon>
                </div>
              </v-col>
            </v-row></v-card-text></v-card>
      </v-col>
      <v-col style="max-width: 16.66%">
        <v-card elevation="2" style="height: 80px; border-radius: 0.5rem"
          class="teal-border-bottom custom-card"><v-card-text><v-row style="height: 80px">
              <v-col class="d-flex1 justify-center" style="margin: auto; line-height: 20px; text-align: center">
                <div class="teal-text" style="font-size: 40px">{{ statisstics ? statisstics.moreThanOneHour : 0 }}
                </div>
                <br />
                <div style="font-size: 16px">More than 1 Hour</div>
              </v-col>
              <v-col class="d-flex justify-right" style="max-width: 100px">
                <div style="" class="image-box teal-border">
                  <v-icon color="teal">mdi-clock</v-icon>
                </div>
              </v-col>
            </v-row></v-card-text></v-card>
      </v-col>


    </v-row>

    <DashboardRooms :updateKey="updateKey" />

    <!-- <v-dialog v-model="dialogMQTTVehicleInfo" max-width="80%">
      <v-card style="padding:0px!important">
        <v-card-title dense class="popup_background" style="background-color: #4caf50!important;padding:0px!important">
          New Vehicle Information
          <v-spacer></v-spacer>
          <v-btn icon @click="dialogMQTTVehicleInfo = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text> -->

    <NewVehiclePopupMqTT @updateDashboard="getStatistics()" />
    <!-- </v-card-text></v-card>
    </v-dialog> -->
    <!-- <v-btn @click="openGate" :disabled="this.status != 'Connected'" width="100%" height="50px" elevation="2" color="red"
      style="font-size: 25px;"> <v-icon size="40">mdi-boom-gate-arrow-up</v-icon> Open
      Gate {{ this.status != "Connected" ? ' - Error Gate is not Connected' : '' }}</v-btn> -->
    <v-row class="padding:0px">
      <v-col cols="12">
        <v-card style="height: 500px; overflow-y: auto; overflow-x: hidden" elevation="2"
          class="eventslistscroll table-font12"><v-card-text>
            <CarWashingReports :showFilters="true" :key="mqttNewMessage?.response.record.id || 1" />
          </v-card-text></v-card>
      </v-col>
    </v-row>


  </div>

</template>

<script>


import AudioSoundPlay from "../../components/Alarm/AudioSoundPlay.vue";

import DashboardRooms from "../../components/CarWashing/DashboardRooms.vue";
import NewVehiclePopupMqTT from "../../components/CarWashing/NewVehiclePopupMqTT.vue";
import CarWashingReports from "../../components/CarWashing/CarWashingReports.vue";


export default {
  components: {
    AudioSoundPlay, DashboardRooms, NewVehiclePopupMqTT, CarWashingReports
  },
  data: () => ({
    dialogMQTTVehicleInfo: false,
    vehicle_notification_status: "",
    dialogParkingFull: false,
    gatePassStatus: false,
    mqttLoading: false,
    activeAudio: false,
    client: null,
    status: "Disconnected",
    message: "",
    Openkey: 1,
    Closedkey: 1,
    Forwardkey: 1,

    isMobileDevice: false,
    mapHeight: 600,
    windowHeight: 1000,
    key: 1,
    date_from: "",
    data: [],
    chartEventOpenStatistics: null,
    chartEventClosedStatistics: null,

    chartEventForwardStatistics: null,
    categoriesStats: null,
    customerStatusData: null,
    apiLoading: false,
    cancelgetEventCategoriesStatsToken: null,
    cancelgetEventsTypeStatsToken: null,
    cancelgetEventsOpenCountStatusToken: null,
    loadAllEventsTable: false,
    interval: null,

    mqttNewMessage: null,

    eventVehicleDetails: null,

    dialogImagePreview: false,
    dialogImageUrl: "",
    snackbar: false,
    response: "",
    statisstics: null,
    paymentCompaltedStatus: false,
    vehicleStatusEntryExit: '',
    vehicleGustNoEntryImage: null,
    updateKey: 1,

  }),


  beforeDestroy() {
    if (this.interval) clearInterval(this.interval);


    if (this.client) {
      this.client.end();
    }

  },
  mounted() {
    try {
      if (window)
        if (window.__APP_CONFIG__.PARKING_MODE == true || window.__APP_CONFIG__.PARKING_MODE == 'true') {

          this.$router.push('/parking/dashboard');

          return;
        }
    } catch (ex) { }

    this.getStatistics();
    // this.getDashboardData();

    //this.initMqtt();


    // this.mqttNewMessage = {
    //   "response": {
    //     "record": {
    //       "id": 123,
    //       "company_id": 8,
    //       "log_timestamp": "20250903163928293",
    //       "log_vehicle_number": "F53310",
    //       "in_background_file_name": "20250908200916528_F53310_VEHICLE_DETECTION_XTP100002_FORWARD_EMI_BACKGROUND.jpg",
    //       "out_background_file_name": "20250908200916528_F53310_VEHICLE_DETECTION_XTP100002_FORWARD_EMI_BACKGROUND.jpg",
    //       "in_time": "2025-09-03 16:39:28",
    //       "out_time": "2025-09-03 16:39:28",
    //       "duration_in_minutes": 200,
    //       "total_amount": 20,
    //       "payment_mode": null,
    //       "membership_id": 1,
    //       "cancel_status": 0,
    //       "cancel_reason": null,
    //       "raw_device_no": "XTP100001",
    //       "raw_capture_time": "09-08-2025 20:09:16",
    //       "raw_plate_no": "F53310",
    //       "raw_vehicle_color": "White",
    //       "raw_vehicle_type": null,
    //       "raw_vehicle_brand": null,
    //       "raw_moving_direction": "Forward",
    //       "raw_validity": "96%",
    //       "raw_country_region": "DXB",
    //       "raw_plate_color": "White",
    //       "raw_plate_size": "Long",
    //       "raw_plate_type": "Private",
    //       "raw_province": "Unknown",
    //       "raw_camera_no": null,
    //       "raw_info": "{\"camera_info\":\"Camera info 1\",\"device_no\":\"XTP100001\",\"capture_time\":\"09-08-2025 20:09:16\",\"plate_no\":\"F53310\",\"vehicle_color\":\"White\",\"vehicle_type\":null,\"vehicle_brand\":null,\"moving_direction\":\"Forward\",\"validity\":\"96%\",\"country_region\":\"DXB\",\"plate_color\":\"White\",\"plate_size\":\"Long\",\"plate_type\":\"Private\",\"province\":\"Unknown\",\"category\":\"F\",\"camera_number\":\":CameraNumber 1\"}",
    //       "raw_event_category": "VEHICLE",
    //       "raw_event_type": "DETECTION",
    //       "raw_camera_code": "XTP100002",
    //       "raw_direction": "FORWARD",
    //       "raw_lane": "ent",
    //       "created_at": "2025-09-13T12:46:41.000000Z",
    //       "updated_at": "2025-09-13T12:46:41.000000Z",
    //       "device_id_in": 99,
    //       "device_id_out": 99,
    //       "duration_in_hours": 2,
    //       "function": "out",
    //       "duration_per_hour_amount": 4,
    //       "manual_gate_opened_at": null,
    //       "automatic_gate_opened_at": null,
    //       "acknowledged_from_device_at": null,
    //       "device_serrial_number": "XTP100001",
    //       "is_membership": 1,
    //       "membership_status": "Active",
    //       "gate_open_automatically": "Tenant  is Active  - Gate Open Automatically",
    //       "membership_start_date": "2025-09-01",
    //       "membership_end_date": "2026-09-30",
    //       "member_type": "Tenant",
    //       "public_image_url": "http://127.0.0.1:8000/parking_camera_logs/8",
    //       "parking_image_path": "http://127.0.0.1:8000/parking_camera_logs/8",
    //       "parking_allowed_status": true
    //     },
    //     "message": "success2",
    //     "status": true
    //   }
    // }


  },




  methods: {

    UpdateStatusfromComponent(data) {
      this.dialogMQTTVehicleInfo = data.dialogStatus;
      this.getStatistics();
    },
    /*
        async openGate(timeout = 5000, trigger = 'manual') {
          this.$axios.post("/parking_open_gate", {
            company_id: this.$auth.user.company_id,
            event_id: this.mqttNewMessage.response.record.id,
            device_id: this.mqttNewMessage.response.record.device_id_out,
            device_serial_number: this.mqttNewMessage.response.record.device_serrial_number,
            function: this.mqttNewMessage.response.record.function,

            parking_gate_close_time: this.$auth.user.company.parking_gate_close_time,



            trigger: trigger

          }).then((response) => {
            console.log(response);
            this.snackbar = true;
            this.response = "Gate open command is sent successfully.";

            setTimeout(() => {
              this.snackbar = false;
              this.mqttNewMessage = null;
            }, timeout);

          }).catch((error) => {
            this.snackbar = true;
            // this.response = error.message;
            this.response = "Error occurred while sending open gate command.";

          });
        },*/
    openImage(imageUrl) {
      this.dialogImagePreview = true;
      this.dialogImageUrl = imageUrl;
    },
    /*
    async initMqtt() {
      // Example: ws://test.mosquitto.org:8080
      const options = {
        clientId: "xtremeparking_" + Math.random().toString(16).substr(2, 8),
        clean: true,
        reconnectPeriod: 1000,
      };
      // const host = this.$env.settings.MQTT_SOCKET_HOST; // "wss://mqtt.xtremeguard.org:8084"; // If TLS WebSocket is available




      const { data } = await this.$axios.get(`/get_mqtt_server`);


      // this.client = mqtt.connect(host, options);

      this.client = mqtt.connect(data.host, options);


      const newEventTopic = "xtremeparking/" + this.$auth.user.company_id + "/cameralogs/new_event";

      this.client.on("connect", () => {
        this.status = "Connected";
        this.client.subscribe(newEventTopic, (err) => {
          if (!err) {
            console.log("Subscribed to", newEventTopic);
          }
        });
      });

      this.client.on("message", (topic, message) => {

        this.mqttLoading = true;
        this.message = message.toString();
        console.log("MQTT Received message:", this.message);
        try {

          this.dialogMQTTVehicleInfo = true;

          this.activeAudio = true;
          this.gatePassStatus = false;

          this.snackbar = false;

          this.mqttNewMessage = JSON.parse(this.message);

          // this.mqttNewMessage.response.record.image_background =
          //   this.mqttNewMessage.response.record.public_image_url + "/" +
          //   this.mqttNewMessage.response.record.in_background_file_name;

          this.vehicleGustNoEntryImage = null;

          if (!this.mqttNewMessage.response.status) //error
          {
            this.snackbar = true;
            this.vehicle_notification_status = this.mqttNewMessage.response.message;
            this.response = this.mqttNewMessage.response.record.message;

            this.vehicleStatusEntryExit = "entry";

            this.vehicleGustNoEntryImage =

              this.mqttNewMessage.response.record.image.replace("_BACKGROUND", "_VEHICLE");

          }

          if (this.mqttNewMessage.response.record.out_time == null) {
            this.vehicleStatusEntryExit = "entry";

            // Entry

            if (this.mqttNewMessage.response.record.in_background_file_name) {
              this.mqttNewMessage.response.record.image_vehicle =
                this.mqttNewMessage.response.record.public_image_url + "/" +
                this.mqttNewMessage.response.record.in_background_file_name.replace("_BACKGROUND", "_VEHICLE");

              this.mqttNewMessage.response.record.image_number_plate =
                this.mqttNewMessage.response.record.public_image_url + "/" +
                this.mqttNewMessage.response.record.in_background_file_name.replace("_BACKGROUND", "_PLATE");

            }
          }
          else {
            this.vehicleStatusEntryExit = "exit";

            if (this.mqttNewMessage.response.record.out_background_file_name) {
              this.mqttNewMessage.response.record.image_vehicle =
                this.mqttNewMessage.response.record.public_image_url + "/" +
                this.mqttNewMessage.response.record.out_background_file_name.replace("_BACKGROUND", "_VEHICLE");

              this.mqttNewMessage.response.record.image_number_plate =
                this.mqttNewMessage.response.record.public_image_url + "/" +
                this.mqttNewMessage.response.record.out_background_file_name.replace("_BACKGROUND", "_PLATE");
            }
          }

          //messsage

          if (this.mqttNewMessage?.response.record.membership_status == 'Membership Expired') {
            this.snackbar = true;
            this.response = "Membership Expired. Please pay the parking fee.";
          }

          // console.log("gate_open_automatically", this.mqttNewMessage?.response.record.gate_open_automatically);
          if (this.mqttNewMessage?.response.record.gate_open_automatically) {
            setTimeout(() => {

              this.snackbar = true;
              this.response = this.mqttNewMessage?.response.record.gate_open_automatically;

              this.gatePassStatus = true;

              setTimeout(() => {
                this.mqttNewMessage = null;
              }, 1000 * 5);
            }, 1000 * 3);

            setTimeout(() => {

              this.snackbar = false;
              this.response = "";

            }, 1000 * 10);
          }
          else
            this.gatePassStatus = false;
          setTimeout(() => {
            this.activeAudio = false;
          }, 1000 * 10);



        } catch (ex) {

          console.error("MQTT processing error:", ex);

          this.mqttNewMessage = null;
          this.snackbar = true;
          this.response = `Error: ${ex.message || "Error occurred while processing MQTT message."}`;


        }

        this.getStatistics();

        setTimeout(() => {
          this.mqttLoading = false;
        }, 1000 * 5);

      });

      this.client.on("error", (err) => {
        console.error("MQTT Error:", err);
        this.status = "Error";
      });

      this.client.on("close", () => {
        this.status = "Disconnected";
      });



    },*/
    sendMessage() {
      if (this.client && this.client.connected) {
        this.client.publish("test/vue2", "Hello from Vue2 + MQTT");
      }
    },

    async getStatistics() {


      try {
        const options = {
          params: {
            company_id: this.$auth.user.company_id,

          },

        };

        const { data } = await this.$axios.get(`/dashboard_carwashingstatistics`, options);



        // Update data
        if (data) {
          this.statisstics = data;
        }

        // if (this.statisstics.total_available <= 0) {
        //   this.dialogParkingFull = true;

        // }

        this.updateKey++;

      } catch (error) {
        this.snackbar = true;
        // this.response = error.message;
        this.response = "Error occurred while processing statistics.";

      }
    },
    /*
    async paymentProcess(paymentMethod, id) {
      console.log("Processing payment for method:", paymentMethod);

      if (confirm("Are you sure want to Update Payment?")) {

        try {
          const options = {
            params: {
              company_id: this.$auth.user.company_id,
              id: id,
              payment_mode: paymentMethod
            },

          };

          const { data } = await this.$axios.post(`/parking_payment_process`, options.params);

          console.log(data);


          // Update data
          if (data.status) {
            this.snackbar = true;
            this.response = "Payment is updated successfully And Gate is Open Now";

            this.gatePassStatus = true;
            // this.openGate(8000, 'automatic');


            setTimeout(() => {
              this.mqttNewMessage = null;
              this.snackbar = false;

            }, 1000 * 5);
          }

        } catch (error) {
          this.snackbar = true;
          // this.response = error.message;
          this.response = "Error occurred while processing payment.";

        }
      }
    }*/
  },
};
</script>
