<template>
  <v-container class="pa-0 wrap" fluid>

    <v-dialog v-model="dialogParkingReceipt" :key="keyId" width="500px" persistent>
      <v-card style="padding:0px!important">
        <v-card-title dense small class="popup_background" style="height: 40px;">
          Parking Receipt - Success
          <v-spacer></v-spacer>
          <v-btn icon @click="dialogParkingReceipt = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>

          <ParkingReceipt v-if="PaymentResponseData" :details="PaymentResponseData" @close_popup="close_popup" />




        </v-card-text></v-card>

    </v-dialog>

    <!-- Header -->
    <v-app-bar flat dense color="#1d2126">
      <v-toolbar-title class="d-flex align-center">
        <v-img :src="logoSrc" alt="Logo" max-height="40" contain class="mr-2" />

      </v-toolbar-title>
    </v-app-bar>

    <!-- Content -->
    <v-container class="mx-auto py-4" style="max-width: 480px">
      <!-- Input -->
      <v-card class="rounded-xl pa-3 mb-3 card-dark">
        <div class="caption text--secondary mb-1">Enter Vehicle Number (Ex: <strong>B123456</strong>)</div>
        <v-text-field v-model.trim="vehicleNo" placeholder="e.g. D12345" dense outlined hide-details="auto" clearable
          :disabled="loading" @keyup.enter="fetchDetails" />
        <v-btn block large color="primary" class="rounded-xl mt-2" :loading="loading" :disabled="!vehicleNo"
          @click="fetchDetails">
          Fetch Parking Details
        </v-btn>
        <v-alert v-if="error" type="error" dense class="mt-3">
          {{ error }}
        </v-alert>
      </v-card>

      <!-- Details -->
      <v-slide-y-transition v-if="details">
        <div v-if="details">
          <!-- Vehicle -->
          <v-card class="rounded-xl pa-4 mb-3 card-dark text-center">
            <div class="caption text--secondary">Vehicle</div>
            <div class="veh-num mt-1">{{ details.vehicle.log_vehicle_number }}</div>
          </v-card>
          <v-row>
            <v-col cols="6"> <!-- In Time -->
              <v-card class="rounded-xl pa-3 mb-3 card-dark text-center">
                <!-- <div class="parkingTime" style="font-size: 15px;;">{{ fmt(details.vehicle.in_time) }}</div> -->


                <div class="parkingTime" style="font-size: 15px;;">{{ details.vehicle.in_time ?
                  $dateFormat.formatTimeAMPM(details.vehicle.in_time) : '--'
                  }}
                </div>
                <div style="font-size: 12px;">{{ details?.vehicle.in_time ?
                  $dateFormat.formatDate(details.vehicle.in_time) : '--'
                  }}</div>
                <div class="parkingLabel mt-1">In Time</div>
              </v-card></v-col>
            <v-col cols="6"><!-- Out Time -->
              <v-card class="rounded-xl pa-3 mb-3 card-dark text-center">

                <!-- <div class="parkingTime" style="font-size: 15px;;">{{ details?.fee.out_time ? fmt(details.fee.out_time) : '--'
                  }}
                </div> -->

                <div class="parkingTime" style="font-size: 15px;;">{{ details?.fee?.out_time ?
                  $dateFormat.formatTimeAMPM(details.fee.out_time) : '--'
                  }}
                </div>
                <div style="font-size: 12px;">{{ details?.fee?.out_time ?
                  $dateFormat.formatDate(details.fee.out_time) : '--'
                  }}</div>

                <div class="parkingLabel mt-1" style="color:red">Out Time</div>
              </v-card></v-col>

          </v-row>




          <!-- Duration -->
          <v-card class="rounded-xl pa-3 mb-3 card-dark text-center">
            <div class="parkingTime">{{ humanDuration(details.fee.duration_in_minutes) }}</div>
            <div class="parkingLabel mt-1">Total Duration</div>
          </v-card>

          <!-- Total Price -->
          <v-card class="rounded-xl pa-3 mb-3 card-dark text-center" style="border:1px solid green;font-size: 25px;;">
            <div class="parkingTime">{{ currency }} {{ price(details.fee.total_amount) }}</div>
            <div class="parkingLabel mt-1">Total Price ({{ details.fee.duration_in_hours }} X {{
              details.fee.parking_price_per_hour }} rate)</div>



          </v-card>
          <!-- Total Price -->
          <v-card v-if="!paymentPopupDisplay" class="rounded-xl pa-3 mb-3 card-dark text-center"
            style="border:1px solid green;font-size: 25px;;">


            <Creditcard @success="successEvent" v-if="details?.fee.total_amount > 0 && !cardPaymentStatus"
              :displayAmount="details.fee.total_amount" :parkingid="details.vehicle.id" />

            <div v-else style="font-size:16px;color:green">Payment Received. Thank you</div>
          </v-card>



          <!-- Pay -->
          <!-- <v-btn block x-large class="rounded-xl gate-btn" color="success" :loading="loadingPay"
            :disabled="details.paid" @click="payNow">
            {{ details.paid ? 'Paid' : 'Click to Pay' }}
          </v-btn> -->

          <!-- If you also want an Open Gate button after payment, add it below -->
          <!-- <v-btn v-if="details.paid" block large class="rounded-xl mt-2" :disabled="openingGate || bufferSeconds <= 0"
            @click="openGate">
            <v-icon left>mdi-door-open</v-icon>
            Open Gate
          </v-btn> -->

          <v-card v-if="details?.fee.total_amount > 0 && paymentPopupDisplay"
            class="rounded-xl pa-3 mb-3 card-dark text-center" style="border:1px solid green;font-size: 25px;;">

            <div v-if="!dialogParkingReceipt && !details.vehicle.payment_datetime">

              <!-- <v-btn color="primary" @click="startPopupPayment(details.fee.total_amount)">Pay {{
                details.fee.total_amount
                }} AED
                <v-icon>mdi-credit-card</v-icon></v-btn> -->

              <v-btn @click="startPopupPayment(details.fee.total_amount)" block x-large v-if="!dialogParkingReceipt"
                class="rounded-xl gate-btn mt-3 d-flex align-center justify-center" color="success">

                <span>
                  {{ `Pay ${details.fee.total_amount} AED` }}
                </span>

                <v-icon> mdi-credit-card-settings-outline</v-icon>
              </v-btn>

              <v-dialog v-model="popupPaymentloading" width="380">
                <v-card class="pa-4">
                  <div class="subtitle-1 mb-2">Waiting for payment…</div>
                  <div class="caption">Complete the payment in the popup window.</div>
                </v-card>
              </v-dialog>

              <div v-if="popupPaymentSuccessreceipt">
                <h3>Payment Success</h3>

              </div>
              <div v-if="!popupPaymentSuccessreceipt && popupTriggered">
                <div class="pt-2" style="font-size: 15px;color:red;">{{ popupPaymentMessage }}</div>




              </div>
            </div>
            <div class="pt-2" v-else style="font-size:16px;color:green">Payment Received. Thank you</div>

          </v-card>
        </div>
      </v-slide-y-transition>
    </v-container>


    <!-- Info Section -->
    <div class="mt-3 pa-10 pt-4">
      <div style="text-align: center;margin: auto;">
        <img src="/stripe2.png" alt="Stripe"
          style="text-align: center;margin: auto;width: 100%;max-width:400px;; background-color: #fff; border-radius: 8px; padding: 8px;" />

      </div>

      <!-- Section 1 -->
      <div class="font-weight-bold mb-3">Scan and Pay Parking Charges</div>

      <div class="feature">
        <v-icon color="green" left small>mdi-run-fast</v-icon>
        <span>No waiting at the Exit gate.</span>
      </div>
      <div class="feature">
        <v-icon color="green" left small>mdi-timer-outline</v-icon>
        <span>15 minutes valid after payment.</span>
      </div>
      <div class="feature">
        <v-icon color="green" left small>mdi-credit-card-outline</v-icon>
        <span>Simple Online / Credit Card Payment</span>
      </div>
      <div class="feature">
        <v-icon color="green" left small>mdi-email-outline</v-icon>
        <span>Email notification after payment success</span>
      </div>

      <v-divider class="my-4" />

      <!-- Section 2 -->
      <div class="font-weight-bold mb-3">Payment & Security</div>

      <div class="feature">
        <v-icon color="green" left small>mdi-credit-card-multiple-outline</v-icon>
        <span>Accepted: Visa, MasterCard, Apple Pay, Google Pay</span>
      </div>
      <div class="feature">
        <v-icon color="green" left small>mdi-shield-check-outline</v-icon>
        <span>Bank-level encryption. Secure transactions.</span>
      </div>

      <v-divider class="my-4" />

      <!-- Section 3 -->
      <div class="feature">
        <v-icon color="green" left small>mdi-headset</v-icon>
        <span>24/7 Support: WhatsApp / Call / Email</span>
      </div>
    </div>
  </v-container>

</template>

<script>
import mqtt from "mqtt";
import { mqttRequestReply } from '@/utils/mqttRequestReplyClient.js'; // adjust path

import Creditcard from './creditcard.vue';
import ParkingReceipt from './ParkingReceipt.vue';

export default {
  name: 'ParkingLookup',
  components: { Creditcard, ParkingReceipt },
  props: {
    logoSrc: { type: String, default: '/logo.png' },
    title: { type: String, default: 'XtremeParking' },
    currency: { type: String, default: 'AED' },
    bufferMinutes: { type: Number, default: 15 }, // free exit window after payment
  },
  data: () => ({
    popupPaymentMessage: 'Payment is not yet completed....',
    popupTriggered: false,
    popupPaymentloading: false,
    popupRef: null,
    receipt: null,
    displayAmount: 20,
    paymentPopupDisplay: true, //true means Popup payment
    popupPaymentSuccessreceipt: false,
    dialogParkingReceipt: false,
    cardPaymentStatus: false,
    vehicleNo: '',
    loading: false,
    loadingPay: false,
    openingGate: false,
    error: '',
    details: null,       // { vehicleNo, inTime, outTime, totalSeconds, totalPrice, paid, paidAt, paymentLink }
    bufferSeconds: 0,
    bufferTimer: null,
    parkingLogid: null,
    PaymentResponseData: null,
    keyId: 1,
  }),

  mounted() {
    // Storage listener = instant handoff from success window (sets localStorage.payment_sessionid)
    window.addEventListener('storage', this.onStorage, false);
    // if (window)
    //   window.addEventListener('message', this.onMessage, false);
  },
  beforeDestroy() {
    // window.removeEventListener('message', this.onMessage, false);
  },
  methods: {

    onStorage(e) {
      if (e.key === 'payment_sessionid' && e.newValue && e.newValue != '') {
        // Got session id from success page → verify immediately
        this.handlePopupClosed();
      }
    },

    fmt(d) {
      if (!d) return '--';
      const date = d instanceof Date ? d : new Date(d);
      return date.toLocaleString(undefined, {
        year: 'numeric', month: 'short', day: '2-digit',
        hour: '2-digit', minute: '2-digit'
      });
    },
    price(n) {
      const num = Number(n || 0);
      return num.toFixed(2);
    },

    close_popup() {

      this.dialogParkingReceipt = false;
      this.fetchDetails();
    },
    humanDuration(totalSec) {


      return this.$dateFormat.minutesToHHMM(totalSec);

      const s = Math.max(0, Math.floor(totalSec || 0));
      const h = Math.floor(s / 3600);
      const m = Math.floor((s % 3600) / 60);
      const ss = s % 60;
      if (h > 0) return `${h}h ${m}m`;
      if (m > 0) return `${m}m ${ss}s`;
      return `${ss}s`;
    },
    startBufferCountdown(paidAt) {
      if (this.bufferTimer) clearInterval(this.bufferTimer);
      const end = new Date(paidAt).getTime() + this.bufferMinutes * 60 * 1000;
      const tick = () => {
        const now = Date.now();
        this.bufferSeconds = Math.max(0, Math.floor((end - now) / 1000));
        if (this.bufferSeconds <= 0) clearInterval(this.bufferTimer);
      };
      tick();
      this.bufferTimer = setInterval(tick, 1000);
    },

    // --- API hooks to replace ---
    async fetchDetails() {
      this.error = '';
      this.details = null;
      if (!this.vehicleNo) return;

      try {
        this.loading = true;

        // Replace with your real API call:
        // const data = await this.$axios.post('/parking_qr_get_vehicle_details', { vehicle_number: this.vehicleNo });


        const data = await mqttRequestReply({
          companyId: 8,
          action: 'parking_qr_get_vehicle_details',
          payload: { vehicle_number: this.vehicleNo },
          timeoutMs: 8000,
        });




        if (data.action == 'parking_qr_get_vehicle_details') {
          this.cardPaymentStatus = false;

          this.parkingLogid = data.data.vehicle.id;

          if (data.data.vehicle.payment_datetime || !data.data.fee)// vehicle out is captured already - No In found
          {
            this.keyId++;
            this.dialogParkingReceipt = true;
            this.PaymentResponseData = data.data.vehicle;
            this.cardPaymentStatus = true;
          }
          else {
            this.details = data.data;
            this.bufferSeconds = 0;


            if (data.paid && data.paidAt) this.startBufferCountdown(data.paidAt);
          }
        }

      } catch (e) {
        this.error = 'Vehicle Details are not Available. Please try again.';

        console.error(e);

      } finally {
        this.loading = false;
      }
    },
    async successEvent(paymentGatewayResponse) {

      if (this.details) {

        this.PaymentResponseData = null;

        //update parking log table
        // const data = await this.$axios.post('/parking_qr_paymentresponse', { fee: this.details.fee, payment_response: paymentGatewayResponse, parking_id: this.parkingLogid });

        const data = await mqttRequestReply({
          companyId: 8,
          action: 'parking_qr_paymentresponse',
          payload: { fee: this.details.fee, payment_response: paymentGatewayResponse, parking_id: this.parkingLogid },
          timeoutMs: 8000,
        });

        if (data.action == 'parking_qr_paymentresponse') {

          this.keyId++;

          this.dialogParkingReceipt = true;
          this.PaymentResponseData = data.data.record;

          this.cardPaymentStatus = true;

        }

      }
    },


    async startPopupPayment(amount) {

      this.popupTriggered = true;
      this.popupPaymentloading = true;
      try {



        localStorage.removeItem("payment_sessionid");
        // 1) Ask backend for a fresh Payment Link URL
        // const { data } = await this.$axios.get('stripe/create-payment-link', {
        //   params: {
        //     amount: amount * 100,            // 20 AED in fils
        //     product: 'Parking Fee',  // example
        //     successReturnURL: window.location.origin + "/qrcode/popupsucess",  // example

        //   }
        // });

        let data = await mqttRequestReply({
          companyId: 8,
          action: 'stripe/create-payment-link',
          payload: {
            amount: amount * 100,            // 20 AED in fils
            product: 'Parking Fee',  // example
            successReturnURL: window.location.origin + "/qrcode/popupsucess",  // example

          },
          timeoutMs: 8000,
        });
        console.log("stripe/create-payment-link", data);

        if (data.action == 'stripe/create-payment-link') {
          data = data.data;

          // 2) Open popup to Stripe-hosted Payment Link
          const features = 'width=480,height=720,menubar=no,toolbar=no,location=no,status=no';
          this.popupRef = window.open(data.url, 'stripe_checkout', features);


          setTimeout(() => {

            this.handlePopupClosed();

          }, 1000 * 30);

          // Optional: poll if user closed popup without paying
          const timer = setInterval(() => {


            try {
              if (!this.popupRef || this.popupRef.closed) {
                clearInterval(timer);
                this.popupPaymentloading = false; // user closed without finishing
                this.handlePopupClosed();

                this.popupPaymentMessage = "Verifying  Payment Details...Please wait...";

              }


            } catch (e) {
              this.popupPaymentloading = false; // user closed without finishing
              clearInterval(timer);
              this.popupPaymentMessage = "Verifying  Payment Details....Please wait...";

              // Accessing cross-origin props can throw; ignore and keep polling
            }
          }, 500);




        }



      } catch (e) {
        this.popupPaymentloading = false;
        this.$toast?.error?.(e?.response?.data?.message || e.message);
      }
    },
    async handlePopupClosed() {
      let payment_sessionid = localStorage.getItem("payment_sessionid") || null;

      this.popupPaymentMessage = "Verifying  Payment Details....Please wait...";

      if (payment_sessionid) {

        console.log("payment_sessionid", payment_sessionid);





        // const { data } = await this.$axios.get(`/stripe/session/${payment_sessionid}`);

        // // console.log(data.payment_intent.id);

        // //update parking log table
        // const data2 = await this.$axios.post('/parking_qr_paymentresponse', { fee: this.details.fee, payment_response: data.payment_intent, parking_id: this.parkingLogid });
        // // console.log("data", data2);
        console.log({
          companyId: 8,
          action: '/stripe/session',
          payload: { payment_sessionid: payment_sessionid },
          timeoutMs: 8000,
        });


        let data = await mqttRequestReply({
          companyId: 8,
          action: '/stripe/session',
          payload: { payment_sessionid: payment_sessionid },
          timeoutMs: 8000,
        });

        console.log(data.action);


        if (data.action == '/stripe/session') {

          data = data.data;


          const data2 = await mqttRequestReply({
            companyId: 8,
            action: 'parking_qr_paymentresponse',
            payload: { fee: this.details.fee, payment_response: data.payment_intent, parking_id: this.parkingLogid },
            timeoutMs: 8000,
          });
          if (data2.action == 'parking_qr_paymentresponse') {







            this.keyId++;

            this.popupPaymentMessage = "Payment is received. Thank you";
            this.dialogParkingReceipt = true;
            this.PaymentResponseData = data2.data.record;


            this.fetchDetails();

          }
        }
      }
      else {
        this.popupPaymentMessage = "Payment Response is empty. Try again";

        localStorage.removeItem("payment_sessionid");


      }


    }

  }
};
</script>

<style>
.wrap {
  background: #111315;
  min-height: 100vh;
}

.card-dark {
  background: #1d2126 !important;
  border: 1px solid rgba(255, 255, 255, 0.06);
}

.title-text {
  color: #eaeef2;
  font-size: 16px;
  font-weight: 600;
}

.veh-num {
  font-weight: 800;
  font-size: 32px;
  color: #5bd3ff;
  text-shadow: 0 0 10px rgba(91, 211, 255, .55), 0 0 20px rgba(91, 211, 255, .35);
}

.parkingLabel {
  font-size: 12px;
  opacity: .7;
}

.parkingTime {
  font-weight: 700;
  font-size: 16px;
  color: #eaeef2;
}

.gate-btn {
  font-weight: 800;
}

.feature {

  display: flex;
  align-items: center;
  margin-bottom: 10px;
  font-size: 14px;
  color: #FFF;
}
</style>
