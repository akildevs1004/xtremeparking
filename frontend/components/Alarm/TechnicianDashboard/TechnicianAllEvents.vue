<template>
  <div>
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row class="p-0" style="padding-top: 0px">
      <v-col
        cols="12"
        class="text-right"
        style="padding-top: 0px; z-index: 9; padding-right: 0px"
      >
        <v-card elevation="3" class="mt-1">
          <v-card-text>
            <v-row class="mt-0">
              <v-col
                v-if="sensorItems.length >= 1"
                cols="4"
                class="text-left mt-1"
              >
                <h3 style="color: black; font-weight: normal">
                  Technician Alarm Events
                </h3>
              </v-col>

              <v-col
                :cols="sensorItems.length >= 1 ? 8 : 12"
                class="text-right"
                style="width: 600px; padding: 0px"
              >
                <v-row v-if="showFilters || showFilters == 'true'">
                  <v-col cols="7">
                    <v-icon
                      loading="true"
                      @click="getDataFromApi(0)"
                      class="mt-2 mr-2"
                      >mdi-reload</v-icon
                    >

                    <v-text-field
                      style="padding-top: 7px; float: right; width: 300px"
                      height="20"
                      class="employee-schedule-search-box"
                      @input="getDataFromApi(0)"
                      v-model="commonSearch"
                      label="Common Search(All Content)"
                      placeholder="ID,Name,location etc..."
                      dense
                      outlined
                      type="text"
                      append-icon="mdi-magnify"
                      clearable
                      hide-details
                    ></v-text-field
                  ></v-col>
                  <!-- <v-col cols="3"
                    ><v-select
                      class="employee-schedule-search-box"
                      style="
                        padding-top: 7px;
                        z-index: 999;
                        width: 200px;
                        min-width: 100%;
                      "
                      height="20px"
                      outlined
                      @change="getDataFromApi(0)"
                      v-model="filterResponseInMinutes"
                      dense
                      :items="[
                        { id: null, name: 'All Responses' },
                        { id: 1, name: 'Resolved <1 min' },
                        { id: 5, name: 'Resolved 1 to 5 min' },
                        { id: 10, name: 'Resolved 5 to 10 min' },
                        { id: 0, name: 'Resolved > 10 min' },
                      ]"
                      item-text="name"
                      item-value="id"
                    ></v-select>
                  </v-col> -->
                  <v-col cols="2" style="min-width: 100px; padding-right: 0px">
                    <v-select
                      class="employee-schedule-search-box"
                      style="
                        padding-top: 7px;
                        z-index: 999;
                        min-width: 100%;
                        width: 150px;
                      "
                      height="25px"
                      outlined
                      @change="getDataFromApi(0)"
                      v-model="filterAlarmStatus"
                      dense
                      :items="allEventsList"
                      item-text="name"
                      item-value="id"
                    ></v-select>
                  </v-col>
                  <v-col cols="2">
                    <CustomFilter
                      style="float: left; padding-top: 5px; z-index: 999"
                      @filter-attr="filterAttr"
                      :default_date_from="date_from"
                      :default_date_to="date_to"
                      :defaultFilterType="1"
                      :height="'30px'"
                  /></v-col>
                  <!-- <v-col cols="2" style="margin-top: 10px; margin-left: -16px">
                    <v-menu bottom right>
                      <template v-slot:activator="{ on, attrs }">
                        <span v-bind="attrs" v-on="on">
                          <v-icon dark-2 icon color="violet"
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
          </v-card-text>
        </v-card>
        <v-card elevation="3" class="mt-3">
          <v-card-text>
            <v-row v-if="sensorItems.length > 0" style="margin-top: 0px">
              <v-col cols="12" style="margin-top: 0px">
                <v-tabs
                  v-if="sensorItems.length > 1"
                  v-model="tab"
                  background-color="transparent"
                  color="red"
                  right
                  bold
                >
                  <v-tab
                    @click="showTabContent()"
                    v-for="(item, index) in sensorItems"
                    :key="item.id"
                    style="font-weight: bold"
                  >
                    {{ item }}
                  </v-tab>
                </v-tabs>

                <v-tabs-items v-model="tab">
                  <v-tab-item
                    v-for="(item, index) in sensorItems"
                    :key="item.id"
                  >
                    <v-card color="basil" flat>
                      <v-card-text style="padding: 0px">
                        <v-data-table
                          style="height: auto"
                          :name="'table' + index"
                          v-if="showTable"
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
                            {{ item.id }}
                          </template>

                          <template v-slot:item.customer="{ item }">
                            <div>
                              {{
                                item.device?.customer?.building_name ?? "---"
                              }}
                            </div>
                            <div class="secondary-value">
                              {{
                                item.device?.customer?.primary_contact
                                  ?.first_name ?? "---"
                              }}
                              {{
                                item.device?.customer?.primary_contact
                                  ?.last_name ?? "---"
                              }}
                            </div>
                          </template>
                          <template v-slot:item.address="{ item }">
                            <div>{{ item.device?.customer?.area }}</div>
                            <div class="secondary-value">
                              {{ item.device?.customer?.city }}
                            </div>
                          </template>
                          <template v-slot:item.city="{ item }"> </template>

                          <template v-slot:item.sensor="{ item }">
                            <div>
                              {{ item.alarm_type }}
                            </div>
                            <div class="secondary-value">
                              <div class="secondary-value">
                                {{ item.zone_data?.location ?? "---" }}
                              </div>
                            </div>
                          </template>
                          <template v-slot:item.property="{ item }">
                            {{
                              item.device?.customer?.buildingtype?.name ?? "---"
                            }}

                            <!-- <div class="secondary-value">
                          {{ item.device?.customer?.area }}
                        </div> -->
                            <div class="secondary-value">
                              {{ item.device?.customer?.city }}
                            </div>
                          </template>
                          <template v-slot:item.technician="{ item }">
                            {{ item.technician?.first_name ?? "---" }}
                            {{ item.technician?.last_name ?? "---" }}
                          </template>

                          <template v-slot:item.zone="{ item }">
                            <div>{{ item.zone }}</div>
                            <div class="secondary-value">{{ item.area }}</div>
                          </template>
                          <template v-slot:item.alarm_source="{ item }">
                            <div>{{ item.alarm_source ?? "---" }}</div>
                            <div class="secondary-value">
                              {{ item.zone_data?.wired ?? "---" }}
                            </div>
                          </template>
                          <template v-slot:item.zonedata="{ item }">
                            <div>
                              {{ item.zone_data?.sensor_type ?? "---" }}
                            </div>

                            <div class="secondary-value">
                              {{ item.zone_data?.sensor_name ?? "---" }}
                            </div>
                          </template>

                          <template v-slot:item.start_date="{ item }">
                            <div>
                              {{
                                $dateFormat.formatDateMonthYear(
                                  item.alarm_start_datetime
                                )
                              }}
                            </div>
                          </template>
                          <template v-slot:item.end_date="{ item }">
                            <div>
                              {{
                                item.alarm_end_datetime
                                  ? $dateFormat.formatDateMonthYear(
                                      item.alarm_end_datetime
                                    )
                                  : "---"
                              }}
                            </div>
                          </template>
                          <template v-slot:item.duration="{ item }">
                            <div>
                              {{
                                item.alarm_end_datetime
                                  ? $dateFormat.minutesToHHMM(
                                      item.response_minutes
                                    )
                                  : "---"
                              }}
                            </div>
                          </template>
                          <template v-slot:item.notes="{ item }">
                            <div @click="viewNotes(item)">
                              {{ item.notes.length }}
                            </div>
                          </template>

                          <template v-slot:item.alarm_category="{ item }">
                            <div>{{ item.category?.name || "---" }}</div>
                          </template>

                          <template v-slot:item.status="{ item }">
                            <div v-if="item.forwarded === true">Forwarded</div>
                            <div v-else-if="item.alarm_status == 1">
                              Open
                              <!-- <v-icon class="alarm1111111" style="color: red"
                            >mdi mdi-alarm-light</v-icon
                          > -->
                              <!-- <br />
                          <v-btn
                            class="text--red"
                            color="red"
                            title="Click to Stop Alarm "
                            @click="UpdateAlarmStatus(item, 0)"
                            outlined
                            x-small
                            dense
                            >Stop</v-btn
                          > -->
                            </div>
                            <div v-else-if="item.alarm_status == 0">
                              Closed
                              <!-- <v-icon title="Now Alaram is OFF"
                            >mdi mdi-alarm-light-outline</v-icon
                          >
                          <div class="secondary-value">
                            {{
                              item.alarm_end_manually == 1 ? "Manually" : "Auto"
                            }}
                          </div> -->
                            </div>
                          </template>
                          <template v-slot:item.options="{ item }">
                            <v-menu bottom left>
                              <template v-slot:activator="{ on, attrs }">
                                <v-btn dark-2 icon v-bind="attrs" v-on="on">
                                  <v-icon>mdi-dots-vertical</v-icon>
                                </v-btn>
                              </template>
                              <v-list width="120" dense>
                                <v-list-item
                                  v-if="can('branch_view')"
                                  @click="viewAlarminfo(item)"
                                >
                                  <v-list-item-title style="cursor: pointer">
                                    <v-icon color="secondary" small>
                                      mdi-file-tree
                                    </v-icon>
                                    Notes
                                  </v-list-item-title>
                                </v-list-item>
                                <v-list-item
                                  v-if="can('branch_view')"
                                  @click="viewCustomerinfo(item)"
                                >
                                  <v-list-item-title style="cursor: pointer">
                                    <v-icon color="secondary" small>
                                      mdi-eye
                                    </v-icon>
                                    Contacts
                                  </v-list-item-title>
                                </v-list-item>
                                <v-list-item
                                  v-if="can('branch_view')"
                                  @click="eventForward(item)"
                                >
                                  <v-list-item-title style="cursor: pointer">
                                    <v-icon color="secondary" small>
                                      mdi mdi-share-all
                                    </v-icon>
                                    Forward
                                  </v-list-item-title>
                                </v-list-item>
                                <v-list-item
                                  v-if="can('branch_view')"
                                  @click="viewLogs(item)"
                                >
                                  <v-list-item-title style="cursor: pointer">
                                    <v-icon color="secondary" small>
                                      mdi-format-list-numbered
                                    </v-icon>
                                    Operator
                                  </v-list-item-title>
                                </v-list-item>
                              </v-list>
                            </v-menu>
                          </template>
                        </v-data-table>
                      </v-card-text>
                    </v-card>
                  </v-tab-item>
                </v-tabs-items>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-row v-if="sensorItems.length == 0" class="text-center">
      <v-col cols="12" class="text-center"> No Data is available</v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  components: {},
  props: [
    "popup",
    "showFilters",
    "showTabs",
    "eventFilter",
    "filter_customer_id",

    "compFilterAlarmStatus",
    "compFilterSupervisor",
    "filter_date",
    "filter_alarm_type",
  ],
  data() {
    return {
      allEventsList: [],
      selecteAlarm: null,
      dialogViewAlarmFormat: false,
      customer: null,
      dialogViewLogs: false,
      cancelTokenSource: null,
      dialogForwardEventDetails: false,
      dialogCloseAlarm: false,
      filterResponseInMinutes: null,
      dialogTabViewCustomer: false,
      viewCustomerId: null,
      popupEventText: "",
      filterAlarmStatus: null,
      showTable: true,
      requestStatus: false,
      tab: 0,
      sensorItems: [],
      value: "recent",
      customer_id: null,
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
        { text: "Event Id", value: "sno", sortable: false },
        { text: "Ticket Id", value: "ticket_id", sortable: false },
        { text: "Technician", value: "technician", sortable: false },

        // { text: "Building", value: "building", sortable: false },

        { text: "Customer", value: "customer", sortable: false },
        { text: "Property", value: "property", sortable: false },
        // { text: "Address", value: "address", sortable: false },

        // { text: "City", value: "city", sortable: false },

        // { text: "Device", value: "device", sortable: false },
        { text: "Type", value: "sensor", sortable: false },
        { text: "Zone", value: "zonedata", sortable: false },
        { text: "Source", value: "alarm_source", sortable: false },

        // { text: "Zone", value: "zone", sortable: false },
        // { text: "Alarm Type", value: "alarm_type" , sortable: false },
        { text: "Event Time", value: "start_date", sortable: false },
        { text: "Closed time", value: "end_date", sortable: false },
        { text: "Priority", value: "alarm_category", sortable: false },
        // { text: "End Date", value: "end_date" , sortable: false },
        {
          text: "Resolved Time(H:M)",
          value: "duration",
          sortable: false,
          align: "center",
        },
        // { text: "Category", value: "category", sortable: false },

        // { text: "Notes", value: "notes", sortable: false },
        {
          text: "Status",
          value: "status",
          sortable: false,
          align: "center",
        },

        // { text: "Options", value: "options", sortable: false },
      ],
      items: [],
      selectedAlarm: null,
    };
  },
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },

    // tab: {
    //   handler() {
    //     this.showTable = false;
    //     this.getDataFromApi(0);
    //   },
    //   deep: true,
    // },
  },
  created() {
    this.allEventsList = [];

    this.allEventsList = [
      { id: null, name: "All Events" },
      { id: 1, name: "Open" },

      { id: 0, name: "Closed" },
    ];
    // if (!this.compFilterSupervisor)
    {
      //this.allEventsList.push({ id: 3, name: "Forwarded" });
    }
    if (this.compFilterSupervisor) {
      this.filterAlarmStatus = 3;
    }
    // if (this.$route.name != "alarm-dashboard") {
    //   let today = new Date();
    //   let monthObj = this.$dateFormat.monthStartEnd(today);
    //   this.date_from = monthObj.first;
    //   this.date_to = monthObj.last;
    // }
    // if (this.$route.name == "alarm-dashboard") {
    //   this.filterAlarmStatus = 1;
    // }
    if (this.compFilterAlarmStatus) {
      this.filterAlarmStatus = this.compFilterAlarmStatus;
    }
    // setTimeout(() => {
    //   this.getSensorsList();
    // }, 2000);

    if (this.showTabs) {
      setTimeout(() => {
        if (this.sensorItems.length == 0) {
          this.$axios
            .get(`sensor_types`, {
              params: {
                company_id: this.$auth.user.company_id,
              },
            })
            .then(({ data }) => {
              this.sensorItems = ["All", ...data];
            });
        }
      }, 2000);
    } else {
      this.sensorItems = ["All"];
    }
    this.getDataFromApi(0);
    setTimeout(() => {
      setInterval(() => {
        if (
          this.$route.name == "alarm-dashboard" &&
          this.filterAlarmStatus == 1
        )
          this.getDataFromApi(0);
      }, 1000 * 20 * 1);
    }, 1000 * 30);
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    alarmNotesPrint(alarmId, option) {
      //let option = "print";

      let url = process.env.BACKEND_URL;
      if (option == "print") url += "/alarm_notes_print_pdf";
      if (option == "excel") url += "/alarm_notes_download_pdf";
      if (option == "download") url += "/alarm_notes_download_pdf";
      url += "?company_id=" + this.$auth.user.company_id;
      url += "&alarm_id=" + alarmId;

      window.open(url, "_blank");
    },
    showTabContent() {
      this.showTable = false;

      this.getDataFromApi(0);
    },
    closeCustomerDialog() {
      this.dialogTabViewCustomer = false;
    },
    viewAlarminfo(alarm) {
      this.key++;
      this.selecteAlarm = alarm;
      this.dialogViewAlarmFormat = true;
    },
    viewCustomerinfo(item) {
      if (item.device) {
        this.popupEventText =
          "#" +
          item.id +
          " -    " +
          item.alarm_type +
          " ,  " +
          "   Time " +
          item.alarm_start_datetime +
          " -  Priority " +
          item.category.name;
        this.key += 1;
        this.viewCustomerId = item.customer_id;
        this.eventId = item.id;
        this.selectedAlarm = item;
        this.dialogTabViewCustomer = true;
      }
    },

    eventForward(item) {
      this.popupEventText =
        "#" +
        item.id +
        " -    " +
        item.alarm_type +
        " ,  " +
        "   Time " +
        item.alarm_start_datetime +
        " -  Priority " +
        item.category.name;
      this.key += 1;
      this.viewCustomerId = item.customer_id;
      this.eventId = item.id;
      this.customer = item.device.customer;
      this.dialogForwardEventDetails = true;
    },
    viewLogs(item) {
      this.popupEventText =
        "#" +
        item.id +
        " -    " +
        item.alarm_type +
        " ,  " +
        "   Time " +
        item.alarm_start_datetime +
        " -  Priority " +
        item.category.name;
      this.key += 1;
      this.viewCustomerId = item.customer_id;
      this.eventId = item.id;
      this.customer = item.device.customer;
      this.dialogViewLogs = true;
    },
    viewNotes(item) {
      this.key += 1;
      this.eventId = item.id;
      this.customer_id = item.customer_id;
      this.dialogNotesList = true;
    },
    getSensorsList() {
      if (this.$store.state.storeAlarmControlPanel?.SensorTypes) {
        // this.sensorItems = this.$store.state.storeAlarmControlPanel.SensorTypes;
        this.sensorItems = [
          "All",
          ...this.$store.state.storeAlarmControlPanel.SensorTypes,
        ];
      }

      if (this.eventFilter) {
        this.sensorItems = [this.eventFilter];
      }
    },
    addNotes(item) {
      this.eventId = item.id;
      this.dialogAddCustomerNotes = true;
    },
    closeDialog() {
      this.dialogAddCustomerNotes = false;
      this.dialogCloseAlarm = false;
      this.getDataFromApi(0);
      this.$emit("closeDialog");
    },
    filterAttr(data) {
      this.date_from = data.from;
      this.date_to = data.to;

      this.getDataFromApi(0);
    },
    UpdateAlarmStatus(item, status) {
      if (status == 0) {
        if (confirm("Are you sure you want to TURN OFF the Alarm")) {
          this.customer_id = item.customer_id;
          this.eventId = item.id;
          this.dialogCloseAlarm = true;
        }
      }
    },
    deleteEvent(id) {
      if (confirm("Are you sure want to delete Alarm Event notes?")) {
        this.loading = true;
        let options = {
          params: {
            company_id: this.$auth.user.company_id,
            id: id,
          },
        };

        try {
          this.$axios.delete(`delete-event`, options).then(({ data }) => {
            this.snackbar = true;
            this.response = "Event Note Deleted Successfully";
            this.getDataFromApi();
            this.loading = false;
          });
        } catch (e) {}
      }
    },

    downloadOptions(option) {
      let filterSensorname = this.tab > 0 ? this.sensorItems[this.tab] : null;

      if (this.eventFilter) {
        filterSensorname = this.eventFilter;
      }

      let url = process.env.BACKEND_URL;
      if (option == "print") url += "/alarm_events_print_pdf";
      if (option == "excel") url += "/alarm_events_export_excel";
      if (option == "download") url += "/alarm_events_download_pdf";
      //if (option == "download") url += "/alarm_events_download_pdf";

      url += "?company_id=" + this.$auth.user.company_id;
      url += "&date_from=" + this.date_from;
      url += "&date_to=" + this.date_to;
      if (this.commonSearch) url += "&common_search=" + this.commonSearch;
      if (this.filterAlarmStatus)
        url += "&alarm_status=" + this.filterAlarmStatus;
      if (filterSensorname != "null" && filterSensorname)
        url += "&filterSensorname=" + filterSensorname;
      if (this.filterResponseInMinutes)
        url += "&filterResponseInMinutes=" + this.filterResponseInMinutes;
      url += "&tab=" + this.tab;
      //  url += "&alarm_status=" + this.filterAlarmStatus;
      if (this.$auth.user.user_type == "security") {
        let customersList = this.$auth.user.security.customers_assigned.map(
          (e) => e.customer_id
        );
        customersList.forEach((element) => {
          url += "&filter_customers_list[]=" + element;
        });
      }
      window.open(url, "_blank");
    },
    async getDataFromApi(custompage = 1) {
      // Prevent request if loading or no search criteria
      if (this.loading && this.commonSearch == null) return false;

      // Reset pagination if custompage is 0
      if (custompage == 0) this.options = { perPage: 10, page: 1 };

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;
      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";

      this.perPage = itemsPerPage;
      this.currentPage = page;

      // Prevent invalid page request
      if (!(page > 0)) return false;

      this.loading = true;

      // Prepare filter data
      let filterSensorname = this.tab > 0 ? this.sensorItems[this.tab] : null;
      if (this.eventFilter) {
        filterSensorname = this.eventFilter;
      }

      // Cancel previous request if it exists
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel("Operation canceled due to new request.");
      }

      // Create a new cancel token for this request
      this.cancelTokenSource = this.$axios.CancelToken.source();

      let options = {
        params: {
          page: page,
          perPage: itemsPerPage,
          pagination: true,
          company_id: this.$auth.user.company_id,
          date_from: this.date_from,
          date_to: this.date_to,
          common_search: this.commonSearch,
          customer_id: this.filter_customer_id,
          tab: this.tab,
          alarm_status: this.filterAlarmStatus,
          filterSupervisor: this.compFilterSupervisor,

          filterSensorname: filterSensorname,
          filterResponseInMinutes: this.filterResponseInMinutes,
          sortBy: "alarm_start_datetime",
          sortDesc: "DESC",

          filter_date: this.filter_date,
          filter_alarm_type: this.filter_alarm_type,
        },
        cancelToken: this.cancelTokenSource.token, // Assign the cancel token
      };

      try {
        const { data } = await this.$axios.get(
          `get_alarm_events_technician`,
          options
        );

        // Process the response
        this.items = data.data;
        this.totalRowsCount = data.total;
        this.showTable = true;
        this.loading = false;
      } catch (error) {
        if (this.$axios.isCancel(error)) {
          console.log("Request canceled:", error.message);
        } else {
          console.error("Error fetching data:", error);
          this.loading = false;
        }
      }
    },
  },
};
</script>
