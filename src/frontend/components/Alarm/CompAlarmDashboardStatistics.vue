<template>
  <div>
    <v-row>
      <v-col style="height: 80px">
        <v-card elevation="2" style="height: 80px"
          ><v-card-text
            ><v-row>
              <v-col class="d-flex1 justify-center" style="max-width: 80px">
                <v-icon fill color="red" size="40" style="clear: both"
                  >mdi-folder-open-outline</v-icon
                >
                <div style="">OPEN</div>
              </v-col>

              <v-col
                class="d-flex justify-center"
                style="font-size: 40px; margin: auto; font-weight: bold"
                >{{ alarmStatistics.open }}</v-col
              ></v-row
            ></v-card-text
          ></v-card
        >
      </v-col>
      <v-col>
        <v-card elevation="2" style="height: 80px"
          ><v-card-text
            ><v-row>
              <v-col class="d-flex1 justify-center" style="max-width: 80px">
                <v-icon fill color="#07af50" size="40" style="clear: both"
                  >mdi-lock-outline</v-icon
                >
                <div style="">CLOSED</div>
              </v-col>

              <v-col
                class="d-flex justify-center"
                style="font-size: 40px; margin: auto; font-weight: bold"
                >{{ alarmStatistics.closed }}</v-col
              ></v-row
            ></v-card-text
          ></v-card
        >
      </v-col>
      <v-col>
        <v-card elevation="2" style="height: 80px"
          ><v-card-text
            ><v-row>
              <v-col class="d-flex1 justify-center" style="max-width: 80px">
                <v-icon fill color="#0046ff" size="40" style="clear: both"
                  >mdi-lock-open-variant-outline</v-icon
                >
                <div style="">Forward</div>
              </v-col>

              <v-col
                class="d-flex justify-center"
                style="font-size: 40px; margin: auto; font-weight: bold"
                >{{ alarmStatistics.forwarded }}</v-col
              ></v-row
            ></v-card-text
          ></v-card
        >
      </v-col>
      <v-col cols="5" style="height: 80px">
        <v-card elevation="2" style="height: 80px; margin: auto"
          ><v-card-text
            ><v-row style="margin: auto">
              <v-col
                class="d-flex justify-center"
                style="font-size: 25px; margin: auto"
                >High</v-col
              ><v-col
                class="d-flex justify-center"
                style="
                  font-size: 40px;
                  margin: auto;
                  font-weight: bold;
                  color: #a70000;
                  border-right: 1px solid #ddd;
                "
              >
                {{ alarmStatistics.high }}</v-col
              >
              <v-col
                class="d-flex justify-center"
                style="font-size: 25px; margin: auto"
                >Medium</v-col
              ><v-col
                class="d-flex justify-center"
                style="
                  font-size: 40px;
                  margin: auto;
                  font-weight: bold;
                  color: #1e71c3;
                  border-right: 1px solid #ddd;
                "
              >
                {{ alarmStatistics.medium }}</v-col
              >
              <v-col
                class="d-flex justify-center"
                style="font-size: 25px; margin: auto"
                >Low</v-col
              ><v-col
                class="d-flex justify-center"
                style="
                  font-size: 40px;
                  margin: auto;
                  font-weight: bold;
                  color: #a2b117;
                "
              >
                {{ alarmStatistics.low }}</v-col
              ></v-row
            ></v-card-text
          ></v-card
        >
      </v-col>
    </v-row>
    <!-- <v-row>
      <v-col cols="3" class="pr-0" style="max-width: 20%">
        <v-card class="elevation-2" style="height: 180px">
          <v-card-text>
            <AlarmBurglaryPieChart
              v-if="key"
              :key="key"
              :name="'AlarmBurglaryPieChart'"
              :compFilterSupervisor="compFilterSupervisor"
              :filter_customers_list="filter_customers_list"
          /></v-card-text>
        </v-card>
      </v-col>
      <v-col cols="3" class="pr-0" style="max-width: 20%">
        <v-card class="elevation-2" style="height: 180px">
          <v-card-text>
            <AlarmMedicalPieChart
              v-if="key"
              :key="key"
              :name="'AlarmMedicalPieChart'"
              :compFilterSupervisor="compFilterSupervisor"
              :filter_customers_list="filter_customers_list"
          /></v-card-text>
        </v-card>
      </v-col>
      <v-col cols="3" class="pr-0" style="max-width: 20%">
        <v-card class="elevation-2" style="height: 180px">
          <v-card-text>
            <AlarmWaterPieChart
              v-if="key"
              :key="key"
              :name="'AlarmWaterPieChart3'"
              :compFilterSupervisor="compFilterSupervisor"
              :filter_customers_list="filter_customers_list"
          /></v-card-text>
        </v-card>
      </v-col>
      <v-col cols="3" class="pr-0" style="max-width: 20%">
        <v-card class="elevation-2" style="height: 180px">
          <v-card-text>
            <AlarmFirePieChart
              v-if="key"
              :key="key"
              :name="'AlarmBFirePieChart4'"
              :compFilterSupervisor="compFilterSupervisor"
              :filter_customers_list="filter_customers_list"
          /></v-card-text>
        </v-card>
      </v-col>
      <v-col cols="3" style="max-width: 20%">
        <v-card class="elevation-2" style="height: 180px">
          <v-card-text>
            <AlarmTemperaturePieChart
              v-if="key"
              :key="key"
              :name="'AlarmTemperaturePieChart5'"
              :compFilterSupervisor="compFilterSupervisor"
              :filter_customers_list="filter_customers_list"
          /></v-card-text>
        </v-card>
      </v-col>
    </v-row> -->

    <!-- <v-row style="margin-top: 0px">
        <v-col cols="3" class="pr-0" style="max-width: 20%">
          <v-card style="height: 35px; padding: 10px" class="elevation-0">
            <v-progress-linear
              style="pointer-events: none"
              rounded
              v-model="onlineStats.Burglary.percentage"
              :background-color="
                onlineStats.Burglary.online + onlineStats.Burglary.offline == 0
                  ? '#969696'
                  : 'red'
              "
              color="green"
              height="12"
            >
              <template v-slot:default="{ value }" style="color: #fff">
                <span
                  style="
                    width: 50%;
                    font-size: 12px;
                    text-align: left;
                    padding-left: 5%;
                    color: #fff;
                  "
                >
                  {{ onlineStats.Burglary.online }}
                </span>
                <span
                  style="
                    width: 50%;
                    font-size: 12px;
                    text-align: right;
                    padding-right: 5%;
                    color: #fff;
                  "
                >
                  {{ onlineStats.Burglary.offline }}
                </span>
              </template>
            </v-progress-linear>
            <v-row style="font-size: 10px"
              ><v-col>Online</v-col
              ><v-col style="text-align: right; padding-right: 6%"
                >Offline</v-col
              ></v-row
            >
          </v-card>
        </v-col>
        <v-col cols="3" class="pr-0" style="max-width: 20%">
          <v-card style="height: 35px; padding: 10px" class="elevation-0">
            <v-progress-linear
              style="pointer-events: none"
              rounded
              v-model="onlineStats.Medical.percentage"
              :background-color="
                onlineStats.Medical.online + onlineStats.Medical.offline == 0
                  ? '#969696'
                  : 'red'
              "
              color="green"
              height="12"
            >
              <template v-slot:default="{ value }" style="color: #fff">
                <span
                  style="
                    width: 50%;
                    font-size: 12px;
                    text-align: left;
                    padding-left: 5%;
                    color: #fff;
                  "
                >
                  {{ onlineStats.Medical.online }}
                </span>
                <span
                  style="
                    width: 50%;
                    font-size: 12px;
                    text-align: right;
                    padding-right: 5%;
                    color: #fff;
                  "
                >
                  {{ onlineStats.Medical.offline }}
                </span>
              </template>
            </v-progress-linear>
            <v-row style="font-size: 10px"
              ><v-col>Online</v-col
              ><v-col style="text-align: right; padding-right: 6%"
                >Offline</v-col
              ></v-row
            >
          </v-card>
        </v-col>
        <v-col cols="3" class="pr-0" style="max-width: 20%">
          <v-card style="height: 35px; padding: 10px" class="elevation-0">
            <v-progress-linear
              style="pointer-events: none"
              rounded
              v-model="onlineStats.Water.percentage"
              :background-color="
                onlineStats.Water.online + onlineStats.Water.offline == 0
                  ? '#969696'
                  : 'red'
              "
              color="green"
              height="12"
            >
              <template v-slot:default="{ value }" style="color: #fff">
                <span
                  style="
                    width: 50%;
                    font-size: 12px;
                    text-align: left;
                    padding-left: 5%;
                    color: #fff;
                  "
                >
                  {{ onlineStats.Water.online }}
                </span>
                <span
                  style="
                    width: 50%;
                    font-size: 12px;
                    text-align: right;
                    padding-right: 5%;
                    color: #fff;
                  "
                >
                  {{ onlineStats.Water.offline }}
                </span>
              </template>
            </v-progress-linear>
            <v-row style="font-size: 10px"
              ><v-col>Online</v-col
              ><v-col style="text-align: right; padding-right: 6%"
                >Offline</v-col
              ></v-row
            >
          </v-card>
        </v-col>
        <v-col cols="3" class="pr-0" style="max-width: 20%">
          <v-card style="height: 35px; padding: 10px" class="elevation-0">
            <v-progress-linear
              style="pointer-events: none"
              rounded
              v-model="onlineStats.Fire.percentage"
              :background-color="
                onlineStats.Fire.online + onlineStats.Fire.offline == 0
                  ? '#969696'
                  : 'red'
              "
              color="green"
              height="12"
            >
              <template v-slot:default="{ value }" style="color: #fff">
                <span
                  style="
                    width: 50%;
                    font-size: 12px;
                    text-align: left;
                    padding-left: 5%;
                    color: #fff;
                  "
                >
                  {{ onlineStats.Fire.online }}
                </span>
                <span
                  style="
                    width: 50%;
                    font-size: 12px;
                    text-align: right;
                    padding-right: 5%;
                    color: #fff;
                  "
                >
                  {{ onlineStats.Fire.offline }}
                </span>
              </template>
            </v-progress-linear>
            <v-row style="font-size: 10px"
              ><v-col>Online</v-col
              ><v-col style="text-align: right; padding-right: 6%"
                >Offline</v-col
              ></v-row
            >
          </v-card>
        </v-col>

        <v-col cols="3" style="max-width: 20%">
          <v-card style="height: 35px; padding: 10px" class="elevation-0">
            <v-progress-linear
              style="pointer-events: none"
              rounded
              v-model="onlineStats.Temperature.percentage"
              :background-color="
                onlineStats.Temperature.online +
                  onlineStats.Temperature.offline ==
                0
                  ? '#969696'
                  : 'red'
              "
              color="#FFF"
              height="12"
            >
              <template v-slot:default="{ value }" style="color: #fff">
                <span
                  style="
                    width: 50%;
                    font-size: 12px;
                    text-align: left;
                    padding-left: 5%;
                    color: #fff;
                  "
                >
                  {{ onlineStats.Temperature.online }}
                </span>
                <span
                  style="
                    width: 50%;
                    font-size: 12px;
                    text-align: right;
                    padding-right: 5%;
                    color: #fff;
                  "
                >
                  {{ onlineStats.Temperature.offline }}
                </span>
              </template>
            </v-progress-linear>
            <v-row style="font-size: 10px"
              ><v-col>Online</v-col
              ><v-col style="text-align: right; padding-right: 6%"
                >Offline</v-col
              ></v-row
            >
          </v-card>
        </v-col>
      </v-row> -->
    <!-- <v-row
        ><v-col><v-divider color="#DDD" /></v-col>
      </v-row> -->
  </div>
</template>

<script>
// import AlarmBurglaryPieChart from "../../components/Alarm/SecurityDashboard/AlarmBurglaryPieChart.vue";
// import AlarmMedicalPieChart from "../../components/Alarm/SecurityDashboard/AlarmMedicalPieChart.vue";
// import AlarmTemperaturePieChart from "../../components/Alarm/SecurityDashboard/AlarmTemperaturePieChart.vue";

// import AlarmFirePieChart from "../../components/Alarm/SecurityDashboard/AlarmFirePieChart.vue";

// import AlarmWaterPieChart from "../../components/Alarm/SecurityDashboard/AlarmWaterPieChart.vue";
// import AlarmLivePieChart from "../../components/Alarm/SecurityDashboard/AlarmLivePieChart.vue";
// import AlamAllEvents from "../../components/Alarm/ComponentAllEvents.vue";

export default {
  props: ["compFilterSupervisor", "filter_customers_list"],
  components: {
    // AlarmBurglaryPieChart,
    // AlarmTemperaturePieChart,
    // AlarmMedicalPieChart,
    // AlarmFirePieChart,
    // AlarmWaterPieChart,
    // AlarmLivePieChart,
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

    alarmStatistics: {
      open: 0,
      closed: 0,
      forwarded: 0,
      high: 0,
      medium: 0,
      low: 0,
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
      if (
        this.$route.name == "security-dashboard" ||
        this.$route.name == "alarm-allevents"
      ) {
        this.key = this.key + 1;
        this.getDatafromApi();
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
    // getDatafromApi() {
    //   let options = {
    //     params: {
    //       company_id: this.$auth.user.company_id,

    //       filter_customers_list: this.filter_customer_id
    //         ? [this.filter_customer_id]
    //         : null,
    //     },
    //   };

    //   this.$axios
    //     .get(`/security_device_live_stats_groupby`, options)
    //     .then(({ data }) => {
    //       this.onlineStats = data;
    //     });
    // },
    getDatafromApi() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,

          filter_customers_list: this.filter_customer_id
            ? [this.filter_customer_id]
            : null,
        },
      };

      this.$axios
        .get(`/dashboard_alarm_statistics`, options)
        .then(({ data }) => {
          this.alarmStatistics = data;
        });
    },
  },
};
</script>
