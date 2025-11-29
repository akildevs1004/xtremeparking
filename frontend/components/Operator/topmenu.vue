<template>
  <div>
    <v-dialog
      v-model="dialogAlarmPopupNotificationStatus"
      transition="dialog-top-transition"
      max-width="1000"
      style="z-index: 9999"
      key="key"
    >
      <template v-slot:default="dialog">
        <v-card style="z-index: 9999">
          <v-card-title
            dense
            class="error popup_background_red"
            style="
              text-align: center !important;
              padding-left: 30%;
              color: #fff !important;
              background-color: red;
            "
          >
            <div style="text-align: right; width: 60%">
              Attention : Alarm Notification(s)
            </div>
            <v-spacer></v-spacer>
            <v-icon
              style="color: #fff"
              @click="wait5MinutesNextNotification()"
              outlined
            >
              mdi mdi-close-circle
            </v-icon>
          </v-card-title>

          <v-card-text style="padding-left: 0px">
            <AlarmPopupAllAlarmEvents
              :items="notificationAlarmDevicesContent"
              @callwait5MinutesNextNotification="wait5MinutesNotification"
              @callReset5Minutes="Reset5Minutes"
              :key="popupKey"
              :alarm_icons="alarm_icons"
            />
          </v-card-text>
        </v-card>
      </template>
    </v-dialog>

    <v-row
      align="center"
      justify="center"
      style="background-color: #516067; color: #fff; height: 70px"
      ><v-col
        class="text-left"
        style="
          margin: auto;
          padding-left: 5px;
          font-size: 12px;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        "
      >
        <v-icon color="white">mdi-account-tie</v-icon> {{ displayName }}
      </v-col>
      <v-col style="max-width: 40px">
        <v-icon v-if="displayFullScreenButton()" color="red" @click="openWindow"
          >mdi-view-dashboard</v-icon
        >

        <v-icon
          v-if="!displayFullScreenButton()"
          color="white"
          @click="refreshEventsList()"
          >mdi-refresh</v-icon
        >
      </v-col>
      <!-- <v-col
        class="text-right  "
        style="max-width: 85px; padding-left: 0px"
      >
        <v-icon
          v-if="displayFullScreenButton()"
          class="mr-2"
          color="red"
          @click="openWindow"
          >mdi-overscan</v-icon
        >

        <v-icon v-if="!displayFullScreenButton()" @click="refreshEventsList()"
          >mdi-refresh</v-icon
        >
      </v-col> -->
      <v-col style="max-width: 55px">
        <v-menu
          nudge-bottom="50"
          transition="scale-transition"
          origin="center center"
          bottom
          left
          min-width="200"
        >
          <template v-slot:activator="{ on, attrs }">
            <v-btn icon color="red" v-bind="attrs" v-on="on">
              <v-avatar size="35" style="border: 1px solid #6946dd">
                <v-img class="company_logo" :src="getLogo()"></v-img>
              </v-avatar>
            </v-btn>
          </template>

          <v-list light nav dense>
            <v-list-item-group color="primary">
              <v-list-item>
                <v-list-item-icon>
                  <v-icon>mdi-account</v-icon>
                </v-list-item-icon>
                <v-list-item-content>
                  <v-list-item-title class="black--text">{{
                    displayName
                  }}</v-list-item-title>
                </v-list-item-content>
              </v-list-item>
              <v-list-item @click="gotoDashboard()">
                <v-list-item-icon>
                  <v-icon>mdi-view-dashboard</v-icon>
                </v-list-item-icon>
                <v-list-item-content>
                  <v-list-item-title class="black--text"
                    >Dashboard</v-list-item-title
                  >
                </v-list-item-content>
              </v-list-item>
              <v-list-item @click="logout">
                <v-list-item-icon>
                  <v-icon>mdi-logout</v-icon>
                </v-list-item-icon>
                <v-list-item-content>
                  <v-list-item-title class="black--text"
                    >Logout</v-list-item-title
                  >
                </v-list-item-content>
              </v-list-item>
            </v-list-item-group>
          </v-list>
        </v-menu>
      </v-col>
    </v-row>

    <v-row
      v-if="$route?.name != 'operator-eventslist'"
      align="center"
      justify="center"
      style="background-color: #516067; color: #fff; border-top: 1px solid #fff"
    >
      <v-col style="padding: 6px"> <Clock></Clock></v-col>
    </v-row>
  </div>
</template>

<script>
import Clock from "../../components/Operator/Clock.vue";

export default {
  components: { Clock },
  data() {
    return {
      globalsearch: "",
      displayName: "",
      popupKey: 1,
      key: 1,
      snackbar: false,
      response: "",
      alarm_icons: {},
      dialogAlarmPopupNotificationStatus: false,
      wait5Minutes: false,
      pendingNotificationsCount: 0,
      notificationsMenuItems: [],
      notificationAlarmDevicesContent: {},
    };
  },
  watch: {
    globalsearch() {
      this.$emit("applyGlobalSearch", this.globalsearch);
    },
  },
  mounted() {
    // if (!this.$auth.user) {
    //   this.$router.push("/logout");
    //   return;
    // }

    setTimeout(() => {
      if (
        this.$route.name != "login" &&
        this.$route.name != "operator-eventslist"
      )
        this.loadHeaderNotificationMenu();
      //this.verifyPopupAlarmStatus();
    }, 1000 * 5);

    setInterval(() => {
      //if (!this.$route.name.includes("alarm")) return false;
      //this.loadHeaderNotificationMenu();

      //console.log("wait5Minutes", this.wait5Minutes);
      //if (this.wait5Minutes == false)
      if (
        this.$route.name != "login" &&
        this.$route.name != "operator-eventslist"
      ) {
        this.resetTimer();
        this.loadHeaderNotificationMenu();

        // if (!this.wait5Minutes) {
        //   const notificationContent = this.notificationAlarmDevicesContent;

        //   if (notificationContent && notificationContent.length > 0) {
        //     let criticalList = notificationContent.filter(
        //       (notification) => notification.alarm_category == 1
        //     );
        //     if (criticalList.length > 0 || 1 == 1) {
        //       if (!this.dialogAlarmPopupNotificationStatus) {
        //         this.popupKey += 1;
        //         if (this.$route.name == "operator-dashboard")
        //           this.dialogAlarmPopupNotificationStatus = true;
        //       }
        //     } else {
        //       //this.dialogAlarmPopupNotificationStatus = false;
        //     }
        //   } else {
        //     this.dialogAlarmPopupNotificationStatus = false;
        //   }
        // }
      }
    }, 1000 * 6 * 1);
  },
  created() {
    this.displayName =
      this.$auth.user.security.first_name +
      " " +
      this.$auth.user.security.last_name;
    this.loadAlarmNotificationIcons();
  },
  methods: {
    refreshEventsList() {
      this.$emit("refreshEventsList");
    },
    gotoDashboard() {
      this.$router.push("/operator/dashboard");
    },
    displayFullScreenButton() {
      return this.$route.name == "operator-dashboard" ?? false;
    },
    openWindow() {
      const width = window.screen.width;
      const height = window.screen.height;
      window.open(
        process.env.APP_URL + "/operator/eventslist",
        "_blank",
        `width=${width},height=${height},toolbar=yes, location=yes,resizable=yes,scrollbars=yes`
      );
    },
    resetTimer() {
      // Time in milliseconds after which the user is considered inactive
      const INACTIVITY_TIME = 1000 * 60 * 5; //30 minutes
      clearTimeout(this.inactivityTimeout);
      this.inactivityTimeout = setTimeout(
        this.handleInactivity,
        INACTIVITY_TIME
      );
    },
    async logout() {
      this.activeAudio = false;
      try {
        await this.$axios.get(`/logout`);
        await this.$auth.logout();
        window.location.href = "../login";
      } catch (error) {
        console.error("Logout failed:", error);
      }
    },
    getLogo() {
      const { user } = this.$auth;

      if (!user) {
        return "/no-image.PNG";
      }

      const defaultLogo = "/no-image.PNG";
      const profilePicture = "/no-profile-image.jpg";

      switch (user.user_type) {
        case "company":
          return user.company?.logo || defaultLogo;
        case "security":
          return user.security?.profile_picture || profilePicture;
        case "master":
          return defaultLogo;
        case "employee":
          return user.employee?.profile_picture || profilePicture;
        case "branch":
          return user.branch_logo || profilePicture;
        default:
          return defaultLogo;
      }
    },
    loadHeaderNotificationMenu() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          alarm_status: this.filterAlarmStatus,
          pageSource: "topmenu",
        },
        cancelToken: new this.$axios.CancelToken((cancel) => {
          this.cancelRequest = cancel; // Store the cancel function
        }),
      };

      this.$axios
        .get(`get_alarm_notification_display`, options)
        .then(({ data }) => {
          this.isBackendRequestOpen = false;
          this.notificationsMenuItems = [];
          this.pendingNotificationsCount = 0;
          this.notificationAlarmDevicesContent = data;
          this.key += 1;

          data.forEach((element) => {
            let notification = {
              title: element.device?.customer?.building_name
                ? element.device.customer.building_name +
                  " - " +
                  element.alarm_type
                : "---",
              date_from: element?.alarm_start_datetime,
              click: "/alarm/allevents",
              icon:
                this.alarm_icons[element.alarm_type] ?? element.alarm_type.png,
              key: "leaves",
            };

            this.notificationsMenuItems.push(notification);
          });

          this.pendingNotificationsCount = data.length;
        })
        .catch((error) => {
          if (this.$axios.isCancel(error)) {
            console.log("Previous request canceled");
          } else {
            console.error("Error loading notifications:", error);
          }
          this.isBackendRequestOpen = false;
        });
    },

    showPopupAlarmStatus() {
      this.wait5Minutes = false;
      if (!this.dialogAlarmPopupNotificationStatus) {
        this.popupKey += 1;
        this.dialogAlarmPopupNotificationStatus = true;
      }

      // this.verifyPopupAlarmStatus();
    },
    loadAlarmNotificationIcons() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`alarm_notification_icons`, options).then(({ data }) => {
        this.alarm_icons = data;
      });
    },
    wait5MinutesNextNotification() {
      this.snackbar = true;
      this.response = "New Alarm will be Display after 5 minutes";
      // alert("New Alarm will be Display after 5 minutes");
      this.wait5Minutes = true;
      setTimeout(() => {
        this.wait5Minutes = false;
      }, 1000 * 60 * 5);

      this.dialogAlarmPopupNotificationStatus = false;
    },
    Reset5Minutes() {
      this.wait5Minutes = false;
    },
    wait5MinutesNotification() {
      this.wait5Minutes = true;
      setTimeout(() => {
        this.wait5Minutes = false;
      }, 1000 * 60 * 60);
    },
  },
};
</script>
