<template>
  <div>
    <p>Token expires in: {{ timeLeft }} seconds</p>
  </div>
</template>

<script>
import jwtDecode from "jwt-decode";
import { mapState } from "vuex";

export default {
  data() {
    return {
      timeLeft: null,
    };
  },
  computed: {
    ...mapState({
      token: (state) => state.auth.token, // adjust according to how you store the token
    }),
  },
  mounted() {
    this.calculateTimeLeft();
    this.interval = setInterval(this.calculateTimeLeft, 1000);
  },
  beforeDestroy() {
    clearInterval(this.interval);
  },
  methods: {
    calculateTimeLeft() {
      if (this.token) {
        const decoded = jwtDecode(this.token);
        const expirationTime = decoded.exp * 1000; // convert to milliseconds
        const currentTime = Date.now();
        this.timeLeft = Math.floor((expirationTime - currentTime) / 1000);
        if (this.timeLeft <= 0) {
          this.timeLeft = "Expired";
          clearInterval(this.interval);
        }
      } else {
        this.timeLeft = "No token";
      }
    },
  },
};
</script>
