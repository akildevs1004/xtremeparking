<template>
  <div style="width: 100%; height: 100%">
    <div class="text-center" v-if="data.length == 0">
      No Alarm Events Data is Available
    </div>
    <!-- <v-row v-if="$auth.user.user_type != 'company'">
      <v-col cols="8">
        <h4>{{ display_title }}</h4>
      </v-col>

      <v-col
        cols="2"
        class="align-right"
        v-if="$auth.user.user_type !== 'customer'"
      >
        <v-autocomplete
          style="padding-top: 6px"
          @change="getDataFromApi()"
          class="reports-events-autocomplete"
          v-model="customer_id"
          :items="customersList"
          dense
          placeholder="Select Building"
          outlined
          item-value="id"
          item-text="building_name"
        >
        </v-autocomplete>
      </v-col>
      <v-col class="text-right"
        ><CustomFilter
          v-if="$auth.user.user_type !== 'customer'"
          id="test"
          style="float: right; padding-top: 5px"
          @filter-attr="filterAttr"
          :default_date_from="date_from"
          :default_date_to="date_to"
          :defaultFilterType="1"
          :height="'40px'"
        />
      </v-col>
    </v-row> -->

    <div
      :id="name"
      style="width: 100%; height: 300px"
      :key="display_title"
    ></div>
  </div>
</template>

<script>
export default {
  props: [
    "name",
    "height",
    "branch_id",
    "device_serial_number",
    "customer_id",
    "date_from",
    "date_to",
  ],

  data() {
    return {
      data: [],
      key: 1,
      filterDeviceId: null,
      customersList: [],
      devices: [],
      loading: false,
      display_title: "Alarm Events",
      filter_customer_id: "",
      series: [
        {
          name: "Burglary",
          data: [],
        },

        {
          name: "Medical",
          data: [],
        },
        {
          name: "Fire",
          data: [],
        },
        {
          name: "Water",
          data: [],
        },
        {
          name: "Temperature",
          data: [],
        },
      ],

      chartOptions2: {
        colors: [
          "#6A5ACD",
          "#efb700",
          "#FF0000",

          "#0071bd",
          "#b81d13",
          // "#008450",
        ],
        series: [],

        chart: {
          type: "bar",
          width: "98%",
          height: "300px",
          toolbar: {
            show: false,
          },
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: "100%",
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
    // async branch_id(val) {
    //   this.$store.commit("CommDashboard/setDashboardData", null);
    //   //this.$store.commit("setDashboardData", null);
    //   await this.getDataFromApi();
    // },
  },
  mounted() {
    // this.chartOptions2.chart.height = this.height;
    // this.chartOptions2.series = this.series;
  },
  async created() {
    //this.customer_id = this.filter_customer_id;

    await this.getDataFromApi();
    setInterval(() => {
      if (this.$route.name == "alarm-view-customer-id") {
        this.getDataFromApi();
      }
    }, 1000 * 20);
  },

  methods: {
    filterAttr(data) {
      this.date_from = data.from;
      this.date_to = data.to;

      this.getDataFromApi();
    },
    getDeviceList() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`/device_list`, options).then(({ data }) => {
        this.devices = data;
      });
    },
    filterDevice() {
      this.$store.commit("CommDashboard/setDashboardData", null);
      this.$store.commit("CommDashboard/every_hour_count", null);

      this.getDataFromApi();
    },

    async getDataFromApi() {
      if (!this.date_from) return false;
      this.loading = true;

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

      await this.$axios
        .get(`/alarm_logs_data_month_data`, options)
        .then(({ data }) => {
          this.data = data;
          this.renderChart2(data);
        });

      this.key = this.key + 1;
    },

    async renderChart2(data) {
      try {
        // this.chartOptions2.chart.height = this.height;

        let counter = 0;

        this.chartOptions2.series = [
          {
            name: "Burglary",
            data: [],
          },

          {
            name: "Medical",
            data: [],
          },
          {
            name: "Fire",
            data: [],
          },
          {
            name: "Water",
            data: [],
          },
          {
            name: "Temperature",
            data: [],
          },
        ];

        this.chartOptions2.xaxis = {
          categories: [],
        };
        data.forEach((item) => {
          this.chartOptions2.series[0]["data"][counter] = parseInt(
            item.Burglary
          );

          this.chartOptions2.series[1]["data"][counter] = parseInt(
            item.Medical
          );
          this.chartOptions2.series[2]["data"][counter] = parseInt(item.Water);
          this.chartOptions2.series[3]["data"][counter] = parseInt(item.Fire);
          this.chartOptions2.series[4]["data"][counter] = parseInt(
            item.Temperature
          );

          this.chartOptions2.xaxis.categories[counter] =
            this.$dateFormat.format2(item.date);

          counter++;
        });
        this.loading = false;

        // await new ApexCharts(
        //   document.querySelector("#" + this.name),
        //   this.chartOptions2
        // ).render();

        // Destroy the existing chart instance if it exists
        if (this.chart) {
          this.chart.destroy();
        }

        // Render the chart
        this.chart = await new ApexCharts(
          document.querySelector("#" + this.name),
          this.chartOptions2
        );
        if (this.chart) this.chart.render();
      } catch (error) {}

      // setTimeout(() => {
      //   this.key += 1;
      // }, 1000 * 3);
    },
  },
};
</script>
