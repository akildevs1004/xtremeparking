<template>
  <div
    style="
      padding: 0px;
      width: 100%;
      height: auto;
      border: 1px solid #ddd;
      height: 225px;
    "
  >
    <v-row class="pt-0 mt-0" style="height: 150px">
      <v-col
        class="text-center pt-0"
        style="margin: 0 auto; text-align: left; margin-left: -10px"
      >
        <div
          style="
            margin: auto;
            height: 20px;
            text-align: center;
            font-weight: bold;
          "
        >
          {{ componentData ? componentData.title : "---" }}
        </div>

        <div
          v-if="
            chartOptions.customTotalValue == 0 ||
            chartOptions.customTotalValue == null ||
            chartOptions.customTotalValue == 'NaN'
          "
          class="empty-doughnut3"
        >
          0%
        </div>
        <div style="width: 150px; margin: auto; text-align: cener">
          <div
            :style="chartOptions.customTotalValue == 0 ? 'display:none' : ''"
            :id="name"
            :name="name"
            style="width: 150px; margin: 0 auto; text-align: cener"
          ></div>
        </div>
      </v-col>
    </v-row>

    <v-row style="margin-top: 0px">
      <v-col
        cols="12"
        class="text-center justify-center"
        style="margin-top: 10px"
      >
        <div
          style="
            font-size: 20px;
            color: #07af50;
            font-weight: bold;
            border-top: 1px solid #ddd;
          "
        >
          {{ componentData ? componentData.series[0] : "0" }}
        </div>

        <div style="font-size: 16px">
          {{ componentData ? componentData.title : "---" }}
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import Devices from "../Devices.vue";

export default {
  props: ["name", "componentData"],
  components: { Devices },
  data() {
    return {
      key: 1,
      dialogDevicesList: false,
      totalCount: 0,
      filter1: "categories",
      categories: [],
      chartOptions: {
        theme: {
          mode: "light",
        },
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
        colors: [],

        series: [],
        chart: {
          toolbar: {
            show: false,
          },
          width: 150,
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
                  // offsetY: -10,
                },
                value: {
                  show: true,
                  fontSize: "16px",
                  fontFamily: "Helvetica, Arial, sans-serif",
                  color: undefined,
                  // offsetY: 16,
                  formatter: function (val) {
                    return val;
                  },
                },
                total: {
                  show: true,
                  // label: this.componentData
                  //   ? this.componentData.title
                  //   : "Total",
                  label: this.componentData?.percentage + "%" || 0 + "%",
                  color: "#373d3f",
                  // fontSize: "30px",
                  position: "center",
                  // padding: {
                  //   top: 20, // Adds padding at the top
                  // },

                  formatter: function (val) {
                    return " ";
                    // return this.componentData
                    //   ? this.componentData.percentage + "%"
                    //   : "0%";
                    return val.config?.customTotalValue + "%" || 0 + "%";
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
    if (this.componentData) {
      this.renderChartComponent();
    }
  },
  methods: {
    async renderChartComponent() {
      if (this.componentData) {
        this.chartOptions.labels[0] = this.componentData.labels[0];
        this.chartOptions.series[0] = this.componentData.series[0];

        this.chartOptions.labels[1] = this.componentData.labels[1];
        this.chartOptions.series[1] = this.componentData.series[1];
        if (this.componentData.labels[2]) {
          this.chartOptions.labels[2] = this.componentData.labels[2];
          this.chartOptions.series[2] = this.componentData.series[2];
        }

        this.chartOptions.colors = this.componentData.colors;

        this.chartOptions.customTotalValue = this.componentData.percentage; // this.componentData.customTotalValue;

        if (this.chart) {
          this.chart.destroy();
        }

        // Render the chart
        this.chart = await new ApexCharts(
          document.querySelector("#" + this.name),
          this.chartOptions
        );
        if (this.chart) this.chart.render();
      }
    },
  },
  created() {},
};
</script>
