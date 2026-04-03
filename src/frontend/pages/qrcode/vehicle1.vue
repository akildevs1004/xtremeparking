<!-- Example Vue 2 component -->
<template>
  <div>
    <v-btn color="primary" @click="startPayment">Pay {{ displayAmount }} AED</v-btn>

    <v-dialog v-model="loading" width="380">
      <v-card class="pa-4">
        <div class="subtitle-1 mb-2">Waiting for payment…</div>
        <div class="caption">Complete the payment in the popup window.</div>
      </v-card>
    </v-dialog>

    <div v-if="receipt">
      <h3>Payment Success</h3>
      <pre>{{ receipt }}</pre> <!-- replace with your pretty receipt UI -->
    </div>
  </div>
</template>

<script>
export default {
  layout: 'qrcodelayout',
  data() {

    return {
      loading: false,
      popupRef: null,
      receipt: null,
      displayAmount: 20,
    };
  },
  mounted() {
    if (window)
      window.addEventListener('message', this.onMessage, false);
  },
  beforeDestroy() {
    window.removeEventListener('message', this.onMessage, false);
  },
  methods: {
    async startPayment() {
      this.loading = true;
      try {
        // 1) Ask backend for a fresh Payment Link URL
        const { data } = await this.$axios.get('stripe/create-payment-link', {
          params: {
            amount: 2000,            // 20 AED in fils
            product: 'Parking Fee',  // example
          }
        });

        // 2) Open popup to Stripe-hosted Payment Link
        const features = 'width=480,height=720,menubar=no,toolbar=no,location=no,status=no';
        this.popupRef = window.open(data.url, 'stripe_checkout', features);

        // Optional: poll if user closed popup without paying
        const timer = setInterval(() => {
          if (this.popupRef && this.popupRef.closed) {
            clearInterval(timer);
            this.loading = false; // user closed popup
          }
        }, 500);
      } catch (e) {
        this.loading = false;
        this.$toast?.error?.(e?.response?.data?.message || e.message);
      }
    },

    async onMessage(event) {
      // Ensure message is from your domain
      const allowedOrigin = window.location.origin;
      if (event.origin !== allowedOrigin) return;
      const { type, sessionId } = event.data || {};
      if (type !== 'STRIPE_CHECKOUT_SUCCESS' || !sessionId) return;

      try {
        // 3) Retrieve full details from your Laravel API
        const { data } = await this.$axios.get(`/api/stripe/session/${sessionId}`);

        // Example: normalize a handy subset for your UI
        const pi = data.payment_intent;
        const charge = pi?.charges?.data?.[0];
        this.receipt = {
          session_id: data.id,
          amount: (pi?.amount_received || pi?.amount || 0) / 100,
          currency: (pi?.currency || 'aed').toUpperCase(),
          status: pi?.status,
          payment_method: charge?.payment_method_details?.type,
          receipt_url: charge?.receipt_url,
          customer_email: data.customer_details?.email,
          line_items: (data.line_items?.data || []).map(li => ({
            name: li.description,
            qty: li.quantity,
            unit_amount: (li.price?.unit_amount || 0) / 100,
          })),
        };

        // Close loading dialog
        this.loading = false;

        // Optionally: navigate, show success toast, etc.
        // this.$router.push({ name: 'receipt', params: { id: data.id } });
      } catch (e) {
        this.loading = false;
        this.$toast?.error?.('Could not load Stripe session: ' + (e?.response?.data?.message || e.message));
      }
    },
  },
};
</script>
