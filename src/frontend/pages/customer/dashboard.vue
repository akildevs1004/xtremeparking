<template>
  <!-- <NoAccess v-if="!$pagePermission.can('dashboard_view', this)" /> -->
  <div>
    <v-row class="padding:0px">
      <v-col cols="12"
        ><v-row>
          <v-col>
            <v-card elevation="2"
              ><v-card-text
                ><v-row>
                  <v-col class="d-flex justify-center" style="max-width: 40px">
                    <img
                      src="/notification_icons/sos.png"
                      style="width: 40px; margin: auto"
                    />
                  </v-col>

                  <v-col
                    class="d-flex justify-center"
                    style="
                      font-size: 25px;
                      margin: auto;

                      color: black;
                    "
                    >SOS</v-col
                  ><v-col
                    class="d-flex justify-center"
                    style="
                      font-size: 40px;
                      margin: auto;
                      font-weight: bold;
                      color: red;
                    "
                    >{{ data ? data.sosCount : 0 }}</v-col
                  ></v-row
                ></v-card-text
              ></v-card
            >
          </v-col>
          <v-col>
            <v-card elevation="2"
              ><v-card-text
                ><v-row>
                  <v-col class="d-flex justify-center" style="max-width: 40px">
                    <img
                      src="/notification_icons/critical.png"
                      style="width: 40px"
                    />
                  </v-col>

                  <v-col
                    class="d-flex justify-center"
                    style="
                      font-size: 25px;
                      margin: auto;

                      color: black;
                    "
                    >Critical</v-col
                  ><v-col
                    class="d-flex justify-center"
                    style="
                      font-size: 40px;
                      margin: auto;
                      font-weight: bold;
                      color: red;
                    "
                    >{{ data ? data.criticalCount : 0 }}</v-col
                  ></v-row
                ></v-card-text
              ></v-card
            >
          </v-col>
          <v-col>
            <v-card elevation="2"
              ><v-card-text
                ><v-row>
                  <v-col class="d-flex justify-center" style="max-width: 40px">
                    <img
                      src="/notification_icons/medical.png"
                      style="width: 40px"
                    />
                  </v-col>

                  <v-col
                    class="d-flex justify-center"
                    style="
                      font-size: 25px;
                      margin: auto;

                      color: black;
                    "
                    >Medical</v-col
                  ><v-col
                    class="d-flex justify-center"
                    style="
                      font-size: 40px;
                      margin: auto;
                      font-weight: bold;
                      color: red;
                    "
                    >{{ data ? data.medicalCount : 0 }}</v-col
                  ></v-row
                ></v-card-text
              ></v-card
            >
          </v-col>

          <v-col>
            <v-card elevation="2"
              ><v-card-text
                ><v-row>
                  <v-col class="d-flex justify-center" style="max-width: 40px">
                    <img
                      src="/notification_icons/fire.png"
                      style="width: 40px"
                    />
                  </v-col>

                  <v-col
                    class="d-flex justify-center"
                    style="
                      font-size: 25px;
                      margin: auto;

                      color: black;
                    "
                    >Fire</v-col
                  ><v-col
                    class="d-flex justify-center"
                    style="
                      font-size: 40px;
                      margin: auto;
                      font-weight: bold;
                      color: red;
                    "
                    >{{ data ? data.fireCount : 0 }}</v-col
                  ></v-row
                ></v-card-text
              ></v-card
            >
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="3">
            <v-card style="height: 275px" elevation="2"
              ><v-card-text>
                <h3 style="color: black; font-weight: normal">System Status</h3>
                <CustomerDashbaordDeviceCountPieChart
                  :key="key"
                  :name="'CustomerDashbaordDeviceCountPieChart'"
                  style="
                    height: 200px;

                    max-height: 200px;
                    overflow: hidden;
                  "
                /> </v-card-text
            ></v-card> </v-col
          ><v-col cols="3">
            <v-card style="height: 275px" elevation="2"
              ><v-card-text
                ><h3
                  style="
                    color: black;
                    font-weight: normal;
                    margin-top: -10px;
                    padding-bottom: 10px;
                  "
                >
                  Tickets Status
                </h3>

                <v-row
                  ><v-col cols="6">
                    <AlarmEventStatusCountPieChart
                      v-if="chartEventOpenStatistics"
                      :key="key"
                      :name="'AlarmEventStatusCountPieChart1'"
                      :componentData="chartEventOpenStatistics"
                      :filter_customers_list="[customer_id]"
                    /> </v-col
                  ><v-col cols="6">
                    <AlarmEventStatusCountPieChart
                      v-if="chartEventClosedStatistics"
                      :key="key + 2"
                      :name="'AlarmEventStatusCountPieChart2'"
                      :componentData="chartEventClosedStatistics"
                      :filter_customers_list="[customer_id]"
                    />
                  </v-col>

                  <!-- <v-col cols="4">
                    <AlarmEventStatusCountPieChart
                      v-if="chartEventForwardStatistics"
                      :key="key + 4"
                      :name="'AlarmEventStatusCountPieChart3'"
                      :componentData="chartEventForwardStatistics"
                    /> </v-col
                >
               -->
                </v-row>
              </v-card-text></v-card
            >
          </v-col>
          <v-col cols="3">
            <v-row>
              <v-col cols="12">
                <v-card elevation="2"
                  ><v-card-text
                    ><v-row style="height: 76px">
                      <v-col
                        class="d-flex justify-center"
                        style="
                          font-size: 15px;
                          margin: auto;

                          color: black;
                        "
                        >Next Maintanance </v-col
                      ><v-col
                        class="d-flex justify-center"
                        style="
                          font-size: 20px;
                          margin: auto;
                          font-weight: bold;
                          color: orange;
                        "
                      >
                        {{ nextMaintenanceDate }}</v-col
                      ></v-row
                    ></v-card-text
                  ></v-card
                > </v-col
              ><v-col cols="12" class="pt-0">
                <v-card elevation="2"
                  ><v-card-text
                    ><v-row style="height: 76px">
                      <v-col
                        class="d-flex justify-center"
                        style="
                          font-size: 15px;
                          margin: auto;

                          color: black;
                        "
                        >Last Maintanance </v-col
                      ><v-col
                        class="d-flex justify-center"
                        style="
                          font-size: 20px;
                          margin: auto;
                          font-weight: bold;
                          color: green;
                        "
                        >{{ previousMaintenanceDate || "---" }}</v-col
                      ></v-row
                    ></v-card-text
                  ></v-card
                >
              </v-col>
              <v-col cols="12" class="pt-0">
                <v-card elevation="2"
                  ><v-card-text
                    ><v-row style="height: 76px">
                      <v-col
                        class="text-left"
                        style="
                          font-size: 15px;
                          margin: auto;

                          color: black;
                        "
                        >Invoice Due</v-col
                      ><v-col
                        class="d-flex justify-center"
                        style="
                          font-size: 20px;
                          margin: auto;
                          font-weight: bold;
                          color: black;
                        "
                        >{{ invoiceDue }}</v-col
                      ></v-row
                    ></v-card-text
                  ></v-card
                >
              </v-col>
            </v-row>
          </v-col>
          <v-col cols="3">
            <v-card
              style="height: 300px; background-color: transparent"
              elevation="0"
            >
              <v-card-text class="font-black">
                <v-row>
                  <v-col
                    style="padding: 5px; padding-left: 0px; padding-top: 0px"
                  >
                    <v-card style="height: 80px" elevation="2">
                      <v-card-text class="m-0 p-0">
                        <v-row>
                          <v-col style="max-width: 45px; margin: auto">
                            <img
                              src="/notification_icons/technical.png"
                              style="width: 45px"
                            />
                          </v-col>

                          <v-col style="text-align: center">
                            <div style="font-size: 40px">
                              {{ data ? data.technicalCount : 0 }}
                            </div>
                            <div style="padding-top: 5px">Technical</div>
                          </v-col>
                        </v-row>
                      </v-card-text>
                    </v-card>
                  </v-col>
                  <v-col
                    style="padding: 5px; padding-right: 0px; padding-top: 0px"
                  >
                    <v-card style="height: 80px" elevation="2">
                      <v-card-text class="m-0 p-0">
                        <v-row>
                          <v-col style="max-width: 45px; margin: auto">
                            <img
                              src="/notification_icons/event.png"
                              style="width: 45px"
                            />
                          </v-col>

                          <v-col style="text-align: center">
                            <div style="font-size: 40px">
                              {{ data ? data.eventsCount : 0 }}
                            </div>
                            <div style="padding-top: 5px">Events</div>
                          </v-col>
                        </v-row>
                      </v-card-text>
                    </v-card>
                  </v-col>
                </v-row>

                <v-row>
                  <v-col
                    style="padding: 5px; padding-left: 0px; padding-top: 8px"
                  >
                    <v-card style="height: 85px" elevation="2">
                      <v-card-text class="m-0 p-0">
                        <v-row>
                          <v-col style="max-width: 45px; margin: auto">
                            <img
                              src="/notification_icons/water.png"
                              style="width: 45px"
                            />
                          </v-col>

                          <v-col style="text-align: center">
                            <div style="font-size: 40px">
                              {{ data ? data.waterCount : 0 }}
                            </div>
                            <div style="padding-top: 5px">Water</div>
                          </v-col>
                        </v-row>
                      </v-card-text>
                    </v-card>
                  </v-col>
                  <v-col
                    style="padding: 5px; padding-right: 0px; padding-top: 8px"
                  >
                    <v-card style="height: 85px" elevation="2">
                      <v-card-text class="m-0 p-0">
                        <v-row>
                          <v-col style="max-width: 45px; margin: auto">
                            <img
                              src="/notification_icons/temperature.png"
                              style="width: 50px"
                            />
                          </v-col>

                          <v-col style="text-align: center">
                            <div style="font-size: 40px">
                              {{ data ? data.temperatureCount : 0 }}
                            </div>
                            <div style="padding-top: 5px">Temperature</div>
                          </v-col>
                        </v-row>
                      </v-card-text>
                    </v-card>
                  </v-col>
                </v-row>
                <v-row
                  ><v-col style="padding: 0px; padding-top: 5px">
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
                                customerStatusData
                                  ? customerStatusData.onlineCount
                                  : 0
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
                                customerStatusData
                                  ? customerStatusData.offlineCount
                                  : 0
                              }}
                            </div>
                            <div style="color: #fe0000">Offline</div>
                          </v-col>
                        </v-row>
                      </v-card-text>
                    </v-card>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <v-row class="mt-0 pt-0">
          <v-col cols="6" class="pt-0">
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
          <v-col cols="6" class="pt-0"
            ><v-card
              :style="'height: ' + tableHeight + 'px'"
              elevation="2"
              class="eventslistscroll table-font12"
              ><v-card-text style="padding-left: 0px">
                <CustomerMonthlyChart
                  v-if="loadAllEventsTable"
                  :height="tableHeight - 100"
                  :filter_customers_list="[customer_id]"
                  name="CustomerMonthlyChart1111" /></v-card-text></v-card
          ></v-col>
        </v-row>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import DashboardLoginActivities from "../../components/Admin/DashboardLoginActivities.vue";

import AlarmEventStatusCountPieChart from "../../components/Alarm/Dashboard/AlarmEventStatusCountPieChart.vue";
import DashboardOperatorLiveStatus from "../../components/Admin/DashboardOperatorLiveStatus.vue";

import CustomerDashbaordDeviceCountPieChart from "../../components/Alarm/Customer/CustomerDashbaordDeviceCountPieChart.vue";
import AllEventsDashboard2 from "../../components/Alarm/ComponentAllEvents.vue";
import CustomerMonthlyChart from "../../components/Alarm/Customer/CustomerMonthlyChart.vue";

export default {
  layout: "customer",
  components: {
    CustomerDashbaordDeviceCountPieChart,
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
    data: [],
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
  }),
  computed: {},
  async mounted() {
    setTimeout(() => {
      if (window) {
        this.windowHeight = window.innerHeight;

        this.tableHeight = window.innerHeight - 450;
      }
    }, 1000 * 5);

    let today = new Date();

    this.date_from = today.toISOString().split("T")[0];
    this.loadAllEventsTable = true;
    await this.updateEventsOpenCountStatus();
    await this.getEventsTypeStats();
    await this.getEventCategoriesStats();

    await this.getMaintenanceInfo();
  },
  async created() {
    if (!this.$auth.user) {
      this.$router.push("/logout");
      return;
    }

    // if (window) {
    //   if (window) {
    //     console.log("window.innerWidth ", window.innerWidth);

    //     if (window.innerWidth < 700) {
    //       this.$router.push("/customer/customermobile"); // Fallback in case of null values
    //     }
    //   }
    // }

    // setTimeout(() => {}, 1000 * 1);

    setInterval(async () => {
      if (this.$route.name == "customer-dashboard") {
        this.key++;
        await this.getEventsTypeStats();
        await this.getEventCategoriesStats();
        await this.updateEventsOpenCountStatus();
      }
    }, 1000 * 20);
    if (this.$auth.user.customer)
      this.customer_id = this.$auth.user.customer.id;
  },
  watch: {},
  methods: {
    async getMaintenanceInfo() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          customer_id: this.$auth.user.customer?.id || null,
        },
      };

      this.$axios
        .get("get_customer_maintenanace_info", options)
        .then((data) => {
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
