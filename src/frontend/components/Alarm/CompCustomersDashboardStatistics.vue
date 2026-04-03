<template>
  <div>
    <v-row>
      <v-col style="max-width: 20%">
        <v-card
          elevation="0"
          :style="
            'height: 90px;border-radius: 0.5rem;background-color:' +
            ($vuetify.theme.dark ? '#283a36!important' : '#FFF!important')
          "
          class="custom-card bg-primary-transparent"
          ><v-card-text
            ><v-row style="height: 130px">
              <v-col
                cols="3"
                style="line-height: 30px; margin-top: 5px"
                class="text-left"
              >
                <div class="image-box-simple" style="background-color: #007d61">
                  <v-icon size="20">mdi-account-outline</v-icon>
                </div>
              </v-col>
              <v-col cols="9" style="line-height: 30px" class="text-left"
                ><span class="bold">Total Customers</span>

                <v-row class="text-left" no-gutters align="center">
                  <v-col cols="auto" style="font-size: 30px">
                    {{ customersStats?.total || 0 }}
                  </v-col>
                  <v-col
                    cols="auto"
                    class="d-flex align-center"
                    style="font-size: 16px; color: green"
                  >
                    <!-- <v-icon small class="mr-1">mdi-arrow-up-thin</v-icon>
                    +10% -->
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col style="max-width: 20%">
        <v-card
          elevation="0"
          :style="
            'height: 90px;border-radius: 0.5rem;background-color:' +
            ($vuetify.theme.dark ? '#353345!important' : '#FFF!important')
          "
          class="custom-card bg-primary-transparent"
          ><v-card-text
            ><v-row style="height: 130px">
              <v-col
                cols="3"
                style="line-height: 30px; margin-top: 5px"
                class="text-left"
              >
                <div class="image-box-simple" style="background-color: #5a4da2">
                  <v-icon size="20">mdi-account-outline</v-icon>
                </div>
              </v-col>
              <v-col cols="9" style="line-height: 30px" class="text-left"
                ><span class="bold">Active Customers</span>

                <v-row class="text-left" no-gutters align="center">
                  <v-col cols="auto" style="font-size: 30px">
                    {{
                      customersStats &&
                      customersStats.total - customersStats.expired
                        ? customersStats.total - customersStats.expired
                        : 0
                    }}
                  </v-col>
                  <v-col
                    cols="auto"
                    class="d-flex align-center"
                    style="font-size: 16px; color: green"
                  >
                    <!-- <v-icon small class="mr-1">mdi-arrow-up-thin</v-icon>
                    +10% -->
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col style="max-width: 20%">
        <v-card
          elevation="0"
          :style="
            'height: 90px;border-radius: 0.5rem;background-color:' +
            ($vuetify.theme.dark ? '#42392c!important' : '#FFF!important')
          "
          class="custom-card bg-primary-transparent"
          ><v-card-text
            ><v-row style="height: 130px">
              <v-col
                cols="3"
                style="line-height: 30px; margin-top: 5px"
                class="text-left"
              >
                <div class="image-box-simple" style="background-color: #a1711b">
                  <v-icon size="20">mdi-account-outline</v-icon>
                </div>
              </v-col>
              <v-col cols="9" style="line-height: 30px" class="text-left"
                ><span class="bold">Account Expire In 30 Days</span>

                <v-row class="text-left" no-gutters align="center">
                  <v-col cols="auto" style="font-size: 30px">
                    {{ customersStats?.expiringin30days || 0 }}
                  </v-col>
                  <v-col
                    cols="auto"
                    class="d-flex align-center"
                    style="font-size: 16px; color: green"
                  >
                    <!-- <v-icon small class="mr-1">mdi-arrow-up-thin</v-icon>
                    +10% -->
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card></v-col
      >
      <v-col style="max-width: 20%">
        <v-card
          elevation="0"
          :style="
            'height: 90px;border-radius: 0.5rem;background-color:' +
            ($vuetify.theme.dark ? '#422f35!important' : '#FFF!important')
          "
          class="custom-card bg-primary-transparent"
          ><v-card-text
            ><v-row style="height: 130px">
              <v-col
                cols="3"
                style="line-height: 30px; margin-top: 5px"
                class="text-left"
              >
                <div class="image-box-simple" style="background-color: #a03c4c">
                  <v-icon size="20">mdi-account-outline</v-icon>
                </div>
              </v-col>
              <v-col cols="9" style="line-height: 30px" class="text-left"
                ><span class="bold">Accounts Expired</span>

                <v-row class="text-left" no-gutters align="center">
                  <v-col cols="auto" style="font-size: 30px">
                    {{ customersStats?.expired || 0 }}
                  </v-col>
                  <v-col
                    cols="auto"
                    class="d-flex align-center"
                    style="font-size: 16px; color: green"
                  >
                    <!-- <v-icon small class="mr-1">mdi-arrow-up-thin</v-icon>
                    +10% -->
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card></v-col
      >
      <v-col style="max-width: 20%">
        <v-card
          elevation="0"
          :style="
            'height: 90px;border-radius: 0.5rem;background-color:' +
            ($vuetify.theme.dark ? '#2a3b44!important' : '#FFF!important')
          "
          class="custom-card bg-primary-transparent"
          ><v-card-text
            ><v-row style="height: 130px">
              <v-col
                cols="3"
                style="line-height: 30px; margin-top: 5px"
                class="text-left"
              >
                <div class="image-box-simple" style="background-color: #6993a9">
                  <v-icon size="20">mdi-account-outline</v-icon>
                </div>
              </v-col>
              <v-col cols="9" style="line-height: 30px" class="text-left"
                ><span class="bold">Total Devices</span>

                <v-row class="text-left" no-gutters align="center">
                  <v-col cols="auto" style="font-size: 30px">
                    {{
                      devicesStats
                        ? devicesStats.find((e) => e.name == "total").count
                        : 0
                    }}
                  </v-col>
                  <v-col
                    cols="auto"
                    class="d-flex align-center"
                    style="font-size: 16px; color: green"
                  >
                    <!-- <v-icon small class="mr-1">mdi-arrow-up-thin</v-icon>
                    +10% -->
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card></v-col
      >
    </v-row>
    <!-- <v-row>
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
    </v-row> -->

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
    devicesStats: null,
    customersStats: null,
    accountStats: null,
    expiredStats: null,
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

    this.loadDevicesStatistics();
    this.loadCustomersStatistics();
    this.loadAccountsStatistics();
    // this.loadExpiredStatistics();
  },

  watch: {},
  methods: {
    loadAccountsStatistics() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      this.$axios.get(`/customer_contract_stats`, options).then(({ data }) => {
        this.customersStats = data;
      });
    },
    // loadAccountsStatistics() {
    //   let options = {
    //     params: {
    //       company_id: this.$auth.user.company_id,
    //     },
    //   };

    //   this.$axios
    //     .get(`/customer_contract_expin30days_stats`, options)
    //     .then(({ data }) => {
    //       this.accountStats = data;
    //     });
    // },
    loadDevicesStatistics() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      this.$axios
        .get(`/customer_devices_types_stats`, options)
        .then(({ data }) => {
          this.devicesStats = data;
        });
    },
    loadCustomersStatistics() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      this.$axios
        .get(`/buildingtype_customer_stats`, options)
        .then(({ data }) => {
          this.customersStats = data;
        });
    },
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
