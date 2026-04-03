<!-- src/views/StripePopupSuccess.vue -->
<template>
  <div class="pa-6">
    <h2 class="mb-2">Completing your payment…</h2>
    <p v-if="!done">You can close this window after we confirm.</p>
    <div v-else>
      <p>Payment Received. Popup window will close automatically......</p>
      <!-- <p v-if="receiptUrl">
        <a :href="receiptUrl" target="_blank" rel="noopener">View Stripe receipt</a>
      </p> -->
    </div>
  </div>
</template>

<script>
export default {
  layout: 'qrcodelayout',
  name: 'StripePopupSuccess',
  auth: false,
  data() {
    return {
      done: false,
      receiptUrl: null, // optional (if you want to fetch and show it)
    };
  },
  async mounted() {
    const params = new URLSearchParams(window.location.search);
    const sessionId = params.get('session_id');

    // Optional: if you added a one-time token to protect against random hits, validate it here.

    if (sessionId) {
      // const payload = { type: 'STRIPE_CHECKOUT_SUCCESS', sessionId };
      this.done = true;

      localStorage.setItem("payment_sessionid", sessionId);

      setTimeout(() => {
        try { if (window.opener && !window.opener.closed) window.close(); } catch (_) { }

      }, 1000 * 3);


    } else {

    }
  },
};
</script>
