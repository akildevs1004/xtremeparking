<template>
  <div style="width: 100%">
    <!-- <CustomFilter
      style="float: right; padding-top: 5px; z-index: 9999"
      @filter-attr="filterAttr"
      :default_date_from="date_from"
      :default_date_to="date_to"
      :defaultFilterType="1"
      :height="'40px'"
    /> -->

    <v-card class="py-2" style="width: 100%">
      <v-row>
        <v-col cols="5"
          ><span class="pl-5" style="font-size: 16px">
            Temperature Hourly</span
          ></v-col
        >
        <v-col cols="4">
          <v-select
            class="pb-0"
            hide-details
            v-model="filterSerialNumber"
            outlined
            @change="getDataFromApi()"
            dense
            x-small
            label="Temperature Device"
            :items="temperatureDeviceslist"
            item-value="serial_number"
            item-text="name"
          ></v-select
        ></v-col>
        <v-col cols="3" class="pull-right">
          <!-- <v-icon @click="getDataFromApi(1)" style="float: right"
            >mdi mdi-reload</v-icon
          > -->

          <v-menu
            style="z-index: 9999"
            v-model="from_menu"
            :close-on-content-click="false"
            :nudge-left="145"
            transition="scale-transition"
            offset-y
            min-width="auto"
          >
            <template v-slot:activator="{ on, attrs }">
              <v-text-field
                style="width: 230px; float: right; z-index: 9999; height: 10px"
                outlined
                v-model="date_from"
                v-bind="attrs"
                v-on="on"
                dense
                x-small
                hide-details
                class="custom-text-box shadow-none"
                label="Date Filter"
              ></v-text-field>
            </template>
            <v-date-picker
              no-title
              scrollable
              v-model="date_from"
              @input="from_menu = false"
              @change="getDataFromApi()"
            ></v-date-picker>
          </v-menu>
        </v-col>
      </v-row>
      <v-col lg="12" md="12" style="text-align: center; padding-top: 0px">
        <div :id="name" style="width: 100%; margin-top: 0px" class="pt-2"></div>
      </v-col>
    </v-card>
  </div>
</template>

<script>
// import VueApexCharts from 'vue-apexcharts'
export default {
  props: [
    "name",
    "height",
    "branch_id",
    "device_serial_number",
    "from_date",
    "customer_id",
  ],
  data() {
    return {
      temperatureDeviceslist: [],
      filterSerialNumber: "",
      date_from: "",
      date_to: "",
      key: 1,
      from_menu: false,
      series: [
        {
          name: "Temparature",
          type: "column",
          data: [],
        },
      ],
      chartOptions: {
        chart: {
          height: 190, //
          type: "line",
          toolbar: {
            show: false,
          },
        },
        stroke: {
          width: [0, 2],

          curve: "smooth",
        },
        // title: {
        //   show: false,
        //   // text: "Temperature Hourly Chart",
        // },
        dataLabels: {
          enabled: true,
          enabledOnSeries: [1],
        },
        labels: [],
        plotOptions: {
          bar: {
            columnWidth: "20%",
            colors: {
              ranges: [
                {
                  from: 0,
                  to: 23,
                  color: "#008450",
                },
                {
                  from: 23,
                  to: 50,
                  color: "#EFB700",
                },
                {
                  from: 50,
                  to: 99,
                  color: "#B81D13",
                },
              ],
            },
          },
        },
        xaxis: {
          type: "string",
        },

        dataLabels: {
          enabled: false,
        },
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
    // from_date(val) {
    //   this.getDataFromApi();
    // },
  },

  created() {
    const today = new Date();
    this.date_from = today.toISOString().slice(0, 10);
  },
  async mounted() {
    this.chartOptions.chart.height = this.height;
    this.chartOptions.series = this.series;
    // setTimeout(() => {
    ////this.getDataFromApi();
    setInterval(() => {
      if (this.$route.name == "alarm-view-customer-id") {
        this.getDataFromApi();
      }
    }, 1000 * 60 * 15);

    /// }, 2000);

    // this.$store.commit(
    //   "AlarmDashboard/alarm_temperature_chart2_date",
    //   this.from_date
    // );
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
    // this.getDataFromApi();
    // setTimeout(() => {
    //   this.getDataFromApi();
    // }, 1000 * 10);

    // setTimeout(() => {
    //   this.getDataFromApi();
    // }, 6000);
  },

  methods: {
    getTemparatureDevices() {
      this.$axios
        .get("customer_temperature_devices", {
          params: {
            company_id: this.$auth.user.company_id,
            branch_id: this.branch_id > 0 ? this.branch_id : null,
            customer_id: this.customer_id,
          },
        })
        .then(({ data }) => {
          this.temperatureDeviceslist = data;
          if (data.length > 0) {
            this.filterSerialNumber = data[0].serial_number;
            this.getDataFromApi();
          }
        });
    },
    filterAttr(data) {
      this.date_from = data.from;
      this.date_to = data.to;

      this.getDataFromApi();
      //this.filterType = "Monthly";
    },
    filterDate() {},
    viewLogs() {
      this.$router.push("/attendance_report");
    },
    getDataFromApi() {
      if (this.device_serial_number != this.filterSerialNumber)
        this.$emit("updateData", this.filterSerialNumber);

      this.key = 1;
      // const data = await this.$store.dispatch("dashboard/every_hour_count");
      this.$axios
        .get("alarm_dashboard_get_hourly_data", {
          params: {
            company_id: this.$auth.user.company_id,
            branch_id: this.branch_id > 0 ? this.branch_id : null,
            serial_number: this.filterSerialNumber,
            from_date: this.date_from,
            customer_id: this.customer_id,
          },
        })
        .then(({ data }) => {
          this.key = this.key + 1;
          this.data = data;
          this.loading = false;
          //this.$store.commit("AlarmDashboard/alarm_temparature_hourly", data);

          this.temperature_hourly_data = data.data;

          this.renderChart(this.temperature_hourly_data);

          // setTimeout(() => {
          //   this.key = this.key + 1;
          // }, 1000);
        });
    },
    async renderChart(data) {
      let counter = 0;

      let IsDataAvailable = false;

      this.chartOptions.series[0]["data"] = [];
      data.forEach((item, index) => {
        this.chartOptions.series[0]["data"][index] = item.count; //parseInt(item.count);

        //this.chartOptions.series[1]["data"][counter] = item.count;

        this.chartOptions.labels[index] = item.hour;
        counter++;
        IsDataAvailable = true;
      });
      try {
        if (IsDataAvailable) {
          // new ApexCharts(
          //   document.querySelector("#" + this.name),
          //   this.chartOptions
          // ).render();

          let chart = await new ApexCharts(
            document.querySelector("#" + this.name),
            this.chartOptions
          );
          if (chart) chart.render();
        }
      } catch (error) {}
    },
  },
};
</script>
