<template>
  <parking-exit-screen @gate-opened="onGateOpened" />
</template>

<script>
import ParkingExitScreen from '@/components/qrcode/ParkingExitScreen.vue';

export default {
  auth: false,
  components: { ParkingExitScreen },
  layout: 'qrcodelayout',
  data: () => ({
    // dataFromApi: {
    //   "vehicleNo": "D12345",
    //   "inTime": "2025-09-30T07:05:00+04:00",
    //   "outTime": null,
    //   "totalSeconds": 12120,
    //   "totalPrice": 33.67,
    //   "paid": false,
    //   "paidAt": null,
    //   "paymentLink": null
    // }
  }),
  methods: {
    onGateOpened(v) {
      // toast, log, etc.
      console.log('Gate opened for', v);
    }
  }
};
</script>
