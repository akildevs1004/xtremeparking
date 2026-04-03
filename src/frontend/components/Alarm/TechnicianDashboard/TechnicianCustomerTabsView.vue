<template>
  <div max-width="100%" class="background">
    <v-row>
      <v-col style="max-width: 250px">
        <v-card style="height: 270px"
          ><v-card-text elevation="2">
            <v-row>
              <v-col class="p1-0" style="padding: 0px">
                <v-img
                  style="
                    width: 100%;

                    border-radius: 5%;
                    border: 1px solid #ddd;
                    margin: auto;
                    max-height: 100px;
                  "
                  :src="
                    selectedCustomer && selectedCustomer.profile_picture
                      ? selectedCustomer.profile_picture
                      : '/no-business_profile.png'
                  "
                ></v-img>
              </v-col>
              <v-col style="font-weight: bold; text-align: center">
                <div style="font-size: 16px; text-align: center">
                  {{
                    selectedCustomer && selectedCustomer.building_name
                      ? selectedCustomer.building_name
                      : "---"
                  }}
                </div>
              </v-col>
            </v-row>

            <v-row style="margin-top: 0px">
              <v-col style="line-height: 25px; padding: 0px">
                <div style="border-top: 0px solid #ddd" v-if="selectedCustomer">
                  {{ selectedCustomer.house_number }},
                  {{ selectedCustomer.street_number }},
                  {{ selectedCustomer.area }}, {{ selectedCustomer.city }}
                </div>
                <div style="border-top: 1px solid #ddd" v-if="selectedCustomer">
                  {{ selectedCustomer.state }},
                  {{ selectedCustomer.country }}
                </div>
                <div style="border-top: 1px solid #ddd" v-if="selectedCustomer">
                  Landmark:
                  {{ selectedCustomer.landmark }}
                </div>

                <div style="border-top: 1px solid #ddd">
                  Email: {{ customer ? selectedCustomer.user?.email : "---" }}
                </div>
              </v-col>
            </v-row>
          </v-card-text></v-card
        >

        <v-card
          :style="'height: ' + (browserHeight - 245) + 'px; margin-top: 5px'"
          ><v-card-text elevation="2">
            <v-row style="margin-top: 0x">
              <v-col class="p-0" style="padding: 0px">
                <v-img
                  style="
                    width: 60%;

                    border-radius: 5%;
                    border: 1px solid #ddd;
                    margin: auto;
                  "
                  :src="
                    selectedCustomer && selectedCustomer.primary_contact
                      ? selectedCustomer.primary_contact?.profile_picture
                      : '/no-profile-image.jpg'
                  "
                ></v-img>
              </v-col>
              <v-col style="font-weight: bold; text-align: center">
                <div style="font-size: 16px; text-align: center">
                  {{
                    selectedCustomer && selectedCustomer.primary_contact
                      ? selectedCustomer.primary_contact?.first_name +
                        " " +
                        selectedCustomer.primary_contact?.last_name
                      : "---"
                  }}
                </div>
                <div style="font-size: 10px">(Primary Contact)</div>
              </v-col>
            </v-row>

            <v-row style="margin-top: 0px">
              <v-col style="line-height: 25px">
                <!-- <div style="font-weight: bold">
              <v-icon size="13" style="border: 0px solid #ddd"
                >mdi-account</v-icon
              >
              {{
                selectedCustomer.primary_contact?.first_name
                  ? selectedCustomer.primary_contact.first_name +
                    " " +
                    selectedCustomer.primary_contact.last_name
                  : "---"
              }}
            </div> -->
                <div style="border-top: 0px solid #ddd">
                  <v-icon size="13">mdi-phone</v-icon>
                  {{ selectedCustomer.primary_contact?.phone1 || "---" }}
                </div>
                <div style="border-top: 1px solid #ddd">
                  <v-icon size="13">mdi-phone-classic</v-icon>
                  {{ selectedCustomer.primary_contact?.phone2 || "---" }}
                </div>
                <div style="border-top: 1px solid #ddd">
                  <v-icon size="13">mdi-whatsapp</v-icon>
                  {{ selectedCustomer.primary_contact?.whatsapp || "---" }}
                </div>
                <div style="border-top: 1px solid #ddd">
                  <v-icon size="13">mdi-at</v-icon>
                  {{ selectedCustomer.primary_contact?.email || "---" }}
                </div>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col>
        <v-card
          ><v-col>
            <v-tabs
              height="25"
              center-active
              right
              class="customerEmergencyContactTabs1 customerEmergencyContactTabsBGcolor12222"
            >
              <v-tab style="font-size: 12px; min-width: 50px !important">
                Google Map
              </v-tab>
              <v-tab style="font-size: 12px; min-width: 50px !important">
                Camera
              </v-tab>
              <v-tab style="font-size: 12px; min-width: 50px !important"
                >Premises Photo </v-tab
              ><v-tab style="font-size: 12px; min-width: 50px !important"
                >Floor Plan
              </v-tab>
              <v-tab style="font-size: 12px; min-width: 50px !important"
                >Address
              </v-tab>

              <v-tab style="font-size: 12px; min-width: 50px !important"
                >System
              </v-tab>
              <v-tab style="font-size: 12px; min-width: 50px !important"
                >Events
              </v-tab>
              <v-tab-item style="height: 530px">
                <CompGoogleMapLatLan
                  v-if="customer"
                  :alarm="alarm"
                  :latitude="selectedCustomer.latitude"
                  :longitude="selectedCustomer.longitude"
                  :title="selectedCustomer.building_name"
                  :mapheight="parseInt(browserHeight - 20) + 'px'"
                  :contact_id="selectedCustomer.id"
                  :key="keySelectedItem"
                />
              </v-tab-item>
              <v-tab-item style="height: 530px">
                <v-row v-if="customer">
                  <v-col>
                    <div
                      style="
                        text-align: center;
                        min-height: 600px;
                        padding-top: 50px;
                      "
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
                      :height="browserHeight - 120"
                      hide-delimiter-background
                      show-arrows-on-hover
                      style="width: 100%"
                      hide-delimiters
                    >
                      <template
                        v-for="(item, index) in customer.cameras"
                        :name="item.id"
                        style="width: 100%"
                      >
                        <v-carousel-item style="width: 100%">
                          <v-chip color="#203864" style="color: #fff" label
                            >{{ index + 1 }}: {{ item.title }}</v-chip
                          >
                          <div
                            style="
                              width: 100%;
                              margin: auto;
                              text-align: center;
                            "
                          >
                            <iframe
                              :style="';  border: none;  min-width:750px'"
                              :width="browserWidth - 700"
                              :height="browserHeight - 150"
                              v-if="browserWidth && getCameraUrl(item) != ''"
                              :src="getCameraUrl(item)"
                            ></iframe>
                          </div>
                        </v-carousel-item>
                      </template>
                    </v-carousel>
                  </v-col>
                </v-row>
                <v-row v-if="customer">
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
                          :class="{
                            'active-thumbnail': index === currentCameraSlide,
                          }"
                        >
                          <v-icon size="50">mdi-camera</v-icon>
                        </div>
                        <label style="font-size: 12px; text-align: center">{{
                          item.title
                        }}</label>
                      </v-col>
                    </v-row>
                  </v-col>
                </v-row>
              </v-tab-item>
              <v-tab-item style="height: 530px"
                ><v-row v-if="customer">
                  <v-col>
                    <v-carousel
                      height="380"
                      v-model="currentSlide"
                      v-if="customer"
                      hide-delimiter-background
                      hide-arrows-on-hover
                      hide-delimiters
                      hide-arrows
                    >
                      <template
                        v-for="(item, index) in customer.profile_pictures"
                        :name="item.id"
                      >
                        <v-carousel-item>
                          <!-- <div style="text-align: Left">
                    {{ index + 1 }}: {{ item.title }}
                  </div> -->
                          <v-img
                            max-width="800"
                            height="400"
                            class="mx-auto border"
                            :src="item?.picture || '/noimage.png'"
                            contain
                            lazy-src="/noimage.png"
                          ></v-img>
                        </v-carousel-item>
                      </template>
                    </v-carousel>
                    <div v-if="customer.profile_pictures.length == 0">
                      No data is available
                    </div>
                  </v-col>
                </v-row>
                <v-row v-if="customer">
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
                          :class="{
                            'active-thumbnail': index === currentSlide,
                          }"
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
              <v-tab-item style="margin-top: -20px">
                <EventsBusinessTabFloorPlan
                  v-if="customer"
                  :alarm="alarm"
                  :customer="customer"
                  :browserHeight="browserHeight"
                  :key="keySelectedItem"
                />
              </v-tab-item>
              <v-tab-item style="height: 530px"
                ><EventsBusinessTab1Address
                  v-if="customer"
                  :customer="customer"
              /></v-tab-item>
              <v-tab-item style="height: 530px">
                <div v-if="customer" style="padding-top: 20px">
                  <table
                    v-if="customer && customer.devices && customer.devices[0]"
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
                    <tr
                      v-for="(sensor, index) in customer.devices[0].sensorzones"
                    >
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

                    <div v-if="customer.devices[0].sensorzones == 0">
                      No data is available
                    </div>
                  </table>
                  <div v-else>No data is available</div>
                  <div v-else>No data is available</div>
                </div>
              </v-tab-item>
              <v-tab-item style="height: 530px">
                <AlarmCustomerEventsLog
                  v-if="customer"
                  :showTabs="false"
                  style="
                    font-size: 12px !important;
                    height: 530px;
                    overflow-y: scroll;
                    overflow-x: hidden;
                  "
                  :filter_customer_id="customer.id"
                  name="PopupAlarmEventsCustoemrInfoAlamAllEventsPopup"
              /></v-tab-item>
            </v-tabs> </v-col
        ></v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import AlramCustomerContacts from "../../../components/Alarm/CustomerContacts.vue";
import AlramEmergencyContacts from "../../../components/Alarm/AlarmEmergencyContacts.vue";
import AlramPhotos from "../../../components/Alarm/Photos.vue";
import AlarmDevices from "../../../components/Alarm/Devices.vue";
import AlarmDashboardTemparatureChart1 from "../../../components/Alarm/CustomerDashboardTemparatureChart1.vue";
import AlarmDashboardHumidityChart1 from "../../../components/Alarm/CustomerDashboardHumidityChart1.vue";
import CustomerAlarmEvents from "../../../components/Alarm/CustomerAlarmEvents.vue";
import AlarmSettings from "../../../components/Alarm/Settings.vue";
import CustomerDashboard from "../../../components/Alarm/CustomerDashboard.vue";
// import NewCustomer from "../../components/Alarm/NewCustomer.vue";
import BuildingPhotos from "../BuildingPhotos.vue";
import CameraList from "../CameraList.vue";
import CompGoogleMapLatLan from "../CompGoogleMapLatLan.vue";
import EventsBusinessTabFloorPlan from "../../Operator/EventsBusinessTabFloorPlan.vue";
import EventsBusinessTab1Address from "../../Operator/EventsBusinessTab1Address.vue";
import AlarmCustomerEventsLog from "../AlarmCustomerEventsLog.vue";

export default {
  components: {
    AlramCustomerContacts,
    AlramEmergencyContacts,
    AlramPhotos,
    AlarmDevices,
    AlarmSettings,
    CustomerDashboard,
    CustomerAlarmEvents,
    // NewCustomer,
    CompGoogleMapLatLan,
    BuildingPhotos,
    CameraList,
    EventsBusinessTab1Address,
    EventsBusinessTabFloorPlan,
    AlarmCustomerEventsLog,
  },
  props: [
    "_id",
    "isPopup",
    "isMapviewOnly",
    "isEditable",
    "selectedCustomer",
    "selectedAlarm",
  ],
  data: () => ({
    keySelectedItem: 1,
    currentCameraSlide: 0,
    currentSlide: 0,
    browserWidth: 1200,
    alarm: null,
    dialogEditBuilding: false,
    messages: [],
    customerSensors: [],
    keyContacts: 1,
    keyEmergencyy: 1,
    keyPhotos: 1,
    keyDevices: 1,
    keyReports: 1,
    keyEvents: 1,
    keyAutomation: 1,
    keyPayments: 1,
    keySettings: 1,
    keyArmed: 1,

    key: 1,
    profile_percentage: 0,
    tab: null,
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
    data: null,
    BuildingTypes: null,
    building_type_name: null,
    customer_contacts: null,
    customer_primary_contact: null,
    customer_secondary_contact: null,
    browserHeight: 550,
    customer: null,
  }),
  computed: {},
  mounted() {},
  created() {
    // try {
    //   if (window) this.browserWidth = window.innerWidth;
    // } catch (e) {}
    if (this._id) this.getDataFromApi();

    this.getUniqueDevices();
    this.getCustomerProfilePercentage();

    if (window) {
      if (window) {
        this.browserHeight = 550; //window.innerHeight - 100;
      }
    }

    if (this.selectedAlarm) {
      this.alarm = this.selectedAlarm;
    } else {
      this.alarm = { device: { customer: this.selectedCustomer } };
    }
  },
  // watch: {
  //   "selectedCustomer.alarm_status": function () {
  //     this.keySelectedItem++;

  //     console.log(this.keySelectedItem);
  //   },
  // },

  methods: {
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
    async updateContactsData() {
      // console.log("reloadContent");
      try {
        await this.getDataFromApi();
      } catch (e) {}
      this.keyEmergencyy++;
    },
    gotoCustomers() {
      this.$router.push("/alarm/customers");
      return;
    },
    changeTab() {
      // if (this.tab == "tab-1") {
      //   this.keyContacts = this.keyContacts + 1;
      // } else if (this.tab == "tab-2") {
      //   this.keyEmergencyy = this.keyEmergencyy + 1;
      // } else if (this.tab == "tab-3") {
      //   this.keyPhotos = this.keyPhotos + 1;
      // } else if (this.tab == "tab-4") {
      //   this.keyDevices = this.keyDevices + 1;
      // } else if (this.tab == "tab-5") {
      //   this.keyReports = this.keyReports + 1;
      // } else if (this.tab == "tab-6") {
      //   this.keyEvents = this.keyEvents + 1;
      // } else if (this.tab == "tab-7") {
      //   this.keyAutomation = this.keyAutomation + 1;
      // } else if (this.tab == "tab-8") {
      //   this.keyPayments = this.keyPayments + 1;
      // } else if (this.tab == "tab-9") {
      //   this.keySettings = this.keySettings + 1;
      // } else if (this.tab == "tab-10") {
      //   this.keyArmed = this.keyArmed + 1;
      // }
    },
    closeDialog() {
      this.key = this.key + 1;
      this.getDataFromApi();
      this.getCustomerProfilePercentage();
      this.$emit("closeCustomerDialog");
    },
    getBuildingTypeById(id) {
      let buildingTypes =
        this.$store.state.storeAlarmControlPanel.BuildingTypes;
      if (buildingTypes) {
        let specificValue = buildingTypes.find((e) => e.id == id);

        return (this.data.building_type_name = specificValue.name);
      } else {
        return "";
      }
    },

    getBuildingTypes() {
      // console.log(
      //   " BuildingTypes2222",
      //   this.$store.state.storeAlarmControlPanel.BuildingTypes
      // );
    },
    closePopup() {
      this.$emit("closeCustomerDialog");
    },
    getUniqueDevices() {
      this.payloadOptions = {
        params: {
          company_id: this.$auth.user.company_id,
          customer_id: this._id,
        },
      };

      try {
        this.$axios
          .get(`customer_device_types`, this.payloadOptions)
          .then(({ data }) => {
            this.customerSensors = data;
          });
      } catch (e) {}
    },
    getCustomerProfilePercentage() {
      this.payloadOptions = {
        params: {
          company_id: this.$auth.user.company_id,
          customer_id: this._id,
        },
      };

      try {
        this.$axios
          .get(`customer_profile_completion_percentage`, this.payloadOptions)
          .then(({ data }) => {
            this.profile_percentage = data.percentage;
            if (data.percentage == 100) {
              this.messages = "Profile is successfully completed";
            } else
              data.message.forEach((element) => {
                this.messages = this.messages + element + "\n";
              });
          });
      } catch (e) {}
    },

    async getDataFromApi() {
      if (this._id) {
        this.payloadOptions = {
          params: {
            company_id: this.$auth.user.company_id,
            customer_id: this._id,
          },
        };

        try {
          this.$axios.get(`customers`, this.payloadOptions).then(({ data }) => {
            this.data = data.data[0] || null;
            this.customer = this.data;

            //console.log("customer", this.customer);

            if (this.data) {
              this.customer_contacts = this.data.contacts;

              this.getBuildingTypes();
              this.building_type_name = this.getBuildingTypeById(
                this.data.building_type_id
              );
              this.data.building_type_name = this.building_type_name;
            }
          });
        } catch (e) {}

        const { data } = await this.$axios.get("address_types", {
          params: {
            company_id: this.$auth.user.company_id,
          },
        });

        this.$store.commit("storeAlarmControlPanel/AddressTypes", data);
      }
    },
  },
};
</script>
