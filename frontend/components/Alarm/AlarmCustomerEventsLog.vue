<template>
  <div>
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <!-- <v-dialog v-model="dialogViewLogs" width="80%">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span style="color: black111">Operator Logs #{{ eventId }}</span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="
              closeDialog();
              dialogViewLogs = false;
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text style="padding: 0px">
          <v-container style="min-height: 100px; padding-left: 0px">
            <SecurityAlarmNotes
              v-if="customer"
              :alarmId="eventId"
              :customer="customer"
              :key="key"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogForwardEventDetails" width="800px">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span style="color: black3333"
            >Alarm - Forward Details #{{ eventId }}</span
          >
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="
              closeDialog();
              dialogForwardEventDetails = false;
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text>
          <v-container style="min-height: 100px">
            <AlarmForwardEvent
              name="AlramCloseNotes"
              :key="key"
              :customer_id="customer_id"
              @closeDialog="closeDialog"
              :alarm_id="eventId"
              :popupEventText="popupEventText"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogCloseAlarm" width="600px">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span style="color: black111">Alarm Event Close/Turn off </span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="
              closeDialog();
              dialogCloseAlarm = false;
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text>
          <v-container style="min-height: 100px">
            <AlramCloseNotes
              name="AlramCloseNotes"
              :key="key"
              :customer_id="customer_id"
              @closeDialog="closeDialog"
              :alarmId="eventId"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogAddCustomerNotes" width="600px">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span style="color: black111">Alarm Notes </span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="
              closeDialog();
              dialogAddCustomerNotes = false;
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text>
          <v-container style="min-height: 100px">
            <EditAlarmCustomerEventNotes
              name="EditAlarmCustomerEventNotes"
              :key="key"
              :customer_id="customer_id"
              @closeDialog="closeDialog"
              :alarmId="eventId"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogNotesList" width="900px">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span style="color: black111">Alarm Notes List</span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="
              closeDialog();
              dialogNotesList = false;
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text>
          <v-container style="min-height: 100px">
            <AlarmEventNotesListView
              name="AlarmEventNotesListView"
              :key="key"
              :customer_id="customer_id"
              @closeDialog="closeDialog"
              :alarm_id="eventId"
              showOptions="false"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogTabViewCustomer" width="80%">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span style="color: black111">Alarm : {{ popupEventText }}</span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="
              closeDialog();
              dialogTabViewCustomer = false;
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text style="padding: 0px; overflow: hidden">
          <AlarmEventCustomerContactsTabView
            @closeCustomerDialog="closeCustomerDialog()"
            :key="key"
            :_customerID="viewCustomerId"
            :alarmId="eventId"
            :customer="customer"
            :isPopup="true"
          />
        </v-card-text>
      </v-card>
    </v-dialog> -->
    <v-row style="padding-top: 0px">
      <v-col cols="12" class="text-right" style="padding-top: 15px; z-index: 9">
        <v-row class="mt-0" v-if="!eventFilter">
          <!-- <v-col v-if="sensorItems.length > 1" class="text-left mt-1">
            <h3>Customer Alarm Events</h3></v-col
          > -->

          <v-col class="text-right mt-1">
            <v-row>
              <v-col></v-col>
              <v-col style="max-width: 100px">
                <v-icon
                  loading="true"
                  @click="getDataFromApi(0)"
                  class="mt-2 mr-2"
                  >mdi-reload</v-icon
                >
              </v-col>
              <v-col style="max-width: 200px">
                <v-text-field
                  style="padding-top: 7px; float: right"
                  height="20"
                  class="employee-schedule-search-box"
                  @input="getDataFromApi(0)"
                  v-model="commonSearch"
                  label="Common Search "
                  placeholder="ID,Name  etc..."
                  dense
                  outlined
                  type="text"
                  append-icon="mdi-magnify"
                  clearable
                  hide-details
                ></v-text-field>
              </v-col>
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
              <v-col style="padding-right: 0px; max-width: 200px">
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
                  :items="[
                    { id: null, name: 'All Events' },
                    { id: 1, name: 'Open' },
                    { id: 0, name: 'Closed' },
                  ]"
                  item-text="name"
                  item-value="id"
                ></v-select>
              </v-col>
              <v-col style="max-width: 230px">
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

        <v-row v-if="sensorItems.length > 0">
          <v-col cols="12" style="padding: 0px; margin-top: -30px">
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
              <v-tab-item v-for="(item, index) in sensorItems" :key="item.id">
                <v-card color="basil" flat>
                  <v-card-text style="padding: 0px">
                    <v-data-table
                      style="font-size: 12px"
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
                      class="elevation-0 operatorcustomerTop1"
                    >
                      <template v-slot:item.sno="{ item, index }">
                        {{ item.id }}
                      </template>

                      <template v-slot:item.customer="{ item }">
                        <div>
                          {{
                            item.device?.customer?.primary_contact
                              ?.first_name ?? "---"
                          }}
                          {{
                            item.device?.customer?.primary_contact?.last_name ??
                            "---"
                          }}
                        </div>
                        <div class="secondary-value">
                          {{ item.device?.customer?.building_name ?? "---" }}
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
                        {{ item.device?.customer?.buildingtype?.name ?? "---" }}
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
                              ? $dateFormat.minutesToHHMM(item.response_minutes)
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
                        <div v-if="item.alarm_forwarded.length > 0">
                          Forwarded
                        </div>
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
      </v-col>
    </v-row>

    <v-row v-if="sensorItems.length == 0" class="text-center">
      <v-col cols="12" class="text-center"> No Data is available</v-col>
    </v-row>
  </div>
</template>

<script>
import AlramCloseNotes from "../../components/Alarm/AlramCloseNotes.vue";

import EditAlarmCustomerEventNotes from "../../components/Alarm/EditCustomerEventNotes.vue";
import AlarmEventNotesListView from "../../components/Alarm/AlarmEventsNotesList.vue";
import AlarmEventCustomerContactsTabView from "../../components/Alarm/AlarmEventCustomerContactsTabView.vue";

import AlarmForwardEvent from "../../components/Alarm/AlarmForwardEvent.vue";
import SecurityAlarmNotes from "./SecurityDashboard/SecurityAlarmNotes.vue";

export default {
  components: {
    EditAlarmCustomerEventNotes,
    AlarmEventNotesListView,
    AlarmEventCustomerContactsTabView,
    AlramCloseNotes,
    AlarmForwardEvent,
    SecurityAlarmNotes,
  },
  props: [
    "showFilters",
    "eventFilter",
    "filter_customer_id",
    "compFilterAlarmStatus",
    "showTabs",
  ],
  data() {
    return {
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
        // { text: "Building", value: "building", sortable: false },

        // { text: "Customer", value: "customer", sortable: false },
        // { text: "Property", value: "property", sortable: false },
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
        // {
        //   text: "Status",
        //   value: "status",
        //   sortable: false,
        //   align: "center",
        // },

        // { text: "Options", value: "options", sortable: false },
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

    // tab: {
    //   handler() {
    //     this.showTable = false;
    //     this.getDataFromApi(0);
    //   },
    //   deep: true,
    // },
  },
  created() {
    // let today = new Date();
    // let monthObj = this.$dateFormat.monthStartEnd(today);
    // this.date_from = monthObj.first;
    // this.date_to = monthObj.last;

    setTimeout(() => {
      if (!this.showTabs || showTabs) this.getSensorsList();
    }, 2000);
    if (this.sensorItems.length == 0) {
      this.$axios
        .get(`sensor_types`, {
          params: {
            company_id: this.$auth.user.company_id,
          },
        })
        .then(({ data }) => {
          //this.sensorItems = ["All", ...data];
          this.sensorItems = ["All"];
        });
    }
    if (this.compFilterAlarmStatus) {
      this.filterAlarmStatus = this.compFilterAlarmStatus;
    }
    // setTimeout(() => {}, 1000);

    setTimeout(() => {
      setInterval(() => {
        if (
          (this.$route.name == "alarm-dashboard" ||
            this.$route.name == "alarm-allevents" ||
            this.$route.name == "alarm-alarm-events") &&
          this.filterAlarmStatus == 1
        )
          this.getDataFromApi(0);
      }, 1000 * 20 * 1);
    }, 1000 * 40);
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    showTabContent() {
      this.showTable = false;

      this.getDataFromApi(0);
    },
    closeCustomerDialog() {
      this.dialogTabViewCustomer = false;
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
      // Check for existing request and cancel it
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel("Operation canceled due to new request.");
      }

      // Create a new cancel token for this request
      this.cancelTokenSource = this.$axios.CancelToken.source();

      // Check loading and commonSearch conditions
      if (this.loading == true && this.commonSearch == null) return false;

      if (custompage == 0) this.options = { perPage: 10, page: 1 };

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";
      this.perPage = itemsPerPage;
      this.currentPage = page;

      if (!page > 0) return false;
      this.loading = true;

      let filterSensorname = this.tab > 0 ? this.sensorItems[this.tab] : null;

      if (this.eventFilter) {
        filterSensorname = this.eventFilter;
      }

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
          filterSensorname: filterSensorname,
          filterResponseInMinutes: this.filterResponseInMinutes,
          sortBy: "alarm_start_datetime",
          sortDesc: "DESC",
        },
        cancelToken: this.cancelTokenSource.token, // Add the cancel token here
      };

      try {
        const { data } = await this.$axios.get("get_alarm_events", options);
        this.items = data.data;
        this.totalRowsCount = data.total;
        this.showTable = true;
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
