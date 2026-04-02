<template>
  <div>
    <v-dialog v-model="dialog" max-width="1100px">
      <v-card v-if="customerInfo">
        <v-toolbar class="primary white--text" flat dense>
          <v-toolbar-title>Alarm Event Info </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-icon @click="dialog = false" color="white">mdi-close</v-icon>
        </v-toolbar>
        <v-row>
          <v-col cols="12" md="6">
            <v-container>
              <v-card outlined>
                <v-card-title>Primary Contact</v-card-title>
                <v-card-text>
                  <v-list dense>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>Name:</v-list-item-title>
                        <v-list-item-subtitle
                          >{{ customerInfo?.primary_contact?.first_name }}
                          {{
                            customerInfo?.primary_contact?.last_name
                          }}</v-list-item-subtitle
                        >
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>Phone:</v-list-item-title>
                        <v-list-item-subtitle
                          >{{ customerInfo?.primary_contact?.phone1 }} /
                          {{
                            customerInfo?.primary_contact?.phone2
                          }}</v-list-item-subtitle
                        >
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>Office Phone:</v-list-item-title>
                        <v-list-item-subtitle>{{
                          customerInfo?.primary_contact?.office_phone
                        }}</v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>Email:</v-list-item-title>
                        <v-list-item-subtitle>{{
                          customerInfo?.primary_contact?.email
                        }}</v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>WhatsApp:</v-list-item-title>
                        <v-list-item-subtitle>{{
                          customerInfo?.primary_contact?.whatsapp
                        }}</v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>
            </v-container>
          </v-col>

          <v-col cols="12" md="6">
            <v-container>
              <v-card outlined>
                <v-card-title>Secondary Contact</v-card-title>
                <v-card-text>
                  <v-list dense>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>Name:</v-list-item-title>
                        <v-list-item-subtitle
                          >{{ customerInfo?.secondary_contact?.first_name }}
                          {{
                            customerInfo?.secondary_contact?.last_name
                          }}</v-list-item-subtitle
                        >
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>Phone:</v-list-item-title>
                        <v-list-item-subtitle
                          >{{ customerInfo?.secondary_contact?.phone1 }} /
                          {{
                            customerInfo?.secondary_contact?.phone2
                          }}</v-list-item-subtitle
                        >
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>Office Phone:</v-list-item-title>
                        <v-list-item-subtitle>{{
                          customerInfo?.secondary_contact?.office_phone
                        }}</v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>Email:</v-list-item-title>
                        <v-list-item-subtitle>{{
                          customerInfo?.secondary_contact?.email
                        }}</v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>WhatsApp:</v-list-item-title>
                        <v-list-item-subtitle>{{
                          customerInfo?.secondary_contact?.whatsapp
                        }}</v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>
            </v-container>
          </v-col>

          <v-col cols="12" md="6">
            <v-container>
              <v-card outlined>
                <v-card-title>Customer Info</v-card-title>
                <v-card-text>
                  <v-list dense>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>Customer Name:</v-list-item-title>
                        <v-list-item-subtitle>{{
                          customerInfo.building_name
                        }}</v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>

                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>Address:</v-list-item-title>
                        <v-list-item-subtitle
                          >{{ customerInfo.house_number }},
                          {{ customerInfo.street_number }},
                          {{ customerInfo.area }}, {{ customerInfo.city }},
                          {{ customerInfo.state }},
                          {{ customerInfo.country }}</v-list-item-subtitle
                        >
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>Email:</v-list-item-title>
                        <v-list-item-subtitle>{{
                          customerInfo.email
                        }}</v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>Landmark:</v-list-item-title>
                        <v-list-item-subtitle>{{
                          customerInfo.landmark
                        }}</v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>
            </v-container>
          </v-col>

          <v-col cols="12" md="6">
            <v-container>
              <v-card outlined>
                <v-card-title>Alarm Info</v-card-title>
                <v-card-text>
                  <v-list dense>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>Device:</v-list-item-title>
                        <v-list-item-subtitle>{{
                          customerInfo?.latest_alarm_event?.serial_number
                        }}</v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>Zone:</v-list-item-title>
                        <v-list-item-subtitle>{{
                          customerInfo?.latest_alarm_event?.zone
                        }}</v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>Area:</v-list-item-title>
                        <v-list-item-subtitle>{{
                          customerInfo?.latest_alarm_event?.area
                        }}</v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title
                          >Alarm Start Datetime:</v-list-item-title
                        >
                        <v-list-item-subtitle>{{
                          customerInfo?.latest_alarm_event?.alarm_start_datetime
                        }}</v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>Alarm Type:</v-list-item-title>
                        <v-list-item-subtitle>{{
                          customerInfo?.latest_alarm_event?.alarm_type
                        }}</v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>
            </v-container>
          </v-col>
        </v-row>
      </v-card>
    </v-dialog>
    <v-row>
      <v-col cols="9">
        <div id="map" style="height: 100vh"></div>
      </v-col>
      <v-col cols="3">
        <v-data-table
          dense
          :headers="headers"
          :items="data"
          :loading="loading"
          :options.sync="options"
          :footer-props="{
            itemsPerPageOptions: [10, 50, 100, 500, 1000],
          }"
          fixed-header
          :height="tableHeight"
          hide-default-header
        >
          <template v-slot:top>
            <v-container>
              <v-row>
                <v-col cols="4">Alarm List</v-col>
                <v-col>
                  <v-text-field
                    class="small-custom-textbox"
                    @input="getCustomers()"
                    v-model="commonSearch"
                    label="Search..."
                    dense
                    outlined
                    type="text"
                    append-icon="mdi-magnify"
                    clearable
                    hide-details
                  ></v-text-field>
                </v-col>
              </v-row>
              <v-divider class="mt-3" color="#DDD"></v-divider>
            </v-container>
          </template>
          <template v-slot:item.building_name="{ item, index }">
            <v-row
              style="border-bottom: 0px solid #ddd"
              @click="setCustomerLocationOnMap(item)"
            >
              <v-col cols="1">{{ index + 1 }}) </v-col>
              <v-col>
                <span style="font-size: 13px; margin-bottom: 15px">
                  {{ item.building_name || "" }} ,{{ item.city }}
                </span>
                <div style="height: 10px">&nbsp;</div>
                <v-row
                  :key="index1 + 11"
                  v-for="(alarm, index1) in item.alarm_events"
                >
                  <v-col cols="12">
                    <v-row>
                      <v-col
                        cols="1"
                        style="max-width: 20px; padding-top: 16px"
                      >
                        {{ alarm.alarm_type }}
                        <img
                          :title="alarm.alarm_type"
                          style="width: 15px; float: left"
                          :src="$utils.getRelaventImage(alarm.alarm_type)"
                        />
                        <!-- <img
                          v-if="alarm.alarm_type == 'Burglary'"
                          title="Burglary"
                          style="width: 15px; float: left"
                          src="/device-icons/burglary.png"
                        />

                        <img
                          v-if="alarm.alarm_type == 'Temperature'"
                          title="Temperature"
                          style="width: 15px; float: left"
                          src="/device-icons/temperature.png"
                        />

                        <img
                          v-if="alarm.alarm_type == 'Medical'"
                          title="Medical"
                          style="width: 15px; float: left"
                          src="/device-icons/medical.png"
                        />

                        <img
                          v-if="alarm.alarm_type == 'Fire'"
                          title="Fire"
                          style="width: 15px; float: left"
                          src="/device-icons/fire.png"
                        />

                        <img
                          v-if="alarm.alarm_type == 'Water'"
                          title="Water"
                          style="width: 15px; float: left"
                          src="/device-icons/water.png"
                        /> -->
                      </v-col>

                      <v-col cols="7">
                        {{
                          $dateFormat.formatDateMonthYear(
                            alarm.alarm_start_datetime
                          )
                        }}
                      </v-col>
                      <v-col cols="4"> {{ alarm.alarm_type }}</v-col>
                    </v-row>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </template>
        </v-data-table>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  data: () => ({
    dialog: false,
    dialogContent: "",
    customerInfo: null,
    map: null,
    mapKey: null,
    geocoder: null,
    infowindow: null,
    viewCustomerId: null,
    commonSearch: "",
    perPage: 10,
    cumulativeIndex: 1,
    currentPage: 1,
    branchesList: [],
    isCompany: true,
    tableHeight: 750,
    id: "",

    newCustomerDialog: false,
    dialogViewCustomer: false,
    totalRowsCount: 0,

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
    pagination: {
      current: 1,
      total: 0,
      itemsPerPage: 1000,
    },
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
    _id: null,

    mapMarkersList: [],
    mapInfowindowsList: [],
  }),
  computed: {},
  async mounted() {
    await this.getMapKey();
  },
  created() {
    this._id = 4; //this.$route.params.id;

    if (this.$auth.user.branch_id) {
      this.branch_id = this.$auth.user.branch_id;
      this.isCompany = false;
      return;
    }

    this.getCustomers();
    this.getBuildingTypes();
  },
  watch: {
    options: {
      handler() {
        this.getCustomers();
      },
      deep: true,
    },
  },
  methods: {
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

    closeCustomerDialog() {
      this.dialogViewCustomer = false;
    },
    getBuildingTypes() {
      if (this.$store.state.storeAlarmControlPanel?.BuildingTypes) {
        this.buildingTypes =
          this.$store.state.storeAlarmControlPanel.BuildingTypes;
      }
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

    async getCustomers() {
      this.loading = true;
      let config = {
        params: {
          company_id: this.$auth.user.company_id,
          commonSearch: this.commonSearch,
        },
      };
      let { data } = await this.$axios.get(`alarm_customers__for_map`, config);
      this.data = data.data;
      this.loading = false;

      await this.getMapKey();
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

    setCustomerLocationOnMap(item) {
      if (item.latitude && item.longitude) {
        const position = {
          lat: parseFloat(item.latitude),
          lng: parseFloat(item.longitude),
        };

        //close all infowindows
        this.mapInfowindowsList.forEach((infowindow) => {
          infowindow.close();
        });

        this.map.panTo(position);
        let infowindow = this.mapInfowindowsList[item.id];
        let marker = this.mapMarkersList[item.id];

        infowindow.open(this.map, marker);
      }
    },
    initMap() {
      this.map = new google.maps.Map(document.getElementById("map"), {
        zoom: 12,
        center: { lat: 25.276987, lng: 55.296249 },
        styles: [
          {
            featureType: "administrative",
            stylers: [{ visibility: "off" }],
          },
          {
            featureType: "administrative",
            stylers: [{ visibility: "off" }],
          },
          {
            featureType: "landscape",
            stylers: [{ visibility: "off" }],
          },
          {
            featureType: "poi",
            stylers: [{ visibility: "off" }],
          },
        ],
      });

      this.geocoder = new google.maps.Geocoder();
      // this.infowindow = new google.maps.InfoWindow();
      this.plotLocations();
    },
    plotLocations() {
      this.data.forEach((item) => {
        try {
          // { latitude: lat, longitude: lng, name, alarm }
          const position = {
            lat: parseFloat(item.latitude),
            lng: parseFloat(item.longitude),
          };

          const icon = {
            url: this.$utils.getRelaventMarkers(item.alarm), // Path to the customer image
            scaledSize: new google.maps.Size(75, 75), // Adjust the size as needed

            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(25, 25), // Adjust anchor point to the center
          };

          const marker = new google.maps.Marker({
            position,
            map: this.map,
            title: item.name,
            //icon: icon,
          });
          let html =
            "<div style='width:250px'><div style='width:100px;float:left'>  " +
            "<img style='width:100px;padding-right:5px;' src='" +
            item.profile_picture +
            "'/>" +
            "</div>";
          html +=
            "<div style='width:150px; float:left'>" +
            item.building_name +
            " <br/> " +
            item.city +
            "<div>Landmark: " +
            item.landmark +
            "</div>" +
            "" +
            " ";
          html +=
            "<br/> <a target='_blank' href='https://www.google.com/maps?q=" +
            item.latitude +
            "," +
            item.longitude +
            "'>Google Map Link</a>" +
            " ";
          html += "</div></div>";

          var infowindow = new google.maps.InfoWindow({
            content: html,
            map: this.map,
            position: position,
          });
          infowindow.close();
          this.mapInfowindowsList[item.id] = infowindow;
          this.mapMarkersList[item.id] = marker;

          marker.addListener("mouseover", function () {
            infowindow.open(this.map, this);
          });

          // marker.addListener("mouseout", function () {
          //   infowindow.close();
          // });
          marker.addListener("click", () => {
            this.dialog = true;

            this.customerInfo = item;
          });
        } catch (e) {}
      });
    },
  },
};
</script>
