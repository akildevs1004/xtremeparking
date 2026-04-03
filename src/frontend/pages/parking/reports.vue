<template>
  <NoAccess v-if="!$pagePermission.can('dashboard_view', this)" />
  <div v-else-if="!isMobileDevice">
    <ParkingReports />
  </div>

</template>

<script>


import ParkingReports from "../../components/Parking/ParkingReports.vue";


export default {
  components: {
    ParkingReports
  },
  data: () => ({
    isMobileDevice: false
  }),


};
</script>
