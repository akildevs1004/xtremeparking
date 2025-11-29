<template>
  <div>
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="dialogEventsList" max-width="80%">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span style="color: black3333" dense>Intruder Events</span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="dialogEventsList = false"
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text style="padding: 20px; padding-left: 0px">
          <v-card class="elevation-2">
            <v-card-text class="mt-5">
              <AlamAllEvents
                name="DeviceArmedLogs"
                style="padding: 0px; padding-top: 0px"
                :key="key"
                :popup="true"
                :compAlarmFilter="1"
                :date_from="date_from"
                :date_to="date_to"
              /> </v-card-text
          ></v-card>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-row>
      <v-col cols="12" class="text-right" style="padding-top: 0px">
        <v-row>
          <v-col></v-col>
          <v-col class="text-right" style="min-width: 500px">
            <v-row>
              <v-col></v-col>
              <v-col class="mt-2" style="max-width: 100px">
                <v-icon @click="getDataFromApi()">mdi-refresh</v-icon>
              </v-col>
              <v-col style="max-width: 250px"
                ><v-text-field
                  style="padding-top: 7px; width: 200px"
                  height="20"
                  class="employee-schedule-search-box"
                  @input="getDataFromApi()"
                  v-model="commonSearch"
                  label="Device Name"
                  dense
                  outlined
                  type="text"
                  append-icon="mdi-magnify"
                  clearable
                  hide-details
                ></v-text-field
              ></v-col>
              <v-col style="max-width: 200px">
                <CustomFilter
                  style="float: right; padding-top: 5px; z-index: 9999"
                  @filter-attr="filterAttr"
                  :default_date_from="date_from"
                  :default_date_to="date_to"
                  :defaultFilterType="1"
                  :height="'40px'"
              /></v-col>
              <!-- <v-col cols="2" style="width: 100px; margin-top: 10px">
                <v-menu bottom right>
                  <template v-slot:activator="{ on, attrs }">
                    <span v-bind="attrs" v-on="on">
                      <v-icon dark-2 icon color="violet" small
                        >mdi-printer-outline</v-icon
                      >
                      Print
                    </span>
                  </template>
                  <v-list width="100" dense>
                    <v-list-item @click="downloadOptions(`print`)">
                      <v-list-item-title style="cursor: pointer">
                        <v-row>
                          <v-col cols="5"
                            ><img
                              style="padding-top: 5px"
                              src="/icons/icon_print.png"
                              class="iconsize"
                          /></v-col>
                          <v-col
                            cols="7"
                            style="padding-left: 0px; padding-top: 19px"
                          >
                            Print
                          </v-col>
                        </v-row>
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="downloadOptions('download')">
                      <v-list-item-title style="cursor: pointer">
                        <v-row>
                          <v-col cols="5"
                            ><img
                              style="padding-top: 5px"
                              src="/icons/icon_pdf.png"
                              class="iconsize"
                          /></v-col>
                          <v-col
                            cols="7"
                            style="padding-left: 0px; padding-top: 19px"
                          >
                            PDF
                          </v-col>
                        </v-row>
                      </v-list-item-title>
                    </v-list-item>

                    <v-list-item @click="downloadOptions('excel')">
                      <v-list-item-title style="cursor: pointer">
                        <v-row>
                          <v-col cols="5"
                            ><img
                              style="padding-top: 5px"
                              src="/icons/icon_excel.png"
                              class="iconsize"
                          /></v-col>
                          <v-col
                            cols="7"
                            style="padding-left: 0px; padding-top: 19px"
                          >
                            EXCEL
                          </v-col>
                        </v-row>
                      </v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </v-col> -->
            </v-row>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-data-table
          :headers="headers"
          :items="items"
          :server-items-length="totalRowsCount"
          :loading="loading"
          :options.sync="options"
          :footer-props="{
            itemsPerPageOptions: [10, 50, 100, 500, 1000],
          }"
          class="elevation-0"
        >
          <template v-slot:item.sno="{ item, index }">
            {{
              currentPage
                ? (currentPage - 1) * perPage +
                  (cumulativeIndex + items.indexOf(item))
                : "-"
            }}
          </template>
          <template v-slot:item.device="{ item }">
            <div>{{ item.device?.customer?.building_name ?? "---" }}</div>
            <div class="secondary-value">
              {{ item.device?.serial_number ?? "---" }}
            </div>
          </template>
          <template v-slot:item.disarm_datetime="{ item }">
            <div>
              {{ $dateFormat.formatDateMonthYear(item.disarm_datetime) }}
            </div>
          </template>
          <template v-slot:item.armed_datetime="{ item }">
            <div>
              {{
                item.armed_datetime != ""
                  ? $dateFormat.formatDateMonthYear(item.armed_datetime)
                  : "---"
              }}
            </div>
          </template>
          <template v-slot:item.duration="{ item }">
            <div>
              {{
                item.duration_in_minutes
                  ? $dateFormat.minutesToHHMM(item.duration_in_minutes)
                  : "---"
              }}
            </div>
          </template>
          <template v-slot:item.alarm_events_count="{ item }">
            <div @click="showEvents(item)">
              {{ item.alarm_events_count || 0 }}
            </div>
          </template>
          <!-- <template v-slot:item.sos="{ item }">
            <div @click="showEvents(item)">
              {{ item.alarm_events_count }}
            </div>
          </template> -->
        </v-data-table>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import AlamAllEvents from "../../components/Alarm/ComponentAllEvents.vue";
export default {
  components: {
    AlamAllEvents,
  },
  props: ["customer_id"],
  data() {
    return {
      date_from: null,
      date_to: null,
      dialogEventsList: false,
      snackbar: false,
      response: "",
      key: "",
      eventId: "",
      dialogAddCustomerNotes: false,
      dialogNotesList: false,
      date_from: "",
      date_to: "",
      loading: false,
      commonSearch: "",
      options: { perPage: 10 },
      cumulativeIndex: 1,
      perPage: 10,
      currentPage: 1,
      totalRowsCount: 0,
      headers: [
        { text: "#", value: "sno", sortable: false },
        { text: "Customer", value: "device", sortable: false },
        { text: "Armed Time", value: "armed_datetime", sortable: false },
        { text: "Disam Time", value: "disarm_datetime", sortable: false },

        { text: "Duration(HH:MM)", value: "duration", sortable: false },
        { text: "Alarms", value: "alarm_events_count", sortable: false },
        // { text: "SOS", value: "sos", sortable: false },
      ],
      items: [],
    };
  },
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  created() {
    let today = new Date();
    let monthObj = this.$dateFormat.monthStartEnd(today);
    this.date_from = monthObj.first;
    this.date_to = monthObj.last;
    //this.getDataFromApi();

    setInterval(() => {
      if (
        this.$route.name == "alarm-view-customer-id" ||
        this.$route.name == "alarm-armedreports"
      ) {
        this.getDataFromApi();
      }
    }, 1000 * 20);
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    showEvents(item) {
      this.dialogEventsList = true;
      this.date_from = item.armed_datetime;
      this.date_to = item.disarm_datetime;
    },
    viewNotes(item) {
      this.key = this.key + 1;
      this.eventId = item.id;
      this.dialogNotesList = true;
    },

    addNotes(item) {
      this.eventId = item.id;
      this.dialogAddCustomerNotes = true;
    },
    closeDialog() {
      this.dialogAddCustomerNotes = false;
      this.getDataFromApi();
      this.$emit("closeDialog");
    },
    filterAttr(data) {
      this.date_from = data.from;
      this.date_to = data.to;

      this.getDataFromApi();
    },
    downloadOptions(option) {
      let filterSensorname = this.tab > 0 ? this.sensorItems[this.tab] : null;

      if (this.eventFilter) {
        filterSensorname = this.eventFilter;
      }

      let url = process.env.BACKEND_URL;
      if (option == "print") url += "/device_armed_logs_print_pdf";
      if (option == "excel") url += "/device_armed_logs_export_excel";
      if (option == "download") url += "/device_armed_logs_download_pdf";
      url += "?company_id=" + this.$auth.user.company_id;
      url += "&date_from=" + this.date_from;
      url += "&date_to=" + this.date_to;
      if (this.commonSearch) url += "&common_search=" + this.commonSearch;

      //  url += "&alarm_status=" + this.filterAlarmStatus;

      window.open(url, "_blank");
    },

    getDataFromApi() {
      this.loading = true;

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";
      this.perPage = itemsPerPage;
      this.currentPage = page;
      if (!page > 0) return false;
      let options = {
        params: {
          page: page,
          //sortBy: sortedBy,
          sortDesc: sortedDesc,
          perPage: itemsPerPage,
          pagination: true,
          company_id: this.$auth.user.company_id,
          customer_id: this.customer_id,
          date_from: this.date_from,
          date_to: this.date_to,
          common_search: this.commonSearch,
          sortBy: "alarm_start_datetime",
        },
      };

      try {
        this.$axios.get(`device_armed_logs`, options).then(({ data }) => {
          this.items = data.data;

          this.totalRowsCount = data.total;
          this.loading = false;
        });
      } catch (e) {}
    },
  },
};
</script>
