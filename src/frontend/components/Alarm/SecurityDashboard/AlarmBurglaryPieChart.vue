<template>
  <div style="padding: 0px; width: 100%; height: auto">
    <v-dialog v-model="dialogEventsList" max-width="80%">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span style="color: black3333" dense>Intruder Events</span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="dialogEventsList = false"
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text style="padding: 20px; padding-left: 0px">
          <v-card class="elevation-2">
            <v-card-text class="mt-5">
              <AlamAllEvents
                :compFilterAlarmStatus="1"
                name="burgleryPieChart"
                style="padding: 0px; padding-top: 0px"
                :key="key"
                :popup="true"
                :eventFilter="'Intruder'"
                :compAlarmFilter="1"
              /> </v-card-text
          ></v-card>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-row style="margin-top: -27px"
      ><v-col cols="8" style="color: black; font-size: 12px">Intruder</v-col>

      <v-col cols="4" class="text-right align-right"
        ><img
          @click="showDialogEvents()"
          src="/dashboard-arrow.png"
          style="width: 18px; padding-top: 5px"
      /></v-col>
    </v-row>
    <v-divider color="#5a82ca" style="margin-bottom: 10px" />

    <v-row class="pt-0 mt-0">
      <v-col cols="7" class="text-center p-0" style="padding: 0px">
        <div v-if="chartOptions.customTotalValue == 0" class="empty-doughnut1">
          Total {{ chartOptions.customTotalValue }}
        </div>
        <div
          :style="chartOptions.customTotalValue == 0 ? 'display:none' : ''"
          :id="name"
          :name="name"
          style="margin: 0 auto; text-align: left; margin-left: -10px"
        ></div>
      </v-col>
      <v-col
        cols="5"
        class="p-0 pt-2"
        style="
          font-size: 11px;
          color: #000000;
          padding-left: 0px;
          padding-right: 0px;
          line-height: 32px;
        "
      >
        <v-row>
          <v-col cols="8"
            ><v-icon color="#92d050">mdi mdi-square-medium</v-icon>Low</v-col
          ><v-col cols="4" style="padding-left: 0px">{{
            data[1]?.length ?? "0"
          }}</v-col>
        </v-row>
        <v-divider color="#dddddd" />
        <v-row>
          <v-col cols="8"
            ><v-icon color="#ffc000">mdi mdi-square-medium</v-icon>Medium</v-col
          ><v-col cols="4" style="padding-left: 0px">{{
            data[2]?.length ?? "0"
          }}</v-col>
        </v-row>
        <v-divider color="#dddddd" />
        <v-row>
          <v-col cols="8"
            ><v-icon color="#ff0000">mdi mdi-square-medium</v-icon>High</v-col
          ><v-col cols="4" style="padding-left: 0px">{{
            data[3]?.length ?? "0"
          }}</v-col>
        </v-row>
        <v-divider color="#dddddd" />
      </v-col>
    </v-row>

    <!-- <div
      v-if="data.length == 0"
      style="
        padding: 0px;
        margin: auto;
        text-align: center;
        vertical-align: middle;
        height: auto;
      "
    >
      No Data available
    </div> -->
  </div>
</template>

<script>
import AlamAllEvents from "../../Alarm/ComponentAllEvents.vue";
export default {
  props: ["name", "filter_customers_list"],
  components: { AlamAllEvents },
  data() {
    return {
      dialogEventsList: false,
      key: 1,
      totalCount: 0,
      filter1: "categories",
      data: [],
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

        colors: ["#92d050", "#ffc000", "#ff0000"],

        series: [],
        chart: {
          toolbar: {
            show: false,
          },
          width: 130,
          height: 150,
          type: "donut",
        },
        customTotalValue: 0,
        plotOptions: {
          pie: {
            donut: {
              labels: {
                show: true,
                name: {
                  show: true,
                  fontSize: "22px",
                  fontFamily: "Rubik",
                  color: "#dfsda",
                  offsetY: -10,
                },
                value: {
                  show: true,
                  fontSize: "16px",
                  fontFamily: "Helvetica, Arial, sans-serif",
                  color: undefined,
                  offsetY: 16,
                  formatter: function (val) {
                    return val;
                  },
                },
                total: {
                  show: true,
                  label: "Total",
                  color: "#373d3f",
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
    };
  },
  mounted() {
    this.loadDevicesStatistics();
    // setInterval(() => {
    //   if (this.$route.name == "security-dashboard")
    //     this.loadDevicesStatistics();
    // }, 1000 * 30);
  },
  methods: {
    showDialogEvents() {
      this.key += 1;
      this.dialogEventsList = true;
    },
    applyFilter() {
      this.loadDevicesStatistics();
    },
    loadDevicesStatistics() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          filter_customers_list: this.filter_customers_list,
        },
      };

      this.$axios
        .get(`/security_device_alarm_burglary_stats`, options)
        .then(({ data }) => {
          this.data = data;
          this.renderChart1(data);
        });
    },

    async renderChart1(data) {
      let counter = 0;
      let total = 0;

      this.chartOptions.labels[0] = "Low";
      this.chartOptions.series[0] = data["1"]?.length ?? 0;

      this.chartOptions.labels[1] = "Medium";
      this.chartOptions.series[1] = data["2"]?.length ?? 0;

      this.chartOptions.labels[2] = "High";
      this.chartOptions.series[2] = data["3"]?.length ?? 0;

      total =
        this.chartOptions.series[0] +
        this.chartOptions.series[1] +
        this.chartOptions.series[2];

      this.chartOptions.customTotalValue = total;

      if (this.chart) {
        this.chart.destroy();
      }

      // Render the chart
      this.chart = await new ApexCharts(
        document.querySelector("#" + this.name),
        this.chartOptions
      );
      if (this.chart) this.chart.render();
    },
  },
  created() {},
};
</script>
