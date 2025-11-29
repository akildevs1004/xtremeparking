const data = async ({ $auth, redirect }) => {
  let userType = {
    // user: "/alarm/dashboard",
    company: "/parking/dashboard",
    // customer: "/customer/dashboard",
    // //security: "/security/dashboard",
    // security: "/operator/dashboard",
    member: "/members/dashboard",
    //operator: "/technician/dashboard",
    master: "/master",
  };

  if (userType == "company") {
    try {
      if (typeof window !== "undefined" && window.innerWidth < 700) {
        // console.log("window.innerWidth", window.innerWidth);
        //this.$router.push("/alarm/mobiledashboard");
        return redirect("/alarm/mobiledashboard");
        return false;
      }
    } catch (e) {}
    // return redirect("/customer/dashboard"); // Fallback in case of null values
  }

  if ($auth.user.user_type === "master" || $auth.user.is_master === true) {
    return redirect("/master");
  }

  if (!$auth.user || !$auth.user.user_type) {
    return redirect("/alarm/dashboard"); // Fallback in case of null values
  }

  // if ($auth.user.user_type === "master" || $auth.user.is_master === true) {
  //   return redirect("/master");
  // }
  //console.log("$auth.user.user_type", $auth.user.user_type);

  redirect(userType[$auth.user.user_type] || "/alarm/dashboard");
};
export default data;
