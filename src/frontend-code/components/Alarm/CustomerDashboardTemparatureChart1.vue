<template>
  <div style="width: 100%; height: 190px">
    <v-card class="py-2" style="width: 100%">
      <v-row>
        <v-col cols="8"
          ><span class="pl-5" style="font-size: 16px"
            >Today Temperature  </span
          ></v-col
        >
        <v-col cols="4" class="pull-right"
          >
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
            ><img
              src="/alarm-icons/temperature.png"
              style="width: 40px"
            ></img>
          </v-col>
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
              style="width: 150px; margin-top: 0px"
            />

            <div style="font-size: 12px; margin-top: -50px">
              Updated at : {{ temperature_date_time }}
            </div>
          </v-col>
        </v-row>
      </v-col>
    </v-card>
    <!-- <ComonPreloader
      icon="face-scan"
    
      style="max-height: 180px"
      height="150px"
    /> -->
  </div>
</template>

<script>
// import VueGauge from "vue-gauge";
export default {
  props: [
    "name",
    "temperature_latest",
    "temperature_date_time",
    "humidity_latest",
    "humidity_date_time",
    "device_serial_number",
    "height",
    "branch_id",
    "temperature_date_time",
  ],
  // components: { VueGauge },
  data() {
    return {
      key:1,
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
        arcDelimiters: [23, 50, 99],

        //arcLabels: [23, 50, 99],
        arcColors: ["#008450", "#EFB700", "#B81D13"],
      },

      key: 1,
    };
  },
  watch: {},
  mounted() {
    let value = this.temperature_latest;
    if (value == "--") {
      value = 0;
    }
    setTimeout(() => {
      this.VueGaugeoptions = {
        needleValue: value,
        centralLabel:value + "°C",
        hasNeedle: true,
        arcDelimiters: [23, 50, 99],
        //arcLabels: [23, 50, 99],
        arcColors: ["#008450", "#EFB700", "#B81D13"],
        chartWidth: 280,
        label: {
          show: true, // Display labels
        },
      };

      this.loading = false;
    }, 1000);
  },
  created() {
    
  },

  methods: {},
};
</script>
