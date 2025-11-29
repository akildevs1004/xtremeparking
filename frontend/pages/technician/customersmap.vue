<template>
  <div>
    <CustomersAlarmMap :displayTable="true" isWithOutAlarms="true" />
  </div>
</template>

<script>
import CustomersAlarmMap from "../../components/Alarm/Map/CustomersAlarmMap.vue";
export default {
  layout: "technician",
  components: { CustomersAlarmMap },
};
</script>
