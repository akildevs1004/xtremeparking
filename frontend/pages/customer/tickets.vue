<template>
  <div>
    <TicketsList :customer_id="customer_id" />
  </div>
</template>

<script>
import TicketsList from "../../components/Tickets/TicketsList.vue";

export default {
  layout: "customer",
  components: {
    TicketsList,
  },

  data() {
    return { customer_id: null };
  },
  watch: {},
  created() {
    this.customer_id = this.$auth.user.customer.id;
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
  },
};
</script>
