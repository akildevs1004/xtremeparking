<template>
  <NoAccess v-if="!$pagePermission.can('weblogs_view', this)" />
  <div v-else>
    <v-card class="mb-5 mt-2 rounded-md" elevation="0">
      <v-toolbar class="rounded-md" dense flat>
        <v-toolbar-title><span> Web Logs/Activites</span></v-toolbar-title>
        <v-col
          ><span>
            <v-btn
              dense
              class="ma-0 px-0"
              x-small
              :ripple="false"
              text
              title="Reload"
            >
              <v-icon class="ml-2" @click="getDatafromApi()" dark
                >mdi-reload</v-icon
              >
            </v-btn>
          </span>
        </v-col>
        <v-col style="max-width: 200px">
          <v-text-field
            class="employee-schedule-search-box"
            style="
              padding-top: 35px;
              z-index: 999;
              min-width: 100%;
              width: 150px;
            "
            @keyup="getDatafromApi()"
            height="25px"
            outlined
            v-model="filterText"
            dense
            label="Filter"
          ></v-text-field>
        </v-col>
        <v-col style="max-width: 200px">
          <CustomFilter
            style="float: left; padding-top: 5px; z-index: 999"
            @filter-attr="filterAttr"
            :default_date_from="date_from"
            :default_date_to="date_to"
            :defaultFilterType="1"
            :height="'30px'"
        /></v-col>
      </v-toolbar>
      <v-data-table
        class="pt-5"
        dense
        :headers="headers"
        :items="logs"
        :loading="loading"
        :options.sync="options"
        :footer-props="{
          itemsPerPageOptions: [10, 50, 100],
        }"
        :server-items-length="totalRowsCount"
        :height="tableHeight"
      >
        <template v-slot:item.sno="{ item, index }">
          {{
            currentPage
              ? (currentPage - 1) * perPage +
                (cumulativeIndex + logs.indexOf(item))
              : "-"
          }}
        </template>

        <template v-slot:item.employee.pic="{ item, index }">
          <v-row no-gutters>
            <v-col
              style="
                padding: 5px;
                padding-left: 0px;
                width: 50px;
                max-width: 50px;
              "
            >
              <v-img
                v-if="item.user.user_type == 'company' && item.user.role_id > 1"
                style="
                  border-radius: 50%;
                  height: 45px;
                  min-height: 45px;
                  width: 45px;
                  max-width: 45px;
                "
                :src="
                  item.user.profile_picture
                    ? item.user.profile_picture
                    : '/no-profile-image.jpg'
                "
              >
              </v-img
              ><v-img
                v-else-if="item.user.user_type == 'security'"
                style="
                  border-radius: 50%;
                  height: 45px;
                  min-height: 45px;
                  width: 45px;
                  max-width: 45px;
                "
                :src="
                  item.user.security
                    ? item.user.security.picture
                    : '/no-profile-image.jpg'
                "
              >
              </v-img
              ><v-img
                v-else-if="item.user.user_type == 'technician'"
                style="
                  border-radius: 50%;
                  height: 45px;
                  min-height: 45px;
                  width: 45px;
                  max-width: 45px;
                "
                :src="
                  item.user.technician
                    ? item.user.technician.profile_picture
                    : '/no-profile-image.jpg'
                "
              >
              </v-img
              ><v-img
                v-else-if="
                  item.user.user_type == 'company' && item.user.role_id == 1
                "
                style="
                  border-radius: 50%;
                  height: 45px;
                  min-height: 45px;
                  width: 45px;
                  max-width: 45px;
                "
                :src="
                  item.company.logo
                    ? item.company.logo
                    : '/no-profile-image.jpg'
                "
              >
              </v-img>
            </v-col>
            <v-col style="padding: 10px">
              <div
                v-if="item.user.user_type == 'company' && item.user.role_id > 1"
              >
                {{ item.user.first_name }} {{ item.user.last_name }}
              </div>
              <div
                v-if="
                  item.user.user_type == 'company' && item.user.role_id == 1
                "
              >
                {{ item.user.user_type == "company" ? "Admin" : "" }}
              </div>

              {{
                item.user.security
                  ? item.user.security.first_name +
                    " " +
                    item.user.security.last_name
                  : ""
              }}{{ item.user.customer ? item.user.customer.building_name : "" }}

              <div>{{ item.user.email }}</div>
            </v-col>
          </v-row>
        </template>

        <template v-slot:item.user.role="{ item }">
          <div
            v-if="item.user.user_type == 'company' && item.user.role_id == 1"
          >
            Company
          </div>
          <div v-else>
            {{
              item.user.user_type == "company"
                ? item.user.role.name
                : caps(item.user.user_type)
            }}
          </div>
        </template>

        <template v-slot:item.employee.first_name="{ item, index }">
          <div v-if="item.user.user_type == 'company' && item.user.role_id > 1">
            {{ item.user.first_name }} {{ item.user.last_name }}
          </div>
          <div
            v-else-if="
              item.user.user_type == 'company' && item.user.role_id == 1
            "
          >
            {{ item.user.user_type == "company" ? "Company" : "" }}
          </div>
          <div v-else-if="item.user.security">
            {{
              item.user.security
                ? item.user.security.first_name +
                  " " +
                  item.user.security.last_name
                : ""
            }}
          </div>
          <div v-else-if="item.user.security">
            {{ item.user.customer ? item.user.customer.building_name : " " }}
          </div>
          <div v-else>---</div>
        </template>
        <template v-slot:item.description="{ item }">
          {{ caps(item.description) }}
        </template>
        <template v-slot:item.action="{ item }">
          {{ caps(item.action) }}
        </template>
        <template v-slot:item.customer="{ item }">
          {{ item.customer?.building_name || "---" }}
        </template>
        <template v-slot:item.LogTime="{ item }" style="color: green">
          <v-icon color="green" fill>mdi-clock-outline</v-icon
          >{{ item.date_time }}
        </template>
        <!--  <template v-slot:item.UserID="{ item }"> #{{ item.UserID }} </template>
        <template v-slot:item.employee.employee_id="{ item }">
          {{ item.employee && item.employee.employee_id }}
        </template>
        <template v-slot:item.LogTime="{ item }" style="color: green">
          <v-icon color="green" fill>mdi-clock-outline</v-icon
          >{{ item.date_time }}
        </template>

         <template v-slot:item.online="{ item }">
          <v-icon v-if="item.device.location" color="green" fill
            >mdi-map-marker-radius</v-icon
          >
          <v-icon v-else color="red" fill>mdi-map-marker-radius</v-icon>
        </template>
        <template v-slot:item.device.device_name="{ item }">
          <div :style="item.device.location ? 'color:green' : 'color: red;'">
            {{ item.device ? caps(item.device.name) : "---" }} <br />

            {{ item.device.location ? item.device.location : "---" }}
          </div>
        </template> -->
      </v-data-table>
    </v-card>
  </div>
  <NoAccess v-else />
</template>

<script>
export default {
  data() {
    return {
      tableHeight: 500,

      cumulativeIndex: 1,

      currentPage: 1,
      totalRowsCount: 0,
      cancelTokenSource: null,
      filterText: "",
      date_from: null,
      date_to: null,
      loading: false,
      items: [],
      emptyLogmessage: "",
      number_of_records: 5,
      logs: [],
      url: process.env.SOCKET_ENDPOINT,
      socket: null,
      totalRowsCount: 0,

      total: 0,
      options: {},
      headers: [
        {
          text: "#",
          align: "left",
          sortable: false,
          filterable: true,

          value: "sno",
        },
        {
          text: "User",
          align: "left",
          sortable: false,
          filterable: true,

          value: "employee.pic",
        },

        // {
        //   text: "Type",
        //   align: "left",
        //   sortable: false,
        //   filterable: true,

        //   value: "action",
        // },
        {
          text: "Description",
          align: "left",
          sortable: false,
          filterable: true,

          value: "description",
        },
        {
          text: "Customer",
          align: "left",
          sortable: false,
          filterable: true,

          value: "customer",
        },
        {
          text: "IP",
          align: "left",
          sortable: false,
          filterable: true,

          value: "ipaddress",
        },
        // {
        //   text: "Action",
        //   align: "left",
        //   sortable: false,
        //   filterable: true,

        //   value: "action",
        // },
        // {
        //   text: "Name",
        //   align: "left",
        //   sortable: false,
        //   filterable: true,

        //   value: "employee.first_name",
        // },
        // {
        //   text: "Role",
        //   align: "left",
        //   sortable: false,
        //   filterable: true,

        //   value: "user.role",
        // },

        // {
        //   text: "Page",
        //   align: "left",
        //   sortable: false,
        //   filterable: true,

        //   value: "action",
        // },

        // {
        //   text: "Email",
        //   align: "left",
        //   sortable: false,
        //   filterable: true,

        //   value: "user.email",
        // },

        {
          text: "Time",
          align: "left",
          sortable: false,
          filterable: true,

          value: "LogTime", //edit purpose
        },
      ],
      branch_id: null,
      branchesList: [],
      isCompany: true,
      pageLoadingFirstTime: true,
    };
  },
  watch: {
    options: {
      handler() {
        this.getDatafromApi();
      },
      deep: true,
    },
  },

  async created() {
    if (this.$auth.user.branch_id) {
      this.branch_id = this.$auth.user.branch_id;
      this.isCompany = false;
      return;
    }

    this.tableHeight = window.innerHeight - 200;
    window.addEventListener("resize", () => {
      this.tableHeight = window.innerHeight - 200;
    });

    // let today = new Date();
    // let monthObj = this.$dateFormat.monthStartEnd(today);
    // this.date_from = monthObj.first;
    // this.date_to = monthObj.last;

    // const branch_header = [
    //   {
    //     text: "Branch",
    //     align: "left",
    //     sortable: true,
    //     key: "branch_id",
    //     value: "user.employee.branch.branch_name",
    //     width: "300px",
    //     filterable: true,
    //     filterSpecial: true,
    //   },
    // ];
    // this.headers.splice(1, 0, ...branch_header);

    try {
      const { data } = await this.$axios.get(`branches_list`, {
        params: {
          per_page: 100,
          company_id: this.$auth.user.company_id,
        },
      });
      this.branchesList = data;
    } catch (error) {
      // Handle the error
      console.error("Error fetching branch list", error);
    }
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    caps(str) {
      if (str == "" || str == null) {
        return "";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    filterAttr(data) {
      this.date_from = data.from;
      this.date_to = data.to;

      this.getDatafromApi();
    },
    async getDatafromApi(filter_column = "", filter_value = "") {
      try {
        // Cancel previous request if it exists
        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel(
            "Operation canceled due to new request."
          );
        }

        // Create a new cancel token for this request
        this.cancelTokenSource = this.$axios.CancelToken.source();

        const { sortBy, sortDesc, page = 1, itemsPerPage = 10 } = this.options;

        // if (sortBy && page == 1) return false;
        this.loading = page === 1;

        this.perPage = itemsPerPage;
        this.currentPage = page;

        const params = {
          branch_id: this.branch_id,
          page,
          sortBy: sortBy ? sortBy[0] : "",
          sortDesc: sortDesc ? sortDesc[0] : "",
          per_page: itemsPerPage,
          company_id: this.$auth.user.company_id,
          date_from: this.date_from,
          date_to: this.date_to,
          filter_text: this.filterText,
          page_loading_first_time: this.pageLoadingFirstTime,

          ...this.filters,
          ...(filter_column ? { [filter_column]: filter_value } : {}), // Conditionally add filter column
        };

        // Fetch data from the API
        const { data } = await this.$axios.get(`activity`, {
          params,
          cancelToken: this.cancelTokenSource.token,
        });

        this.pageLoadingFirstTime = false;

        // Process and assign data
        this.totalRowsCount = data.total;
        this.logs = data.data;

        this.totalRowsCount = data.total;
      } catch (error) {
        if (this.$axios.isCancel(error)) {
          console.log("Request canceled", error.message);
        } else {
          console.error(error);
        }
      } finally {
        this.loading = false;
        this.cancelTokenSource = null; // Reset the cancel token
      }
    },
  },
};
</script>
