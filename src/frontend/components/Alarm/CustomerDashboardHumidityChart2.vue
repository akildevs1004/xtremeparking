<template>
  <div style="width: 100%">
    <!-- <v-menu
      style="z-index: 9999"
      v-model="from_menu"
      :close-on-content-click="false"
      :nudge-left="50"
      transition="scale-transition"
      offset-y
      min-width="auto"
    >
      <template v-slot:activator="{ on, attrs }">
        <v-text-field
          style="
            width: 230px;
            float: right;
            z-index: 9999;
            height: 5px;
            padding-top: 16px;
          "
          outlined
          v-model="from_date"
          v-bind="attrs"
          v-on="on"
          dense
          class="custom-text-box shadow-none"
          label="Date Filter"
        ></v-text-field>
      </template>
      <v-date-picker
        no-title
        scrollable
        v-model="from_date"
        @input="from_menu = false"
        @change="getDataFromApi()"
      ></v-date-picker>
    </v-menu> -->
    <div :id="name" style="width: 100%; margin-top: 0px" class="pt-2"></div>
  </div>
</template>

<script>
// import VueApexCharts from 'vue-apexcharts'
export default {
  props: ["name", "height", "branch_id", "device_serial_number", "from_date"],
  data() {
    return {
      key: 1,
      // from_date: "",
      from_menu: false,
      series: [
        {
          name: "Humidity",
          type: "column",
          data: [],
        },
        // {
        //   name: " ",
        //   type: "line",
        //   data: [],
        // },
      ],
      chartOptions: {
        plotOptions: {
          bar: {
            columnWidth: "20%",
            colors: {
              ranges: [
                {
                  from: 0,
                  to: 60,
                  color: "#0071bd",
                },
                {
                  from: 60,
                  to: 80,
                  color: "#a9dcf4",
                },
                {
                  from: 80,
                  to: 99,
                  color: "#B81D13",
                },
              ],
            },
          },
        },
        colors: ["#0071bd", "#FF8000"],
        chart: {
          height: 220,
          type: "line",
          toolbar: {
            show: false,
          },
        },
        stroke: {
          width: [0, 2],
          curve: "smooth",
        },
        title: {
          text: "Humidity Hourly Chart",
        },
        dataLabels: {
          enabled: true,
          enabledOnSeries: [1],
        },
        labels: [],
        xaxis: {
          type: "string",
        },
        // yaxis: [
        //   {
        //     title: {
        //       text: "Humidity",
        //     },
        //   },
        //   {
        //     opposite: true,
        //     title: {
        //       text: "",
        //     },
        //   },
        // ],
      },
    };
  },
  watch: {
    // branch_id() {
    //   try {
    //     this.$store.commit("AlarmDashboard/alarm_temparature_hourly", null);
    //     this.getDataFromApi();
    //   } catch (e) {}
    // },
    from_date(val) {
      setTimeout(() => {
        this.getDataFromApi();
      }, 1000);
    },
  },

  created() {
    // const today = new Date();
    // this.from_date = today.toISOString().slice(0, 10);
  },
  mounted() {
    this.chartOptions.chart.height = this.height;
    this.chartOptions.series = this.series;
    //console.log("this.$route.name", this.$route.name);

    setInterval(() => {
      if (this.$route.name == "alarm-dashboard") {
        this.getDataFromApi();
      }
    }, 1000 * 60 * 15);

    try {
      new ApexCharts(
        document.querySelector("#" + this.name),
        this.chartOptions
      ).render();
    } catch (error) {}
    setTimeout(() => {
      this.getDataFromApi();
    }, 1000 * 10);
    setTimeout(() => {
      this.getDataFromApi();
    }, 4000);
  },

  methods: {
    viewLogs() {
      this.$router.push("/attendance_report");
    },
    async getDataFromApi() {
      if (!this.device_serial_number) return;

      try {
        // new ApexCharts(
        //   document.querySelector("#" + this.name),
        //   this.chartOptions
        // ).render();

        let chart = await new ApexCharts(
          document.querySelector("#" + this.name),
          this.chartOptions
        );
        if (chart) chart.render();
      } catch (error) {}
      this.key = 1;
      // const data = await this.$store.dispatch("dashboard/every_hour_count");
      this.$axios
        .get("alarm_dashboard_get_humidity_hourly_data", {
          params: {
            company_id: this.$auth.user.company_id,
            branch_id: this.branch_id > 0 ? this.branch_id : null,
            device_serial_number: this.device_serial_number,
            from_date: this.from_date,
          },
        })
        .then(({ data }) => {
          this.key = this.key + 1;
          this.data = data;
          this.loading = false;
          this.$store.commit("AlarmDashboard/alarm_humidity_hourly", data);

          this.temperature_hourly_data = data.houry_data;
          this.renderChart(this.temperature_hourly_data);
          // setTimeout(() => {
          //   this.key = this.key + 1;
          // }, 1000);

          this.key = this.key + 1;
        });
    },
    async renderChart(data) {
      let counter = 0;

      data.forEach((item) => {
        this.chartOptions.series[0]["data"][counter] = item.count; //parseInt(item.count);

        // this.chartOptions.series[1]["data"][counter] = item.count;

        this.chartOptions.labels[counter] = item.hour;
        counter++;
      });
      try {
        // new ApexCharts(
        //   document.querySelector("#" + this.name),
        //   this.chartOptions
        // ).render();

        let chart = await new ApexCharts(
          document.querySelector("#" + this.name),
          this.chartOptions
        );
        if (chart) chart.render();
      } catch (error) {}
    },
  },
};
</script>
