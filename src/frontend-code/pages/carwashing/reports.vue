<template>
  <NoAccess v-if="!$pagePermission.can('dashboard_view', this)" />
  <div v-else-if="!isMobileDevice">
    <CarWashingReports />
  </div>

</template>

<script>


import CarWashingReports from "../../components/CarWashing/CarWashingReports.vue";


export default {
  components: {
    CarWashingReports
  },
  data: () => ({
    isMobileDevice: false
  }),


};
</script>
