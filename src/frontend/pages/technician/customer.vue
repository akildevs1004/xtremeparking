<template>
  <div>
    <TechnicianCustomerTabsView
      v-if="selectedCustomer"
      :key="6"
      :_id="6"
      :selectedCustomer="selectedCustomer"
      :isPopup="true"
      :isEditable="false"
    />
  </div>
</template>

<script>
import TechnicianCustomerTabsView from "../../components/Alarm/TechnicianDashboard/TechnicianCustomerTabsView.vue";

export default {
  layout: "technician",
  components: { TechnicianCustomerTabsView },
  data: () => ({ selectedCustomer: null }),
  computed: {},
  mounted() {},
  created() {
    this.$axios
      .get(`customers`, { params: { customer_id: 6 } })
      .then(({ data }) => {
        this.selectedCustomer = data.data[0];
      });
  },
  watch: {},
  methods: {},
};
</script>
