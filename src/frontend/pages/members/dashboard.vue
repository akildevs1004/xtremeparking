<template>
  <div>
    <MemberVehiclesList v-if="$auth.user?.member" :memberId="$auth.user.member.id" :isMQTT="true" />
  </div>
</template>

<script>

import MemberVehiclesList from "../../components/Parking/MemberVehiclesList.vue";

export default {
  layout: "member",
  components: { MemberVehiclesList },
};
</script>
