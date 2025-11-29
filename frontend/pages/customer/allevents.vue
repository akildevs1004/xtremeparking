<template>
  <div>
    <CompAlarmDashboardStatistics :filter_customers_list="[customer_id]" />
    <v-card class="elevation-2">
      <v-card-text class="mt-5">
        <AllEventsDashboard2
          name="AllEvents1"
          showFilters="true"
          showTabs="true"
          :filter_customer_id="customer_id"
        /> </v-card-text
    ></v-card>
  </div>
</template>

<script>
import AllEventsDashboard2 from "../../components/Alarm/ComponentAllEvents.vue";

import CompAlarmDashboardStatistics from "../../components/Alarm/CompAlarmDashboardStatistics.vue";

export default {
  layout: "customer",
  components: {
    AllEventsDashboard2,
    CompAlarmDashboardStatistics,
  },

  data() {
    return {
      customer_id: null,
    };
  },
  watch: {},
  created() {
    this.customer_id = this.$auth.user?.customer?.id || null;
  },

  methods: {},
};
</script>
