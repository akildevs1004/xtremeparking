<template>
  <div style="width: 100%">
    <v-row>
      <v-col md="6"> <h4>Events Category</h4></v-col>

      <v-col md="6">
        <CustomFilter
          style="float: right"
          @filter-attr="filterAttr"
          :default_date_from="date_from"
          :default_date_to="date_to"
          :defaultFilterType="1"
          :height="'35px'"
      /></v-col>
    </v-row>

    <div :id="name" style="width: 100%"></div>
  </div>
</template>

<script>
export default {
  props: ["name", "height"],
  data() {
    return {
      loading: false,
      display_title: "Events Category",
      date_from: "",
      date_to: "",
      series: [
        {
          name: "SOS",
          data: [],
        },
        {
          name: "Critical",
          data: [],
        },
        {
          name: "Technical",
          data: [],
        },
        {
          name: "Events",
          data: [],
        },
      ],

      chartOptions: {
        colors: ["#FF0000", "#894F24", "#FFB600", "#2196F3", "#14B012"],
        chart: {
          toolbar: {
            show: false,
          },
          type: "bar",
          width: "98%",
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: "25%",
            endingShape: "rounded",
          },
        },
        dataLabels: {
          enabled: false,
        },
        stroke: {
          show: true,
          width: 2,
          colors: ["transparent"],
        },
        xaxis: {
          categories: [],
        },
        yaxis: {
          title: {
            text: " ",
          },
        },
        fill: {
          opacity: 1,
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val;
            },
          },
        },
      },
    };
  },
  watch: {
    // async display_title() {
    //   await this.getDataFromApi();
    // },
    async branch_id(val) {
      this.$store.commit("dashboard/setDashboardData", null);
      //this.$store.commit("setDashboardData", null);
      await this.getDataFromApi();
    },
  },
  mounted() {
    setTimeout(() => {
      this.getDataFromApi();
    }, 1000 * 3);

    if (this.$route.page == "alarm-intruderdashboard") {
      setInterval(() => {
        this.getDataFromApi();
      }, 1000 * 30);
    }
  },
  async created() {
    // Get today's date
    let today = new Date();

    let monthObj = this.$dateFormat.monthStartEnd(today);

    // Subtract 7 days from today
    let sevenDaysAgo = new Date(today);
    sevenDaysAgo.setDate(today.getDate() - 7);

    // Format the dates (optional)
    this.date_to = today.toISOString().split("T")[0];
    this.date_from = sevenDaysAgo.toISOString().split("T")[0];

    // this.display_title =
    //   "Attendance : " + this.date_from + " to " + this.date_to;
  },

  methods: {
    filterAttr(data) {
      this.date_from = data.from;
      this.date_to = data.to;

      this.filterType = "Monthly"; // data.type;

      this.getDataFromApi();
    },
    async getDataFromApi() {
      this.loading = true;
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          date_from: this.date_from,
          date_to: this.date_to,
        },
      };

      this.$axios
        .get("dashboard_statistics_date_range", options)
        .then((data) => {
          this.renderChart(data.data);
        });
    },
    async renderChart(data) {
      //  try {
      this.chartOptions.chart.height = this.height;
      this.chartOptions.series = this.series;

      let counter = 0;

      this.chartOptions.series = [
        {
          name: "SOS",
          data: [],
        },
        {
          name: "Critical",
          data: [],
        },
        {
          name: "Technical",
          data: [],
        },
        {
          name: "Events",
          data: [],
        },
      ];

      this.chartOptions.xaxis = {
        categories: [],
      };

      data.forEach((item) => {
        this.chartOptions.series[0]["data"][counter] = item.sosCount;
        this.chartOptions.series[1]["data"][counter] = item.crititalCount ?? 0;
        this.chartOptions.series[2]["data"][counter] = item.technicalCount ?? 0;
        this.chartOptions.series[3]["data"][counter] = item.eventsCount ?? 0;
        // this.chartOptions.series[2]["data"][counter] = item.mediumCount;
        // this.chartOptions.series[3]["data"][counter] = item.lowCount;
        this.chartOptions.xaxis.categories[counter] = this.$dateFormat.format2(
          item.date
        );

        counter++;
      });
      this.loading = false;

      //   new ApexCharts(
      //     document.querySelector("#" + this.name),
      //     this.chartOptions
      //   ).render();

      //   await new ApexCharts(
      //     document.querySelector("#AttendanceChart1"),
      //     this.chartOptions
      //   ).render();
      //   console.log(this.chartOptions);
      try {
        if (this.chart) {
          this.chart.destroy();
        }
      } catch (error) {}
      // Render the chart
      this.chart = await new ApexCharts(
        document.querySelector("#" + this.name),
        this.chartOptions
      );
      if (this.chart) this.chart.render();
      //   } catch (error) {}
    },
  },
};
</script>

<style>
.apexcharts-canvas {
  width: 100%;
}
</style>
