<template>
  <div style="padding: 0px; width: 100%; height: auto">
    <v-dialog v-model="dialogDevicesList" max-width="1000px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense style="color: black3333">Disamed Devices</span>
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
          <Devices style="padding: 0px" :key="key" :eventFilter="'disamed'" />
        </v-card-text>
      </v-card>
    </v-dialog>

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
          height: 200px;
        "
      >
        <v-row style="">
          <v-col cols="12" class="text-center justify-center">
            <div style="font-size: 35px; color: #fe0004; font-weight: bold">
              {{ activeAlarmCount ? activeAlarmCount : "0" }}
            </div>

            <div style="font-size: 15px">Open</div>
          </v-col>
        </v-row>
        <v-divider color="#dddddd" />
        <v-row>
          <v-col cols="12" class="text-center justify-center mt-2">
            <div style="font-size: 35px; color: #07af50; font-weight: bold">
              {{ closedAlarmCount ? closedAlarmCount : "0" }}
            </div>

            <div style="font-size: 15px">Closed</div>
          </v-col>
        </v-row>
        <v-divider color="#dddddd" />
      </v-col>
    </v-row>
  </div>
</template>

<script>
import Devices from "../Devices.vue";

export default {
  props: ["name", "activeAlarmCount", "closedAlarmCount"],
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
        colors: ["#fe0004", "#07af50"],

        series: [],
        chart: {
          toolbar: {
            show: false,
          },
          width: 200,
          height: 200,
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
    };
  },
  mounted() {
    // setInterval(() => {
    //   if (this.$route.name == "alarm-dashboard") this.loadDevicesStatistics();
    // }, 1000 * 20);
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
      if (this.activeAlarmCount) this.renderChart1();
    },

    async renderChart1() {
      let counter = 0;
      let total = 1000;

      // this.chartOptions.labels[0] = "Total";
      // this.chartOptions.series[0] = data.total;

      this.chartOptions.labels[0] = "Open";
      this.chartOptions.series[0] = this.activeAlarmCount ?? 0;

      this.chartOptions.labels[1] = "Closed";
      this.chartOptions.series[1] = this.closedAlarmCount ?? 0;

      this.chartOptions.customTotalValue = this.activeAlarmCount
        ? this.activeAlarmCount + this.closedAlarmCount
        : 0; //this.items.ExpectingCount;

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
