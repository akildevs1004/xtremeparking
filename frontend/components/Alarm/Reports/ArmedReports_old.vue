<template>
  <div>
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="dialogEventsList" max-width="1200px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span style="color: black3333" dense>Alarm Events</span>
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
                :eventFilter="false"
                :showFilter="false"
                :showTabs="false"
                :filter_date="filter_date"
                :filter_customer_id="filter_customer_id"
                :filter_alarm_type="filter_alarm_type"
              /> </v-card-text
          ></v-card>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-card>
      <v-row>
        <v-col cols="12" class="text-right" style="padding-top: 0px">
          <v-row>
            <v-col class="text-left pa-5"><h3>Armed Report</h3></v-col>
            <v-col class="text-right" style="max-width: 750px">
              <v-row>
                <v-col class="mt-3">
                  <v-icon @click="getDataFromApi()">mdi-refresh</v-icon>
                </v-col>
                <v-col style="text-align: right; max-width: 100px">
                  <v-checkbox
                    style="margin-top: 8px"
                    @change="getDataFromApi()"
                    v-model="only_show_alarms"
                    label="Alarms"
                  ></v-checkbox>
                </v-col>
                <v-col style="max-width: 300px">
                  <v-autocomplete
                    clearable
                    style="padding-top: 6px; max-width: 300px"
                    @change="getDataFromApi()"
                    class="reports-events-autocomplete bgwhite"
                    v-model="filter_customer_id"
                    :items="customersList"
                    dense
                    placeholder="All Customers"
                    outlined
                    item-value="id"
                    item-text="building_name"
                  >
                  </v-autocomplete>
                  <!-- <v-text-field
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
                ></v-text-field> -->
                </v-col>
                <v-col style="">
                  <CustomFilter
                    style="
                      float: right;
                      padding-top: 5px;
                      z-index: 9999;
                      padding-right: 15px;
                    "
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
      <v-row style="margin-top: -30px">
        <v-col style="">
          <div class="v-data-table elevation-0 v-data-table--has-bottom">
            <v-data-table
              :headers="headers"
              :items="formattedDataItems"
              class="elevation-0 table-td-padding-10"
              :items-per-page="100"
              :loading="loading"
            >
              <template v-slot:item="{ item }">
                <tr>
                  <td style="font-size: 13px">{{ item.date }}</td>
                  <td>
                    {{ item.customer }}
                    <div class="secondary-value">{{ item.city }}</div>
                  </td>

                  <template v-for="index in 5">
                    <td
                      :title="item.armed[index - 1]?.armed_datetime"
                      style="color: red"
                    >
                      {{
                        $dateFormat.format6(
                          item.armed[index - 1]?.armed_datetime
                        ) || "---"
                      }}

                      <div class="secondary-value">
                        {{
                          $dateFormat.format2(
                            item.armed[index - 1]?.armed_datetime
                          ) || "---"
                        }}
                      </div>
                    </td>
                    <td
                      style="color: green"
                      :title="item.armed[index - 1]?.disarm_datetime"
                    >
                      {{
                        $dateFormat.format6(
                          item.armed[index - 1]?.disarm_datetime
                        ) || "---"
                      }}
                      <div class="secondary-value">
                        {{
                          $dateFormat.format2(
                            item.armed[index - 1]?.disarm_datetime
                          ) || "---"
                        }}
                      </div>
                    </td>
                  </template>
                  <td>{{ getArmedTotalDuration(item.armed) }}</td>
                  <td>{{ getDisarmTotalDuration(item.armed, item.date) }}</td>
                  <td>
                    <!-- <div v-if="item.events_count == 0">0</div>
                    <div
                      v-else
                      @click="showAlarmEvents(item.date, item.customer_id)"
                    >
                      {{ item.events_count || "0" }}
                    </div> -->
                    <!-- {{ item.events_count }} -->
                    <v-menu>
                      <template v-slot:activator="{ on, attrs }">
                        <v-btn
                          style="color: black3333"
                          text
                          dense
                          small
                          dark
                          v-bind="attrs"
                          v-on="on"
                        >
                          {{ item.events_count || 0 }}
                        </v-btn>
                      </template>
                      <v-list>
                        <v-list-item
                          @click="showAlarmEvents(item.date, item.customer_id)"
                          style="
                            min-height: 22px;
                            border-bottom: 1px solid #ddd;
                          "
                        >
                          <v-list-item-title style="font-size: 12px">
                            {{ item.events_count || 0 }} - All
                          </v-list-item-title>
                        </v-list-item>

                        <v-list-item
                          v-for="(alarmType, index) in alarmTypesList"
                          :key="'ArmedReports' + index + 20"
                          style="
                            min-height: 22px;
                            border-bottom: 1px solid #ddd;
                          "
                          @click="
                            showAlarmEvents(
                              item.date,
                              item.customer_id,
                              alarmType.name
                            )
                          "
                        >
                          <v-list-item-title style="font-size: 12px">
                            <div v-if="alarmType.name == 'SOS'">
                              {{ item.SOS_count || 0 }} - SOS
                            </div>
                            <div v-else-if="alarmType.name == 'Intruder'">
                              {{ item.Intruder_count || 0 }} - Intruder
                            </div>
                            <div v-else-if="alarmType.name == 'Offline'">
                              {{ item.Offline_count || 0 }} - Offline
                            </div>
                            <div v-else-if="alarmType.name == 'Tampered'">
                              {{ item.Tampered_count || 0 }} - Offline
                            </div>
                            <div v-else-if="alarmType.name == 'AC Off'">
                              {{ item.AC_off_count || 0 }} - AC Off
                            </div>
                            <div v-else-if="alarmType.name == 'DC Off'">
                              {{ item.DC_off_count || 0 }} - DC Off
                            </div>
                            <div v-else-if="alarmType.name == 'Medical'">
                              {{ item.Medical_count || 0 }} - Medical
                            </div>
                            <div v-else-if="alarmType.name == 'Temperature'">
                              {{ item.Temperature_count || 0 }} - Temperature
                            </div>
                            <div v-else-if="alarmType.name == 'Water'">
                              {{ item.Water_count || 0 }} - Water
                            </div>
                            <div v-else-if="alarmType.name == 'Fire'">
                              {{ item.Fire_count || 0 }} - Fire
                            </div>
                          </v-list-item-title>
                        </v-list-item>
                      </v-list>
                    </v-menu>
                  </td>
                  <!-- <td>
                    <div v-if="item.sos_count == 0">0</div>
                    <div
                      v-else
                      @click="showSOSEvents(item.date, item.customer_id)"
                    >
                      {{ item.sos_count || "0" }}
                    </div>
                  </td> -->
                </tr>
              </template>
            </v-data-table>
          </div>
        </v-col>
      </v-row>
    </v-card>
  </div>
</template>

<script>
import EditAlarmCustomerEventNotes from "../../../components/Alarm/EditCustomerEventNotes.vue";
import AlarmEventNotesListView from "../../../components/Alarm/AlarmEventsNotesList.vue";
import AlamAllEvents from "../../../components/Alarm/ComponentAllEvents.vue";
export default {
  components: {
    EditAlarmCustomerEventNotes,
    AlarmEventNotesListView,
    AlamAllEvents,
  },
  props: ["customer_id"],
  data() {
    return {
      only_show_alarms: false,
      filter_date: "",
      filter_customer_id: "",
      filter_alarm_type: "",
      headers: [
        { text: "Date", value: "date", sortable: false },
        { text: "Customer", value: "customer_id", sortable: false },

        { text: "Armed1", value: "armed1", sortable: false },
        { text: "Disarm1", value: "disarm1", sortable: false },
        { text: "Armed2", value: "armed2", sortable: false },
        { text: "Disarm2", value: "disarm2", sortable: false },
        { text: "Armed3", value: "armed3", sortable: false },
        { text: "Disarm3", value: "disarm3", sortable: false },
        { text: "Armed4", value: "armed4", sortable: false },
        { text: "Disarm4", value: "disarm4", sortable: false },
        { text: "Armed5", value: "armed5", sortable: false },
        { text: "Disarm5", value: "disarm5", sortable: false },
        { text: "Armed (HH:MM)", value: "armed", sortable: false },

        { text: "Disarm (HH:MM)", value: "disarm", sortable: false },

        { text: "Events", value: "events", sortable: false },
      ],
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
      customersList: [],
      formattedDataItems: [],
      filter_customer_id: null,
      alarmTypesList: [],
    };
  },
  watch: {
    // options: {
    //   handler() {
    //     this.getDataFromApi();
    //   },
    //   deep: true,
    // },
  },
  async created() {
    let today = new Date();
    let monthObj = this.$dateFormat.monthStartEnd(today);
    this.date_from = monthObj.first;
    this.date_to = monthObj.last;

    await this.getDataFromApi();
    await this.getCustomersList();

    await this.getAlaramTypesList();
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    async getAlaramTypesList() {
      const { data } = await this.$axios.get("alarm_types", {
        params: {
          company_id: this.$auth.user.company_id,
        },
      });

      this.alarmTypesList = data;
    },
    getDisarmTotalDuration(array, date) {
      const startOfDay = new Date(date);
      startOfDay.setHours(0, 0, 0, 0); // Set to 12:00 AM
      const endOfDay = new Date(startOfDay);
      endOfDay.setHours(23, 59, 59, 999); // Set to 11:59 PM

      let totalDurationInSeconds = 0;

      for (let i = 0; i < array.length; i++) {
        if (array[i]) {
          let armedTime = new Date(array[i].armed_datetime);
          let disarmedTime = new Date(array[i].disarm_datetime);

          // // Adjust the armed and disarmed times to fit within the day's boundaries
          // if (armedTime < startOfDay) armedTime = startOfDay;
          // if (disarmedTime > endOfDay) disarmedTime = endOfDay;

          const durationInSeconds = (disarmedTime - armedTime) / 1000;

          // Only add positive durations within the day's boundaries
          if (durationInSeconds > 0) {
            totalDurationInSeconds += durationInSeconds;
          }
        }
      }

      if (isNaN(totalDurationInSeconds)) {
        return "---";
      }
      totalDurationInSeconds = 24 * 60 * 60 - totalDurationInSeconds;

      let totalHours = Math.floor(totalDurationInSeconds / 3600);
      let totalMinutes = Math.floor((totalDurationInSeconds % 3600) / 60);

      if (totalHours <= 9) totalHours = "0" + totalHours;
      if (totalMinutes <= 9) totalMinutes = "0" + totalMinutes;

      return `${totalHours}:${totalMinutes}`;
    },
    getArmedTotalDuration(array) {
      let totalDurationInSeconds = 0;

      for (let i = 0; i <= array.length; i++) {
        if (array[i]) {
          const armedTime = new Date(array[i].armed_datetime);
          const disarmedTime = new Date(array[i].disarm_datetime);

          const duration = (disarmedTime - armedTime) / 1000;
          totalDurationInSeconds += duration;
        }
      }

      if (isNaN(totalDurationInSeconds)) {
        return "00:00";
      }

      let totalHours = Math.floor(totalDurationInSeconds / 3600);
      let totalMinutes = Math.floor((totalDurationInSeconds % 3600) / 60);
      let remainingSeconds = totalDurationInSeconds % 60;

      if (totalHours <= 9) totalHours = "0" + totalHours;
      if (totalMinutes <= 9) totalMinutes = "0" + totalMinutes;

      return `${totalHours}:${totalMinutes}`;
    },
    async getCustomersList() {
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
    formattedData(jsonData) {
      const result = [];
      jsonData.forEach((day) => {
        if (day.customers.length == 0) {
          result.push({
            date: day.date,
            customer_id: "---",
            customer: "---",
            city: "---",
            armed: "---",
            sos: "---",
            events: [],
          });
        }
        day.customers.forEach((customer) => {
          result.push({
            date: day.date,
            ...customer,
          });
        });
      });
      return result;
    },
    showSOSEvents(date, customer_id) {
      this.key++;

      this.dialogEventsList = true;
      this.filter_date = date;
      this.filter_customer_id = customer_id;
      this.filter_alarm_type = "sos";
    },
    showAlarmEvents(date, customer_id, alarm_type) {
      this.key++;
      this.dialogEventsList = true;
      this.filter_date = date;
      this.filter_customer_id = customer_id;
      this.filter_alarm_type = alarm_type;
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

    async getDataFromApi() {
      this.loading = true;

      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          customer_id: this.customer_id,
          date_from: this.date_from,
          date_to: this.date_to,
          filter_customer_id: this.filter_customer_id,
          only_show_alarms: this.only_show_alarms,
        },
      };

      try {
        this.$axios.get(`device_armed_reports`, options).then(({ data }) => {
          //this.items = data;
          this.formattedDataItems = this.formattedData(data);
          this.totalRowsCount = data.total;
          this.loading = false;
        });
      } catch (e) {}
    },
  },
};
</script>
