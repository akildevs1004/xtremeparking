<template>
  <div>
    <Componentqrcodeevent
      v-if="alarm_id"
      :alarm_id="alarm_id"
      :key="alarm_id"
    />
    <div v-else>
      <div
        style="
          width: 100%;
          margin: auto;
          height: 600px;
          padding-top: 20%;
          font-weight: bold;
          text-align: center;
        "
      >
        <div>Event Details are Not available.</div>
      </div>
    </div>
  </div>
</template>

<script>
import Componentqrcodeevent from "../components/Operator/QrcodeEventComponent.vue";

export default {
  auth: false,
  layout: "qrcodelayout",
  components: { Componentqrcodeevent },
  data: () => ({
    alarm_id: null,
  }),

  created() {
    this.alarm_id = null; //parseInt("209");
    try {
      let id = parseInt(this.$route.query.eventid) / 9999;

      id = parseInt(id);

      if (id > 0 && id != NaN) this.alarm_id = parseInt(id);
    } catch (e) {}
  },
};
</script>
