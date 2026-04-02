<template>
  <div v-if="filter_customer_id">
    <v-card class="elevation-2" style="height: 880px">
      <v-row>
        <v-col cols="8">
          <h3 class="ml-2">Alarms</h3>
        </v-col>

        <v-col cols="2" class="align-right">
          <!-- <v-autocomplete
            style="padding-top: 6px"
            @change="updateFilters()"
            class="reports-events-autocomplete"
            v-model="filter_customer_id"
            :items="customersList"
            dense
            placeholder="Select Building/Customer"
            outlined
            item-value="id"
            item-text="building_name"
          >
          </v-autocomplete> -->
        </v-col>
        <v-col cols="2" class="text-right"
          ><CustomFilter
            id="test"
            style="float: right; padding-top: 5px"
            @filter-attr="filterAttr"
            :default_date_from="date_from"
            :default_date_to="date_to"
            :defaultFilterType="1"
            :height="'40px'"
          />
        </v-col>
      </v-row>

      <v-row class="pb-2">
        <v-col cols="9">
          <div style="height: 300px">
            <AlarmEventsChart
              v-if="key > 2"
              style="height: 250px"
              :name="'AlarmEventsChart'"
              :height="'250'"
              :date_from="date_from"
              :date_to="date_to"
              :customer_id="filter_customer_id"
              :key="key"
            />
          </div>
          <v-divider />
          <div style="height: 500px">
            <AlarmEventsResponseChart
              v-if="key > 2"
              style="height: 450px"
              :name="'AlarmEventsResponseChart'"
              :height="'300'"
              :date_from="date_from"
              :date_to="date_to"
              :customer_id="filter_customer_id"
              :key="key"
            />
          </div>
        </v-col>
        <v-col cols="3">
          <div style="height: 200px">
            <AlamCustomerReportsEventsPieChart
              v-if="key > 2"
              :name="'AlamCustomerReportsEventsPieChart'"
              :date_from="date_from"
              :date_to="date_to"
              :customer_id="filter_customer_id"
              :key="key"
            />
          </div>
          <!-- <AlamCustomerReportsCustomerEventsPieChart
            v-if="key > 2"
            :name="'AlamCustomerReportsCustomerEventsPieChart'"
            :date_from="date_from"
            :date_to="date_to"
            :customer_id="filter_customer_id"
            :key="key"
          /> -->
          <div class="mt-5">
            <AlamCustomerResponsePieChart
              v-if="key > 2"
              :name="'AlamCustomerResponsePieChart2'"
              :date_from="date_from"
              :date_to="date_to"
              :customer_id="filter_customer_id"
              :key="key"
            />
          </div>
        </v-col>
      </v-row>

      <v-row class="pt-5">
        <v-col cols="9"> </v-col>

        <v-col cols="3">
          <div>
            <div style="height: 200px"></div>
          </div>
        </v-col>
      </v-row>

      <!-- <v-card class="elevation-2" style="height: 600px">
      <v-row>
        <AlarmEventsChart
          :name="'AlarmEventsChart'"
          :height="'250'"
          customer_id=""
        />
      </v-row>
    </v-card> -->
    </v-card>
  </div>
</template>

<script>
import AlamCustomerResponsePieChart from "../../components/Alarm/Dashboard/AlamCustomerResponsePieChart.vue";
import AlamCustomerReportsEventsPieChart from "../../components/Alarm/Dashboard/AlamCustomerReportsEventsPieChart.vue";
import AlamCustomerReportsCustomerEventsPieChart from "../../components/Alarm/Dashboard/AlamCustomerReportsCustomerEventsPieChart.vue";

import AlarmEventsResponseChart from "../../components/Alarm/AlarmEventsResponseChart.vue";

// import VueApexCharts from 'vue-apexcharts'
export default {
  layout: "customer",
  components: {
    AlamCustomerResponsePieChart,
    AlamCustomerReportsEventsPieChart,
    AlarmEventsResponseChart,
    AlamCustomerReportsCustomerEventsPieChart,
  },
  data() {
    return {
      key: 1,
      display_title: "",
      customersList: [],
      filter_customer_id: "",
      customer_id: "",
      items: [],
      date_from: null,
      date_to: null,
      customer: null,
    };
  },

  async created() {
    this.filter_customer_id = this.customer_id =
      this.$auth.user?.customer?.id || null;
    //await this.getDataFromApi(this.$auth.user?.customer?.id || null);
    let today = new Date();

    let monthObj = this.$dateFormat.monthStartEnd(today);
    this.date_from = monthObj.first;
    this.date_to = monthObj.last;
    this.getCustomersList();
    setTimeout(() => {
      this.updateFilters();
    }, 1000 * 3);

    setInterval(() => {
      if (this.$route.name == "alarm-reports") {
        this.updateFilters();
      }
    }, 1000 * 20);
  },
  methods: {
    async getDataFromApi(customer_id) {
      try {
        this.$axios
          .get(`customers?customer_id=${customer_id}`)
          .then(({ data }) => {
            this.data = data.data[0] || null;
            this.customer = this.data;
          });
      } catch (e) {}
    },

    getCustomersList() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`/customers_list`, options).then(({ data }) => {
        this.customersList = [
          { id: "", building_name: "All Customers" },
          ...data,
        ];
      });
    },
    filterAttr(data) {
      this.date_from = data.from;
      this.date_to = data.to;
      this.updateFilters();
    },
    updateFilters() {
      this.key = this.key + 1;
    },
  },
};
</script>
