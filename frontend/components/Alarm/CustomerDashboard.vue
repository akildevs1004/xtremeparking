<template>
  <div max-width="100%">
    <v-row
      class="pt-5"
      v-if="
        customer &&
        $dateFormat.verifyDeviceSensorName('Temperature', customer.devices)
      "
    >
      <v-col lg="3" md="3" sm="12" xs="12">
        <AlarmDashboardTemparatureChart1
          :key="key"
          :name="'AlarmDashboardTemparatureChart1Customer1'"
          :height="'130'"
          :temperature_latest="temperature_latest"
          :temperature_date_time="temperature_date_time"
        />
      </v-col>
      <v-col lg="3" md="3" sm="12" xs="12">
        <AlarmDashboardHumidityChart1
          :key="keyTemperature"
          :name="'AlarmDashboardHumidityChart1Customer1'"
          :height="'130'"
          :humidity_latest="humidity_latest"
          :humidity_date_time="humidity_date_time"
        />
      </v-col>

      <v-col lg="6" md="6" sm="12" xs="12">
        <AlarmDashboardTemparatureChart2
          :name="'AlarmDashboardTemparatureChart2Customer1'"
          :height="'115'"
          :device_serial_number="device_serial_number"
          :customer_id="customer_id"
          @updateData="updateData"
        />
      </v-col>
    </v-row>
    <v-col lg="12" md="12" sm="12" xs="12" class="mt-5" style="padding: 3px">
      <AlarmEventsChart
        :name="'AlarmEventsChartCustomer1'"
        :height="'300'"
        :device_serial_number="device_serial_number"
        :customer_id="customer_id"
        :date_from="date_from"
        :date_to="date_to"
      />
    </v-col>
  </div>
</template>

<script>
import AlarmDashboardTemparatureChart1 from "../../components/Alarm/CustomerDashboardTemparatureChart1.vue";
import AlarmDashboardHumidityChart1 from "../../components/Alarm/CustomerDashboardHumidityChart1.vue";
import AlarmDashboardTemparatureChart2 from "../../components/Alarm/CustomerDashboardTemparatureChart2.vue";

export default {
  components: {
    AlarmDashboardTemparatureChart1,
    AlarmDashboardHumidityChart1,
    AlarmDashboardTemparatureChart2,
  },
  props: ["customer_id", "customer"],
  data: () => ({
    key: 1,
    keyTemperature: 1,

    temperature_latest: "",
    temperature_date_time: "",
    humidity_latest: "",
    humidity_date_time: "",
    device_serial_number: "",
    date_from: "",
    date_to: "",
  }),
  computed: {},
  mounted() {},
  created() {
    let today = new Date();
    let monthObj = this.$dateFormat.monthStartEnd(today);
    this.date_from = monthObj.first;
    this.date_to = monthObj.last;
    this.getDataFromApi();

    setInterval(() => {
      this.getDataFromApi();
    }, 1000 * 60);
  },
  watch: {},
  methods: {
    closeDialog() {
      this.key = this.key + 1;
    },

    closePopup() {
      this.$emit("closeCustomerDialog");
    },
    updateData(serial_number) {
      if (this.device_serial_number != serial_number) {
        this.device_serial_number = serial_number;
        this.getDataFromApi();
      }
    },
    getDataFromApi() {
      if (this.device_serial_number == "") return false;
      this.$axios
        .get("alarm_dashboard_get_temparature_latest", {
          params: {
            company_id: this.$auth.user.company_id,
            customer_id: this.customer_id,
            serial_number: this.device_serial_number,
          },
        })
        .then(({ data }) => {
          this.data = data;
          //this.device = data.device;
          this.loading = false;

          this.temperature_latest = data.temperature_latest;
          this.temperature_date_time = this.$dateFormat.format4(
            data.temperature_date_time
          );

          this.temperature_min = data.temperature_min + "&deg;C";
          this.temperature_max = data.temperature_max + "&deg;C";
          this.temperature_min_date_time = this.$dateFormat.format6(
            data.temperature_min_date_time
          );
          this.temperature_max_date_time = this.$dateFormat.format6(
            data.temperature_max_date_time
          );

          //humidity
          this.humidity_latest = data.humidity_latest;
          this.humidity_date_time = this.$dateFormat.format4(
            data.humidity_date_time
          );

          this.humidity_min = data.humidity_min + "%";
          this.humidity_max = data.humidity_max + "%";
          this.humidity_min_date_time = this.$dateFormat.format6(
            data.humidity_min_date_time
          );
          this.humidity_max_date_time = this.$dateFormat.format6(
            data.humidity_max_date_time
          );

          this.keyTemperature = this.keyTemperature + 1;
        });
    },
  },
};
</script>
