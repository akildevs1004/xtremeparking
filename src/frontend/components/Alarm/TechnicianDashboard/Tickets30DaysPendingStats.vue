<template>
  <div style="padding: 0px; width: 100%; height: auto">
    <v-dialog v-model="dialogEventsList" max-width="80%">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span style="color: black3333" dense>Pending More than 30 days</span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="
              dialogEventsList = false;
              $emit('emitStartrefresh');
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text style="padding: 20px; padding-left: 0px">
          <TicketsList
            :filterWord="'30daysPending'"
            style="padding: 0px; padding-top: 0px"
            :key="key"
            :popup="true"
            :eventFilter="'Burglary'"
          />
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-row style="margin-top: -27px"
      ><v-col cols="8" style="color: black; font-size: 12px"
        >Pending More than 30 Days
      </v-col>

      <v-col cols="4" class="text-right align-right"
        ><img
          @click="showDialogEvents()"
          src="/dashboard-arrow.png"
          style="width: 18px; padding-top: 5px"
      /></v-col>
    </v-row>
    <v-divider color="#5a82ca" style="margin-bottom: 10px" />

    <v-row class="pt-0 mt-0">
      <v-col cols="7" class="text-center p-0" style="padding: 0px">
        <div v-if="chartOptions.customTotalValue == 0" class="empty-doughnut1">
          Total {{ chartOptions.customTotalValue }}
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
        class="p-0 pt-5"
        style="
          font-size: 11px;
          color: #000000;
          padding-left: 0px;
          padding-right: 0px;
          line-height: 32px;
        "
        ><v-row>
          <v-col cols="9"
            ><v-icon color="#ffc000">mdi mdi-square-medium</v-icon
            >Customer</v-col
          ><v-col cols="3" style="padding-left: 0px">{{
            data.customer_count ?? "0"
          }}</v-col> </v-row
        ><v-divider color="#dddddd" />
        <v-row>
          <v-col cols="9"
            ><v-icon color="#92d050">mdi mdi-square-medium</v-icon
            >Operator</v-col
          ><v-col cols="3" style="padding-left: 0px">{{
            data.security_count ?? "0"
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
      "
    >
      No Data available
    </div> -->
  </div>
</template>

<script>
import TicketsList from "../../Tickets/TicketsList.vue";
export default {
  props: ["name", "technician_id"],
  components: { TicketsList },
  data() {
    return {
      dialogEventsList: false,
      key: 1,
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

        colors: ["#92d050", "#ffc000", "#ff0000"],

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
    this.loadDevicesStatistics();
    // setInterval(() => {
    //   if (this.$route.name == "security-dashboard")
    //     this.loadDevicesStatistics();
    // }, 1000 * 30);
  },
  methods: {
    showDialogEvents() {
      this.key += 1;
      this.dialogEventsList = true;
      this.$emit("emitStoprefresh");
    },
    applyFilter() {
      this.loadDevicesStatistics();
    },
    loadDevicesStatistics() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          days: 30,
        },
      };

      this.$axios
        .get(`/technician_tickets_pending_days_stats`, options)
        .then(({ data }) => {
          this.data = data;
          this.renderChart1(data);
        });
    },

    async renderChart1(data) {
      let counter = 0;
      let total = 0;

      this.chartOptions.labels[0] = "Customer";
      this.chartOptions.series[0] = data.customer_count ?? 0;

      this.chartOptions.labels[1] = "Operator";
      this.chartOptions.series[1] = data.security_count ?? 0;

      total = this.chartOptions.series[0] + this.chartOptions.series[1];

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
