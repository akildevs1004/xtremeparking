<template>
  <div class="checkout">
    <h2>Pay {{ displayAmount }} {{ currency }}</h2>

    <div id="card-element" style="border:1px solid #ddd;padding:12px;border-radius:6px;"></div>
    <p v-if="cardErrors" style="color:#ef4444;margin-top:8px;">{{ cardErrors }}</p>

    <button :disabled="loading || !cardMounted" @click="pay" style="margin-top:12px;">
      {{ loading ? 'Processing…' : `Pay ${displayAmount} ${currency}` }}
    </button>

    <p v-if="status" style="margin-top:10px;">{{ status }}</p>
  </div>
</template>

<script>
export default {
  data() {
    return {
      amountMinor: null,      // e.g., 10000 (fils)
      currency: 'AED',
      displayAmount: '100',   // show “100” to user
      publishableKey: null,
      clientSecret: null,
      stripe: null,
      elements: null,
      card: null,
      cardMounted: false,
      cardErrors: '',
      loading: false,
      status: ''
    };
  },
  async mounted() {
    // hit your Laravel API to create the PaymentIntent
    // const res = await fetch('http://127.0.0.1:8000/api/stripe/create-payment-intent', {
    //   method: 'POST',
    //   headers: { 'Content-Type': 'application/json' },
    //   // body: JSON.stringify({}) // add order info if you need
    // });
    // const data = await res.json();

    const data = await this.$axios.post('/stripe/create-payment-intent', {});

    this.publishableKey = data.data.publishableKey;
    this.clientSecret = data.data.clientSecret;
    this.amountMinor = data.data.amount;
    this.currency = data.data.currency;
    this.displayAmount = (this.amountMinor / 100).toFixed(0); // 10000 → “100”

    this.stripe = window.Stripe(this.publishableKey);
    this.elements = this.stripe.elements();

    const cardStyle = {
      base: { fontSize: '16px', '::placeholder': { color: '#a0aec0' } },
      invalid: { color: '#e53e3e' }
    };
    this.card = this.elements.create('card', { style: cardStyle });
    this.card.mount('#card-element');
    this.cardMounted = true;

    this.card.on('change', (event) => {
      this.cardErrors = event.error ? event.error.message : '';
    });
  },
  methods: {
    async pay() {
      if (!this.clientSecret) return;

      this.loading = true;
      this.status = '';
      this.cardErrors = '';

      const { error, paymentIntent } = await this.stripe.confirmCardPayment(
        this.clientSecret,
        {
          payment_method: {
            card: this.card
          }
        }
      );

      this.loading = false;

      if (error) {
        this.cardErrors = error.message || 'Payment failed. Please try again.';
        return;
      }
      if (paymentIntent && paymentIntent.status === 'succeeded') {
        this.status = 'Payment successful! Thank you.';
        // TODO: optionally call your backend to mark the order as paid
      } else {
        this.status = `Payment status: ${paymentIntent?.status || 'unknown'}`;
      }
    }
  }
};
</script>
