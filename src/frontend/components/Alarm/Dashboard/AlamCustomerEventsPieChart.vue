<template>
  <div style="padding: 0px; width: 100%; height: auto">
    <v-row style="margin-top: -27px"
      ><v-col cols="8" style="color: black; font-size: 12px">Events</v-col>

      <v-col cols="4" class="text-right align-right"
        ><img src="/dashboard-arrow.png" style="width: 18px; padding-top: 5px"
      /></v-col>
    </v-row>
    <v-divider color="#5a82ca" style="margin-bottom: 10px" />
    <v-row class="pt-0 mt-0">
      <v-col
        cols="6"
        class="text-center pt-0"
        style="margin: 0 auto; text-align: left; margin-left: -10px"
      >
        <div v-if="chartOptions.customTotalValue == 0" class="empty-doughnut2">
          Total <br />
          0
        </div>
        <div
          :style="chartOptions.customTotalValue == 0 ? 'display:none' : ''"
          :id="name"
          :name="name"
          style="width: 320px; margin: 0 auto; text-align: left"
        ></div>
      </v-col>
      <v-col
        cols="6"
        class="pt-0"
        style="
          font-size: 11px;
          color: #000000;
          padding-left: 0px;
          padding-right: 0px;
          line-height: 32px;
          margin: auto;
          margin-top: -10px;
        "
      >
        <v-row>
          <v-col cols="8"
            ><v-icon color="#7B1FA2">mdi mdi-square-medium</v-icon
            >Intruder</v-col
          ><v-col cols="4" style="padding-left: 0px">{{
            categories ? categories.burglary : 0
          }}</v-col>
        </v-row>
        <v-divider color="#dddddd" />
        <v-row>
          <v-col cols="8"
            ><v-icon color="#8BC34A">mdi mdi-square-medium</v-icon
            >Medical</v-col
          ><v-col cols="4" style="padding-left: 0px">{{
            categories ? categories.medical : 0
          }}</v-col>
        </v-row>
        <v-divider color="#dddddd" />
        <v-row>
          <v-col cols="8"
            ><v-icon color="red">mdi mdi-square-medium</v-icon>Fire</v-col
          ><v-col cols="4" style="padding-left: 0px">{{
            categories ? categories.fire : 0
          }}</v-col>
        </v-row>
        <v-divider color="#dddddd" />
        <v-row>
          <v-col cols="8"
            ><v-icon color="#4A90E2">mdi mdi-square-medium</v-icon>Water</v-col
          ><v-col cols="4" style="padding-left: 0px">{{
            categories ? categories.water : 0
          }}</v-col>
        </v-row>
        <v-divider color="#dddddd" />

        <v-row>
          <v-col cols="8"
            ><v-icon color="#F57C00">mdi mdi-square-medium</v-icon
            >Temperature</v-col
          ><v-col cols="4" style="padding-left: 0px">{{
            categories ? categories.temperature : 0
          }}</v-col>
        </v-row>
        <!-- <v-divider color="#dddddd" /> -->
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
export default {
  props: ["name"],
  data() {
    return {
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

        colors: ["#7B1FA2", "#8BC34A", "#F57C00", "#4A90E2", "RED"],

        series: [],
        chart: {
          toolbar: {
            show: false,
          },
          width: 180,
          height: 180,
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
  created() {},
  mounted() {
    setInterval(() => {
      if (this.$route.name == "alarm-dashboard") this.loadDevicesStatistics();
    }, 1000 * 20);
    this.loadDevicesStatistics();
  },
  methods: {
    applyFilter() {
      this.loadDevicesStatistics();
    },
    loadDevicesStatistics() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      this.$axios.get(`/alarm_statistics`, options).then(({ data }) => {
        this.categories = data;
        this.renderChart1(data);
        // try {
        // } catch (e) {}
      });
    },

    async renderChart1(data) {
      let counter = 0;
      let total = 1000;

      this.chartOptions.labels[0] = "Burglary";
      this.chartOptions.series[0] = data.burglary ?? 0;

      this.chartOptions.labels[1] = "Medical";
      this.chartOptions.series[1] = data.medical ?? 0;

      this.chartOptions.labels[2] = "Temperature";
      this.chartOptions.series[2] = data.temperature ?? 0;

      this.chartOptions.labels[3] = "Water";
      this.chartOptions.series[3] = data.water ?? 0;
      this.chartOptions.labels[4] = "Fire";
      this.chartOptions.series[4] = data.fire ?? 0;

      this.chartOptions.customTotalValue =
        data.burglary + data.medical + data.temperature + data.water;
      +data.fire;

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
};
</script>
