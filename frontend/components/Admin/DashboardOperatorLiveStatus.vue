<template>
  <div>
    <v-row>
      <v-col md="10">
        <h3 class="pl-2" style="font-weight: normal; color: black">
          Operators Live
        </h3>
      </v-col>
      <v-col md="2" class="text-end">
        <!-- <v-menu bottom left>
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
        </v-menu> -->
      </v-col>
    </v-row>

    <v-data-table
      dense
      :headers="headers"
      :items="logs"
      :loading="loading"
      hide-default-header
      :footer-props="{
        itemsPerPage: 8,
        itemsPerPageOptions: [8, 10, 50, 100, 500, 1000],
      }"
    >
      <template v-slot:item.employee.pic="{ item, index }">
        <v-row no-gutters>
          <v-col
            style="
              padding: 5px;
              padding-left: 0px;
              width: 30px;
              max-width: 30px;
            "
          >
            <v-img
              style="
                border-radius: 50%;
                height: auto;
                width: 30px;
                max-width: 30px;
              "
              :src="item.picture ? item.picture : '/no-profile-image.jpg'"
            >
            </v-img>
          </v-col>
        </v-row>
      </template>

      <!-- <template v-slot:item.employee.employee_id="{ item }">
        {{ item.employee && item.employee.employee_id }}
      </template> -->
      <template v-slot:item.employee.first_name="{ item }">
        {{ item.first_name + " " + item.last_name }}

        <div
          calss="secondary-value"
          style="font-size: 10px"
          v-if="parseInt(item.idle_time) <= 5"
        >
          <span
            ><v-icon style="color: green" size="15">mdi mdi-web</v-icon></span
          >
          Online
          <!-- {{
            item.last_active_datetime
              ? $dateFormat.format5(item.last_active_datetime)
              : "---"
          }} -->
        </div>
      </template>
      <template v-slot:item.LogTime="{ item }" style="color: green">
        <v-icon
          :title="item.last_active_datetime || '---'"
          v-if="item.idle_time <= 5"
          color="green"
          fill
          >mdi-monitor-eye</v-icon
        >
        <v-icon
          :title="item.last_active_datetime || '---'"
          v-else-if="item.idle_time <= 10"
          color="yellow"
          fill
          >mdi-monitor-eye</v-icon
        >
        <v-icon
          :title="item.last_active_datetime || '---'"
          v-else
          color="red"
          fill
          >mdi-monitor-eye</v-icon
        >
      </template>
    </v-data-table>
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
      options: { perPage: 8 },
      cumulativeIndex: 1,
      perPage: 8,
      currentPage: 1,
      headers: [
        {
          text: "Pic",
          align: "left",
          sortable: true,
          filterable: true,

          value: "employee.pic",
        },
        {
          text: "Employee Name",
          align: "left",
          sortable: true,
          filterable: true,

          value: "employee.first_name",
        },

        {
          text: "Time",
          align: "left",
          sortable: true,
          filterable: true,

          value: "LogTime", //edit purpose
        },
      ],
    };
  },
  watch: {
    options: {
      handler() {
        this.getRecords();
      },
      deep: true,
    },
  },
  mounted() {
    setInterval(() => {
      if (this.$auth.user) this.getRecords();
    }, 1000 * 45);

    setTimeout(() => {
      if (this.$auth.user) this.getRecords();
    }, 1000 * 8);

    // setTimeout(() => {
    //   this.loading = false; // Stop loading
    //   this.triggerFirstPage(); // Trigger initial actions
    // }, 1000 * 20); // Example delay for loading
  },
  created() {
    this.getRecords();
  },
  computed: {
    employees() {
      return this.$store.state.employeeList.map((e) => ({
        system_user_id: e.system_user_id,
        first_name: e.first_name,
        last_name: e.last_name,
        display_name: e.display_name,
      }));
    },
    devices() {
      if (this.$store.state.devices)
        return this.$store.state.devices.map((e) => e.device_id);
    },
  },
  methods: {
    // triggerFirstPage() {
    //   console.log("First 8 items are displayed"); // Perform any other actions here
    // },
    // onPageChange(page) {
    //   console.log("Page changed to:", page);
    // },
    // onItemsPerPageChange(itemsPerPage) {
    //   console.log("Items per page changed to:", itemsPerPage);
    // },
    viewLogs() {
      this.$router.push("/web_login_logs");
    },
    caps(str) {
      if (str == "" || str == null) {
        return "";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    getRecords() {
      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";
      this.perPage = itemsPerPage;
      this.currentPage = page;

      if (!itemsPerPage) itemsPerPage = 8;

      // if (!itemsPerPage) return false;
      this.loading = true;
      this.$axios
        .get(`operators_live_status`, {
          params: {
            per_page: itemsPerPage,
            page: page,
            sortBy: sortedBy,
            sortDesc: sortedDesc,
            perPage: itemsPerPage,
            pagination: true,
            company_id: this.$auth.user.company_id,
            filter_user_type: this.filter_user_type,
          },
        })
        .then(({ data }) => {
          this.logs = data;
          this.loading = false;
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
