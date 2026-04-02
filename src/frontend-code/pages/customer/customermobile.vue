<template>
  <!-- <NoAccess v-if="!$pagePermission.can('dashboard_view', this)" /> -->
  <div>
    <v-row class=" ">
      <v-col cols="12">
        <v-row>
          <v-col>
            <v-card style="height: 85px" elevation="2">
              <v-card-text class="m-0 p-0">
                <v-row>
                  <v-col style="margin: auto">
                    <img
                      src="/notification_icons/water.png"
                      style="width: 45px"
                    />
                  </v-col>

                  <v-col style="text-align: center; margin: auto">
                    <div style="font-size: 50px; color: red">
                      {{ data ? data.waterCount : 0 }}
                    </div>
                  </v-col>
                  <v-col
                    style="
                      text-align: center;
                      margin: auto;
                      font-size: 20px;
                      font-weight: bold;
                    "
                  >
                    <div style="padding-top: 5px">Water</div>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
        <v-row class="pt-0" v-if="data?.waterCount == 0">
          <v-col cols="12" class="pt-0">
            <v-card style="" elevation="2">
              <v-card-text class="m-0 p-0" style="text-align: center">
                <v-icon color="green" size="250">mdi-check-circle</v-icon>

                <v-row style="font-weight: bold; color: green" class="pt-0">
                  <v-col
                    >Device Online :
                    <!-- {{ customerData?.devices[0]?.last_live_datetime }} -->
                    {{
                      customerData
                        ? customerData.devices && customerData.devices[0]
                          ? $dateFormat.formatTimeDateTime(
                              customerData.devices[0].last_live_datetime
                            )
                          : "---"
                        : "---"
                    }}
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
        <v-row class="pt-0" v-if="data?.waterCount > 0">
          <v-col cols="12" class="pt-0">
            <v-card style="" elevation="2">
              <v-card-text class="m-0 p-0">
                <v-row>
                  <v-col style="margin: auto; text-align: center">
                    <img
                      src="/notification_icons/critical.png"
                      style="width: 60%"
                    />
                  </v-col>
                </v-row>
                <v-row
                  style="
                    font-weight: bold;
                    color: red;
                    font-size: 20px;
                    text-align: center;
                  "
                  class="pt-0;"
                >
                  <v-col>
                    Alarm Time:
                    {{
                      customerData
                        ? customerData.devices && customerData.devices[0]
                          ? $dateFormat.formatTimeDateTime(
                              customerData.devices[0].alarm_start_datetime
                            )
                          : "---"
                        : "---"
                    }}
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
        <v-row
          ><v-col class="pt-0">
            <v-card style="height: 85px" elevation="2"
              ><v-card-text>
                <v-row>
                  <v-col style="padding: 5px"
                    ><div style="font-size: 18px">Device</div></v-col
                  >
                </v-row>

                <v-row>
                  <v-col
                    style="
                      padding: 0px;
                      text-align: center;

                      font-weight: bold;
                    "
                    ><div style="font-size: 30px; color: #06b050">
                      {{
                        customerStatusData ? customerStatusData.onlineCount : 0
                      }}
                    </div>
                    <div style="color: #06b050">Online</div>
                  </v-col>
                  <v-col
                    style="
                      padding: 0px;
                      text-align: center;

                      font-weight: bold;
                    "
                    ><div style="font-size: 30px; color: #fe0000">
                      {{
                        customerStatusData ? customerStatusData.offlineCount : 0
                      }}
                    </div>
                    <div style="color: #fe0000">Offline</div>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
        <v-row>
          <v-col class="pt-0">
            <v-card style="height: 280px" elevation="2"
              ><v-card-text>
                <h3 style="color: black; font-weight: normal">Alarm Status</h3>
                <CustomerDashbaordAlarmPieChart
                  :key="key"
                  :name="'CustomerDashbaordAlarmPieChart'"
                  :activeAlarmCount="5"
                  :closedAlarmCount="10"
                  style="
                    height: 200px;

                    max-height: 200px;
                    overflow: hidden;
                  "
                />

                <v-row style="font-weight: bold; color: green" class="pt-0">
                  <v-col
                    >Device Online :
                    <!-- {{ customerData?.devices[0]?.last_live_datetime }} -->
                    {{
                      customerData
                        ? customerData.devices && customerData.devices[0]
                          ? $dateFormat.formatTimeDateTime(
                              customerData.devices[0].last_live_datetime
                            )
                          : "---"
                        : "---"
                    }}
                  </v-col>
                </v-row>
              </v-card-text></v-card
            >
          </v-col>
        </v-row>

        <v-row>
          <v-col cols="12"
            ><v-card
              :style="'height:400px'"
              elevation="2"
              class="eventslistscroll table-font12"
              ><v-card-text>
                <CustomerMonthlyChart
                  v-if="loadAllEventsTable"
                  :height="300"
                  :filter_customers_list="[customer_id]"
                  daterange="week"
                  name="CustomerMonthlyChart1111" /></v-card-text></v-card
          ></v-col>
        </v-row>
        <v-row>
          <v-col></v-col>
        </v-row>

        <!-- <v-row class=" ">
          <v-col cols="12">
            <v-card
              :style="
                'height: ' +
                tableHeight +
                'px; overflow-y: auto; overflow-x: hidden'
              "
              elevation="2"
              class="eventslistscroll table-font12"
              ><v-card-text>
                <AllEventsDashboard2
                  name="AllEvents1"
                  :showFilters="true"
                  :showTabs="false"
                  :filter_customer_id="customer_id"
                  :hide_customer_details="true"
                  v-if="loadAllEventsTable && customer_id"
                /> </v-card-text
            ></v-card>
          </v-col>
        </v-row> -->
      </v-col>
    </v-row>
    <br />
    <br />
    <br />
  </div>
</template>

<script>
import DashboardLoginActivities from "../../components/Admin/DashboardLoginActivities.vue";

import AlarmEventStatusCountPieChart from "../../components/Alarm/Dashboard/AlarmEventStatusCountPieChart.vue";
import DashboardOperatorLiveStatus from "../../components/Admin/DashboardOperatorLiveStatus.vue";

import CustomerDashbaordAlarmPieChart from "../../components/Alarm/Customer/CustomerDashbaordAlarmPieChart.vue";
import AllEventsDashboard2 from "../../components/Alarm/ComponentAllEvents.vue";
import CustomerMonthlyChart from "../../components/Alarm/Customer/CustomerMonthlyChart.vue";

export default {
  layout: "customer",
  components: {
    CustomerDashbaordAlarmPieChart,
    AllEventsDashboard2,
    DashboardOperatorLiveStatus,
    DashboardLoginActivities,
    CustomerMonthlyChart,
  },
  data: () => ({
    customer_id: null,
    tableHeight: 500,
    windowHeight: 1000,
    key: 1,
    date_from: "",
    data: { waterCount: 0 },
    chartEventOpenStatistics: null,
    chartEventClosedStatistics: null,

    chartEventForwardStatistics: null,
    categoriesStats: null,
    customerStatusData: null,
    apiLoading: false,
    cancelgetEventCategoriesStatsToken: null,
    cancelgetEventsTypeStatsToken: null,
    cancelupdateEventsOpenCountStatusToken: null,
    loadAllEventsTable: false,
    nextMaintenanceDate: "---",
    previousMaintenanceDate: "---",
    invoiceDue: 0,
    customerData: null,
  }),
  computed: {},
  mounted() {
    setTimeout(() => {
      if (window) {
        this.windowHeight = window.innerHeight;

        this.tableHeight = window.innerHeight - 450;
      }
    }, 1000 * 5);
  },
  async created() {
    if (!this.$auth.user) {
      this.$router.push("/logout");
      return;
    }
    let today = new Date();

    this.date_from = today.toISOString().split("T")[0];
    this.loadAllEventsTable = true;
    await this.updateEventsOpenCountStatus();
    await this.getEventsTypeStats();
    await this.getEventCategoriesStats();

    await this.getMaintenanceInfo();

    // setTimeout(() => {}, 1000 * 1);

    setInterval(async () => {
      if (this.$route.name == "customer-customermobile") {
        this.key++;
        await this.getEventsTypeStats();
        await this.getEventCategoriesStats();
        await this.updateEventsOpenCountStatus();
      }
    }, 1000 * 10);

    this.customer_id = this.$auth.user.customer.id;
  },
  watch: {},
  methods: {
    async getMaintenanceInfo() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          customer_id: this.$auth.user.customer.id,
        },
      };

      this.$axios
        .get("get_customer_maintenanace_info", options)
        .then((data) => {
          this.customerData = data.data;
          console.log(data, this.customerData.data.devices);

          this.nextMaintenanceDate = this.$dateFormat.formatDate(
            data.data.next_maintenance_due_date
          );
          this.previousMaintenanceDate = this.$dateFormat.formatDate(
            data.data.last_maintenance_date
          );
          this.invoiceDue = data.data.invoice_due;
        });
    },
    async getEventCategoriesStats() {
      if (this.cancelgetEventCategoriesStatsToken) {
        this.cancelgetEventCategoriesStatsToken.cancel(
          "Previous request canceled."
        );
      }

      this.cancelgetEventCategoriesStatsToken =
        this.$axios.CancelToken.source();

      try {
        const options = {
          params: {
            company_id: this.$auth.user.company_id,
            date_from: this.date_from,
          },
          cancelgetEventCategoriesStatsToken:
            this.cancelgetEventCategoriesStatsToken.token, // Attach cancel token
        };

        const { data } = await this.$axios.get(`/alarm_statistics`, options);

        // Update data
        this.categoriesStats = data;
      } catch (error) {
        if (this.$axios.isCancel(error)) {
          console.log("Request canceled:", error.message);
        } else {
          ///////console.error("API Error:Custom ", error);
        }
      } finally {
        this.apiLoading = false; // Reset loading state
      }
    },

    async getEventsTypeStats() {
      // Cancel any ongoing request
      if (this.cancelgetEventsTypeStatsToken) {
        this.cancelgetEventsTypeStatsToken.cancel("Previous request canceled.");
      }

      // Create a new cancel token
      this.cancelgetEventsTypeStatsToken = this.$axios.CancelToken.source();

      this.apiLoading = true; // Start loading state

      try {
        const response = await this.$axios.get(
          "dashboard_statistics_date_range",
          {
            params: {
              company_id: this.$auth.user.company_id,
              date_from: this.date_from,
              date_to: this.date_from, // Ensure this is intentional
              customer_id: this.$auth.user?.customer?.id || null,
            },
            cancelgetEventsTypeStatsToken:
              this.cancelgetEventsTypeStatsToken.token, // Attach cancel token
          }
        );

        // Handle response
        if (response.data.length > 0) {
          this.data = response.data[0];
        }
      } catch (error) {
        if (this.$axios.isCancel(error)) {
          console.log("Request canceled:", error.message);
        } else {
          /////////console.error("API Error:Custom ", error);
        }
      } finally {
        this.apiLoading = false; // End loading state
      }
    },

    async updateEventsOpenCountStatus() {
      // Cancel any ongoing request
      if (this.cancelupdateEventsOpenCountStatusToken) {
        this.cancelupdateEventsOpenCountStatusToken.cancel(
          "Previous request canceled."
        );
      }

      // Create a new cancel token
      this.cancelupdateEventsOpenCountStatusToken =
        this.$axios.CancelToken.source();

      //if (this.apiLoading) return false;

      this.apiLoading = true;
      let today = new Date();
      let date = today.toISOString().split("T")[0];
      this.$axios
        .get("get_customer_tickets_statistics", {
          params: {
            company_id: this.$auth.user.company_id,
            customer_id: this.$auth.user?.customer?.id || null,
          },
          cancelupdateEventsOpenCountStatusToken:
            this.cancelupdateEventsOpenCountStatusToken.token, // Attach cancel token
        })
        .then(({ data }) => {
          if (data) {
            this.customerStatusData = data;
            this.chartEventOpenStatistics = {
              labels: [],
              series: [],
              colors: [],
              customTotalValue: 0,
              percentage: 0,
            };

            const total =
              parseInt(data.openCount) +
              parseInt(data.closedCount) +
              parseInt(data.forwardCount);

            let openPercentage = 0;
            let closedPercentage = 0;
            let forwardPercentage = 0;
            if (total > 0) {
              openPercentage = Math.round((data.openCount / total) * 100, 2);
              closedPercentage = Math.round((data.closedCount / total) * 100);
              forwardPercentage = Math.round((data.forwardCount / total) * 100);
            }

            this.chartEventOpenStatistics.title = "Open";
            this.chartEventOpenStatistics.labels[0] = "Open";
            this.chartEventOpenStatistics.series[0] = data.openCount; // data.total - data.armed;

            this.chartEventOpenStatistics.labels[1] = "Closed";
            this.chartEventOpenStatistics.series[1] = data.closedCount;

            this.chartEventOpenStatistics.labels[2] = "Forward";
            this.chartEventOpenStatistics.series[2] = data.forwardCount; // data.armed;

            this.chartEventOpenStatistics.colors = ["#07af50", "#DDD", "#DDD"];
            this.chartEventOpenStatistics.customTotalValue = total; //this.items.ExpectingCount;

            this.chartEventOpenStatistics.percentage = openPercentage;

            //----------------------------

            this.chartEventClosedStatistics = {
              labels: [],
              series: [],
              colors: [],
              customTotalValue: 0,
              percentage: 0,
            };
            this.chartEventClosedStatistics.title = "Closed";
            this.chartEventClosedStatistics.labels[0] = "Closed";
            this.chartEventClosedStatistics.series[0] = data.closedCount; // data.total - data.armed;

            this.chartEventClosedStatistics.labels[1] = "Forward";
            this.chartEventClosedStatistics.series[1] = data.forwardCount; // data.armed;

            this.chartEventClosedStatistics.labels[2] = "Open";
            this.chartEventClosedStatistics.series[2] = data.openCount; // data.armed;

            this.chartEventClosedStatistics.colors = [
              "#fe0000",
              "#DDD",
              "#DDD",
            ];
            this.chartEventClosedStatistics.customTotalValue = total; //this.items.ExpectingCount;

            this.chartEventClosedStatistics.percentage = closedPercentage;

            //----------------------------

            this.chartEventForwardStatistics = {
              labels: [],
              series: [],
              colors: [],
              customTotalValue: 0,
              percentage: 0,
            };
            this.chartEventForwardStatistics.title = "Forward";
            this.chartEventForwardStatistics.labels[0] = "Forward";
            this.chartEventForwardStatistics.series[0] = data.forwardCount; // data.total - data.armed;

            this.chartEventForwardStatistics.labels[1] = "Closed";
            this.chartEventForwardStatistics.series[1] = data.closedCount; // data.armed;

            this.chartEventForwardStatistics.labels[2] = "Open";
            this.chartEventForwardStatistics.series[2] = data.openCount; // data.armed;

            this.chartEventForwardStatistics.colors = [
              "#ffbe00",
              "#DDD",
              "#DDD",
            ];
            this.chartEventForwardStatistics.customTotalValue = total; //this.items.ExpectingCount;

            this.chartEventForwardStatistics.percentage = forwardPercentage;

            this.apiLoading = false;
          }
        });
    },
  },
};
</script>
