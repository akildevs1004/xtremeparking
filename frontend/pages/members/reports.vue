<template>
  <NoAccess v-if="!$pagePermission.can('dashboard_view', this)" />
  <div v-else-if="!isMobileDevice">


    <ParkingReports v-if="$auth.user?.member" :isMQTT="true" :memberId="$auth.user.member.id" />
  </div>

</template>

<script>


import ParkingReports from "../../components/Parking/ParkingReports.vue";


export default {

  layout: "member",
  components: {
    ParkingReports
  },
  data: () => ({
    isMobileDevice: false
  }),


};
</script>
