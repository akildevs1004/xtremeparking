<template>
  <div>
    <v-row>
      <v-col cols="3" class="pr-0" style="max-width: 25%">
        <v-card class="elevation-2" style="height: 180px">
          <v-card-text>
            <CustomersDeviceStatsPieChart
              v-if="key"
              :key="key"
              :name="'CustomersDeviceStatsPieChart'"
          /></v-card-text>
        </v-card>
      </v-col>
      <v-col cols="3" class="pr-0" style="max-width: 25%">
        <v-card class="elevation-2" style="height: 180px">
          <v-card-text>
            <CustomersTypePieChart
              v-if="key"
              :key="key"
              :name="'CustomersTypePieChart'"
            />
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="3" class="pr-0">
        <v-card class="elevation-2" style="height: 180px">
          <v-card-text>
            <CustomersExpirein30daysPieChart
              v-if="key"
              :key="key"
              :name="'CustomersExpirein30daysPieChart'"
            />
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="3" class="pr-0">
        <v-card class="elevation-2" style="height: 180px">
          <v-card-text>
            <CustomersContractExpiteChart
              :key="key"
              name="AlamCustomerContractPieChartCustomer"
              height="150"
            />
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- <v-row
        ><v-col><v-divider color="#DDD" /></v-col>
      </v-row> -->
  </div>
</template>

<script>
import CustomersTypePieChart from "../../components/Alarm/Dashboard/CustomersBuildingTypePieChart.vue";
import CustomersContractExpiteChart from "./Dashboard/CustomersContractExpiteChart.vue";
import CustomersExpirein30daysPieChart from "./Dashboard/CustomersExpirein30daysPieChart.vue";
import CustomersDeviceStatsPieChart from "./Dashboard/CustomersDeviceTypesStatsPieChart.vue";

// import AlamAllEvents from "../../components/Alarm/ComponentAllEvents.vue";

export default {
  props: ["setIntervalLoopstatus"],
  layout: "security_old",
  components: {
    CustomersTypePieChart,
    CustomersContractExpiteChart,
    CustomersExpirein30daysPieChart,
    CustomersDeviceStatsPieChart,

    // AlamAllEvents,
  },
  data: () => ({
    dialogEventsList: false,
    // burglaryOnline: { offline: 0, online: 0 },
    // fireOnline: { offline: 0, online: 0 },
    // waterOnline: { offline: 0, online: 0 },
    // temperatureOnline: { offline: 0, online: 0 },
    // medicalOnline: { offline: 0, online: 0 },

    onlineStats: {
      Burglary: { online: 0, offline: 0 },
      Water: { online: 0, offline: 0 },
      Fire: { online: 0, offline: 0 },
      Temperature: { online: 0, offline: 0 },
      Medical: { online: 0, offline: 0 },
    },

    key: false,
    profile_percentage: 60,
    tab: null,

    _id: null,
    date_from: null,
    date_to: null,
  }),
  computed: {},
  mounted() {
    setTimeout(() => {
      this.key = 1;
      this.getDatafromApi();
    }, 2000);
  },
  async created() {
    setInterval(() => {
      if (this.$route.name == "alarm-customers") {
        if (this.setIntervalLoopstatus) {
          this.key = this.key + 1;
          this.getDatafromApi();
        }
      }
    }, 1000 * 60);
    // this._id = this.$route.params.id;
    let today = new Date();

    let monthObj = this.$dateFormat.monthStartEnd(today);
    this.date_from = monthObj.first;
    this.date_to = monthObj.last;
  },
  watch: {},
  methods: {
    getPercentage($key) {
      let test =
        (this.onlineStats.Temperature.online * 100) /
        (this.onlineStats.Temperature.online,
        this.onlineStats.Temperature.offline);

      return test;
    },
    getDatafromApi() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      this.$axios
        .get(`/security_device_live_stats_groupby`, options)
        .then(({ data }) => {
          this.onlineStats = data;
        });
    },
  },
};
</script>
