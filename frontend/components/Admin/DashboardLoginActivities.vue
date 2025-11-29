<template>
  <div>
    <!--<v-row>
      <v-col md="10">
        <h3 style="font-weight: normal" class="pl-2">
          Operator Login Activities
        </h3>
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
    </v-row>-->

    <v-data-table
      dense
      :headers="headers"
      :items="logs.data"
      :server-items-length="totalRowsCount"
      :loading="loading"
      :options.sync="options"
      :footer-props="{
        itemsPerPageOptions: [5, 50],
      }"
      hide-default-header
    >
      <template v-slot:item.employee="{ item, index }">
        <v-row no-gutters>
          <v-col style="padding-left: 0px; width: 25px; max-width: 25px">
            <v-img
              style="
                border-radius: 50%;
                height: auto;
                width: 25px;
                max-width: 25px;
                max-height: 25px;
              "
              :src="
                item.user.security
                  ? item.user.security.picture
                  : '/no-profile-image.jpg'
              "
            >
            </v-img>
          </v-col>
        </v-row>
      </template>

      <!-- <template v-slot:item.employee.employee_id="{ item }">
        {{ item.employee && item.employee.employee_id }}
      </template> -->
      <template v-slot:item.employee.first_name="{ item }">
        {{
          item.user.security
            ? item.user.security.first_name + " " + item.user.security.last_name
            : ""
        }}{{ item.user.customer ? item.user.customer.building_name : "" }}
        {{ item.model_type == "company" ? "Admin" : "" }}
      </template>
      <template v-slot:item.email="{ item }">{{ item.user.email }}</template>
      <template v-slot:item.LogTime="{ item }" style="color: green">
        <!-- <v-icon color="green" fill>mdi-clock-outline</v-icon> -->
        {{ $dateFormat.format5(item.date_time) }}
      </template>
      <template v-slot:item.duration="{ item }" style="color: green">
        {{ $dateFormat.getTimeDifferenceOnlyMinutes(item.date_time) }}
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
      options: { perPage: 10 },
      cumulativeIndex: 1,
      perPage: 10,
      currentPage: 1,
      headers: [
        {
          text: "Pic",
          align: "left",
          sortable: true,
          filterable: true,

          value: "employee",
        },
        {
          text: "Employee Name",
          align: "left",
          sortable: true,
          filterable: true,

          value: "employee.first_name",
        },
        // {
        //   text: "Email",
        //   align: "left",
        //   sortable: true,
        //   filterable: true,

        //   value: "email",
        // },
        {
          text: "Time",
          align: "left",
          sortable: true,
          filterable: true,

          value: "LogTime", //edit purpose
        },
        {
          text: "Duration",
          align: "left",
          sortable: true,
          filterable: true,

          value: "duration", //edit purpose
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
  created() {
    //this.getRecords();
  },
  mounted() {
    if (this.$route.page == "alarm-intruderdashboard") {
      setInterval(() => {
        this.getRecords();
      }, 1000 * 60);
    }
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

      // if (!itemsPerPage) return false;
      this.loading = true;
      this.$axios
        .get(`activity`, {
          params: {
            per_page: 5,
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
