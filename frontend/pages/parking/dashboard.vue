<template>
  <NoAccess v-if="!$pagePermission.can('dashboard_view', this)" />
  <div v-else-if="!isMobileDevice">


    <!-- <div class="text-center">

      <v-snackbar centered color="secondary" elevation="24">
        {{ response }}


      </v-snackbar>
    </div> -->
    <v-dialog v-model="dialogBlocked" max-width="700px" persistent>
      <v-card>
        <v-card-title dense class="popup_background" style="background-color: red!important">
          Information - {{ $utils.caps(vehicleStatusEntryExit) }} - blocked vehicle
          <v-spacer></v-spacer>
          <v-btn icon @click=" dialogBlocked = false">
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

            <!-- <v-img v-if="vehicleGustNoEntryImage" :src="vehicleGustNoEntryImage" cover width="500px;"></v-img> -->
            <v-img :src="dialogImageUrl" cover width="100%;"></v-img>
          </div>

        </v-card-text></v-card>

    </v-dialog>
    <!-- <v-dialog v-model="dialogBlocked" width="800px" persistent>
      <v-card style="padding:0px!important">
        <v-card-title dense class="popup_background" style="background-color: red!important;padding:0px!important">
          Blocked Vehicle
          <v-spacer></v-spacer>
          <v-btn icon @click="dialogBlocked = false">
            <v-img :src="dialogImageUrl"></v-img>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-img src="/parking-full.png" cover></v-img> </v-card-text></v-card>

    </v-dialog> -->
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
    <!-- <v-dialog v-model="snackbar" max-width="700px">
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

    </v-dialog> -->
    <v-dialog v-model="dialogImagePreview" max-width="600px">
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

    </v-dialog>
    <v-row class="dashboard">
      <v-col style="max-width: 16.66%">

        <AudioSoundPlay v-if="activeAudio" :notificationsMenuItemsCount="mqttNewMessage ? 1 : 0" />
        <v-card elevation="2" style="height: 130px; border-radius: 0.5rem"
          class="blue-border-bottom custom-card"><v-card-text><v-row style="height: 130px">
              <v-col class="d-flex1 justify-center" style="margin: auto; line-height: 20px; text-align: center">
                <div class="blue-text" :key="statsKey" :mqttNewMessage="mqttNewMessage" style="font-size: 40px">{{
                  statisstics ?
                    statisstics.total_parking_count : 0 }}
                </div>
                <br />
                <div style="font-size: 16px">Total Parking</div>
              </v-col>
              <v-col class="d-flex justify-right" style="max-width: 150px">
                <div style="" class="image-box blue-border">
                  <v-icon color="blue">mdi-car</v-icon>
                </div>
              </v-col>
            </v-row></v-card-text></v-card>
      </v-col>
      <v-col style="max-width: 16.66%">
        <v-card elevation="2" style="height: 130px; border-radius: 0.5rem"
          class="red-border-bottom custom-card"><v-card-text><v-row style="height: 130px">
              <v-col class="d-flex1 justify-center" style="margin: auto; line-height: 20px; text-align: center">
                <div class="red-text" :key="statsKey" :mqttNewMessage="mqttNewMessage" style="font-size: 40px">{{
                  statisstics ? statisstics.total_parked
                    : 0 }}</div>
                <br />
                <div style="font-size: 16px">Occupied</div>
              </v-col>
              <v-col class="d-flex justify-right" style="max-width: 150px">
                <div style="" class="image-box red-border">
                  <v-icon color="red">mdi-car</v-icon>
                </div>
              </v-col>
            </v-row></v-card-text></v-card>
      </v-col>
      <v-col style="max-width: 16.66%">
        <v-card elevation="2" style="height: 130px; border-radius: 0.5rem"
          class="green-border-bottom custom-card"><v-card-text><v-row style="height: 130px">
              <v-col class="d-flex1 justify-center" style="margin: auto; line-height: 20px; text-align: center">
                <div class="green-text" :key="statsKey" :mqttNewMessage="mqttNewMessage" style="font-size: 40px">{{
                  statisstics ?
                    statisstics.total_available : 0 }}
                </div>
                <br />
                <div style="font-size: 16px">Available</div>
              </v-col>
              <v-col class="d-flex justify-right" style="max-width: 150px">
                <div style="" class="image-box green-border">
                  <v-icon color="green">mdi-car</v-icon>
                </div>
              </v-col>
            </v-row></v-card-text></v-card>
      </v-col>
      <v-col style="max-width: 16.66%">
        <v-card elevation="2" style="height: 130px; border-radius: 0.5rem"
          class="yellow-border-bottom custom-card"><v-card-text><v-row style="height: 130px">
              <v-col class="d-flex1 justify-center" style="margin: auto; line-height: 20px; text-align: center">
                <div class="yellow-text" :key="statsKey" :mqttNewMessage="mqttNewMessage" style="font-size: 40px">{{
                  statisstics ?
                    statisstics.vehicle_count_today : 0 }}
                </div>
                <br />
                <div style="font-size: 16px">Today Count</div>
              </v-col>
              <v-col class="d-flex justify-right" style="max-width: 150px">
                <div style="" class="image-box yellow-border">
                  <v-icon color="yellow">mdi-car</v-icon>
                </div>
              </v-col>
            </v-row></v-card-text></v-card>
      </v-col>
      <v-col style="max-width: 16.66%">
        <v-card elevation="2" style="height: 130px; border-radius: 0.5rem"
          class="darkgreen-border-bottom custom-card"><v-card-text><v-row style="height: 130px">
              <v-col class="d-flex1 justify-center" style="margin: auto; line-height: 20px; text-align: center">
                <div class="darkgreen-text" :key="statsKey" :mqttNewMessage="mqttNewMessage" style="font-size: 40px">{{
                  statisstics ?
                    statisstics.total_payments : 0
                }}</div>
                <br />
                <div style="font-size: 16px">Total Amount</div>
              </v-col>
              <v-col class="d-flex justify-right" style="max-width: 150px">
                <div style="" class="image-box darkgreen-border">
                  <v-icon color="green">mdi-cash-100</v-icon>
                </div>
              </v-col>
            </v-row></v-card-text></v-card> </v-col><v-col style="max-width: 16.66%">
        <v-card elevation="2" style="height: 130px; border-radius: 0.5rem"
          class="sos-border-bottom custom-card"><v-card-text>
            <v-row style="height: 45px">
              <v-col style="padding: 5px">
                <div style="font-size: 18px">Devices</div>
              </v-col>
            </v-row>

            <v-row>
              <v-col style="
                  max-width: 50px;

                  text-align: center;

                  font-weight: bold;
                ">
                <v-icon size="60" style="margin-top:-28px;" color="yellow">mdi-boom-gate</v-icon>
              </v-col>
              <v-col style="
                  padding: 0px;
                  text-align: center;

                  font-weight: bold;
                ">
                <div style="font-size: 30px; color: #06b050">
                  {{ statisstics ? statisstics.devices_online_count : 0 }}
                </div>
                <div style="color: #06b050">Online</div>
              </v-col>
              <v-col style="
                  padding: 0px;
                  text-align: center;

                  font-weight: bold;
                ">
                <div style="font-size: 30px; color: red">
                  {{ statisstics ? statisstics.devices_offline_count : 0 }}
                </div>
                <div style="color: red">Offline</div>
              </v-col>
            </v-row></v-card-text></v-card>
      </v-col>
    </v-row>

    <v-row class="pt-0 mt-0">
      <v-col cols="9" class="pt-0 mt-0">

        <!-- <RtspLiveCamera :ws-port="8082" title="Camera 1 Live"></RtspLiveCamera>
            -->

        <RtspLiveCamerasList :mqttNewMessage="mqttNewMessage" :mqttLoading="mqttLoading"></RtspLiveCamerasList>
        <!-- <NewVehiclePopupMqTT @updateDashboard="getStatistics()" /> -->
        <!-- <v-card elevation="2" class="eventslistscroll table-font12" :loading="mqttLoading">
          <v-card-text class="pa-1">
            <img v-if="mqttNewMessage?.response.record.image_vehicle"
              style="width: 100%;height:700px;border-radius: 10px;"
              :src="mqttNewMessage.response.record.image_vehicle"></img>

            <img v-else style="width: 100%;height:700px;;border-radius: 10px;" src="/novehicle.png"></img>
          </v-card-text>
        </v-card> -->
      </v-col>

      <v-col cols="3" class="pt-0 mt-0">
        <DashboardVehicleInfo @paymentProcess="paymentProcess" :mqttNewMessage="mqttNewMessage"
          :gatePassStatus="gatePassStatus" @openGate="openGate()" :snackbarColor="snackbarColor" :snackbar="snackbar"
          :response="response" />



      </v-col>

    </v-row>

    <!-- <v-btn @click="openGate" :disabled="this.status != 'Connected'" width="100%" height="50px" elevation="2" color="red"
      style="font-size: 25px;"> <v-icon size="40">mdi-boom-gate-arrow-up</v-icon> Open
      Gate {{ this.status != "Connected" ? ' - Error Gate is not Connected' : '' }}</v-btn> -->
    <v-row class="padding:0px">
      <v-col cols="12">
        <v-card style="height: 500px; overflow-y: auto; overflow-x: hidden" elevation="2"
          class="eventslistscroll table-font12"><v-card-text>
            <ParkingReports :showFilters="true" :key="loadingKey" />
          </v-card-text></v-card>
      </v-col>
    </v-row>


  </div>

</template>

<script>


import AudioSoundPlay from "../../components/Alarm/AudioSoundPlay.vue";
import ParkingReports from "../../components/Parking/ParkingReports.vue";
import mqtt from "mqtt";
import RtspLiveCamera from "../../components/Parking/RtspLiveCamera.vue";
import RtspLiveCamerasList from "../../components/Parking/RtspLiveCamerasList.vue";
import NewVehiclePopupMqTT from "../../components/CarWashing/NewVehiclePopupMqTT.vue";
import DashboardVehicleInfo from "../../components/Parking/DashboardVehicleInfo.vue";

export default {
  components: {
    ParkingReports, AudioSoundPlay, RtspLiveCamera, RtspLiveCamerasList, NewVehiclePopupMqTT, DashboardVehicleInfo
  },
  data: () => ({
    dialogBlocked: false,
    snackbarColor: "green",
    loadingKey: 1,
    statsKey: 1,

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
  }),


  beforeDestroy() {
    if (this.interval) clearInterval(this.interval);


    // if (this.client) {
    //   this.client.end();
    // }

  },
  created() {
    // Detect mobile device
    this.initMqtt();


  },
  mounted() {

    try {
      if (window)
        if (window.__APP_CONFIG__.PARKING_MODE == false || window.__APP_CONFIG__.PARKING_MODE == 'false') {

          this.$router.push('/carwashing/dashboard');
        }
    } catch (ex) { }
    this.getStatistics();
    // this.getDashboardData();

    this.initMqtt();




    this.mqttNewMessage = {
      "response": {
        "record": {
          "id": 123,
          "company_id": 8,
          "log_timestamp": "20250903163928293",
          "log_vehicle_number": "F53310",
          "in_background_file_name": "20250908200916528_F53310_VEHICLE_DETECTION_XTP100002_FORWARD_EMI_BACKGROUND.jpg",
          "out_background_file_name": "20250908200916528_F53310_VEHICLE_DETECTION_XTP100002_FORWARD_EMI_BACKGROUND.jpg",
          "in_time": "2025-09-03 16:39:28",
          "out_time": "2025-09-03 16:39:28",
          "duration_in_minutes": 200,
          "total_amount": 20,
          "payment_mode": null,
          "membership_id": 1,
          "cancel_status": 0,
          "cancel_reason": null,
          "raw_device_no": "XTP100001",
          "raw_capture_time": "09-08-2025 20:09:16",
          "raw_plate_no": "F53310",
          "raw_vehicle_color": "White",
          "raw_vehicle_type": null,
          "raw_vehicle_brand": null,
          "raw_moving_direction": "Forward",
          "raw_validity": "96%",
          "raw_country_region": "DXB",
          "raw_plate_color": "White",
          "raw_plate_size": "Long",
          "raw_plate_type": "Private",
          "raw_province": "Unknown",
          "raw_camera_no": null,
          "raw_info": "{\"camera_info\":\"Camera info 1\",\"device_no\":\"XTP100001\",\"capture_time\":\"09-08-2025 20:09:16\",\"plate_no\":\"F53310\",\"vehicle_color\":\"White\",\"vehicle_type\":null,\"vehicle_brand\":null,\"moving_direction\":\"Forward\",\"validity\":\"96%\",\"country_region\":\"DXB\",\"plate_color\":\"White\",\"plate_size\":\"Long\",\"plate_type\":\"Private\",\"province\":\"Unknown\",\"category\":\"F\",\"camera_number\":\":CameraNumber 1\"}",
          "raw_event_category": "VEHICLE",
          "raw_event_type": "DETECTION",
          "raw_camera_code": "XTP100002",
          "raw_direction": "FORWARD",
          "raw_lane": "ent",
          "created_at": "2025-09-13T12:46:41.000000Z",
          "updated_at": "2025-09-13T12:46:41.000000Z",
          "device_id_in": 99,
          "device_id_out": 99,
          "duration_in_hours": 2,
          "function": "out",
          "duration_per_hour_amount": 4,
          "manual_gate_opened_at": null,
          "automatic_gate_opened_at": null,
          "acknowledged_from_device_at": null,
          "device_serrial_number": "XTP100001",
          "is_membership": 1,
          "membership_status": "Active",
          "gate_open_automatically": "Tenant  is Active  - Gate Open Automatically",
          "membership_start_date": "2025-09-01",
          "membership_end_date": "2026-09-30",
          "member_type": "Tenant",
          "public_image_url": "http://127.0.0.1:8000/parking_camera_logs/8",
          "parking_image_path": "http://127.0.0.1:8000/parking_camera_logs/8",
          "parking_allowed_status": true
        },
        "message": "success2",
        "status": true
      }
    }




  },


  methods: {

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
        // console.log(response);
        this.snackbar = true;
        this.snackbarColor = "green";

        this.response = "Gate open command is sent successfully.";

        setTimeout(() => {
          this.snackbar = false;
          this.mqttNewMessage = null;
        }, timeout);

      }).catch((error) => {
        this.snackbar = true;
        // this.response = error.message;
        this.snackbarColor = "red";
        this.response = "Error occurred while sending open gate command.";

      });
    },
    openImage(imageUrl) {
      this.dialogImagePreview = true;
      this.dialogImageUrl = imageUrl;
    },
    async initMqtt() {

      console.log("this.$env", this.$env);


      if (this.$env?.settings) {
        // Example: ws://test.mosquitto.org:8080
        const options = {
          clientId: "xtremeparking_" + Math.random().toString(16).substr(2, 8),
          clean: true,
          reconnectPeriod: 1000,
        };
        // const host = this.$env.settings.MQTT_SOCKET_HOST; // "wss://mqtt.xtremeguard.org:8084"; // If TLS WebSocket is available




        // const { data } = await this.$axios.get(`/get_mqtt_server`);
        // if (data.host.includes("192.168.") || data.host.includes("localhost") || data.host.includes("127.0.0.1")) {

        // }
        // else {
        //   options.protocol = 'wss';
        // }


        // this.client = mqtt.connect(host, options);

        // this.client = mqtt.connect(data.host, options);


        console.log(" this.$env.settings.MQTT_SOCKET_HOST)", this.$env.settings.MQTT_SOCKET_HOST);

        const host = this.$env.settings.MQTT_SOCKET_HOST;

        // if (host.includes("192.168.") || host.includes("localhost") || host.includes("127.0.0.1")) {

        // }
        // else {
        //   options.protocol = 'wss';
        // }


        if (this.$env.settings.MQTT_SSL && this.$env.settings.MQTT_SSL == true) {
          options.protocol = 'wss';
        }
        if (this.$env.settings.MQTT_SOCKET_HOST == "165.22.222.17") {
          options.protocol = 'wss';
        }
        // this.client = mqtt.connect(host, options);
        console.log("MQTT Host:", host);
        this.client = mqtt.connect(host, options);

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
          // console.log("MQTT Received message:", this.message);
          try {

            this.activeAudio = true;
            this.gatePassStatus = false;

            this.snackbar = false;

            this.mqttNewMessage = JSON.parse(this.message);

            this.statsKey++;

            // this.mqttNewMessage.response.record.image_background =
            //   this.mqttNewMessage.response.record.public_image_url + "/" +
            //   this.mqttNewMessage.response.record.in_background_file_name;

            this.vehicleGustNoEntryImage = null;

            if (!this.mqttNewMessage.response.status) //error
            {
              this.snackbar = true;
              this.snackbarColor = "red";

              this.vehicle_notification_status = this.mqttNewMessage.response.message;
              this.response = this.mqttNewMessage.response.record.message;

              this.vehicleStatusEntryExit = "entry";

              let vehicleGustNoEntryImage =

                this.mqttNewMessage.response.record.image.replace("_BACKGROUND", "_VEHICLE");

              console.log("this.mqttNewMessage.response", this.mqttNewMessage.response.record);



              let LiveImagePath = "http://" + this.$env.settings.BACKEND_URL2 + "/api/parking_camera_logs/" + this.$auth.user.company_id + "/";

              LiveImagePath =
                LiveImagePath.replace("_BACKGROUND", "_VEHICLE");


              if (this.mqttNewMessage.response.record.blocked == true) {
                this.dialogBlocked = true;
                this.dialogImageUrl = vehicleGustNoEntryImage;

                console.log("Blocked vehicle image url:", this.dialogImageUrl);



                //                 this.response += `
                //   - <a href="/parking/blacklistlogs" style="color:#1976d2;text-decoration:underline">
                //     Reports
                //   </a>
                // `;
              }

              else this.dialogBlocked = false;

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
              // console.log(this.mqttNewMessage.response);
              this.snackbar = true;
              this.snackbarColor = "yellow";

              this.response = "Exit vehicle - Payment Pending";

              if (this.mqttNewMessage.response.record.out_background_file_name) {
                this.mqttNewMessage.response.record.image_vehicle =
                  this.mqttNewMessage.response.record.public_image_url + "/" +
                  this.mqttNewMessage.response.record.out_background_file_name.replace("_BACKGROUND", "_VEHICLE");

                this.mqttNewMessage.response.record.image_number_plate =
                  this.mqttNewMessage.response.record.public_image_url + "/" +
                  this.mqttNewMessage.response.record.out_background_file_name.replace("_BACKGROUND", "_PLATE");
              }

              setTimeout(() => {

                this.snackbar = false;
                this.snackbarColor = "green";

                this.response = "";

              }, 1000 * 10);
            }

            //messsage

            if (this.mqttNewMessage?.response.record.membership_status == 'Membership Expired') {
              this.snackbar = true;
              this.snackbarColor = "red";
              this.response = "Membership Expired. Please pay the parking fee.";
            }

            // console.log("gate_open_automatically", this.mqttNewMessage?.response.record.gate_open_automatically);
            if (this.mqttNewMessage?.response.record.gate_open_automatically) {
              setTimeout(() => {

                this.snackbar = true;
                this.snackbarColor = "green";
                this.response = this.mqttNewMessage?.response.record.gate_open_automatically;

                this.gatePassStatus = true;

                setTimeout(() => {
                  this.mqttNewMessage = null;
                }, 1000 * 5);
              }, 1000 * 3);

              setTimeout(() => {
                this.snackbarColor = "green";

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
            this.snackbarColor = "red";

            this.response = `Error: ${ex.message || "Error occurred while processing MQTT message."}`;


          }

          this.getStatistics();

          setInterval(() => {
            this.getStatistics();
          }, 1000 * 60);

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

      } else {
        console.warn("MQTT settings are not received");
      }

    },
    sendMessage() {
      if (this.client && this.client.connected) {
        this.client.publish("test/vue2", "Hello from Vue2 + MQTT");
      }
    },

    async getStatistics() {


      //this.loadingKey++;

      try {
        const options = {
          params: {
            company_id: this.$auth.user.company_id,

          },
          timeout: 1000 * 10 //10 seconds
        };

        const { data } = await this.$axios.get(`/parking_dashboard_statistics`, options);



        // Update data
        if (data) {
          this.statisstics = data;

          Object.assign(this.statisstics, data);

        }

        if (this.statisstics.total_available <= 0) {
          this.dialogParkingFull = true;

        }

      } catch (error) {
        this.snackbar = true;
        // this.response = error.message;
        this.snackbarColor = "red";

        console.error("Error fetching statistics:", error);

        this.response = "Processing statistics Update.";

      }
    },
    async paymentProcess(paymentMethod, id) {
      // console.log("Processing payment for method:", paymentMethod);

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

          // console.log(data);


          // Update data
          if (data.status) {
            this.snackbar = true;
            this.snackbarColor = "green";
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
          this.snackbarColor = "red";
          // this.response = error.message;
          this.response = "Error occurred while processing payment.";

        }
      }
    }
  },
};
</script>
