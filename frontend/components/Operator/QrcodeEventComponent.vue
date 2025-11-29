<template>
  <div>
    <v-row style="margin-left: 5px; margin-top: 0px" :key="2">
      <v-col style="max-width: 300px; padding-top: 0px">
        <v-card elevation="2">
          <v-card-text
            :style="
              ' background-color:#ecf0f4;overflow:scroll1;min-height:200px;height:' +
              browserHeight +
              'px'
            "
          >
            <div
              v-if="
                data.length > 0 &&
                selectedAlarm &&
                selectedAlarm.device?.customer
              "
            >
              <EventContactNotes
                :key="selectedAlarm.id"
                :colorcodes="colorcodes"
                :customer="selectedAlarm?.device?.customer"
                :alarm="selectedAlarm"
                @emitreloadEventNotes3="reloadEventNotes4()"
                @emitShowCustomerInfoTabs="changeStatusBusinessInfoTabs"
                :browserHeight="browserHeight"
                :qrcode="true"
              />
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col style="padding-top: 0px; padding-left: 0px">
        <v-card elevation="2">
          <v-card-text
            :style="' padding:0px;height:' + parseInt(browserHeight) + 'px'"
          >
            <div
              style="
                width: 100%;
                margin: auto;
                height: 600px;
                padding-top: 20%;
                font-weight: bold;
                text-align: center;
              "
              v-if="data.length == 0"
            >
              <div>Event Details are Not available.</div>
            </div>

            <EventsListBusinessInfoTabs
              v-if="
                data &&
                data.length > 0 &&
                selectedAlarm.device.customer &&
                selectedAlarm
              "
              :customer="selectedAlarm?.device.customer"
              :device="selectedAlarm?.device"
              :alarm="selectedAlarm"
              :key="keySelectedItem"
              :browserHeight="browserHeight"
            />
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import google_map_style_bandw from "../../google/google_style_blackandwhite.json";
import google_map_style_regular from "../../google/google_style_regular.json";

import Topmenu from "../../components/Operator/topmenu.vue";

import EventContactNotes from "../../components/Operator/EventContactNotes.vue";
import EventsListBusinessInfoTabs from "../../components/Operator/EventsListBusinessInfoTabs.vue";

export default {
  components: {
    Topmenu,
    EventsListBusinessInfoTabs,
    EventContactNotes,
  },
  props: ["alarm_id"],
  // alarm_event_operator_statistics
  data: () => ({
    browserHeight: 600, // Initial height
    displayEventsListBusinessInfoTabs: false,
    globalsearch: "",
    keyLogs: 1,
    showMappingSection: false,
    showAlarmEventNotes: false,
    displayAlarmMap: [],
    displayAlarmMapId: null,
    OperatorGoogleMapKey: 1,
    selectedAlarm: null,
    //displayAlarmMap: false,
    loading: true,
    filterBuildingType: null,
    filterAlarmType: null,
    filterAlarmStatus: null,
    fullscreen: false,
    // windowHeight: 1000,
    windowWidth: 600,
    mapStyle: "bw",
    mapkeycount: 1,
    popupEventText: "",
    dialogAlarmEventCustomerContactsTabView: false,
    dialog: false,
    dialogContent: "",
    customerInfo: null,
    map: null,
    key: 1,
    mapKey: null,
    geocoder: null,
    infowindow: null,
    viewCustomerId: null,
    commonSearch: "",
    // perPage: 10,
    cumulativeIndex: 1,
    currentPage: 1,
    totalRowsCount: 0,
    branchesList: [],
    isCompany: true,
    tableHeight: 750,
    id: "",
    eventId: null,
    customer: null,
    newCustomerDialog: false,
    dialogViewCustomer: false,
    totalRowsCount: 0,

    colorcodes: null,
    snack: false,
    snackColor: "",
    snackText: "",
    departments: [],
    Model: "Log",
    endpoint: "customers",
    payload: {},
    loading: false,
    headers: [
      {
        text: "Customer Name",
        value: "building_name",
      },
    ],
    ids: [],

    data: [],
    devices: [],
    total: 0,

    payloadOptions: {},
    options: {
      current: 1,
      total: 0,
      itemsPerPage: 10,
    },
    errors: [],
    snackbar: false,
    branchesList: [],
    branch_id: "",

    responseStatusColor: "",
    response: "",
    buildingTypes: [],
    alarmTypes: [],

    _id: null,

    mapMarkersList: [],
    mapInfowindowsList: [],
    filterText: "",
    keySelectedItem: 1,
    google_map_style_bandw,
    changeSelectedAlarm: true,
    google_map_style_regular,
    cancelGetdatafromAPITokenSource: null, // Keep track of the cancel token
  }),
  computed: {},

  beforeDestroy() {
    if (window) window.removeEventListener("resize", this.onResize);
  },
  async mounted() {
    try {
      if (window) window.addEventListener("resize", this.onResize);

      if (window) {
        // this.windowHeight = window.innerHeight;

        const element = document.getElementById("lefteventlist");
        if (element) {
          // this.windowHeight = element.getBoundingClientRect().height;
        }

        // this.windowWidth = window.innerWidth;
      }

      await this.getDatafromApi(this.filterText);
      await this.getMapKey();

      this.getBuildingTypes();
      this.getAlarmTypes();
    } catch (e) {}
  },

  async created() {
    this.colorcodes = this.$utils.getAlarmIcons();
    try {
      if (window) this.browserHeight = window.innerHeight - 70;

      this.onResize();

      if (window) window.addEventListener("resize", this.onResize);
      let alarm_id = this.alarm_id;

      this.loading = true;
      this.selectedAlarm = null;
      this.keyLogs++;
      this.keySelectedItem++;

      // let options = { params: { eventID: alarm_id } };

      // this.$axios.get("get_operator_alarm_events", options).then((data) => {
      //   if (data.data[0]) this.selectAlarmEvent(data.data[0]);

      //   console.log(" this.selectedAlarm ", this.selectedAlarm);
      // });
    } catch (e) {}
  },
  watch: {},
  methods: {
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    changeStatusBusinessInfoTabs(status) {
      this.displayEventsListBusinessInfoTabs = status;
    },
    reloadEventNotes4() {
      console.log("emitreloadEventNotes3");

      this.keyLogs++;
      setTimeout(() => {
        this.onResize();
      }, 1000 * 5);

      this.getDatafromApi();
      /////this.getMapKey();
    },
    async getAlarmTypes() {
      const { data } = await this.$axios.get("alarm_types", {
        params: {},
      });

      this.alarmTypes = data;
    },
    closeMap() {
      this.selectedAlarm = null;
      this.showMappingSection = false;
      this.showAlarmEventNotes = false;
    },
    showMap(alarm) {
      this.showAlarmEventNotes = false;
      this.showMappingSection = true;
      this.OperatorGoogleMapKey++;
      this.selectedAlarm = alarm;
    },
    closeAlarmEventNotes() {
      this.selectedAlarm = null;
      this.showAlarmEventNotes = false;
      this.showMappingSection = false;
    },
    showNotes(alarm) {
      if (this.showAlarmEventNotes) {
        this.selectedAlarm = null;
        this.showAlarmEventNotes = false;
      } else {
        this.showAlarmEventNotes = true;
        this.OperatorGoogleMapKey++;
        this.selectedAlarm = alarm;
      }
      this.showMappingSection = false;
    },
    async getBuildingTypes() {
      const { data } = await this.$axios.get("building_types", {
        params: {},
      });

      this.buildingTypes = data;
    },
    onResize() {
      // console.log("window.innerHeight", window.innerHeight);

      if (window) {
        if (window) {
          // this.windowHeight = window.innerHeight;

          const element = document.getElementById("lefteventlist");
          if (element) {
            // this.windowHeight = element.getBoundingClientRect().height;
          }

          this.browserHeight = window.innerHeight - 70;

          // this.windowWidth = window.innerWidth;
        }
      }
    },
    toggleFullscreen() {
      let newStyle = "fullscreen";
      if (!document.fullscreenElement) {
        document.documentElement
          .requestFullscreen()
          .then(() => {
            this.fullscreen = true;
          })
          .catch((err) => {
            console.error(
              `Error attempting to enable fullscreen mode: ${err.message}`
            );
          });
      } else {
        document
          .exitFullscreen()
          .then(() => {
            this.fullscreen = false;
          })
          .catch((err) => {
            console.error(
              `Error attempting to exit fullscreen mode: ${err.message}`
            );
          });
      }
    },
    changeGoogleMapColor(type) {
      let newStyle = this.google_map_style_regular;
      if (type == "bw") newStyle = this.google_map_style_bandw;
      if (type == "map") newStyle = this.google_map_style_regular;

      this.map.setOptions({ styles: newStyle });
    },
    viewAlarmInformation(alarm) {
      try {
        this.setCustomerLocationOnMap(alarm.device.customer);
      } catch (e) {
        console.error(e);
      }
      this.popupEventText =
        "#" +
          alarm.id +
          " -    " +
          alarm.alarm_type +
          " ,  " +
          "   Time " +
          alarm?.alarm_start_datetime ||
        "" + " -  Priority " + alarm.category.name;

      this.key += 1;
      this.viewCustomerId = alarm.device.customer_id;
      this.eventId = alarm.id;
      this.customer = alarm.device.customer;
      this.dialogAlarmEventCustomerContactsTabView = true;
    },
    selectAlarmEvent(alarm) {
      this.loading = true;
      this.selectedAlarm = alarm;
      this.keyLogs++;
      this.keySelectedItem++;
      this.loading = false;
    },
    closeCustomerDialog() {
      this.dialogViewCustomer = false;
    },

    viewItem(item) {
      this.viewCustomerId = item.id;
      this.dialogViewCustomer = true;
    },
    viewItem2(item) {
      this.$router.push("/alarm/view-customer/" + item.id);
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    async getMapKey() {
      let { data } = await this.$axios.get(`get-map-key`);
      this.mapKey = data;
      if (this.mapKey && this.loading == false) {
        this.loadGoogleMapsScript(this.initMap);
      }
    },

    async getDatafromApi(filterText = "", changeSelectedAram = true) {
      if (this.cancelGetdatafromAPITokenSource) {
        this.cancelGetdatafromAPITokenSource.cancel(
          "request canceled due to new request."
        );
      }
      this.cancelGetdatafromAPITokenSource = this.$axios.CancelToken.source();
      this.filterText = filterText;

      this.loading = true;

      let options = {
        params: {
          customer_id: this.customer_id,
          // date_from: this.date_from,
          // date_to: this.date_to,
          common_search: this.commonSearch,
          eventID: this.alarm_id ? this.alarm_id : this.filterText,
          filterBuildingType: this.filterBuildingType,
          filterAlarmStatus: this.filterAlarmStatus,

          filterAlarmType: this.filterAlarmType,
        },
        cancelToken: this.cancelGetdatafromAPITokenSource.token,
      };

      try {
        this.$axios
          .get(`get_operator_alarm_events`, options)
          .then(({ data }) => {
            //this.mapkeycount++;
            this.data = data.data; //data.data;

            if (this.data.length == 0 && changeSelectedAram == true) {
              this.selectedAlarm = null;

              //this.$router.push("/operator/eventslist");
              return false;
            }

            this.loading = false;
            if (changeSelectedAram) this.selectedAlarm = this.data[0];

            if (this.data.length > 0 && this.selectedAlarm == null) {
              this.selectedAlarm = this.data[0];
            } else if (this.selectedAlarm != null) {
              this.selectedAlarmNew = this.data.find(
                (x) => x.id == this.selectedAlarm.id
              );

              if (
                this.selectedAlarmNew.alarm_status !=
                this.selectedAlarm.alarm_status
              ) {
                this.selectedAlarm = this.selectedAlarmNew;
                this.keySelectedItem++;
              }
            }

            if (this.$route.query.id) {
              this.selectedAlarmFilter = this.data.find(
                (x) => x.id == this.$route.query.id
              );

              if (this.selectedAlarmFilter)
                this.selectedAlarm = this.selectedAlarmFilter;
            }

            this.onResize();
            // this.mapMarkersList.forEach((marker, index) => {
            //   if (marker) {
            //     marker.visible = false;
            //     marker.setMap(null);
            //     marker = null;
            //     this.mapMarkersList[index] = null;
            //   }
            // });
            // this.mapMarkersList = [];

            // //this.plotLocations();

            if (this.data.length == 0) this.selectedAlarm = null;

            // console.log("this.selectedAlarm", this.selectedAlarm.alarm_status);

            ///////this.keySelectedItem++;
          });
      } catch (e) {}
    },

    // async getGoogleicons() {
    //   let config = {
    //     params: {
    //       company_id: this.$auth.user.company_id,
    //     },
    //   };
    //   let { sortBy, sortDesc, page, itemsPerPage } = this.options;
    //   console.log(this.options);
    //   if (page) this.currentPage = page - 1;
    //   let { data } = await this.$axios.get(`get_google_icons`, config);
    //   this.colorcodes = data;
    // },
    getImageicon(value) {
      if (process) return value.image;
      else return false;
    },
    getAlarmColorObject(alarm, customer = null) {
      if (alarm) {
        if (this.colorcodes[alarm.alarm_type.toLowerCase()])
          return this.colorcodes[alarm.alarm_type.toLowerCase()];
        if (alarm.alarm_status == 1) {
          return this.colorcodes.alarm;
        }
      }
      // else if (alarm.alarm_status == 0) {
      //   return this.colorcodes.closed;
      // }
      //if (
      //   alarm.customer &&
      //   this.findanyArmedDevice(alarm.customer.devices)
      // ) {
      //   return this.colorcodes.armed;
      // }
      else if (customer) {
        if (this.findAnyDeviceisOffline(customer.devices) > 0) {
          return this.colorcodes.offline;
        } else if (this.findanyArmedDevice(customer.devices)) {
          return this.colorcodes.armed;
        } else if (this.findanyDisamrDevice(customer.devices) > 0) {
          return this.colorcodes.disarm;
        }
      }
      // console.log(
      //   "findAnyDeviceisOffline",
      //   this.findAnyDeviceisOffline(item.devices)
      // );
      // console.log(alarm.alarm_status);

      return this.colorcodes.offline;
    },
    getCustomerColorObject(customer) {
      // console.log(
      //   "findAnyDeviceisOffline",
      //   this.findAnyDeviceisOffline(item.devices)
      // );

      if (customer.latest_alarm_event) {
        return this.colorcodes.alarm;
      } else if (this.findanyArmedDevice(customer.devices)) {
        return this.colorcodes.armed;
      }
      if (this.findAnyDeviceisOffline(customer.devices) > 0) {
        return this.colorcodes.offline;
      } else if (this.findanyDisamrDevice(customer.devices)) {
        return this.colorcodes.disarm;
      }

      return this.colorcodes.offline;
    },
    findAnyDeviceisOffline(devices) {
      if (!devices) return 0;
      let offlineArray = devices.filter((device) => device.status_id == 2);
      // console.log("offlineArray", offlineArray);
      // console.log("offlineArray", offlineArray.length);
      return offlineArray ? offlineArray.length : 0;
    },
    findallDeviceisOnline(devices) {
      if (!devices) return 0;
      let onlineArray = devices.filter((device) => device.status_id == 1);

      // console.log("offlineArray", offlineArray.length);
      return onlineArray
        ? onlineArray.length == devices.length
          ? true
          : false
        : 0;
    },
    findanyArmedDevice(devices) {
      if (!devices) return 0;
      let armedArray = devices.filter((device) => device.armed_status == 1);

      return armedArray ? armedArray.length : 0;
    },
    findanyDisamrDevice(devices) {
      if (!devices) return 0;
      let armedArray = devices.filter((device) => device.armed_status == 0);

      return armedArray ? armedArray.length : 0;
    },
    loadGoogleMapsScript(callback) {
      if (window.google && window.google.maps) {
        callback();
        return;
      }
      const script = document.createElement("script");
      script.src = `https://maps.googleapis.com/maps/api/js?loading=async&key=${this.mapKey}&callback=initMap`;
      script.async = true;
      script.defer = true;
      window.initMap = callback;
      document.head.appendChild(script);
    },

    setCustomerLocationOnMap(customer) {
      try {
        if (
          this.map &&
          customer.latitude &&
          customer.longitude &&
          !isNaN(customer.latitude) &&
          !isNaN(customer.longitude)
        ) {
          const position = {
            lat: parseFloat(customer.latitude),
            lng: parseFloat(customer.longitude),
          };

          this.mapInfowindowsList.forEach((infowindow) => {
            infowindow.close();
          });

          this.map.panTo(position);
          this.map.setZoom(14);

          let infowindow = this.mapInfowindowsList[customer.id];
          let marker = this.mapMarkersList[customer.id];
          if (infowindow) infowindow.open(this.map, marker);
        }
      } catch (e) {}
    },
    initMap() {
      if (!this.map && document.getElementById("map")) {
        this.map = new google.maps.Map(document.getElementById("map"), {
          // mapTypeControl: true, // Enables satellite/roadmap controls
          // mapTypeControlOptions: {
          //   style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
          //   position: google.maps.ControlPosition.TOP_RIGHT,
          // },
          controlSize: 20,
          zoom: 12,
          center: { lat: 25.2265191, lng: 55.395225 },
          styles: this.google_map_style_bandw,
          // styles: [
          //   {
          //     featureType: "administrative",
          //     stylers: [{ visibility: "off" }],
          //   },
          //   {
          //     featureType: "administrative",
          //     stylers: [{ visibility: "off" }],
          //   },
          //   {
          //     featureType: "landscape",
          //     stylers: [{ visibility: "off" }],
          //   },
          //   {
          //     featureType: "poi",
          //     stylers: [{ visibility: "off" }],
          //   },
          // ],
        });
      }
      this.geocoder = new google.maps.Geocoder();
      // this.infowindow = new google.maps.InfoWindow();
    },
    plotLocations() {
      //set default one customer

      if (
        this.map &&
        this.data &&
        this.data[0] &&
        this.data[0].device.utc_time_zone != "Asia/Dubai" &&
        this.data[0].device.customer.latitude != "" &&
        this.data[0].device.customer.longitude != "" &&
        !isNaN(this.data[0].device.customer.latitude) &&
        !isNaN(this.data[0].device.customer.longitude)
      ) {
        const position = {
          lat: parseFloat(this.data[0].device.customer.latitude),
          lng: parseFloat(this.data[0].device.customer.longitude),
        };
        this.map.panTo(position);
      }

      this.data.forEach((item) => {
        if (item.device?.customer) {
          const customerId = item.device.customer.id;

          // Check if a marker already exists for this customer
          if (this.mapMarkersList[customerId]) {
            // Skip if marker already exists with alarm_status = 1 (high priority)
            if (
              item.alarm_status == 1 &&
              this.mapMarkersList[customerId].alarm_status != "1"
            )
              this.mapMarkersList[customerId].setMap(null);
          }

          // Determine if we should load a marker for this customer
          let loadMarker =
            item.alarm_status == "1" || !this.mapMarkersList[customerId];

          if (loadMarker) {
            try {
              const position = {
                lat: parseFloat(item.device.customer.latitude),
                lng: parseFloat(item.device.customer.longitude),
              };

              // Determine icon based on alarm or other conditions
              let iconURL =
                process.env.BACKEND_APP_URL +
                "/google_map_icons/google_online.png";
              const colorObject = this.getAlarmColorObject(item);
              if (colorObject) iconURL = colorObject.image;

              const icon = {
                url: iconURL + "?4=4",
                // scaledSize: new google.maps.Size(28, 34),
                scaledSize: new google.maps.Size(40, 40),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(25, 25),
              };

              const marker = new google.maps.Marker({
                position,
                map: this.map,
                title: item.device.customer.name,
                icon: icon,
                alarm_status: item.alarm_status,
              });

              // Create content for the infowindow
              let alarmHtmlLink = "";
              let customerHtmlLink = `<button class="primary v-btn v-btn--is-elevated v-btn--has-bg theme--light v-size--x-small" id="customerInfowindow-btn-${item.id}">View</button>`;

              if (item.alarm_start_datetime)
                alarmHtmlLink = `<button class="error v-btn v-btn--is-elevated v-btn--has-bg theme--light v-size--x-small" id="alarmInfowindow-btn-${item.id}">Alarm</button>`;

              let profile_picture =
                item.device.customer.profile_picture ||
                "https://alarm.xtremeguard.org/no-business_profile.png";

              let html = `
            <table style="width:250px; min-height:100px" id="infowindow-content-${item.id}">
              <tr>
                <td style="width:100px; vertical-align: top;">
                  <img style="width:100px;max-height:100px; padding-right:5px;" src="${profile_picture}" />
                  <br />
                </td>
                <td style="width:150px; vertical-align: top;">
                  ${item.device.customer.building_name} <br/> ${item.device.customer.city}
                  <div>Landmark: ${item.device.customer.landmark}</div>
                </td>
              </tr>
              <tr>
                <td>
                  <a target="_blank" href="https://www.google.com/maps?q=${item.device.customer.latitude},${item.device.customer.longitude}">Google Directions</a>
                </td>
                <td style="text-align:right">
                  ${customerHtmlLink} &nbsp; &nbsp; ${alarmHtmlLink}
                </td>
              </tr>
            </table>`;

              const infowindow = new google.maps.InfoWindow({
                content: html,
                map: this.map,
                position: position,
              });
              infowindow.close();

              // Store marker and infowindow references
              this.mapInfowindowsList[customerId] = infowindow;
              this.mapMarkersList[customerId] = marker;

              // Add bounce animation if alarm is active
              if (item.alarm_status == 1)
                marker.setAnimation(google.maps.Animation.BOUNCE);

              // Marker event listeners
              marker.addListener("mouseover", () => {
                this.mapInfowindowsList.forEach((oldinfowindow) =>
                  oldinfowindow.close()
                );
                infowindow.open(this.map, marker);
              });

              google.maps.event.addListener(infowindow, "domready", () => {
                const alarmBtn = document.getElementById(
                  `alarmInfowindow-btn-${item.id}`
                );
                const customerBtn = document.getElementById(
                  `customerInfowindow-btn-${item.id}`
                );

                if (alarmBtn)
                  alarmBtn.addEventListener("click", () =>
                    this.viewAlarmInformation(item.alarm_start_datetime)
                  );
                if (customerBtn)
                  customerBtn.addEventListener("click", () => {
                    this.dialog = true;
                    this.key += 1;
                    this.customerInfo = item.device.customer;
                  });

                const infowindowContent = document.getElementById(
                  `infowindow-content-${item.id}`
                );
                infowindowContent.addEventListener("mouseout", (e) => {
                  if (!infowindowContent.contains(e.relatedTarget)) {
                    infowindow.close();
                  }
                });
              });

              // Open Vue dialog on marker click
              marker.addListener("click", () => {
                this.dialog = true;
                this.key += 1;
                this.customerInfo = item.device.customer;
              });
            } catch (e) {
              console.error(e);
            }
          }
        }
      });
    },
  },
};
</script>
