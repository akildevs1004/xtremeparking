<template>
  <div max-width="100%">
    <v-row>
      <v-col style="max-width: 300px; min-height: 650px">
        <v-row>
          <v-col>
            <v-card
              elevation="0"
              :style="'margin-top: 0px; height: ' + pageHeight / 2 + 'px'"
              class="customer-background"
              ><v-card-text>
                <v-row>
                  <v-col style="text-align: center">
                    <div
                      style="
                        font-weight: bold;
                        font-size: 16px;
                        text-align: center;
                      "
                    >
                      {{
                        selectedCustomer && selectedCustomer.building_name
                          ? selectedCustomer.building_name
                          : "---"
                      }}
                    </div>

                    <div style="font-size: 13px" class="secondary-text-color">
                      ({{
                        selectedCustomer && selectedCustomer.building_type_id
                          ? getBuildingTypeById(
                              selectedCustomer.building_type_id
                            )
                          : ""
                      }})
                    </div>
                  </v-col>
                  <v-col style="padding: 0px">
                    <v-img
                      class="mx-auto"
                      style="
                        border-radius: 5%;
                        border: 0px solid #ddd;
                        max-height: 150px;
                      "
                      :src="
                        selectedCustomer?.profile_picture ||
                        '/no-business_profile.png'
                      "
                      contain
                    ></v-img>
                  </v-col>
                </v-row>

                <v-row class="mx-auto pt-5">
                  <v-col style="line-height: 25px; padding: 0px">
                    <div
                      style="border-top: 0px solid #ddd"
                      v-if="selectedCustomer"
                    >
                      <v-icon size="15" color="red">mdi-map-marker</v-icon>
                      <span
                        >{{ selectedCustomer.house_number }},

                        {{ selectedCustomer.street_number }},
                        {{ selectedCustomer.area }},
                        {{ selectedCustomer.city }}</span
                      >
                    </div>
                    <div
                      style="border-top: 0px solid #ddd"
                      v-if="selectedCustomer"
                    >
                      {{ selectedCustomer.state }},
                      {{ selectedCustomer.country }}
                    </div>
                    <div
                      style="border-top: 0px solid #ddd"
                      v-if="selectedCustomer"
                    >
                      <v-icon size="15" color="green">mdi-map-marker</v-icon>
                      Landmark:
                      {{ selectedCustomer.landmark }}
                    </div>

                    <div style="border-top: 0px solid #ddd">
                      <v-icon size="15" color="blue">mdi mdi-at</v-icon>
                      {{ customer ? selectedCustomer.user?.email : "---" }}
                    </div>
                  </v-col>
                </v-row>
              </v-card-text></v-card
            >
          </v-col>
        </v-row>

        <v-row class="mt-0 pt-0">
          <v-col class="mt-0 pt-0">
            <v-card
              elevation="0"
              class="customer-background"
              :style="' margin-top: 5px; height: ' + pageHeight / 2 + 'px'"
              ><v-card-text>
                <v-row style="margin-top: 0x">
                  <v-col class="p-0" style="padding: 0px">
                    <v-img
                      style="
                        width: 50%;
                        border-radius: 50%;
                        border: 1px solid #ddd;
                        margin: auto;
                        max-height: 130px;
                      "
                      :src="
                        selectedCustomer?.primary_contact?.profile_picture
                          ? selectedCustomer.primary_contact.profile_picture
                          : '/no-profile-image.jpg'
                      "
                    ></v-img>
                    <!-- <v-img
                  style="
                    width: 60%;
                    border-radius: 5%;
                    border: 1px solid #ddd;
                    margin: auto;
                    max-height: 130px;
                  "
                  :src="
                    selectedCustomer &&
                    selectedCustomer.primary_contact &&
                    selectedCustomer.primary_contact?.profile_picture
                      ? selectedCustomer.primary_contact?.profile_picture
                      : '/no-profile-image.jpg'
                  "
                ></v-img> -->
                  </v-col>
                  <v-col style="text-align: center">
                    <div
                      style="
                        font-weight: bold;
                        font-size: 16px;
                        text-align: center;
                      "
                    >
                      {{
                        selectedCustomer && selectedCustomer.primary_contact
                          ? selectedCustomer.primary_contact?.first_name +
                            " " +
                            selectedCustomer.primary_contact?.last_name
                          : "---"
                      }}
                    </div>
                    <div style="font-size: 12px" class="secondary-text-color">
                      (Primary Contact)
                    </div>
                  </v-col>
                </v-row>

                <v-row style="margin-top: 0px" class="pt-5">
                  <v-col style="line-height: 30px">
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
                      <v-icon size="13" color="red">mdi-phone</v-icon>
                      {{ selectedCustomer?.primary_contact?.phone1 || "---" }}
                    </div>
                    <div style="border-top: 0px solid #ddd">
                      <v-icon size="13" color="orange"
                        >mdi-phone-classic</v-icon
                      >
                      {{ selectedCustomer?.primary_contact?.phone2 || "---" }}
                    </div>
                    <div style="border-top: 0px solid #ddd">
                      <v-icon size="13" color="green">mdi-whatsapp</v-icon>
                      {{ selectedCustomer?.primary_contact?.whatsapp || "---" }}
                    </div>
                    <div style="border-top: 0px solid #ddd">
                      <v-icon size="13" color="blue">mdi-at</v-icon>
                      {{ selectedCustomer?.primary_contact?.email || "---" }}
                    </div>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-col>

      <v-col class="pl-0">
        <v-card class="v-tabs-items customer-background">
          <v-card-text>
            <v-row>
              <v-col class="pa-0"
                ><v-tabs
                  class="customer-background"
                  style="min-height: 100%; border-radius: 5px"
                  icons-and-text
                  v-model="tab"
                  centered
                >
                  <!-- <v-tab class="customer-tab" style="width: 70px" >
        Customer
        <v-icon>mdi-card-account-details</v-icon>
      </v-tab> -->
                  <v-tab class="customer-tab">
                    Info
                    <v-icon>mdi-card-account-details</v-icon>
                  </v-tab>
                  <!-- <v-tab class="customer-tab" style="width: 70px" >
            Contacts
            <v-icon>mdi-card-account-details</v-icon>
          </v-tab> -->
                  <v-tab class="customer-tab">
                    Contacts
                    <v-icon>mdi-account-tie</v-icon>
                  </v-tab>
                  <v-tab class="customer-tab">
                    Camera
                    <v-icon>mdi-webcam</v-icon>
                  </v-tab>
                  <v-tab class="customer-tab">
                    Devices
                    <v-icon>mdi-account</v-icon>
                  </v-tab>
                  <v-tab class="customer-tab">
                    Sensors
                    <v-icon>mdi-motion-sensor</v-icon>
                  </v-tab>
                  <v-tab class="customer-tab" v-if="!isMapviewOnly">
                    Alerts
                    <v-icon>mdi-bell-ring</v-icon> </v-tab
                  ><v-tab class="customer-tab" v-if="!isMapviewOnly">
                    Invoices
                    <v-icon> mdi-currency-usd</v-icon>
                  </v-tab>

                  <v-tab
                    class="customer-tab"
                    v-if="!isMapviewOnly"
                    @click="keySettings++"
                  >
                    Settings
                    <v-icon>mdi mdi-briefcase-account</v-icon>
                  </v-tab>
                  <v-tab class="customer-tab" v-if="!isMapviewOnly">
                    Subscription
                    <v-icon>mdi-package-up</v-icon>
                  </v-tab>
                </v-tabs></v-col
              >
              <v-col
                class="pa-0 justify-center"
                style="max-width: 250px; margin: auto"
                ><v-btn
                  @click="customersList()"
                  class="primary"
                  elevation="2"
                  small
                >
                  Back to Customers List</v-btn
                ></v-col
              >
            </v-row>
          </v-card-text>
        </v-card>

        <v-row class="mt-0 pt-0">
          <v-col>
            <v-tabs-items
              class="customer-background"
              v-model="tab"
              :style="'overflow: visible; min-height: 650px;    border-radius: 5px;'"
            >
              <!-- <v-tab-item>
        <v-card flat>
          <v-card-text
            ><NewCustomer
              v-if="data"
              :customer_id="_id"
              :customer="data"
              @closeDialog="closeDialog"
            />
          </v-card-text>
        </v-card>
      </v-tab-item> -->

              <v-tab-item>
                <BuildingPhotos
                  :style="'height:' + (pageHeight - 75) + 'px'"
                  v-if="_id"
                  @closeDialog="closeDialog"
                  :customer_id="_id"
                  :customer="data"
                  :isMapviewOnly="isMapviewOnly"
                  :isEditable="isEditable"
                />
              </v-tab-item>
              <!-- <v-tab-item>
            <v-card flat>
              <v-card-text>
                <AlarmEditContact
                  v-if="data"
                  :customer_id="_id"
                  :customer_contacts="customer_contacts"
                  :customer="data"
                  @closeDialog="closeDialog"
                  :isMapviewOnly="isMapviewOnly"
                  :isEditable="isEditable"
                />
              </v-card-text>
            </v-card>
          </v-tab-item> -->

              <v-tab-item>
                <AlramEmergencyContacts
                  :style="'height:' + (pageHeight - 85) + 'px'"
                  v-if="data"
                  @closeDialog="closeDialog"
                  :customer_id="_id"
                  :customer_contacts="customer_contacts"
                  :customer="data"
                  :key="keyEmergencyy"
                  :isMapviewOnly="isMapviewOnly"
                  :isEditable="isEditable"
                  @callrefreshData="updateContactsData()"
                />
              </v-tab-item>
              <v-tab-item>
                <CameraList
                  :style="'height:' + (pageHeight - 75) + 'px'"
                  v-if="_id"
                  @closeDialog="closeDialog"
                  :customer_id="_id"
                  :customer="data"
                  :isMapviewOnly="isMapviewOnly"
                  :isEditable="isEditable"
                />
              </v-tab-item>
              <v-tab-item>
                <AlarmDevices
                  :style="'height:' + (pageHeight - 85) + 'px'"
                  v-if="_id"
                  :customer_id="_id"
                  @closeDialog="closeDialog"
                  :key="keyDevices"
                  :isMapviewOnly="isMapviewOnly"
                  :isEditable="isEditable"
                /> </v-tab-item
              ><v-tab-item>
                <AlramPhotos
                  :style="'height:' + (pageHeight - 75) + 'px'"
                  v-if="_id"
                  :key="keyPhotos"
                  @closeDialog="closeDialog"
                  :customer_id="_id"
                  :isMapviewOnly="isMapviewOnly"
                  :isEditable="isEditable"
                /> </v-tab-item
              ><v-tab-item>
                <AlarmAutomation
                  :style="'height:' + (pageHeight - 75) + 'px'"
                  v-if="_id"
                  :key="keyAutomation"
                  :customer_id="_id"
                  :isMapviewOnly="isMapviewOnly"
                  :isEditable="isEditable"
                /> </v-tab-item
              ><v-tab-item>
                <AlarmPayments
                  :style="'height:' + (pageHeight - 75) + 'px'"
                  v-if="_id"
                  :customer="data"
                  :key="keyPayments"
                  :customer_id="_id"
                  :isMapviewOnly="isMapviewOnly"
                  :isEditable="isEditable"
                  :filter_start_date="filter_start_date"
                  :filter_end_date="filter_end_date"
                /> </v-tab-item
              ><v-tab-item>
                <AlarmSettings
                  :style="'height:' + (pageHeight - 75) + 'px'"
                  v-if="_id"
                  @closeDialog="closeDialog"
                  :key="keySettings"
                  :customer_id="_id"
                  :customer="customer"
                  :isMapviewOnly="isMapviewOnly"
                  :isEditable="isEditable"
                />
              </v-tab-item>
              <v-tab-item>
                <Services
                  :style="'height:' + (pageHeight - 75) + 'px'"
                  v-if="_id"
                  @closeDialog="closeDialog"
                  :key="keySettings"
                  :customer_id="_id"
                  :customer="customer"
                  :isMapviewOnly="isMapviewOnly"
                  :isEditable="isEditable"
                  @callInvoiceTab="OpenInvoiceTab"
                />
              </v-tab-item>
            </v-tabs-items>
          </v-col>
        </v-row>
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
import AlarmDashboardTemparatureChart2 from "../../../components/Alarm/CustomerDashboardTemparatureChart2.vue";
import CustomerAlarmEvents from "../../../components/Alarm/CustomerAlarmEvents.vue";
import AlarmSettings from "../../../components/Alarm/Settings.vue";

import CustomerDashboard from "../../../components/Alarm/CustomerDashboard.vue";
// import NewCustomer from "../../../components/Alarm/NewCustomer.vue";
import DeviceArmedLogs from "../../../components/Alarm/DeviceArmedLogs.vue";
import AlamAllEvents from "../../../components/Alarm/ComponentAllEvents.vue";
import AlarmEditContact from "../../../components/Alarm/EditContacts.vue";
import BuildingPhotos from "../../../components/Alarm/BuildingPhotos.vue";
import CameraList from "../../../components/Alarm/CameraList.vue";
import Services from "../../../components/Alarm/Services.vue";

export default {
  props: {
    _id: {
      type: String,
      required: true,
    },
    isPopup: {
      type: Boolean,
      default: false,
    },
    isEditable: {
      type: Boolean,
      default: false,
    },
  },
  components: {
    AlramCustomerContacts,
    AlramEmergencyContacts,
    AlramPhotos,
    AlarmDevices,
    AlarmDashboardTemparatureChart1,
    AlarmDashboardHumidityChart1,
    AlarmSettings,
    CustomerDashboard,
    CustomerAlarmEvents,
    // NewCustomer,
    DeviceArmedLogs,
    AlamAllEvents,
    AlarmEditContact,
    BuildingPhotos,
    CameraList,
    Services,
  },
  // props: ["_id", "isPopup", "isMapviewOnly", "isEditable", "selectedCustomer"],
  data: () => ({
    isMapviewOnly: false,
    pageHeight: 800,
    filter_start_date: null,
    filter_end_date: null,
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

    data: null,
    BuildingTypes: null,
    building_type_name: null,
    customer_contacts: null,
    customer_primary_contact: null,
    customer_secondary_contact: null,

    customer: null,
    selectedCustomer: null,
    // isPopup: false,
    // isMapviewOnly: false,
    // _id: null,
  }),
  computed: {},
  mounted() {
    this.pageHeight = window.innerHeight - 80;
    window.addEventListener("resize", () => {
      this.pageHeight = window.innerHeight - 80;
    });
  },
  created() {
    // this._id = this._id; //this.$route.params.id;
    this.isPopup = false;
    // this.isMapviewOnly = false;
    // this.isEditable = false;
    // this.selectedCustomer = false;

    // if (this.customer_id) {
    //   this._id = this.customer_id;
    // }
    //this._id = this.$route.params.id;
    if (this._id) this.getDataFromApi();
    // setTimeout(() => {
    //   if (this._id) this.getDataFromApi();
    // }, 1000 * 2);

    // setTimeout(() => {
    //   this.getBuildingTypes();
    // }, 3000);

    this.getUniqueDevices();
    this.getCustomerProfilePercentage();
  },
  watch: {},
  methods: {
    customersList() {
      this.$router.push("/alarm/customers");
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

    OpenInvoiceTab(start_date, end_date) {
      this.filter_start_date = start_date;
      this.filter_end_date = end_date;

      this.tab = 6;
      this.keyPayments = this.keyPayments + 1;
    },
    changeTab() {
      if (this.tab == "tab-1") {
        this.keyContacts = this.keyContacts + 1;
      } else if (this.tab == "tab-2") {
        this.keyEmergencyy = this.keyEmergencyy + 1;
      } else if (this.tab == "tab-3") {
        this.keyPhotos = this.keyPhotos + 1;
      } else if (this.tab == "tab-4") {
        this.keyDevices = this.keyDevices + 1;
      } else if (this.tab == "tab-5") {
        this.keyReports = this.keyReports + 1;
      } else if (this.tab == "tab-6") {
        this.keyEvents = this.keyEvents + 1;
      } else if (this.tab == "tab-7") {
        this.keyAutomation = this.keyAutomation + 1;
      } else if (this.tab == "tab-8") {
        this.keyPayments = this.keyPayments + 1;
      } else if (this.tab == "tab-9") {
        this.keySettings = this.keySettings + 1;
      } else if (this.tab == "tab-10") {
        this.keyArmed = this.keyArmed + 1;
      }
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
    // verifyDeviceSensorName(sensorName, devices) {
    //   for (let device of devices) {
    //     if (device.device_type == sensorName) {
    //       return true;
    //     }

    //     if (device.sensorzones) {
    //       let filter = device.sensorzones.filter(
    //         (e) => e.sensor_name == sensorName
    //       );
    //       if (filter.length > 0) {
    //         return true;
    //       }
    //     }
    //   }

    //   return false;
    // },
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

            this.selectedCustomer = this.data;

            //console.log("customer", this.customer);

            if (this.data) {
              this.customer_contacts = this.data.contacts;
            }

            setTimeout(() => {
              this.getBuildingTypes();
              this.building_type_name = this.getBuildingTypeById(
                this.data.building_type_id
              );
              this.data.building_type_name = this.building_type_name;
            }, 3000);
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
