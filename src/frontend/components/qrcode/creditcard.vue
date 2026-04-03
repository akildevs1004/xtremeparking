<template>
  <div class="checkout">
    <!-- <h2>Pay {{ displayAmount }} {{ currency }}</h2> -->

    <div id="card-element" style="border:1px solid #ddd;padding:12px;border-radius:6px;"></div>
    <p v-if="cardErrors" style="color:#ef4444;margin-top:8px;">{{ cardErrors }}




    </p>

    <!-- <button :disabled="loading || !cardMounted" @click="pay" style="margin-top:12px;">
      {{ loading ? 'Processing…' : `Pay ${displayAmount} ${currency}` }}
    </button> -->

    <v-btn block x-large class="rounded-xl gate-btn mt-3 d-flex align-center justify-center" color="success"
      :disabled="loading || !cardMounted" @click="pay">

      <span>
        {{ loading ? 'Processing…' : `Pay ${displayAmount} ${currency}` }}
      </span>


    </v-btn>
    <p v-if="status" style="margin-top:10px;">{{ status }}</p>
  </div>
</template>

<script>
export default {
  props: ["displayAmount", "parkingid"],

  data() {
    return {
      amountMinor: null,      // e.g., 10000 (fils)
      currency: 'AED',

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

    const data = await this.$axios.post('/stripe/create-payment-intent', { amount: this.displayAmount, parking_log_id: this.parkingid });

    this.publishableKey = data.data.publishableKey;
    this.clientSecret = data.data.clientSecret;
    this.amountMinor = data.data.amount;
    this.currency = data.data.currency;


    this.stripe = window.Stripe(this.publishableKey);
    this.elements = this.stripe.elements();

    this.card = this.elements.create('card', {
      style: {
        base: {
          color: '#ffffff',          // input text
          iconColor: '#ffffff',
          fontSize: '16px',
          '::placeholder': { color: '#9ca3af' }
        },
        invalid: {
          color: '#ef4444',
          iconColor: '#ef4444',
          fontSize: '15px',
        }
      }, hidePostalCode: true
    });

    this.card.mount('#card-element');

    // const cardStyle = {
    //   base: { fontSize: '16px', '::placeholder': { color: '#a0aec0' } },
    //   invalid: { color: '#e53e3e' }
    // };
    // this.card = this.elements.create('card', { style: cardStyle });
    //this.card.mount('#card-element');
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

        this.$emit("success", paymentIntent);
        this.status = 'Payment successful! Thank you.';
        // TODO: optionally call your backend to mark the order as paid
      } else {
        this.status = `Payment status: ${paymentIntent?.status || 'unknown'}`;
      }
    }
  }
};
</script>

<style>
.cc-field {
  background: #000;
  border: 1px solid #2a2a2a;
  padding: 12px;
  border-radius: 6px;
}

.CardField-number-fakeNumber-number {
  color: #FFF !important;
}

.ElementsApp input {
  color: #FFF !important;

}

.CardField-number-fakeNumber-last4 InputElement is-complete {
  color: #FFF !important;
}

.ElementsApp .InputElement::placeholder {
  color: #FFF !important;

}

.StripeElement .StripeElement--invalid {
  font-size: 16px;
  ;
}
</style>
