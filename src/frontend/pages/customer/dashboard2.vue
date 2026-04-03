<template>
  <div v-if="customer && customer.id">
    <v-card class="mb-1">
      <v-card-text>
        <AlarmCustomerDashboard
          :customer_id="customer.id"
          :customer="customer"
        />
      </v-card-text>
    </v-card>

    <AlarmDevices :customer_id="customer.id" />
  </div>
</template>

<script>
export default {
  layout: "customer",
  data() {
    return {
      customer: null,
    };
  },

  async created() {
    await this.getDataFromApi(this.$auth.user?.customer?.id || null);
  },
  methods: {
    async getDataFromApi(customer_id) {
      try {
        this.$axios
          .get(`customers?customer_id=${customer_id}`)
          .then(({ data }) => {
            this.data = data.data[0] || null;
            this.customer = this.data;
          });
      } catch (e) {}
    },
  },
};
</script>
