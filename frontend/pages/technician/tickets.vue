<template>
  <div>
    <TicketsList v-if="technician_id" :technician_id="technician_id" />
  </div>
</template>

<script>
import TicketsList from "../../components/Tickets/TechnicianTicketList.vue";

export default {
  layout: "technician",
  components: {
    TicketsList,
  },

  data() {
    return { technician_id: null };
  },
  watch: {},
  created() {
    if (this.$auth.user.technician)
      this.technician_id = this.$auth.user.technician.id;
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
  },
};
</script>
