<template>
  <div style="padding: 0px; width: 100%; height: auto">
    <v-row style="margin-top: -27px"
      ><v-col cols="8" style="color: black; font-size: 12px">Online</v-col>

      <v-col cols="4" class="text-right align-right"
        ><img src="/dashboard-arrow.png" style="width: 18px; padding-top: 5px"
      /></v-col>
    </v-row>
    <v-divider color="#5a82ca" style="margin-bottom: 10px" />

    <v-row class="pt-0 mt-0">
      <v-col cols="7" class="text-center p-0" style="padding: 0px">
        <div v-if="chartOptions.customTotalValue == 0" class="empty-doughnut1">
          Total 0
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
          margin: auto;
        "
      >
        <v-row>
          <v-col cols="8"
            ><v-icon color="#92d050">mdi mdi-square-medium</v-icon>Online</v-col
          ><v-col cols="4" style="padding-left: 0px">{{
            data?.online ?? "0"
          }}</v-col>
        </v-row>
        <v-divider color="#dddddd" />
        <v-row>
          <v-col cols="8"
            ><v-icon color="#ff0000">mdi mdi-square-medium</v-icon
            >Offline</v-col
          ><v-col cols="4" style="padding-left: 0px">{{
            data?.total ? data.total - data.online : "0"
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
        padding-top: 36%;
      "
    >
      No Data available
    </div> -->
  </div>
</template>

<script>
export default {
  props: ["name", "filter_customers_list"],

  data() {
    return {
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

        colors: ["#ff0000", "#92d050"],

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
                  offsetY: 0,
                  show: true,
                  label: "Total",
                  position: "topRight",
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
    setInterval(() => {
      if (this.$route.name == "security-dashboard")
        this.loadDevicesStatistics();
    }, 1000 * 30);
  },
  methods: {
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

      this.$axios.get(`/device_live_stats`, options).then(({ data }) => {
        this.data = data;
        this.renderChart1(data);
      });
    },

    async renderChart1(data) {
      let counter = 0;
      let total = 0;

      this.chartOptions.labels[0] = "Online";
      this.chartOptions.series[0] = data.online;

      this.chartOptions.labels[1] = "Offline";
      this.chartOptions.series[1] = data.total - data.online;

      total = data.total;

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
