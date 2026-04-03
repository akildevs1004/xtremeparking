<template>
  <div>
    <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
      {{ response }}
    </v-snackbar>
    <v-card>
      <v-card-text>
        <v-row>
          <v-col cols="3" style="width: 250px">
            <div class="form-group">

              <!-- <label class="col-form-label">Vehicle Parking Count</label> -->
              <v-text-field label="Total Parking Available  count" style="width: 250px;" type="number" dense outlined
                v-model="parking_count" hide-details></v-text-field>
              <span v-if="errors && errors.parking_count" class="text-danger mt-2">{{
                errors.parking_count[0] }}</span>
            </div>
            <br />

            <div class="form-group">
              <label class="col-form-label">Guest Vehicles Permission</label>

              <v-radio-group class="radiogroup1" style="margin-top:0px " v-model="guset_vehicles">
                <v-radio label="Allowed" :value="true" style="font-size: 10px"></v-radio>
                <v-radio label="Not Allowed" :value="false"></v-radio>

              </v-radio-group>
            </div>

            <br />

            <div class="form-group" v-if="guset_vehicles == 'true' || guset_vehicles == true">
              <label class="col-form-label">Guest Vehicles Free or Paid?</label>
              <v-radio-group class="radiogroup1" style="margin-top:0px  " v-model="guset_vehicles_payment">
                <v-radio label="Free" :value="false" style="font-size: 10px"></v-radio>
                <v-radio label="Paid" :value="true"></v-radio>

              </v-radio-group>

            </div>

            <div class="form-group" style="width: 250px;">
              <label class="col-form-label">Gate Auto Close Time(In seconds)</label>

              <v-select style="width: 250px" outlined dense small v-model="parking_gate_close_time"
                :items="[1, 2, 3, 4, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60]">
              </v-select>
            </div>

            <div class="form-group" v-if="guset_vehicles_payment">

              <!-- <label class="col-form-label">Vehicle Parking Count</label> -->
              <v-text-field label="Rate per Hour" tyle="number" style="width: 250px;" type="number" dense outlined
                v-model="parking_price_per_hour" hide-details></v-text-field>
              <span v-if="errors && errors.parking_price_per_hour" class="text-danger mt-2">{{
                errors.parking_price_per_hour[0] }}</span>
            </div>
            <br />
            <div class="form-group" v-if="guset_vehicles_payment">

              <!-- <label class="col-form-label">Vehicle Parking Count</label> -->
              <v-text-field label="Exit Gate Extra Time  - After Payment" tyle="number" style="width: 250px;"
                type="number" dense outlined v-model="parking_exit_buffertime" hide-details></v-text-field>
              <span v-if="errors && errors.parking_exit_buffertime" class="text-danger mt-2">{{
                errors.parking_exit_buffertime[0] }}</span>
            </div>


            <!-- <div class="form-group">
              <label class="col-form-label">Alarm Notification Popup Pause (Minutes)</label>

              <v-select style="width: 250px" outlined dense small v-model="payload.minutes"
                :items="[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]">
              </v-select>
            </div>
            <div class="form-group">
              <label class="col-form-label">Dashboard Alarm Open Count - Days
              </label>
              <span class="error--text">*</span>
              <v-select style="width: 250px" outlined dense small v-model="payload.dashboard_alarm_open_count_days"
                :items="[5, 10, 30, 60, 90, 120]">
              </v-select>
            </div> -->
            <v-col cols="12" style="width: 210px">
              <div class="text-right">
                <v-btn dark small :loading="loading" color="primary" @click="submit()">
                  Update
                </v-btn>
              </div>
            </v-col>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </div>
</template>
<script>
export default {
  props: ["company"],
  data: () => ({
    payload: {},
    loading: false,
    snackbar: false,
    response: "",
    parking_count: null,
    guset_vehicles: null,
    guset_vehicles_payment: null,
    parking_gate_close_time: 2,
    parking_exit_buffertime: 20,
    errors: null,

  }),
  async mounted() { },
  async created() {

    this.parking_count = this.company.parking_count;
    this.guset_vehicles = this.company.guset_vehicles;
    this.guset_vehicles_payment = this.company.guset_vehicles_payment;
    this.parking_gate_close_time = this.company.parking_gate_close_time;
    this.parking_price_per_hour = this.company.parking_price_per_hour;
    this.parking_exit_buffertime = this.company.parking_exit_buffertime;


  },
  methods: {
    submit() {
      this.loading = true;

      if (!this.guset_vehicles == 'false')
        this.guset_vehicles_payment = false;

      this.payload.parking_count = this.parking_count;
      this.payload.guset_vehicles = this.guset_vehicles;// == 'true' ? true : false;


      this.payload.parking_gate_close_time = this.parking_gate_close_time;


      this.payload.guset_vehicles_payment = this.guset_vehicles_payment;// == 'true' ? true : false;
      if (this.guset_vehicles == false)

        this.payload.guset_vehicles_payment = false;


      this.payload.parking_price_per_hour = this.guset_vehicles_payment ? this.parking_price_per_hour : 0;
      this.payload.parking_exit_buffertime = this.parking_exit_buffertime;

      this.$axios
        .post("update_parking_settings", this.payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.snackbar = true;
            this.response =
              "Parking Settings updated successfully. ";

            // this.upload.name = "";
            this.errors = [];

            // Redirect to dashboard after 4 seconds
            // setTimeout(() => {
            //   window.location.href = "/alarm/dashboard";
            // }, 2000);
          }
        })
        .catch((error) => {
          this.loading = false;
          console.error("Update failed:", error);
        });
    },
  },
};
</script>
