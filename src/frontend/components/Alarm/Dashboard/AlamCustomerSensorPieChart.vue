<template>
  <div style="padding: 0px; width: 100%">
    <v-row class="pt-0 mt-0">
      <v-col cols="12" class="text-center pt-0">
        <div v-if="chartOptions.customTotalValue == 0" class="empty-doughnut2">
          Total 0
        </div>
        <div
          :style="chartOptions.customTotalValue == 0 ? 'display:none' : ''"
          :id="name"
          :name="name"
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
        padding-top: 36%;
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
      //   items: [
      //     { title: "Title1", value: 20 },
      //     { title: "Title2", value: 30 },
      //     { title: "Title3", value: 40 },
      //     { title: "Title4", value: 50 },
      //   ],
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

        colors: [
          "#843c0c",
          "#F4B400",
          "#02B64B",
          "#f44336",
          "#00b0f0",
          "#000000",
        ],

        series: [],
        chart: {
          toolbar: {
            show: false,
          },
          width: 320,
          height: 250,
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
          show: true,
          style: "margin:10px",
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
    applyFilter() {
      this.loadDevicesStatistics();
    },
    loadDevicesStatistics() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      this.$axios.get(`/device_sensors_stats`, options).then(({ data }) => {
        this.categories = data;
        this.renderChart1(data);
      });
    },

    async renderChart1(data) {
      let counter = 0;
      let total = 1000;

      this.chartOptions.labels[0] = "Burglary";
      this.chartOptions.series[0] = data.Burglary ?? 0;

      this.chartOptions.labels[1] = "Attendance";
      this.chartOptions.series[1] = data.Attendance ?? 0;

      this.chartOptions.labels[2] = "Medical";
      this.chartOptions.series[2] = data.Medical ?? 0;

      this.chartOptions.labels[3] = "Temperature" ?? 0;
      this.chartOptions.series[3] = data.Temperature ?? 0;

      this.chartOptions.labels[4] = "Water";
      this.chartOptions.series[4] = data.Water ?? 0;

      this.chartOptions.labels[5] = "Fire";
      this.chartOptions.series[5] = data.Fire ?? 0;

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
