<template>
  <div
    max-width="100%"
    style="background-color: #516067; height: 100%; overflow: hidden"
  >
    <v-row>
      <v-col style="width: 100%; text-align: center" v-if="customerObject">
        <div @click="closeDialog()" style="position: absolute; right: 0px">
          <v-icon style="color: black3333">mdi-close-circle</v-icon>
        </div>
        <h4 style="color: #fff">Customer Information</h4>
        <div>
          <img
            :src="customerObject?.profile_picture || '/no-business_profile.png'"
            style="
              width: 100%;
              border-radius: 6px;
              width: 100px;
              margin: auto;
              vertical-align: bottom;
            "
          />
        </div>

        <div style="padding: 5px">
          <table class="operatormap" style="width: 100%">
            <tr>
              <td class="bold">Building</td>
              <td class="bold">{{ customerObject?.building_name || "---" }}</td>
            </tr>
            <tr>
              <td>Property</td>
              <td>{{ customerObject?.buildingtype?.name || "---" }}</td>
            </tr>

            <tr>
              <td>Address</td>
              <td>{{ customerObject?.house_number || "---" }}</td>
            </tr>
            <tr>
              <td></td>
              <td>{{ customerObject?.street_number || "---" }}</td>
            </tr>
            <tr>
              <td></td>
              <td>
                {{ customerObject?.area || "---" }},
                {{ customerObject?.city || "---" }}
              </td>
            </tr>

            <tr>
              <td>Landmark</td>
              <td>{{ customerObject?.landmark || "---" }}</td>
            </tr>

            <tr>
              <td>Primary</td>
              <td>
                {{
                  $utils.caps(
                    customerObject?.primary_contact?.first_name || "---"
                  )
                }}
                {{
                  $utils.caps(
                    customerObject?.primary_contact?.last_name || "---"
                  )
                }}
              </td>
            </tr>

            <tr>
              <td>Phone1</td>
              <td>{{ customerObject?.phone1 || "---" }}</td>
            </tr>
            <tr>
              <td>Phone2</td>
              <td>{{ customerObject?.phone2 || "---" }}</td>
            </tr>
            <tr>
              <td>Whatsapp</td>
              <td>{{ customerObject?.whatsapp || "---" }}</td>
            </tr>
            <tr>
              <td>Email</td>
              <td>{{ customerObject?.email || "---" }}</td>
            </tr>
            <tr>
              <td>Store Open</td>
              <td>
                <v-icon size="15">mdi-clock-outline</v-icon>
                {{ customerObject?.open_time || "---" }}
              </td>
            </tr>
            <tr>
              <td>Store Close</td>
              <td>
                <v-icon size="15">mdi-clock-outline</v-icon>
                {{ customerObject?.close_time || "---" }}
              </td>
            </tr>
          </table>
        </div>

        <!-- <div>
          <img
            :src="customerObject?.profile_picture || '/no-business_profile.png'"
            style="
              width: 100%;
              border-radius: 6px;
              width: 90%;
              margin: auto;
              vertical-align: bottom;
            "
          />
        </div> -->
        <!--   <div>
           <img
            :src="customer?.profile_picture"
            style="
              width: 100%;
              border-radius: 6px;
              width: 150px;
              margin: auto;
              vertical-align: bottom;
            "
          />
        </div>-->
        <!-- <div v-for="profile_picture in customer.profile_pictures">
          <img
            :src="profile_picture.picture"
            style="
              width: 100%;
              border-radius: 6px;
              width: 150px;
              margin: auto;
              vertical-align: bottom;
            "
          />
        </div> -->
      </v-col>
    </v-row>
  </div>
</template>

<script>
// import AlramCustomerContacts from "../../components/Alarm/CustomerContacts.vue";
// import AlramEmergencyContacts from "../../components/Alarm/AlarmEmergencyContacts.vue";
// import AlramPhotos from "../../components/Alarm/Photos.vue";
// import AlarmDevices from "../../components/Alarm/Devices.vue";
// import AlarmDashboardTemparatureChart1 from "../../components/Alarm/CustomerDashboardTemparatureChart1.vue";
// import AlarmDashboardHumidityChart1 from "../../components/Alarm/CustomerDashboardHumidityChart1.vue";
// import AlarmDashboardTemparatureChart2 from "../../components/Alarm/CustomerDashboardTemparatureChart2.vue";
// import CustomerAlarmEvents from "../../components/Alarm/CustomerAlarmEvents.vue";
// import AlarmSettings from "../../components/Alarm/Settings.vue";
// import CustomerDashboard from "../../components/Alarm/CustomerDashboard.vue";
// // import NewCustomer from "../../components/Alarm/NewCustomer.vue";
// import DeviceArmedLogs from "./DeviceArmedLogs.vue";
// import AlamAllEvents from "../Alarm/ComponentAllEvents.vue";
// import AlarmEditContact from "../../components/Alarm/EditContacts.vue";
// import BuildingPhotos from "./BuildingPhotos.vue";

export default {
  components: {
    // AlramCustomerContacts,
    // AlramEmergencyContacts,
    // AlramPhotos,
    // AlarmDevices,
    // AlarmDashboardTemparatureChart1,
    // AlarmDashboardHumidityChart1,
    // AlarmSettings,
    // CustomerDashboard,
    // CustomerAlarmEvents,
    // // NewCustomer,
    // DeviceArmedLogs,
    // AlamAllEvents,
    // AlarmEditContact,
    // BuildingPhotos,
  },
  props: ["_id", "isPopup", "isMapviewOnly", "isEditable", "customerObject"],
  data: () => ({
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
  }),
  computed: {},
  mounted() {
    console.log(this.customerObject);
  },
  created() {
    // // if (this.customer_id) {
    // //   this._id = this.customer_id;
    // // }
    // //this._id = this.$route.params.id;
    // if (this._id) this.getDataFromApi();
    // // setTimeout(() => {
    // //   if (this._id) this.getDataFromApi();
    // // }, 1000 * 2);
    // // setTimeout(() => {
    // //   this.getBuildingTypes();
    // // }, 3000);
    // this.getUniqueDevices();
    // this.getCustomerProfilePercentage();
  },
  watch: {},
  methods: {
    closeDialog() {
      this.$emit("closeDialog");
    },
    // async updateContactsData() {
    //   console.log("reloadContent");
    //   try {
    //     await this.getDataFromApi();
    //   } catch (e) {}
    //   this.keyEmergencyy++;
    // },
    // gotoCustomers() {
    //   this.$router.push("/alarm/customers");
    //   return;
    // },
    // changeTab() {
    //   if (this.tab == "tab-1") {
    //     this.keyContacts = this.keyContacts + 1;
    //   } else if (this.tab == "tab-2") {
    //     this.keyEmergencyy = this.keyEmergencyy + 1;
    //   } else if (this.tab == "tab-3") {
    //     this.keyPhotos = this.keyPhotos + 1;
    //   } else if (this.tab == "tab-4") {
    //     this.keyDevices = this.keyDevices + 1;
    //   } else if (this.tab == "tab-5") {
    //     this.keyReports = this.keyReports + 1;
    //   } else if (this.tab == "tab-6") {
    //     this.keyEvents = this.keyEvents + 1;
    //   } else if (this.tab == "tab-7") {
    //     this.keyAutomation = this.keyAutomation + 1;
    //   } else if (this.tab == "tab-8") {
    //     this.keyPayments = this.keyPayments + 1;
    //   } else if (this.tab == "tab-9") {
    //     this.keySettings = this.keySettings + 1;
    //   } else if (this.tab == "tab-10") {
    //     this.keyArmed = this.keyArmed + 1;
    //   }
    // },
    // closeDialog() {
    //   this.key = this.key + 1;
    //   this.getDataFromApi();
    //   this.getCustomerProfilePercentage();
    //   this.$emit("closeCustomerDialog");
    // },
    // getBuildingTypeById(id) {
    //   let buildingTypes =
    //     this.$store.state.storeAlarmControlPanel.BuildingTypes;
    //   if (buildingTypes) {
    //     let specificValue = buildingTypes.find((e) => e.id == id);
    //     return (this.data.building_type_name = specificValue.name);
    //   } else {
    //     return "";
    //   }
    // },
    // // verifyDeviceSensorName(sensorName, devices) {
    // //   for (let device of devices) {
    // //     if (device.device_type == sensorName) {
    // //       return true;
    // //     }
    // //     if (device.sensorzones) {
    // //       let filter = device.sensorzones.filter(
    // //         (e) => e.sensor_name == sensorName
    // //       );
    // //       if (filter.length > 0) {
    // //         return true;
    // //       }
    // //     }
    // //   }
    // //   return false;
    // // },
    // getBuildingTypes() {
    //   // console.log(
    //   //   " BuildingTypes2222",
    //   //   this.$store.state.storeAlarmControlPanel.BuildingTypes
    //   // );
    // },
    // closePopup() {
    //   this.$emit("closeCustomerDialog");
    // },
    // getUniqueDevices() {
    //   this.payloadOptions = {
    //     params: {
    //       company_id: this.$auth.user.company_id,
    //       customer_id: this._id,
    //     },
    //   };
    //   try {
    //     this.$axios
    //       .get(`customer_device_types`, this.payloadOptions)
    //       .then(({ data }) => {
    //         this.customerSensors = data;
    //       });
    //   } catch (e) {}
    // },
    // getCustomerProfilePercentage() {
    //   this.payloadOptions = {
    //     params: {
    //       company_id: this.$auth.user.company_id,
    //       customer_id: this._id,
    //     },
    //   };
    //   try {
    //     this.$axios
    //       .get(`customer_profile_completion_percentage`, this.payloadOptions)
    //       .then(({ data }) => {
    //         this.profile_percentage = data.percentage;
    //         if (data.percentage == 100) {
    //           this.messages = "Profile is successfully completed";
    //         } else
    //           data.message.forEach((element) => {
    //             this.messages = this.messages + element + "\n";
    //           });
    //       });
    //   } catch (e) {}
    // },
    // async getDataFromApi() {
    //   if (this._id) {
    //     this.payloadOptions = {
    //       params: {
    //         company_id: this.$auth.user.company_id,
    //         customer_id: this._id,
    //       },
    //     };
    //     try {
    //       this.$axios.get(`customers`, this.payloadOptions).then(({ data }) => {
    //         this.data = data.data[0] || null;
    //         this.customer = this.data;
    //         //console.log("customer", this.customer);
    //         if (this.data) {
    //           this.customer_contacts = this.data.contacts;
    //         }
    //         setTimeout(() => {
    //           this.getBuildingTypes();
    //           this.building_type_name = this.getBuildingTypeById(
    //             this.data.building_type_id
    //           );
    //           this.data.building_type_name = this.building_type_name;
    //         }, 3000);
    //       });
    //     } catch (e) {}
    //     const { data } = await this.$axios.get("address_types", {
    //       params: {
    //         company_id: this.$auth.user.company_id,
    //       },
    //     });
    //     this.$store.commit("storeAlarmControlPanel/AddressTypes", data);
    //   }
    // },
  },
};
</script>
