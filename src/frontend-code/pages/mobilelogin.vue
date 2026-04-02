<template>
  <div class="mobileBGColor111 bg-body">
    <v-dialog persistent v-model="dialogWhatsapp" width="600px">
      <v-card>
        <v-card-title dense class="white--text" style="background-color: #6946dd; color: #fff !important">
          Whatsapp Verification
          <v-spacer></v-spacer>
          <v-icon @click="dialogWhatsapp = false" outlined dark color="white">
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <div class="row g-0">
            <div class="col-lg-12">
              <div class="login-body p-md-5 mx-md-4">
                <v-row class="pb-5">
                  <v-col md="12" cols="12" class="text-center">
                    <h2>Alarm XtremeGuard</h2>
                  </v-col>
                </v-row>

                <h5>
                  Two Step Whatsapp OTP Verification
                  <v-icon dark color="green" fill>mdi-whatsapp</v-icon>
                </h5>
                <p>
                  We sent a verification code to your mobile number. Enter the
                  Code from the mobile in the filed below
                </p>
                <h2 style="font-size: 30px">
                  {{ maskMobileNumber }}
                </h2>
                <br />
                <!-- <v-form ref="form" method="post" v-model="whatsappFormValid" lazy-validation> -->
                <label for="" class="pb-5" style="font-weight: bold; font-size: 20px">Type your 6 Digit Security
                  Code</label>
                <div class="form-outline mb-4">
                  <v-otp-input v-model="otp" length="6" :rules="requiredRules"></v-otp-input>
                </div>

                <div class="text-center pt-1 mb-5 pb-1">
                  <span v-if="msg" class="error--text">
                    {{ msg }}
                  </span>
                  <v-btn :loading="loading" @click="checkOTP(otp)" class="btn btn-block fa-lg mt-1 mb-3"
                    style="background-color: #6946dd; color: #fff">
                    Verify OTP
                  </v-btn>
                  <!-- <v-btn :loading="loading" @click="checkOTP(otp)"
                      class="btn btn-primary btn-block text-white fa-lg primary mt-1 mb-3 btntext">
                      Verify OTP
                    </v-btn> -->
                </div>

                <div class="d-flex align-items-center justify-content-center pb-4"></div>
                <!-- </v-form> -->
              </div>
            </div>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogForgotPassword" width="400px">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          Forgot Password
          <v-spacer></v-spacer>
          <v-icon @click="dialogForgotPassword = false" outlined dark>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <ForgotPassword></ForgotPassword>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-snackbar v-model="snackbar" top="top" color="error" elevation="24">
      {{ snackbarMessage }}
    </v-snackbar>

    <v-row class="" style="height: 100%">
      <v-col style="padding: 0px; margin: auto" class="parent-login-body">
        <div class="login-body p-md-5 mx-md111111-4" style="
            display: none1;
            padding: 20px !important;
            max-width: 80%;
            width: 300px;
            text-align: center;
            background-color: transparent transparent;
            border: 1px solid #fff;
            border-radius: 10px;
          ">
          <div style="min-height: 100px">
            <div class="text-center" style="width: 100%; padding-top: 20px">
              <v-img class="text-center" style="
                  width: 100px;
                  padding: 0px;
                  margin: auto;
                  text-align: center;
                " src="/login/login-logo.png"></v-img>
            </div>
            <!-- <h3 class="pb-5 pt-0" style="font-size: 14px">
              Welcome To
              <span style="font-size: 14px"> Parking Control Panel </span>
            </h3> -->
          </div>
          <div style="color: #fff; text-align: left" class="pt-8">
            <v-form :style="'padding: 20px;' + bgcolor" ref="form" method="post" v-model="valid" lazy-validation
              autocomplete="off">
              <div class="form-outline">
                <label style="font-size: 12px; padding-bottom: 15px">EMAIL ADDRESS</label>
                <v-text-field class="login-input-box emailtext-field" v-model="credentials.email" hide-details
                  id="form2Example11" autofill="false" required dense outlined type="email" autocomplete="off" clearable
                  color="white" style="background-color: #fff; margin-top: 5px"></v-text-field>
              </div>

              <div class="form-outline pt-5">
                <label style="font-size: 12px" class="pb-2">PASSWORD</label>
                <v-text-field hide-details role="presentation" dense outlined clearable autocomplete="off"
                  :append-icon="show_password ? 'mdi-eye' : 'mdi-eye-off'" :type="show_password ? 'text' : 'password'"
                  v-model="credentials.password" class="input-group--focused login-input-box emailtext-field"
                  @click:append="show_password = !show_password"
                  style="background-color: #fff; margin-top: 5px"></v-text-field>
              </div>

              <div class="text-center pt-1 pt-10">
                <span v-if="msg" class="error--text111" style="color: #ff9f87; font-size: 12px">
                  {{ msg }}
                </span>
                <v-btn text :loading="loading" @click="login()" style="
                    width: 100%;
                    height: 48px;
                    color: #88c1dc;
                    border: 1px solid #fff;
                    font-weight: bold;
                    font-size: 17px;
                  ">
                  Login
                </v-btn>
              </div>
            </v-form>

            <v-row>
              <v-col md="12" class="text-center pt-10">
                <!-- <nuxt-link to="/reset-password"
                                  >Forgot password?</nuxt-link
                                > -->

                <div @click="openForgotPassword" style="color: #88c1dc; font-size: 12px">
                  Forgot password
                </div>
                <!-- <v-btn
                  color="#FFF"
                  text
                  @click="openForgotPassword"
                  style="font-weight: normal"
                  >Forgot password?</v-btn
                > -->
              </v-col>
            </v-row>
          </div>
          <!-- <div class="text-center">Don't Have an Account? Contact Admin</div> -->

          <!-- <v-row class="text-center" style="font-size: 13px">
            <v-col class="pa-3">
              For Technical Support :
              <a
                target="_blank"
                href="https://wa.me/971529048025?text=Hello Alarm XtremeGuard. I need your support."
                ><v-icon color="black">mdi-whatsapp</v-icon></a
              >
              <a
                style="text-decoration: none; color: black"
                href="tel:+971529048025"
                >+971 52 904 8025</a
              ></v-col
            >
          </v-row> -->
          <!-- <v-row class="text-center" style="font-size: 13px">
            <v-col class="pa-2">
              <a
                style="text-decoration: none; color: black"
                href="mailto:support@xtremeguard.org"
                >support@xtremeguard.org</a
              ></v-col
            >
          </v-row> -->
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import ForgotPassword from "../components/ForgotPassword.vue";

export default {
  layout: "login",
  components: { ForgotPassword },
  data: () => ({
    bgcolor: "",
    dialogForgotPassword: false,
    maskMobileNumber: "",
    whatsappFormValid: true,
    logo: "/ideaHRMS-final-blue.svg",
    valid: true,
    loading: false,
    snackbar: false,
    snackbarMessage: "",

    show_password: false,
    msg: "",
    requiredRules: [(v) => !!v || "Required"],
    emailRules: [
      (v) => !!v || "E-mail is required",
      (v) => /.+@.+\..+/.test(v) || "E-mail must be valid",
    ],

    passwordRules: [(v) => !!v || "Password is required"],

    dialogWhatsapp: false,
    otp: "",
    userId: "",
    credentials: {
      email: "",
      password: "",
      source: "admin",
    },
  }),
  created() {
    // Manually clear any other user-related data
    // this.$auth.setUser(false); // Clear user data
    // this.$auth.setToken("local", null); // Clear the token

    this.$store.dispatch("dashboard/resetState");
    this.$store.dispatch("resetState");
  },
  mounted() {
    try {
      const userType = this.$auth.user?.user_type;
      if (userType) {
        if (this.$route.name === "login") {
          window.location.reload();
        }
      }
    } catch (error) { }

    this.$store.dispatch("dashboard/resetState");
    this.$store.dispatch("resetState");

    if (window) {
      if (window.innerWidth < 700) {
        this.bgcolor = "background-color: #797979f0";
      }
    }
  },
  methods: {
    requestPermission() {
      Notification.requestPermission().then((permission) => {
        if (permission === "granted") {
          getToken(messaging, {
            vapidKey:
              "BJuAq3YG9HjVeXbgq_eyM7skgkG5Yunk8UjI9jlkt_DXfuqrfdIWVgqZPk6nBHx-_PYb-D8JQamMRNfn9F2XhH8",
          })
            .then((currentToken) => {
              if (currentToken) {
                console.log("FCM Token:", currentToken);
                alert("FCM Token received. Check the console.");
              } else {
                console.log("No registration token available.");
              }
            })
            .catch((err) => {
              console.log("Error retrieving token: ", err);
            });
        } else {
          alert("Notifications denied!");
        }
      });
    },
    openForgotPassword() {
      this.dialogForgotPassword = true;
    },
    login() {
      this.$store.dispatch("dashboard/resetState");
      this.$store.dispatch("resetState");
      localStorage.clear();

      if (this.$refs.form.validate()) {
        this.$store.commit("email", this.credentials.email);
        this.$store.commit("password", this.credentials.password);

        this.msg = "";
        this.loading = true;
        this.$auth
          .loginWith("local", { data: this.credentials })
          .then((data) => {
            try {
              console.log("user_type", this.$auth.user.user_type);
              let userType = {
                user: "/alarm/dashboard",
                company: "/alarm/dashboard",
                customer: "/customer/dashboard",
                //security: "/security/dashboard",
                security: "/operator/dashboard",
                technician: "/technician/dashboard",
                //operator: "/technician/dashboard",
              };

              this.$router.push(
                userType[this.$auth.user.user_type] || "/master"
              );
            } catch (e) {
              console.log(e);
            }

            //redirect("/alarm/dashboard");
            // setTimeout(() => {
            //   {
            //     if (this.loading == true && this.$route.name == "login") {
            //       window.location.reload();
            //     }
            //   }
            // }, 1000 * 2);
          })
          .catch(({ response }) => {
            console.log("catch response", response);
            console.log("catch response", response.data);

            if (response) {
            } else if (response.data == undefined) {
              this.snackbar = true;
              this.snackbarMessage =
                "Server Unable to connect to Login validation";
            }

            if (!response) {
              return false;
            }
            let { status, data, statusText } = response;
            this.msg = status == 422 ? data.message : statusText;
            setTimeout(() => (this.loading = false), 2000);
            if (statusText == "") {
              this.snackbar = true;
              this.snackbarMessage =
                "Server Unable to connect to Login validation";

              this.msg = this.snackbarMessage;
            }
          });
      } else {
        this.snackbar = true;
        this.snackbarMessage = "Invalid Emaild and Password";
      }
    },
  },
};
</script>
<style>
* {
  margin: 0;
  padding: 0;
}

.emailtext-field .v-input__slot {
  padding: 0 2px !important;
}

/* html {
  background: url("../static/login/bgimage3.png") no-repeat center center fixed;
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
} */

/* #page-wrap {
  width: 400px;
  margin: 50px auto;
  padding: 20px;
  background: white;
  -moz-box-shadow: 0 0 20px black;
  -webkit-box-shadow: 0 0 20px black;
  box-shadow: 0 0 20px black;
}
p {
  font: 15px/2 Georgia, Serif;
  margin: 0 0 30px 0;
  text-indent: 40px;
} */
</style>
<style>
.login-input-box .v-input__slot {
  min-height: 30px !important;
}

.login-input-box .v-label {
  line-height: 11px !important;
}

.login-input-box .v-input__icon {
  height: 17px !important;
}

body,
html {
  height: 100%;
}

.bg-body {
  background-image: url("../static/login/banner202502.png") !important;
}

.bg-body {
  padding-top: 5%;

  height: 100%;

  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
}
</style>

<style scoped>
.about-content {
  padding-left: 30%;
  padding-top: 1%;
  color: #fff;

  padding-right: 15%;
}

.btntext {
  color: #6946dd;
  font-weight: bold;
  font-size: 22px;
}

.login-body {
  margin-right: 5%;
  float: right;

  /* margin-left: 10%;
  background-color: #010d1d; */
}

@media (max-width: 1200px) {
  .login-body {
    margin-right: 10% !important;
  }

  .parent-login-body {
    margin: initial !important;
    margin-top: 3% !important;
  }
}

@media (max-width: 780px) {
  .login-body {
    margin-right: 3% !important;
  }
}

@media (max-width: 450px) {
  .login-body {
    margin: auto !important;
    float: none !important;
  }

  .parent-login-body {
    margin: auto !important;
  }
}

/* .parent-login-body {
  margin: auto;
} */
/*
@media (max-width: 1200px) {
  .hide-on-mobile {
    display: none;
  }
}

@media (min-width: 1300px) {
  .bg-body {
    background-image: url("../static/login/bgimage3.png") !important;
  }
  body {
    background-image: url("../static/login/bgimage3.png") !important;
  }
  .gradient-form {
    height: 100vh !important;
  }

  .bgimage2 {
    background-color: #fff;
    background-image: url("../static/login/bgimage.png");
    background-size: cover;
  }

  .loginForm {
    width: 100%;
    position: absolute;
    top: 10%;
    left: 5%;
  }
}
@media (max-width: 700px) {
  .hide-on-mobile {
    display: none;
  }

  .loginForm {
    width: 100%;
    position: absolute;
    top: 10%;
    left: 0%;
  }
  body {
    width: 100%;
    max-width: 100%;
    background-color: #6946dd;
  }
} */
</style>
