<template>
  <div>
    <v-card elevation="1" class="rounded-lg"
      ><v-card-text style="padding-left: 35px; background-color: #516067">
        <v-row>
          <v-col style="padding: 0px; padding: 0px"
            ><div
              style="
                height: 56px;
                width: 105px;
                overflow: hidden;
                border-right: 1px solid #ddd;
              "
            >
              <v-row style="margin: 0px">
                <v-col
                  class="footermarkerstyle"
                  style="
                    /* text-align: center;
                    max-width: 50px;
                    padding-right: 0px;
                    padding-left: 0px;
                    padding-top: 3px;

                    padding-bottom: 0px; */
                  "
                  ><img
                    :src="colorcodes.sos.image + '?7=7'"
                    style="width: 25px"
                /></v-col>

                <v-col
                  style="
                    padding-left: 4px;
                    padding-right: 26px;
                    padding-top: 4px;
                    text-align: center;
                  "
                >
                  <div style="font-size: 20px; line-height: 37px; color: #fff">
                    {{ data.sos }}
                  </div>
                  <div style="font-size: 11px; color: #fff; line-height: 10px">
                    SOS
                  </div></v-col
                >
              </v-row>
            </div>
          </v-col>
          <v-col style="padding: 0px"
            ><div
              style="
                height: 56px;
                width: 105px;
                overflow: hidden;
                border-right: 1px solid #ddd;
              "
            >
              <v-row style="margin: 0px">
                <v-col
                  class="footermarkerstyle"
                  style="
                    /* text-align: center;
                    max-width: 50px;
                    padding-right: 0px;
                    padding-left: 0px;
                    padding-top: 3px;

                    padding-bottom: 0px; */
                  "
                  ><img
                    :src="colorcodes.medical.image + '?7=7'"
                    style="width: 25px"
                /></v-col>

                <v-col
                  style="
                    padding-left: 4px;
                    padding-right: 26px;
                    padding-top: 4px;
                    text-align: center;
                  "
                >
                  <div style="font-size: 20px; line-height: 37px; color: #fff">
                    {{ data.medical }}
                  </div>
                  <div style="font-size: 11px; color: #fff; line-height: 10px">
                    Medical
                  </div></v-col
                >
              </v-row>
            </div>
          </v-col>
          <v-col style="padding: 0px"
            ><div
              style="
                height: 56px;
                width: 105px;
                overflow: hidden;
                border-right: 1px solid #ddd;
              "
            >
              <v-row style="margin: 0px">
                <v-col
                  class="footermarkerstyle"
                  style="
                    /* text-align: center;
                    max-width: 50px;
                    padding-right: 0px;
                    padding-left: 0px;
                    padding-top: 3px;

                    padding-bottom: 0px; */
                  "
                  ><img
                    :src="colorcodes.alarm.image + '?7=7'"
                    style="width: 25px"
                /></v-col>

                <v-col
                  style="
                    padding-left: 4px;
                    padding-right: 26px;
                    padding-top: 4px;
                    text-align: center;
                  "
                >
                  <div style="font-size: 20px; line-height: 37px; color: #fff">
                    {{ data.critical }}
                  </div>
                  <div style="font-size: 11px; color: #fff; line-height: 10px">
                    Critical
                  </div></v-col
                >
              </v-row>
            </div>
          </v-col>
          <v-col style="padding: 0px"
            ><div
              style="
                height: 56px;
                width: 105px;
                overflow: hidden;
                border-right: 1px solid #ddd;
              "
            >
              <v-row style="margin: 0px">
                <v-col
                  class="footermarkerstyle"
                  style="
                    /* text-align: center;
                    max-width: 50px;
                    padding-right: 0px;
                    padding-left: 0px;
                    padding-top: 3px;

                    padding-bottom: 0px; */
                  "
                  ><img
                    :src="colorcodes.offline.image + '?7=7'"
                    style="width: 25px"
                /></v-col>

                <v-col
                  style="
                    padding-left: 4px;
                    padding-right: 26px;
                    padding-top: 4px;
                    text-align: center;
                  "
                >
                  <div style="font-size: 20px; line-height: 37px; color: #fff">
                    {{ data.offline ?? 0 }}
                  </div>
                  <div style="font-size: 11px; color: #fff; line-height: 10px">
                    Technical
                  </div></v-col
                >
              </v-row>
            </div>
          </v-col>

          <v-col style="padding: 0px"
            ><div
              style="
                height: 56px;
                width: 105px;
                overflow: hidden;
                border-right: 1px solid #ddd;
              "
            >
              <v-row style="margin: 0px">
                <v-col
                  class="footermarkerstyle"
                  style="
                    /* text-align: center;
                    max-width: 50px;
                    padding-right: 0px;
                    padding-left: 0px;
                    padding-top: 3px;

                    padding-bottom: 0px; */
                  "
                  ><img
                    :src="colorcodes.armed.image + '?7=7'"
                    style="width: 25px"
                /></v-col>

                <v-col
                  style="
                    padding-left: 4px;
                    padding-right: 26px;
                    padding-top: 4px;
                    text-align: center;
                  "
                >
                  <div style="font-size: 20px; line-height: 37px; color: #fff">
                    {{ data.armed ?? 0 }}
                  </div>
                  <div style="font-size: 11px; color: #fff; line-height: 10px">
                    Armed
                  </div></v-col
                >
              </v-row>
            </div>
          </v-col>
          <v-col style="padding: 0px"
            ><div
              style="
                height: 56px;
                width: 105px;
                overflow: hidden;
                border-right: 1px solid #ddd;
              "
            >
              <v-row style="margin: 0px">
                <v-col
                  class="footermarkerstyle"
                  style="
                    /* text-align: center;
                    max-width: 50px;
                    padding-right: 0px;
                    padding-left: 0px;
                    padding-top: 3px;

                    padding-bottom: 0px; */
                  "
                  ><img
                    :src="colorcodes.disarm.image + '?7=7'"
                    style="width: 25px"
                /></v-col>

                <v-col
                  style="
                    padding-left: 4px;
                    padding-right: 26px;
                    padding-top: 4px;
                    text-align: center;
                  "
                >
                  <div style="font-size: 20px; line-height: 37px; color: #fff">
                    {{ data.disarm ?? 0 }}
                  </div>
                  <div style="font-size: 11px; color: #fff; line-height: 10px">
                    Disarm
                  </div></v-col
                >
              </v-row>
            </div>
          </v-col>

          <v-col style="padding: 0px"
            ><div style="height: 56px; width: 105px; overflow: hidden">
              <v-row style="margin: 0px">
                <v-col
                  class="footermarkerstyle"
                  style="
                    /* text-align: center;
                    max-width: 50px;
                    padding-right: 0px;
                    padding-left: 0px;
                    padding-top: 3px;

                    padding-bottom: 0px; */
                  "
                  ><img
                    :src="colorcodes.ac_off.image + '?7=7'"
                    style="width: 25px"
                /></v-col>

                <v-col
                  style="
                    padding-left: 4px;
                    padding-right: 26px;
                    padding-top: 4px;
                    text-align: center;
                  "
                >
                  <div style="font-size: 20px; line-height: 37px; color: #fff">
                    {{ data.ac_off ?? 0 }}
                  </div>
                  <div style="font-size: 11px; color: #fff; line-height: 10px">
                    Battery
                  </div></v-col
                >
              </v-row>
            </div>
          </v-col>
        </v-row>
      </v-card-text></v-card
    >
  </div>
</template>

<script>
export default {
  props: ["colorcodes"],
  data() {
    return {
      data: {
        cancelgetOperatorStatisticsTokenSource: null,
      },
    };
  },
  mounted() {
    setInterval(() => {
      this.getOperatorStatistics();
    }, 1000 * 10);
  },
  created() {
    this.getOperatorStatistics();
  },
  methods: {
    getOperatorStatistics() {
      if (this.cancelgetOperatorStatisticsTokenSource) {
        this.cancelgetOperatorStatisticsTokenSource.cancel(
          "Operation cancelgetOperatorStatisticsTokenSource canceled due to new request."
        );
      }
      this.cancelgetOperatorStatisticsTokenSource =
        this.$axios.CancelToken.source();
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      try {
        this.$axios
          .get(`alarm_event_operator_statistics`, options)
          .then(({ data }) => {
            this.data = data;
          });
      } catch (e) {}
    },
  },
};
</script>
