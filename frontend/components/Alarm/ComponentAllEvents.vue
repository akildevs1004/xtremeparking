<template>
  <div>
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-dialog v-model="dialogViewAlarmFormat" width="1200px">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span>Alarm Event Track #{{ selectedAlarm?.id }}</span>
          <v-spacer></v-spacer
          ><v-icon
            style="padding-right: 20px"
            @click="alarmNotesPrint(selectedAlarm?.id, 'download')"
            outlined
          >
            mdi-download-box-outline
          </v-icon>
          <v-icon
            style="padding-right: 20px"
            @click="alarmNotesPrint(selectedAlarm?.id, 'print')"
            outlined
          >
            mdi-printer-outline
          </v-icon>
          <v-icon @click="dialogViewAlarmFormat = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text style="padding: 0px">
          <v-container style="min-height: 100px; padding-left: 0px">
            <AlarmNotesFormatView
              v-if="selectedAlarm"
              :alarm="selectedAlarm"
              :key="key"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogViewLogs" width="80%">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span>Operator Logs #{{ selectedAlarm?.id }}</span>
          <v-spacer></v-spacer>
          <v-icon
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
          <span>Alarm - Forward Details #{{ eventId }}</span>
          <v-spacer></v-spacer>
          <v-icon
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
              :customer="customer"
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
    <v-dialog v-model="dialogTabViewCustomer" width="1400" height="700">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span>Alarm : {{ popupEventText }}</span>
          <v-spacer></v-spacer>
          <v-icon @click="dialogTabViewCustomer = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text class="background">
          <TechnicianCustomerTabsView
            v-if="selectedAlarm?.device?.customer"
            :key="key"
            :_id="viewCustomerId"
            :selectedCustomer="selectedAlarm?.device?.customer"
            :isPopup="true"
            :isEditable="false"
            :selectedAlarm="selectedAlarm"
          />

          <!-- <AlarmEventCustomerContactsTabView
            @closeCustomerDialog="closeCustomerDialog()"
            :key="key"
            :_customerID="viewCustomerId"
            :alarmId="eventId"
            :customer="customer"
            :isPopup="true"
            :alarm="selectedAlarm"
          /> -->
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-row class="p-0" style="padding-top: 0px">
      <v-col
        cols="12"
        class="text-right"
        style="padding-top: 0px; z-index: 9; padding-right: 0px"
      >
        <v-card class="mt-3" elevation="0">
          <v-card-text>
            <v-row>
              <v-col
                class="text-left mt-1"
                :cols="sensorItems.length > 1 ? 4 : 4"
              >
                <h3 style="font-weight: normal">Alarm Events History</h3>
              </v-col>

              <v-col
                :cols="sensorItems.length > 1 ? 8 : 8"
                style="width: 600px; padding: 0px"
              >
                <v-row v-if="showFilters == 'true'">
                  <v-col style="margin: auto">
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
                      v-model="commonSearch"
                      label="Search"
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
                  <v-col
                    style="max-width: 200px; padding-right: 0px; margin: auto"
                  >
                    <v-select
                      label="Event Type"
                      class="employee-schedule-search-box"
                      style="
                        padding-top: 7px;
                        z-index: 999;
                        min-width: 100%;
                        width: 200px;
                      "
                      height="25px"
                      outlined
                      v-model="filterAlarmType"
                      dense
                      :items="AlarmTypeNotificationIcons"
                      item-text="notification_type"
                      clearable
                      item-value="notification_type"
                      hide-details
                    ></v-select>
                  </v-col>
                  <v-col
                    style="max-width: 200px; padding-right: 0px; margin: auto"
                  >
                    <v-select
                      class="employee-schedule-search-box"
                      style="
                        padding-top: 7px;
                        z-index: 999;
                        min-width: 100%;
                        width: 200px;
                      "
                      height="25px"
                      outlined
                      v-model="filterAlarmStatus"
                      dense
                      :items="allEventsList"
                      item-text="name"
                      item-value="id"
                      clearable
                      hide-details
                    ></v-select>
                  </v-col>
                  <v-col style="max-width: 200px; margin: auto">
                    <CustomFilter
                      style="float: left; padding-top: 5px; z-index: 999"
                      @filter-attr="filterAttr"
                      :default_date_from="date_from"
                      :default_date_to="date_to"
                      :defaultFilterType="1"
                      :height="'30px'"
                  /></v-col>
                  <v-col
                    style="max-width: 100px; padding-top: 18px; margin: auto"
                  >
                    <v-btn
                      desne
                      small
                      color="primary"
                      @click="getDataFromApi(0)"
                      >Submit</v-btn
                    >
                  </v-col>
                  <v-col style="max-width: 50px"></v-col>
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

        <v-row v-if="sensorItems?.length > 0" style="margin-top: 0px">
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
              <v-tab-item v-for="(item, index) in sensorItems" :key="item.id">
                <v-card color="basil" flat>
                  <v-card-text style="padding: 0px">
                    <v-data-table
                      :height="tableHeight"
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
                      class="elevation-0 table-header-color"
                    >
                      <template v-slot:item.sno="{ item, index }">
                        {{ item.id }}
                      </template>

                      <template v-slot:item.customer="{ item }">
                        <div>
                          {{ item.device?.customer?.building_name ?? "---" }}
                        </div>
                        <div class="secondary-value">
                          {{
                            item.device?.customer?.primary_contact
                              ?.first_name ?? "---"
                          }}
                          {{
                            item.device?.customer?.primary_contact?.last_name ??
                            "---"
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
                        <img
                          :title="item.alarm_type"
                          :src="
                            '/notification_icons/' +
                              item.notificationicon?.image ||
                            '/no-business_profile.png'
                          "
                          style="
                            width: 100%;

                            width: 22px;
                            margin: auto;
                            vertical-align: middle;
                          "
                        />
                        <!-- <div>
                          {{ item.alarm_type }}
                        </div>
                        <div class="secondary-value">
                          <div class="secondary-value">
                            {{ item.zone_data?.location ?? "---" }}
                          </div>
                        </div> -->
                      </template>
                      <template v-slot:item.property="{ item }">
                        {{ item.device?.customer?.buildingtype?.name ?? "---" }}

                        <!-- <div class="secondary-value">
                          {{ item.device?.customer?.area }}
                        </div> -->
                        <div class="secondary-value">
                          {{ item.device?.customer?.city }}
                        </div>
                      </template>
                      <template v-slot:item.zone="{ item }">
                        <div>{{ item.zone }}</div>
                        <div class="secondary-value">{{ item.area }}</div>
                      </template>
                      <template v-slot:item.alarm_source="{ item }">
                        <div>
                          {{ item.alarm_type }}
                        </div>
                        <div class="secondary-value">
                          <div class="secondary-value">
                            {{ item.zone_data?.location ?? "---" }}
                          </div>
                        </div>
                        <!-- <div>{{ item.alarm_source ?? "---" }}</div>
                        <div class="secondary-value">
                          {{ item.zone_data?.wired ?? "---" }}
                        </div> -->
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
                        <!-- <div>{{ item.category?.name || "---" }}</div> -->
                        <v-chip
                          style="
                            width: 60px;
                            color: #fff;
                            height: 18px;
                            text-align: center;
                          "
                          class="alarmevents"
                          v-if="item.category?.name == 'Low'"
                          color="#a2b117"
                          label
                          >{{ item.category?.name || "---" }}</v-chip
                        >
                        <v-chip
                          style="
                            width: 60px;
                            color: #fff;
                            height: 18px;
                            text-align: center;
                          "
                          class="alarmevents"
                          v-else-if="item.category?.name == 'Medium'"
                          color="#1e71c3"
                          label
                          >Med</v-chip
                        >

                        <v-chip
                          style="
                            width: 60px;
                            color: #fff;
                            height: 18px;
                            text-align: center;
                          "
                          class="alarmevents"
                          v-else
                          color="#a70000"
                          label
                          >{{ item.category?.name || "---" }}</v-chip
                        >
                      </template>

                      <template v-slot:item.status="{ item }">
                        <div
                          style="
                            width: 60px;
                            color: #fff;
                            height: 25px;
                            color: #ff0000;
                          "
                          v-if="item.alarm_status == 1"
                          label
                        >
                          Open
                        </div>
                        <div
                          style="
                            width: 60px;
                            color: #fff;
                            height: 25px;
                            color: #0046ff;
                          "
                          v-else-if="item.forwarded == true"
                          label
                        >
                          FWD
                        </div>

                        <div style="width: 60px; height: 25px" v-else label>
                          Closed
                        </div>
                        <!-- <div v-if="item.forwarded === true">Forwarded</div>
                        <div v-else-if="item.alarm_status == 1">
                          Open

                        </div>
                        <div v-else-if="item.alarm_status == 0">
                          Closed

                        </div> -->
                      </template>
                      <template v-slot:item.options="{ item }">
                        <v-menu bottom left>
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn dark-2 icon v-bind="attrs" v-on="on">
                              <v-icon>mdi-dots-vertical</v-icon>
                            </v-btn>
                          </template>
                          <v-list width="120" dense>
                            <v-list-item @click="viewAlarminfo(item)">
                              <v-list-item-title style="cursor: pointer">
                                <v-icon color="secondary" small>
                                  mdi-file-tree
                                </v-icon>
                                Notes
                              </v-list-item-title>
                            </v-list-item>
                            <v-list-item @click="viewCustomerinfo(item)">
                              <v-list-item-title style="cursor: pointer">
                                <v-icon color="secondary" small>
                                  mdi-eye
                                </v-icon>
                                Customer
                              </v-list-item-title>
                            </v-list-item>
                            <v-list-item
                              v-if="
                                $auth.user.user_type == 'operator' ||
                                $auth.user.user_type == 'company'
                              "
                              @click="eventForward(item)"
                            >
                              <v-list-item-title style="cursor: pointer">
                                <v-icon color="secondary" small>
                                  mdi mdi-share-all
                                </v-icon>
                                Forward
                              </v-list-item-title>
                            </v-list-item>
                            <v-list-item @click="viewLogs(item)">
                              <v-list-item-title style="cursor: pointer">
                                <v-icon color="secondary" small>
                                  mdi-format-list-numbered
                                </v-icon>
                                Logs
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

        <v-row v-if="sensorItems.length == 0" class="text-center">
          <v-col cols="12" class="text-center"> No Data is available</v-col>
        </v-row>
      </v-col>
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
import AlarmNotesFormatView from "./Reports/AlarmNotesFormatView.vue";
import TechnicianCustomerTabsView from "./TechnicianDashboard/TechnicianCustomerTabsView.vue";

export default {
  components: {
    EditAlarmCustomerEventNotes,
    AlarmEventNotesListView,
    AlarmEventCustomerContactsTabView,
    AlramCloseNotes,
    AlarmForwardEvent,
    SecurityAlarmNotes,
    AlarmNotesFormatView,
    TechnicianCustomerTabsView,
  },
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
    "hide_customer_details",
  ],
  data() {
    return {
      tableHeight: 750,

      allEventsList: [],
      selectedAlarm: null,
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
      sensorItems: ["All"],
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
        { text: "Event #", value: "sno", sortable: false },
        // { text: "Building", value: "building", sortable: false },

        { text: "Customer", value: "customer", sortable: false },
        { text: "Property", value: "property", sortable: false },
        // { text: "Address", value: "address", sortable: false },

        // { text: "City", value: "city", sortable: false },

        // { text: "Device", value: "device", sortable: false },
        { text: "Type", value: "sensor", sortable: false, align: "center" },
        { text: "Zone", value: "zonedata", sortable: false },
        { text: "Source", value: "alarm_source", sortable: false },

        // { text: "Zone", value: "zone", sortable: false },
        // { text: "Alarm Type", value: "alarm_type" , sortable: false },
        { text: "Event Time", value: "start_date", sortable: false },
        { text: "Closed time", value: "end_date", sortable: false },
        { text: "Priority", value: "alarm_category", sortable: false },
        // { text: "End Date", value: "end_date" , sortable: false },
        {
          text: "Duration",
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

        { text: "Options", value: "options", sortable: false },
      ],
      items: [],
      selectedAlarm: null,
      AlarmTypeNotificationIcons: [],
      filterAlarmType: "All",
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
  async mounted() {
    if (typeof window !== "undefined") {
      this.tableHeight = window.innerHeight - 300;
      window.addEventListener("resize", () => {
        this.tableHeight = window.innerHeight - 300;
      });
    }

    let options = {
      params: {
        company_id: this.$auth.user.company_id,
      },
    };
    this.$axios.get(`alarm_notification_icons`, options).then(({ data }) => {
      this.AlarmTypeNotificationIcons = data;
      const excludedKeys = ["AC_off", "DC_off"];

      const array = Object.entries(this.AlarmTypeNotificationIcons)

        .filter(([key]) => !excludedKeys.includes(key)) // Exclude specific keys
        .map(([key, value]) => ({ notification_type: key, image: value }));

      this.AlarmTypeNotificationIcons = [
        { notification_type: "All", image: "All" },
        ...array,
      ];
    });
  },
  created() {
    this.allEventsList = [];

    this.allEventsList = [
      { id: null, name: "All" },
      { id: 1, name: "Open" },

      { id: 0, name: "Closed" },
    ];
    // if (!this.compFilterSupervisor)
    {
      this.allEventsList.push({ id: 3, name: "Forwarded" });
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

    if (this.hide_customer_details) {
      this.headers = this.headers.filter(
        (header) =>
          !["Customer", "Property", "Options", "Priority"].includes(header.text)
      );
    }
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
      this.selectedAlarm = alarm;
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

      //this.getDataFromApi(0);
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

      url += "&customer_id=" + this.filter_customer_id;

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

      if (this.filterAlarmType == "All") this.filterAlarmType = null;
      // if (this.filterAlarmStatus == "All") this.filterAlarmStatus = null;

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
          filter_alarm_type: this.filterAlarmType
            ? this.filterAlarmType
            : this.filter_alarm_type,
        },
        cancelToken: this.cancelTokenSource.token, // Assign the cancel token
      };

      try {
        const { data } = await this.$axios.get(`get_alarm_events`, options);

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
