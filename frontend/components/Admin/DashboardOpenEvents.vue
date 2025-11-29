<template>
  <div>
    <!-- <v-row>
      <v-col md="10">
        <h4 class="pl-2">Operator Login Activities</h4>
      </v-col>
      <v-col md="2" class="text-end">
        <v-menu bottom left>
          <template v-slot:activator="{ on, attrs }">
            <v-btn dark-2 icon v-bind="attrs" v-on="on">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list width="120" dense>
            <v-list-item @click="viewLogs()">
              <v-list-item-title style="cursor: pointer">
                View Logs
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </v-col>
    </v-row> -->
    <table style="width: 100%" class="simpletable mt-0 font-color">
      <tr v-for="event in logs">
        <!-- <td style="width: 30px">#{{ event.id }}</td> -->
        <td style="width: 50px">
          <img
            :src="event.customer?.profile_picture || '/no-business_profile.png'"
            style="
              width: 100%;
              border-radius: 5px;
              width: 40px;
              margin: auto;
              vertical-align: middle;
            "
          />
        </td>
        <td class="font-color">{{ event.customer.building_name }}</td>
        <td style="width: 40px">
          <img
            :title="event.alarm_type"
            :src="
              '/notification_icons/' + event.notificationicon?.image ||
              '/no-business_profile.png'
            "
            style="
              width: 100%;

              width: 20px;
              margin: auto;
              vertical-align: middle;
            "
          />
        </td>

        <td style="width: 60px">
          <div
            style="width: 60px; color: #fff; height: 25px; color: #ff0000"
            v-if="event.alarm_status == 1"
            label
          >
            OPEN
          </div>
          <div
            style="width: 60px; color: #fff; height: 25px; color: #0046ff"
            v-else-if="event.forwarded == true"
            label
          >
            FWD
          </div>

          <div style="width: 60px; color: #fff; height: 25px" v-else label>
            Closed
          </div>
        </td>
        <td class="font-color">
          {{
            $dateFormat.getTimeDifferenceOnlyMinutes(event.alarm_start_datetime)
          }}
          <div style="font-size: 10px">
            {{ $dateFormat.format5(event.alarm_start_datetime) }}
          </div>
        </td>
      </tr>
    </table>
  </div>
</template>

<script>
export default {
  props: ["filter_user_type"],
  data() {
    return {
      loading: false,
      items: [],
      emptyLogmessage: "",
      number_of_records: 5,
      logs: [],
      url: process.env.SOCKET_ENDPOINT,
      socket: null,
      totalRowsCount: 0,

      total: 0,
      options: { perPage: 10 },
      cumulativeIndex: 1,
      perPage: 10,
      currentPage: 1,
    };
  },

  created() {
    this.getRecords();
  },
  mounted() {
    if (this.$route.page == "alarm-dashboard") {
      setInterval(() => {
        this.getRecords();
      }, 1000 * 30);
    }
  },
  computed: {},
  methods: {
    getRecords() {
      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";
      this.perPage = itemsPerPage;
      this.currentPage = page;

      // if (!itemsPerPage) return false;
      this.loading = true;
      this.$axios
        .get(`dashboard_get_open_alarms`, {
          params: {
            per_page: 5,
            page: page,
            sortBy: sortedBy,
            sortDesc: sortedDesc,
            perPage: itemsPerPage,
            pagination: true,
            company_id: this.$auth.user.company_id,
            filter_customers_list: this.$auth.user.customer?.id
              ? [this.$auth.user.customer.id]
              : null,
          },
        })
        .then(({ data }) => {
          this.logs = data;
          this.loading = false;
          this.totalRowsCount = data.total;
        });
    },
  },
};
</script>

<style>
.v-application--is-ltr .v-data-footer__pagination {
  margin: 0px;
}
</style>
