<template></template>
<script>
export default {
  layout: "login",
  async created() {
    try {
      this.$store.dispatch("dashboard/resetState");
      this.$store.dispatch("resetState");
      // Clear the token and user data
      await this.$auth.logout();

      // // Manually clear any other user-related data
      await this.$auth.setUser(false); // Clear user data
      // this.$auth.setToken("local", null); // Clear the token
      //console.log("logout page", this.$auth.user);
      // Redirect to login or another route
      //this.$router.push("/login", true);
      this.$router.push("/login");
    } catch (error) {
      //console.error("Error during logout:", error);
    }
    // this.$axios.get(`/logout`).then(({ res }) => {
    //   this.$auth.logout();
    //   this.$router.push(`/login`);
    // });
    // setTimeout(() => {
    //   try {
    //     if (localStorage) {
    //       localStorage.setItem("auth._token.local", false);
    //       localStorage.setItem("auth._token_expiration.local", false);
    //     }
    //   } catch (e) {}
    //   this.$router.push(`/login`);
    //   try {
    //     windows.location.href = "/login";
    //   } catch (e) {}
    // }, 1000);
  },
};
</script>
