<template>
  <div style="padding: 0px; width: 100%; height: auto">
    <v-dialog v-model="dialogDevicesList" max-width="80%">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense style="color: black3333">Offline Devices</span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="dialogDevicesList = false"
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text class="p-0" style="padding: 0px">
          <Devices style="padding: 0px" :key="key" :eventFilter="'offline'" />
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-row style="margin-top: -27px"
      ><v-col cols="8" style="color: black; font-size: 12px">Live</v-col>

      <v-col cols="4" class="text-right align-right"
        ><img
          @click="showDialogEvents()"
          src="/dashboard-arrow.png"
          style="width: 18px; padding-top: 5px"
      /></v-col>
    </v-row>
    <v-divider color="#5a82ca" style="margin-bottom: 10px" />
    <v-row class="pt-0 mt-0">
      <v-col
        cols="7"
        class="text-center pt-0"
        style="margin: 0 auto; text-align: left; margin-left: -10px"
      >
        <div v-if="chartOptions.customTotalValue == 0" class="empty-doughnut2">
          Total <br />0
        </div>
        <div
          :style="chartOptions.customTotalValue == 0 ? 'display:none' : ''"
          :id="name"
          :name="name"
          style="width: 320px; margin: 0 auto; text-align: left"
        ></div>
      </v-col>
      <v-col
        cols="5"
        class="pt-0"
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
            categories ? categories.online : "0"
          }}</v-col>
        </v-row>
        <v-divider color="#dddddd" />
        <v-row>
          <v-col cols="8"
            ><v-icon color="#ff0000">mdi mdi-square-medium</v-icon
            >Offline</v-col
          ><v-col cols="4" style="padding-left: 0px">{{
            categories ? categories.total - categories.online : "0"
          }}</v-col>
        </v-row>
        <v-divider color="#dddddd" />
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
  props: ["name"],
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

        colors: ["#92d050", "#ff0000"],

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
  mounted() {
    setInterval(() => {
      if (this.$route.name == "alarm-dashboard") this.loadDevicesStatistics();
    }, 1000 * 20);
    this.loadDevicesStatistics();
  },
  methods: {
    showDialogEvents() {
      this.key += 1;
      this.dialogDevicesList = true;
    },
    applyFilter() {
      this.loadDevicesStatistics();
    },
    loadDevicesStatistics() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      this.$axios.get(`/device_live_stats`, options).then(({ data }) => {
        this.categories = data;
        this.renderChart1(data);
      });
    },

    async renderChart1(data) {
      let counter = 0;
      let total = 1000;

      this.chartOptions.labels[0] = "Online";
      this.chartOptions.series[0] = data.online;

      this.chartOptions.labels[1] = "Offline";
      this.chartOptions.series[1] = data.total - data.online;

      this.chartOptions.customTotalValue = data.total; //this.items.ExpectingCount;

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
