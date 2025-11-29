<template>
  <div max-width="100%">
    <v-row>
      <v-col style="max-width: 115px">
        <v-tabs
          style="max-width: 90px; min-height: 100%"
          icons-and-text
          v-model="tab"
          vertical
          background-color="#203864"
          dark
          color="#0aafeb"
          class="customer-tabs-right-line"
        >
          <v-tab href="#Customer" class="customer-tab">
            Customer
            <v-icon>mdi-card-account-details</v-icon> </v-tab
          ><v-tab class="customer-tab">
            All Contacts
            <v-icon>mdi-account-tie</v-icon>
          </v-tab>
          <v-tab class="customer-tab">
            Discussions
            <v-icon>mdi-text-box-outline</v-icon>
          </v-tab>
          <v-tab class="customer-tab">
            Old Events
            <v-icon>mdi-text-box-outline</v-icon>
          </v-tab>
          <v-tab class="customer-tab">
            Armed Report
            <v-icon>mdi-text-box-outline</v-icon>
          </v-tab>
          <!-- <v-tab class="customer-tab">
            Primary
            <v-icon>mdi-account-tie</v-icon>
          </v-tab>
          <v-tab class="customer-tab">
            Secondary
            <v-icon>mdi-account</v-icon>
          </v-tab>
          <v-tab class="customer-tab">
            Watchman
            <v-icon>mdi-shield-account</v-icon>
          </v-tab>
          <v-tab class="customer-tab">
            Poilice
            <v-icon>mdi-car-emergency</v-icon> </v-tab
          ><v-tab class="customer-tab">
            Medical
            <v-icon>mdi-medical-bag</v-icon> </v-tab
          ><v-tab class="customer-tab">
            Fire
            <v-icon>mdi-fire</v-icon> </v-tab
          ><v-tab class="customer-tab">
            Technician
            <v-icon>mdi mdi-briefcase-account</v-icon>
          </v-tab> -->
        </v-tabs></v-col
      >
      <v-col style="padding-left: 0px">
        <v-tabs-items v-model="tab" style="overflow: visible">
          <v-tab-item value="Customer">
            <v-card flat>
              <v-card-text>
                <SecurityBuildingInfo
                  v-if="customer"
                  :customer="customer"
                  :alarm_id="alarmId"
                  :alarm="alarm"
                />
              </v-card-text>
            </v-card>
          </v-tab-item>

          <v-tab-item>
            <v-card flat>
              <v-card-text>
                <AlarmEventAllEmergencyContacts
                  v-if="customer"
                  :alarmId="alarmId"
                  :customer_id="customer.id"
                  :customer_contacts="customer.contacts"
                  :customer="customer"
                  :key="keyAllContacts"
                />
              </v-card-text>
            </v-card>
          </v-tab-item>
          <v-tab-item>
            <v-card flat>
              <v-card-text>
                <SecurityAlarmNotes
                  :alarmId="alarmId"
                  v-if="customer"
                  :customer="customer"
                  :key="keyLogs"
              /></v-card-text>
            </v-card>
          </v-tab-item>
          <v-tab-item>
            <AlamAllEventsPopup
              v-if="customer"
              :filter_customer_id="customer.id"
              name="PopupAlarmEventsCustoemrInfoAlamAllEventsPopup"
            />
          </v-tab-item>
          <v-tab-item>
            <v-card elevation="0">
              <v-card-text>
                <DeviceArmedLogs
                  style="margin-top: 10px"
                  v-if="customer"
                  :customer_id="customer.id"
                  name="PopupAlarmEventsCustoemrInfoDeviceArmedLogs"
              /></v-card-text>
            </v-card>
          </v-tab-item>
          <!-- <v-tab-item>
            <v-card flat>
              <v-card-text style="width: 108%">
                <SecurityContactInfo
                  :alarmId="alarmId"
                  v-if="customer"
                  :customer="customer"
                  :contact_type="'primary'"
                  :key="keyPrimary"
                  :key1="keyPrimary"
                />
              </v-card-text>
            </v-card>
          </v-tab-item>
          <v-tab-item>
            <v-card flat>
              <v-card-text style="width: 108%">
                <SecurityContactInfo
                  :alarmId="alarmId"
                  v-if="customer"
                  :customer="customer"
                  :contact_type="'secondary'"
                  :key="keySecondary"
              /></v-card-text>
            </v-card>
          </v-tab-item>
          <v-tab-item>
            <v-card flat>
              <v-card-text style="width: 108%">
                <SecurityContactInfo
                  :alarmId="alarmId"
                  v-if="customer"
                  :customer="customer"
                  :contact_type="'Watchman'"
                  :key="keySecurity"
              /></v-card-text>
            </v-card>
          </v-tab-item>
          <v-tab-item>
            <v-card flat>
              <v-card-text style="width: 108%">
                <SecurityContactInfo
                  :alarmId="alarmId"
                  v-if="customer"
                  :customer="customer"
                  :contact_type="'Police'"
                  :key="keyPolice"
              /></v-card-text>
            </v-card>
          </v-tab-item>
          <v-tab-item>
            <v-card flat>
              <v-card-text style="width: 108%">
                <SecurityContactInfo
                  :alarmId="alarmId"
                  v-if="customer"
                  :customer="customer"
                  :contact_type="'Medical'"
                  :key="keyMedical"
              /></v-card-text>
            </v-card>
          </v-tab-item>
          <v-tab-item>
            <v-card flat>
              <v-card-text style="width: 108%">
                <SecurityContactInfo
                  :alarmId="alarmId"
                  v-if="customer"
                  :customer="customer"
                  :contact_type="'Fire'"
                  :key="keyFire"
              /></v-card-text>
            </v-card>
          </v-tab-item>
          <v-tab-item>
            <v-card flat>
              <v-card-text style="width: 108%">
                <SecurityContactInfo
                  :alarmId="alarmId"
                  v-if="customer"
                  :customer="customer"
                  :contact_type="'Technician'"
                  :key="keyTechnician"
              /></v-card-text>
            </v-card>
          </v-tab-item> -->
        </v-tabs-items></v-col
      >
    </v-row>
  </div>
</template>

<script>
import SecurityBuildingInfo from "../../components/Alarm/SecurityDashboard/SecurityBuildingInfo.vue";
import SecurityContactInfo from "../../components/Alarm/SecurityDashboard/SecurityContactInfo.vue";
import SecurityAlarmNotes from "../../components/Alarm/SecurityDashboard/SecurityAlarmNotes.vue";
import AlarmEventAllEmergencyContacts from "./AlarmEventAllEmergencyContacts.vue";
import AlamAllEventsPopup from "./ComponentCustomerAllEvents2.vue";
import DeviceArmedLogs from "./DeviceArmedLogs.vue";
export default {
  components: {
    SecurityBuildingInfo,
    SecurityContactInfo,
    SecurityAlarmNotes,
    AlarmEventAllEmergencyContacts,
    AlamAllEventsPopup,
    DeviceArmedLogs,
  },
  props: ["_customerID", "alarmId", "selectedAlarm", "alarm"],
  data: () => ({
    tab: "",
    customer: null,
    key: 1,
    keyAllContacts: 0,
    keyPrimary: 0,
    keySecondary: 0,
    keySecurity: 0,
    keyPolice: 0,

    keyMedical: 0,
    keyLogs: 0,
    keyFire: 0,
    keyTechnician: 0,
  }),
  computed: {},
  mounted() {},
  created() {
    this.getDataFromApi();
  },
  watch: {
    tab: {
      handler(value) {
        if (value == 1) this.keyAllContacts += 1;
        else if (value == 2) this.keyPrimary += 1;
        else if (value == 3) this.keySecondary += 1;
        else if (value == 4) this.keySecurity += 1;
        else if (value == 5) this.keyPolice += 1;
        else if (value == 6) this.keyMedical += 1;
        else if (value == 7) this.keyFire += 1;
        else if (value == 8) this.keyTechnician += 1;
        else if (value == 9) this.keyLogs += 1;
      },
      deep: true,
    },
  },
  methods: {
    getDataFromApi() {
      if (this._customerID) {
        this.payloadOptions = {
          params: {
            company_id: this.$auth.user.company_id,
            customer_id: this._customerID,
          },
        };
        try {
          this.$axios
            .get(`customerinfo`, this.payloadOptions)
            .then(({ data }) => {
              this.customer = data;

              // if (this.data) {
              //   this.customer_contacts = this.data.contacts;
              // }
            });
        } catch (e) {}
      }
    },
  },
};
</script>
<!-- <style>
.customer-tabs-right-line .v-tabs-slider-wrapper {
  left: auto !important;
  right: 0 !important;
}
.customer-tabs-right-line .v-slide-group__content {
  width: 90px;
  font-size: 9px !important;
  min-width: 75px !important;
}

.customer-tab {
  font-size: 9px !important;
  min-width: 75px !important;
}
</style> -->
