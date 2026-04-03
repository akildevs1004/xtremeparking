<template>
  <div>
    <AlarmCustomerView
      :isPopup="false"
      ViewCustomer.vue
      v-if="_id"
      :_id="_id"
    />
  </div>
</template>

<script>
import AlarmCustomerView from "../../../components/Alarm/ViewCustomer.vue";

export default {
  components: {
    AlarmCustomerView,
  },
  data: () => ({
    profile_percentage: 60,
    tab: null,
  }),
  computed: {},
  mounted() {},
  created() {
    this._id = this.$route.params.id;
  },
  watch: {},
  methods: {},
};
</script>
