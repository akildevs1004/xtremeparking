<template>
  <div style="padding: 0px; width: 100%; height: auto">
    <v-dialog v-model="dialogDevicesList" max-width="1000px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense>Disamed Devices</span>
          <v-spacer></v-spacer>
          <v-icon @click="dialogDevicesList = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text class="p-0" style="padding: 0px">
          <Devices style="padding: 0px" :key="key" :eventFilter="'disamed'" />
        </v-card-text>
      </v-card>
    </v-dialog>
    <!-- <v-row style="margin-top: -27px"
      ><v-col cols="8" style="color: black; font-size: 12px">Armed</v-col>

      <v-col cols="4" class="text-right align-right"
        >
        <img
          @click="showDialogEvents()"
          src="/dashboard-arrow.png"
          style="width: 18px; padding-top: 5px"
      /></v-col>
    </v-row>
    <v-divider color="#5a82ca" style="margin-bottom: 10px" />-->
    <v-row class="pt-0 mt-0">
      <v-col
        cols="5"
        class="pt-0"
        style="
          font-size: 11px;

          padding-left: 0px;
          padding-right: 0px;
          line-height: 32px;
          margin: auto;
          height: 230px;
        "
      >
        <v-row style="">
          <v-col
            cols="5"
            class="text-right justify-right vertical-center"
            style="margin: auto"
          >
            <img style="width: 40px" :src="armedIcon"
          /></v-col>
          <v-col cols="7" class="text-center justify-center">
            <div style="font-size: 35px; color: #07af50; font-weight: bold">
              {{ categories && categories.armed > 0 ? categories.armed : "0" }}
            </div>

            <div style="font-size: 15px">Armed</div>
          </v-col>
        </v-row>
        <v-divider color="#353538" />
        <v-row>
          <v-col
            style="margin: auto"
            cols="5"
            class="text-right justify-right vertical-center"
          >
            <img style="width: 40px" :src="disarmIcon"
          /></v-col>
          <v-col cols="7" class="text-center justify-center mt-2">
            <div style="font-size: 35px; color: #fe0004; font-weight: bold">
              {{
                categories && categories.disarm > 0 ? categories.disarm : "0"
              }}
            </div>

            <div style="font-size: 15px">DisArm</div>
          </v-col>
        </v-row>
        <v-divider color="#353538" />
        <v-row>
          <v-col
            style="margin: auto"
            cols="5"
            class="text-right justify-right vertical-center"
          >
            <img style="width: 40px" :src="technicalIcon"
          /></v-col>
          <v-col cols="7" class="text-center justify-center mt-2">
            <div style="font-size: 35px; color: #ffbe00; font-weight: bold">
              {{ categories && categories.other > 0 ? categories.other : "0" }}
            </div>

            <div style="font-size: 15px">Technical</div>
          </v-col>
        </v-row>

        <!-- <v-row>
          <v-col cols="8"
            ><v-icon color="#92d050">mdi mdi-square-medium</v-icon>Armed</v-col
          ><v-col cols="4" style="padding-left: 0px">{{
            categories ? categories.armed : "0"
          }}</v-col>
        </v-row>
        <v-divider color="#dddddd" />
        <v-row>
          <v-col cols="8"
            ><v-icon color="#ff0000">mdi mdi-square-medium</v-icon
            >Disamed</v-col
          ><v-col cols="4" style="padding-left: 0px">{{
            categories ? categories.total - categories.armed : "0"
          }}</v-col>
        </v-row>
        <v-divider color="#dddddd" /> -->
      </v-col>
      <v-col
        cols="7"
        class="text-center pt-0 doughtchart"
        style="margin: 0 auto; text-align: left; margin-left: -10px"
      >
        <div v-if="chartOptions.customTotalValue == 0" class="empty-doughnut2">
          Total <br />0
        </div>
        <div
          :style="chartOptions.customTotalValue == 0 ? 'display:none' : ''"
          :id="name"
          :name="name"
          class="doughtchart"
          style="width: 320px; margin: 0 auto; text-align: left"
        ></div>
      </v-col>
    </v-row>

    <!-- <div
      v-if="categories.length == 0"
      style="
        padding: 0px;
        margin: auto;
        text-align: center;
        vertical-align: middle;
        height: auto;
        padding-top: 20%;
      "
    >
      No Data available
    </div> -->
  </div>
</template>

<script>
import Devices from "../Devices.vue";

export default {
  props: ["name", "height"],
  components: { Devices },
  data() {
    return {
      key: 1,
      dialogDevicesList: false,
      totalCount: 0,
      filter1: "categories",
      categories: [],
      chartOptions: {
        noData: {
          text: "There's no data",
          align: "center",
          verticalAlign: "middle",
          offsetX: 0,
          offsetY: 0,
        },

        title: {
          //text: "Alarm Events - Categories",
          align: "left",
          margin: 0,
        },
        //colors: ["#033F9B", "#DC7633", "#02B64B", "#ff0000", "#808080", ""],
        colors: ["#07af50", "#fe0004", "#ffbe00"],

        series: [],
        chart: {
          toolbar: {
            show: false,
          },
          width: parseInt(this.height),
          height: parseInt(this.height),
          type: "donut",
        },
        customTotalValue: 0,
        stroke: {
          show: false,
          // width: 0,
          // colors: ["#000"], // 👈 black border around each segment
        },
        plotOptions: {
          pie: {
            donut: {
              size: "80%", // Decrease value to make the bar thinner (e.g., '40%')
              labels: {
                show: true,
                name: {
                  show: true,
                  fontSize: "22px",
                  fontFamily: "Rubik",
                  color: this.$vuetify.theme.dark ? "#FFF" : "#000",
                  offsetY: -10,
                },
                value: {
                  show: true,
                  fontSize: "16px",
                  fontFamily: "Helvetica, Arial, sans-serif",
                  // color: undefined,
                  color: this.$vuetify.theme.dark ? "#FFF" : "#000",
                  offsetY: 16,
                  formatter: function (val) {
                    return val;
                  },
                },
                total: {
                  show: true,
                  label: "Total",
                  color: this.$vuetify.theme.dark ? "#FFF" : "#000",
                  formatter: function (val) {
                    return val.config.customTotalValue;
                  },
                },
              },
            },
          },
        },
        labels: [],

        dataLabels: {
          enabled: false,
          style: {
            fontSize: "10px",
          },
        },
        legend: {
          align: "left",
          show: false,
          fontSize: "12px",
          formatter: (seriesName, opts) => {
            // let Total = "";

            // if (opts.seriesIndex == 0) {
            //   Total = "<div>Total: " + this.totalCount + "</div><br/>";
            // }
            // return ` ${Total}
            //    ${seriesName} ${opts.w.globals.series[opts.seriesIndex]}
            // `;

            return `
               ${seriesName} : ${opts.w.globals.series[opts.seriesIndex]}
            `;
          },
        },
        responsive: [
          {
            breakpoint: 480,
            options: {
              chart: {
                toolbar: {
                  show: false,
                },
              },
              legend: {
                position: "bottom",
              },
            },
          },
        ],
      },
      statsInterval: null,
    };
  },
  beforeDestroy() {
    if (this.statsInterval) clearInterval(this.statsInterval); // Clean up
  },
  computed: {
    armedIcon() {
      return process.env.BACKEND_URL2 + "/google_map_icons/google_armed.png";
    },
    disarmIcon() {
      return process.env.BACKEND_URL2 + "/google_map_icons/google_disarm.png";
    },
    technicalIcon() {
      return process.env.BACKEND_URL2 + "/google_map_icons/google_offline.png";
    },
  },
  mounted() {
    this.statsInterval = setInterval(() => {
      if (this.$route.name === "alarm-dashboard") {
        this.loadDevicesStatistics();
      }
    }, 1000 * 10); // 10 seconds
    this.loadDevicesStatistics();
    this.renderChart1(this.categories);
  },
  watch: {
    "$vuetify.theme.dark"(isDark) {
      const labelColor = isDark ? "#FFF" : "#19191c";

      this.chartOptions.plotOptions.pie.donut.labels.name.color = labelColor;
      this.chartOptions.plotOptions.pie.donut.labels.value.color = labelColor;
      this.chartOptions.plotOptions.pie.donut.labels.total.color = labelColor;
    },

    categories: {
      deep: true,
      handler(newVal, oldVal) {
        const keysToCheck = ["armed", "disarm", "Technical", "total"];

        const hasChanged = keysToCheck.some(
          (key) => newVal[key] !== oldVal[key]
        );

        if (hasChanged) {
          this.renderChart1(newVal);
        }
      },
    },
  },
  methods: {
    showDialogEvents() {
      this.key += 1;
      this.dialogDevicesList = true;
    },
    applyFilter() {
      this.loadDevicesStatistics();
      this.renderChart1(this.categories);
    },
    loadDevicesStatistics() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      this.$axios.get(`/device_armed_stats`, options).then(({ data }) => {
        this.categories = data;
        // this.renderChart1(data);
      });
    },

    async renderChart1(data) {
      let counter = 0;
      let total = 1000;

      // this.chartOptions.labels[0] = "Total";
      // this.chartOptions.series[0] = data.total;

      this.chartOptions.labels[0] = "Armed";
      this.chartOptions.series[0] = data.armed ?? 0;

      this.chartOptions.labels[1] = "DisArm";
      this.chartOptions.series[1] = data.disarm ?? 0;

      this.chartOptions.labels[2] = "Technical";
      this.chartOptions.series[2] = data.other ?? 0;

      this.chartOptions.customTotalValue = data.total; //this.items.ExpectingCount;

      if (this.chart) {
        this.chart.destroy();
      }

      // this.chartOptions.plotOptions.pie.donut.labels.name.color = this.$vuetify
      //   .theme.dark
      //   ? "#FFF"
      //   : "#19191c";
      // this.chartOptions.plotOptions.pie.donut.labels.value.color = this.$vuetify
      //   .theme.dark
      //   ? "#FFF"
      //   : "#19191c";
      // this.chartOptions.plotOptions.pie.donut.labels.total.color = this.$vuetify
      //   .theme.dark
      //   ? "#FFF"
      //   : "#19191c";

      // Render the chart
      this.chart = await new ApexCharts(
        document.querySelector("#" + this.name),
        this.chartOptions
      );
      if (this.chart) this.chart.render();
    },
  },
  created() {
    // try {
    //   this.items.forEach((element) => {
    //     this.totalCount += element.value;
    //   });
    //   this.options.labels = this.items.map((e) => e.title);
    //   this.options.series = this.items.map((e) => e.value);
    //   new ApexCharts(document.querySelector("#pie2"), this.options).render();
    // } catch (error) {}
  },
};
</script>
