<template>
  <div style="padding: 0px; width: 100%">
    <h3>Event Closed in Minutes</h3>
    <v-row class="pt-0 mt-0">
      <v-col cols="12" class="text-center pt-0">
        <div v-if="chartOptions.customTotalValue == 0" class="empty-doughnut2">
          Total 0
        </div>
        <div
          :style="chartOptions.customTotalValue == 0 ? 'display:none' : ''"
          :id="name"
          :name="name"
          style="width: 100%; margin: 0 auto; text-align: left"
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
  props: ["name", "height", "date_from", "date_to", "customer_id"],
  data() {
    return {
      chart: null,
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
          width: "320px",
          height: "200px",
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
  created() {
    //this.loadDevicesStatistics();
  },
  methods: {
    loadDevicesStatistics() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          branch_id: this.branch_id > 0 ? this.branch_id : null,
          device_serial_number: this.device_serial_number,
          date_from: this.date_from,
          date_to: this.date_to,
          customer_id: this.customer_id,
        },
      };

      this.$axios
        .get(`/alarm_response_statistics`, options)
        .then(({ data }) => {
          this.categories = data;
          this.renderChart1(data);
        });
    },

    async renderChart1(data) {
      let counter = 0;
      let total = 1000;

      this.chartOptions.labels[0] = "Less than Min";
      this.chartOptions.series[0] = data.less_than_1_minute ?? 0;

      this.chartOptions.labels[1] = "1 to 5 Min";
      this.chartOptions.series[1] = data.between_1_and_5_minutes ?? 0;

      this.chartOptions.labels[2] = "5 to 10 Min";
      this.chartOptions.series[2] = data.between_5_and_10_minutes ?? 0;

      this.chartOptions.labels[3] = ">10 minutes";
      this.chartOptions.series[3] = data.more_than_10_minutes ?? 0;

      this.chartOptions.customTotalValue =
        data.less_than_1_minute +
        data.between_1_and_5_minutes +
        data.between_5_and_10_minutes +
        data.more_than_10_minutes; //this.items.ExpectingCount;

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
