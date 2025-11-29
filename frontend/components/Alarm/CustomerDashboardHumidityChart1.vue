<template>
  <div style="width: 100%; height: 193px">
    <!-- <ComonPreloader
      icon="face-scan"
      v-if="loading"
      style="max-height: 180px"
      height="150px"
    /> -->
    <v-card class="py-2" style="width: 100%">
      <v-row>
        <v-col cols="8"
          ><span class="pl-5" style="font-size: 16px"
            >Today Humidity
          </span></v-col
        >
        <v-col cols="4" class="pull-right">
          <!-- <v-icon @click="getDataFromApi(1)" style="float: right"
            >mdi mdi-reload</v-icon
          > -->
        </v-col>
      </v-row>
      <v-col lg="12" md="12" style="text-align: center; padding-top: 0px">
        <v-row>
          <v-col
            cols="2"
            class="align-items-center justify-content-center pt-10"
            ><img src="/alarm-icons/humidity.png" width="40px"
          /></v-col>
          <v-col cols="10" class="pa-0">
            <div
              v-if="loading"
              style="height: 180px; margin: auto; padding-top: 20%"
            >
              Loading....
            </div>
            <VueGauge
              v-if="!loading"
              :options="VueGaugeoptions"
              :refid="name"
              :id="name"
              style="width: 180px; margin-top: 0px"
            />

            <div style="font-size: 12px; margin-top: -50px">
              Updated at : {{ humidity_date_time }}
            </div>
          </v-col>
        </v-row>
      </v-col>
    </v-card>
  </div>
</template>

<script>
// import VueGauge from "vue-gauge";
export default {
  props: [
    "name",
    "humidity_latest",
    "height",
    "branch_id",
    "humidity_date_time",
  ],
  // components: { VueGauge },
  data() {
    return {
      filterDeviceId: null,
      devices: [],
      loading: true,
      display_title: "Recent 7 days Attendance",
      date_from: "",
      date_to: "",
      VueGaugeoptions: {
        needleValue: 0,
        centralLabel: "0",
        hasNeedle: true,
        arcDelimiters: [60, 80, 99],
        arcColors: ["#0071bd", "#a9dcf4", "#cc86ec"],
      },

      key: 50,
    };
  },
  watch: {},
  mounted() {
    let value = this.humidity_latest;
    if (value == "--") {
      value = 0;
    }
    setTimeout(() => {
      this.VueGaugeoptions = {
        needleValue: value,
        centralLabel: value + "%",
        hasNeedle: true,
        arcDelimiters: [60, 80, 99],
        // arcLabels: [60, 80, 99],
        arcColors: ["#0071bd", "#a9dcf4", "#cc86ec"],
        chartWidth: 280,
        needleColor: "#0071bd",
        arcOverEffect: true,

        top: true,

        label: {
          show: true, // Display labels
        },
      };

      this.loading = false;
    }, 1000);
  },
  created() {},

  methods: {},
};
</script>
