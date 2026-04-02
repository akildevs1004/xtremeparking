<template>
  <div></div>
</template>

<script>
export default {
  props: ["colorcodes"],
  data() {
    return {
      currentTime: "",
      currentDate: "",
    };
  },
  mounted() {
    setInterval(() => {
      let nowTime = new Date();
      this.currentTime = nowTime.toLocaleTimeString([], { hour12: false });
    }, 1000 * 1);
    let months = [
      "Jan",
      "Feb",
      "Mar",
      "Apr",
      "May",
      "Jun",
      "Jul",
      "Aug",
      "Sep",
      "Oct",
      "Nov",
      "Dec",
    ];
    const days = [
      "Sunday",
      "Monday",
      "Tuesday",
      "Wednesday",
      "Thursday",
      "Friday",
      "Saturday",
    ];

    let now = new Date();
    let day = now.getDate();
    let monthIndex = now.getMonth();
    let year = now.getFullYear();
    let dayIndex = now.getDay(); // Get the day of the week (0-6)

    this.currentDate = `${day} ${months[
      monthIndex
    ].toUpperCase()} ${year} ${days[dayIndex].toUpperCase()}`;
  },
  created() {},
  methods: {},
};
</script>
