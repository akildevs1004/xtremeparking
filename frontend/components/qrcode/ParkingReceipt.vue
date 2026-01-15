<template>
  <v-card class="  receipt-card" v-if="details">
    <!-- Header -->
    <v-card-title class="justify-space-between text-center">
      <div>
        <div class="caption grey--text text-center" style="font-size: 14px!important">Parking Receipt # {{
          details.id
          }}

          <span v-if="details.in_time"> {{
            $dateFormat.format_date_month_name_year(details.in_time)
            }}</span>
          <div v-else-if="details.out_time"> {{
            $dateFormat.format_date_month_name_year(details.out_time)
            }}</div>

        </div>

        <div class="headline font-weight-bold text-center" style="font-size: 16px!important">{{ details.company?.name ||
          '---' }}
        </div>
        <div v-if="details.out_time == null && details.payment_datetime">
          <div style="text-align: right; font-size:12px;"><v-icon size="18" color="blue">mdi-calendar-month</v-icon> {{
            $dateFormat.formatDateTime(details.payment_datetime)
            }}</div>


          <div class="text-right" style="font-size:14px;">



            <div v-if="formatted == '0m:00s'" style="color:red">Expired at {{
              $dateFormat.formatDateTime(endTs)
              }}


              <v-btn small dense color="primary" class="mt-2" @click="payExtra(details.id)">
                Pay Extra

              </v-btn>


            </div>
            <div v-else style="color:red"><v-icon size="18" color="blue"
                style="vertical-align: middle; margin-right: 4px;">
                mdi-timer-outline
              </v-icon> Expire in {{ formatted }}</div>
          </div>
        </div>
      </div>

    </v-card-title>

    <v-divider></v-divider>

    <!-- Body -->
    <v-card-text>
      <!-- Vehicle -->
      <div class="section">

        <div class="row">
          <span><v-icon color="blue">mdi-car</v-icon> Vehicle number</span>
          <strong>{{ details.log_vehicle_number }}</strong>
        </div>
      </div>

      <!-- Parking -->
      <div class="section">

        <div class="row row--stack">
          <span>

            <!-- Parking location -->
            <div class="caption grey--text"><v-icon color="red">mdi-map-marker-radius</v-icon> {{
              details.company.location }}</div>
          </span>
          <!-- <strong>{{ details.company.adress }}</strong> -->
        </div>
        <div class="row"><span><v-icon color="green">mdi-clock-outline</v-icon> Entry time</span> {{
          $dateFormat.formatTimeAMPM(details.in_time)
        }} </div>
        <div class="row"><span><v-icon color="red">mdi-clock-outline</v-icon> Exit time</span> {{
          $dateFormat.formatTimeAMPM(details.out_time)
        }} </div>
        <div class="row"><span> <v-icon color="yellow">mdi-timer-sand-complete</v-icon> Total duration</span> {{
          $dateFormat.minutesToHHMM2(details.duration_in_minutes)
        }} </div>
        <div class="row"><span> <v-icon color="white ">mdi-calculator</v-icon> Total duration</span>({{
          details.duration_in_hours }}h X {{
            details.duration_per_hour_amount }} rate) </div>
      </div>

      <!-- Payment -->
      <div class="section">
        <!-- <div class="section-title">Payment</div> -->
        <div class="row row--emph">
          <span><v-icon color="blue">mdi-cash-100</v-icon> Amount</span>
          <strong class="amount"> {{ (details.total_amount) }}</strong>
        </div>
        <div class="row" style="background-color: #409540;"><span> Online Payment - Completed</span>

          <div v-if="formatted == '0m:00s' && details.out_time == null && details.payment_datetime" style="color:red">
            Expired at {{
              $dateFormat.formatDateTime(endTs)
            }}





          </div>

          <strong> </strong>
        </div>

      </div>

      <div style="text-align: center;"><v-btn small dense color="primary" class="mt-2" v-if="details"
          :href="downloadLink" target="_blank" rel="noopener">
          <v-icon left> mdi-printer</v-icon>
          Download PDF
        </v-btn></div>

    </v-card-text>
  </v-card>
</template>

<script>
import mqtt from "mqtt";
import { mqttRequestReply } from '@/utils/mqttRequestReplyClient.js'; // adjust path
export default {
  props: ["details"],

  computed: {


    // downloadLink() {

    // }
  },

  data() {
    return {
      downloadLink: null,
      bufferMinutes: 1,
      endTs: 0,
      remainingMs: 0,
      timer: null,
      // Static demo data
      companyName: "Main Street Garage",
      vehicleNumber: "CA 1234567",
      parkingLocation: "Main Street Garage",
      entryTime: "10:00 AM",
      exitTime: "12:30 PM",
      duration: "2h 30m",
      amount: "$15.00"
    };
  },


  computed: {
    formatted() {
      const ms = Math.max(0, this.remainingMs);
      const sec = Math.floor(ms / 1000);
      const m = Math.floor(sec / 60);
      const s = sec % 60;
      const pad = (n) => (n < 10 ? "0" + n : "" + n);
      return `${m}m:${pad(s)}s`;
    }
  },
  methods: {

    async payExtra(logid) {
      // Replace with your real API call:
      // const data = await this.$axios.post('/parking_qr_pay_extra_minutes', { log_id: logid });


      let data = await mqttRequestReply({
        companyId: 8,
        action: 'parking_qr_pay_extra_minutes',
        payload: { log_id: logid },
        timeoutMs: 1000 * 30,
      });

      console.log(data);


      if (data.action == 'parking_qr_pay_extra_minutes') {
        data = data.data;
        if (data.status) {
          alert("close_popup")
          this.$emit("close_popup");
        }
        else {

        }

      }

    },
    tick() {
      this.remainingMs = this.endTs - Date.now();


      if (this.remainingMs <= 0) {
        this.remainingMs = 0;
        clearInterval(this.timer);
      }
    },
    init() {
      const paidTs = new Date(this.details.payment_datetime).getTime();
      this.endTs = paidTs + this.bufferMinutes * 60 * 1000;
      console.log(this.remainingMs);

      this.tick();
      this.timer = setInterval(this.tick, 1000);
    }
  },
  mounted() {
    this.bufferMinutes = this.details.parking_exit_buffertime;
    this.downloadLink = this.$env.settings.BACKEND_URL + `/parking-receipts/${this.details.id}/print`;
    this.init();
  },
  beforeDestroy() {
    if (this.timer) clearInterval(this.timer);
  }
};
</script>

<style scoped>
.receipt-card {
  background: #111013;
  color: #f4f4f5;
  border-radius: 12px;
}

/* Section headings */
.section {
  margin-top: 16px;
}

.section-title {
  text-transform: uppercase;
  font-size: 12px;
  font-weight: 600;
  color: #bbb;
  margin-bottom: 6px;
}

/* Row layout */
.row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  background: #1a1a1f;
  border-radius: 8px;
  padding: 4px 6px;
  margin-bottom: 8px;
  font-size: 14px;
}

.row--stack span {
  display: flex;
  flex-direction: column;
}

.row--emph {
  background: #222;
}

.amount {
  font-size: 18px;
}
</style>
