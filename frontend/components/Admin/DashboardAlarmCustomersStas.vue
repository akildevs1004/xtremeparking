<template>
  <div>
    <div>
      <v-card class="py-2 mb-2">
        <v-row align-self="center">
          <v-col
            style="padding-left: 25px"
            lg="6"
            md="6"
            sm="6"
            xs="6"
            class="laptop-padding"
            align-self="center"
            >Total Customers</v-col
          >

          <v-col
            lg="4"
            md="4"
            sm="4"
            xs="4"
            class="text-red bold text-h3 red--text laptop-padding"
            align-self="center"
            style="margin-left: 5px"
          >
            {{ data ? data.customersCount : 0 }}</v-col
          >
        </v-row>
      </v-card>
      <v-row>
        <v-col md="6" xs="12" sm="12" cols="12">
          <v-card class="py-2">
            <v-row class="pa-2">
              <v-row class="pa-0 ma-0" style="width: 100%">
                <v-col md="6">
                  <img
                    src="/alarm_icons/google_alarm.png"
                    style="width: 25px"
                  />
                  <!--<v-avatar color="#E65100">
                    <v-icon size="25" style="color: #fff"
                      >mdi-cellphone-information</v-icon
                    >
                  </v-avatar> -->
                </v-col>

                <v-col md="6" class="menu-icon-right">
                  <v-menu bottom left>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn dark-2 icon v-bind="attrs" v-on="on">
                        <v-icon>mdi-dots-vertical</v-icon>
                      </v-btn>
                    </template>
                    <v-list width="120" dense>
                      <v-list-item @click="viewReports()">
                        <v-list-item-title style="cursor: pointer">
                          View Reports
                        </v-list-item-title>
                      </v-list-item>
                    </v-list>
                  </v-menu>
                </v-col>
              </v-row>
              <v-row>
                <v-col md="12" style="text-align: center; padding-top: 0px">
                  <div class="bold text-h3 red--text">
                    {{ data ? data.openCount : 0 }}
                  </div>

                  <div style="text-align: center">Open Alarm</div>
                </v-col>
              </v-row>
            </v-row>
          </v-card>
        </v-col>

        <v-col md="6" xs="12" sm="12" cols="12">
          <v-card class="py-2">
            <v-row class="pa-2">
              <v-row class="pa-0 ma-0" style="width: 100%">
                <v-col md="6">
                  <!-- <v-avatar color="black">
                    <v-icon size="25" style="color: #fff"
                      >mdi-card-account-mail-outline</v-icon
                    >
                  </v-avatar> -->
                  <img
                    src="/alarm_icons/google_closed_alarm.png"
                    style="width: 25px"
                  />
                </v-col>

                <v-col md="6" class="menu-icon-right">
                  <v-menu bottom left>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn dark-2 icon v-bind="attrs" v-on="on">
                        <v-icon>mdi-dots-vertical</v-icon>
                      </v-btn>
                    </template>
                    <v-list width="120" dense>
                      <v-list-item @click="viewReports()">
                        <v-list-item-title style="cursor: pointer">
                          View Reports
                        </v-list-item-title>
                      </v-list-item>
                    </v-list>
                  </v-menu>
                </v-col>
              </v-row>
              <v-row>
                <v-col md="12" style="text-align: center; padding-top: 0px">
                  <div class="bold text-h3 black--text">
                    {{ data ? data.closedCount : 0 }}
                  </div>

                  <div style="text-align: center">Closed Alarm</div>
                </v-col>
              </v-row>
            </v-row>
          </v-card>
        </v-col>
      </v-row>
      <v-row>
        <v-col md="6" xs="12" sm="12" cols="12">
          <v-card class="py-2">
            <v-row class="pa-2">
              <v-row class="pa-0 ma-0" style="width: 100%">
                <v-col md="6">
                  <!-- <v-avatar color="green">
                    <v-icon size="25" style="color: #fff"
                      >mdi-account-outline</v-icon
                    >
                  </v-avatar> -->
                  <img
                    src="/alarm_icons/google_armed.png"
                    style="width: 25px"
                  />
                </v-col>

                <v-col md="6" class="menu-icon-right text-end">
                  <v-menu bottom left>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn dark-2 icon v-bind="attrs" v-on="on">
                        <v-icon>mdi-dots-vertical</v-icon>
                      </v-btn>
                    </template>
                    <v-list width="120" dense>
                      <v-list-item @click="viewDevices()">
                        <v-list-item-title style="cursor: pointer">
                          View Devices
                        </v-list-item-title>
                      </v-list-item>
                    </v-list>
                  </v-menu>
                </v-col>
              </v-row>
              <v-row>
                <v-col md="12" style="text-align: center; padding-top: 0px">
                  <div class="bold text-h3 green--text">
                    {{ data ? data.armedCount : 0 }}
                  </div>

                  <div style="text-align: center">Armed</div>
                </v-col>
              </v-row>
            </v-row>
          </v-card>
        </v-col>

        <v-col md="6" xs="12" sm="12" cols="12">
          <v-card class="py-2">
            <v-row class="pa-2">
              <v-row class="pa-0 ma-0" style="width: 100%">
                <v-col md="6">
                  <!-- <v-avatar color="red">
                    <v-icon size="25" style="color: #fff"
                      >mdi-account-minus-outline</v-icon
                    >
                  </v-avatar> -->
                  <img
                    src="/alarm_icons/google_disarm.png"
                    style="width: 25px"
                  />
                </v-col>

                <v-col md="6" class="menu-icon-right text-end">
                  <v-menu bottom left>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn dark-2 icon v-bind="attrs" v-on="on">
                        <v-icon>mdi-dots-vertical</v-icon>
                      </v-btn>
                    </template>
                    <v-list width="120" dense>
                      <v-list-item @click="viewDevices()">
                        <v-list-item-title style="cursor: pointer">
                          View Devices
                        </v-list-item-title>
                      </v-list-item>
                    </v-list>
                  </v-menu>
                </v-col>
              </v-row>
              <v-row>
                <v-col md="12" style="text-align: center; padding-top: 0px">
                  <div class="bold text-h3 red--text">
                    {{ data ? data.disarmCount : 0 }}
                  </div>

                  <div style="text-align: center">Disarm</div>
                </v-col>
              </v-row>
            </v-row>
          </v-card>
        </v-col>
      </v-row>

      <v-row>
        <v-col md="6" xs="12" sm="12" cols="12">
          <v-card class="py-2">
            <v-row class="pa-2">
              <v-row class="pa-0 ma-0" style="width: 100%">
                <v-col md="6">
                  <!-- <v-avatar color="blue">
                    <v-icon size="25" style="color: #fff"
                      >mdi-airplane-takeoff</v-icon
                    >
                  </v-avatar> -->
                  <!-- <v-icon color="blue" size="30" fill>mdi-airplane-takeoff</v-icon> -->
                  <img
                    src="/alarm_icons/google_online.png"
                    style="width: 25px"
                  />
                </v-col>

                <v-col md="6" class="menu-icon-right">
                  <v-menu bottom left>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn dark-2 icon v-bind="attrs" v-on="on">
                        <v-icon>mdi-dots-vertical</v-icon>
                      </v-btn>
                    </template>
                    <v-list width="120" dense>
                      <v-list-item @click="viewDevices()">
                        <v-list-item-title style="cursor: pointer">
                          View Devices
                        </v-list-item-title>
                      </v-list-item>
                    </v-list>
                  </v-menu>
                </v-col>
              </v-row>
              <v-row>
                <v-col md="12" style="text-align: center; padding-top: 0px">
                  <div class="bold text-h3 blue--text">
                    {{ data ? data.onlineCount : 0 }}
                  </div>

                  <div style="text-align: center">Online</div>
                </v-col>
              </v-row>
            </v-row>
          </v-card>
        </v-col>
        <v-col md="6" xs="12" sm="12" cols="12">
          <v-card class="py-2">
            <v-row class="pa-2">
              <v-row class="pa-0 ma-0" style="width: 100%">
                <v-col md="6">
                  <!-- <v-avatar color="yellow">
                    <v-icon size="25" style="color: #fff"
                      >mdi-clock-time-eight-outline</v-icon
                    >
                  </v-avatar> -->
                  <img
                    src="/alarm_icons/google_offline.png"
                    style="width: 25px"
                  />
                </v-col>

                <v-col md="6" class="menu-icon-right">
                  <v-menu bottom left>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn dark-2 icon v-bind="attrs" v-on="on">
                        <v-icon>mdi-dots-vertical</v-icon>
                      </v-btn>
                    </template>
                    <v-list width="120" dense>
                      <v-list-item @click="viewDevices()">
                        <v-list-item-title style="cursor: pointer">
                          View Devices
                        </v-list-item-title>
                      </v-list-item>
                    </v-list>
                  </v-menu>
                </v-col>
              </v-row>
              <v-row>
                <v-col md="12" style="text-align: center; padding-top: 0px">
                  <div class="bold text-h3 orange--text">
                    {{ data ? data.offlineCount : 0 }}
                  </div>

                  <div style="text-align: center">Offline</div>
                </v-col>
              </v-row>
            </v-row>
          </v-card>
        </v-col>
      </v-row>
    </div>
    <ComonPreloader icon="face-scan" v-if="loading" />
  </div>
</template>

<script>
// import VueApexCharts from 'vue-apexcharts'

export default {
  props: ["branch_id"],
  data() {
    return {
      offlineDevices: 0,
      leaveRequests: 0,
      data: null,
      loading: false,
    };
  },
  watch: {},
  mounted() {
    setTimeout(() => {
      this.getDataFromApi();
    }, 1000 * 3);
    if (this.$route.page == "alarm-intruderdashboard") {
      setInterval(() => {
        this.getDataFromApi();
      }, 1000 * 30);
    }
  },
  created() {},

  methods: {
    viewReports() {
      this.$router.push("/alarm/allevents");
    },

    viewDevices() {
      this.$router.push("/alarm/devices");
    },
    getDataFromApi() {
      let today = new Date();
      let date = today.toISOString().split("T")[0];
      this.$axios
        .get("dashboard_statistics_customers", {
          params: {
            company_id: this.$auth.user.company_id,
            date: date,
          },
        })
        .then(({ data }) => {
          this.data = data;
        });
    },
  },
};
</script>

<style>
.menu-icon-right {
  margin: auto;
  text-align: right;
}
</style>
