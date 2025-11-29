<template>
  <div>
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
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
              showOptions="true"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-row>
      <v-col cols="12" class="text-right" style="padding-top: 0px">
        <v-row>
          <v-col cols="8"></v-col>
          <v-col cols="4" class="text-right" style="width: 450px">
            <v-row>
              <v-col cols="1" class="mt-2">
                <v-icon @click="getDataFromApi()">mdi-refresh</v-icon>
              </v-col>
              <v-col cols="5"
                ><v-text-field
                  style="padding-top: 7px"
                  width="150px"
                  height="20"
                  class="employee-schedule-search-box"
                  @input="getDataFromApi()"
                  v-model="commonSearch"
                  label="Search"
                  dense
                  outlined
                  type="text"
                  append-icon="mdi-magnify"
                  clearable
                  hide-details
                ></v-text-field
              ></v-col>
              <v-col cols="6">
                <CustomFilter
                  style="float: right; padding-top: 5px; z-index: 9999"
                  @filter-attr="filterAttr"
                  :default_date_from="date_from"
                  :default_date_to="date_to"
                  :defaultFilterType="1"
                  :height="'40px'"
              /></v-col>
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
            <div>{{ item.device?.name ?? "---" }}</div>
            <div class="secondary-value">
              {{ item.device?.serial_number ?? "---" }}
            </div>
          </template>
          <template v-slot:item.sensor="{ item }">
            <div>
              {{ item.alarm_type }}
            </div>
            <div class="secondary-value">{{ item.type }}</div>
          </template>
          <template v-slot:item.location="{ item }">
            {{ item.device?.location ?? "---" }}
          </template>
          <template v-slot:item.zone="{ item }">
            <div>{{ item.zone }}</div>
            <div class="secondary-value">{{ item.area }}</div>
          </template>
          <template v-slot:item.start_date="{ item }">
            <div>
              {{ $dateFormat.formatDateMonthYear(item.alarm_start_datetime) }}
            </div>
            <div class="secondary-value">
              {{
                item.alarm_end_datetime
                  ? $dateFormat.formatDateMonthYear(item.alarm_end_datetime)
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
            <div @click="viewNotes(item)">{{ item.notes.length }}</div>
          </template>

          <template v-slot:item.category="{ item }">
            <div>{{ item.alarm_category }}</div>
          </template>
          <!-- <template v-slot:item.alarm="{ item }">
            <div style="color: red" v-if="item.alarm == 'ON'">
              <v-icon color="red">mdi mdi-alarm-light-outline</v-icon>
            </div>
            <div v-else>
              <v-icon>mdi mdi-alarm-light-outline</v-icon>
            </div>
          </template> -->
          <template v-slot:item.status="{ item }">
            <!-- <div style="color: red" v-if="item.alarm_end_datetime == ''">
              Open
            </div>
            <div v-else>Closed</div> -->

            <div v-if="item.alarm_status == 1">
              <v-icon
                class="alarm"
                @click="UpdateAlarmStatus(item, 0)"
                title="Click to Turn OFF Alarm "
                >mdi mdi-alarm-light</v-icon
              >
            </div>
            <div v-else-if="item.alarm_status == 0">
              <v-icon title="Now Alaram is OFF"
                >mdi mdi-alarm-light-outline</v-icon
              >
              <div class="secondary-value">
                {{
                  item.alarm_end_manually == 1
                    ? "Closed Manually"
                    : "Auto Closed"
                }}
              </div>
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
                <v-list-item v-if="can('branch_view')" @click="viewNotes(item)">
                  <v-list-item-title style="cursor: pointer">
                    <v-icon color="secondary" small> mdi-eye </v-icon>
                    View Notes
                  </v-list-item-title>
                </v-list-item>
                <v-list-item v-if="can('branch_edit')">
                  <v-list-item-title
                    style="cursor: pointer"
                    @click="addNotes(item)"
                  >
                    <v-icon color="secondary" small>
                      mdi-message-bulleted
                    </v-icon>
                    Add Notes
                  </v-list-item-title>
                </v-list-item>
                <!-- <v-list-item v-if="can('branch_edit')">
                  <v-list-item-title
                    style="cursor: pointer"
                    @click="deleteEvent(item.id)"
                  >
                    <v-icon color="error" small> mdi-delete </v-icon>
                    Delete
                  </v-list-item-title>
                </v-list-item> -->
              </v-list>
            </v-menu>
          </template>
        </v-data-table>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import EditAlarmCustomerEventNotes from "../../components/Alarm/EditCustomerEventNotes.vue";
import AlarmEventNotesListView from "../../components/Alarm/AlarmEventsNotesList.vue";

export default {
  components: {
    EditAlarmCustomerEventNotes,
    AlarmEventNotesListView,
  },
  props: ["customer_id"],
  data() {
    return {
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
        { text: "Device", value: "device", sortable: false },
        { text: "Sensor", value: "sensor", sortable: false },
        { text: "Location", value: "location", sortable: false },

        { text: "Zone", value: "zone", sortable: false },
        // { text: "Alarm Type", value: "alarm_type" , sortable: false },
        { text: "Start/End Date", value: "start_date", sortable: false },
        // { text: "End Date", value: "end_date" , sortable: false },
        {
          text: "Resolved Time(H:M)",
          value: "duration",
          sortable: false,
          align: "center",
        },
        // { text: "Category", value: "category", sortable: false },

        { text: "Notes", value: "notes", sortable: false },
        {
          text: "Status",
          value: "status",
          sortable: false,
          align: "center",
        },

        { text: "Options", value: "options", sortable: false },
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
    if (this.customer_id) {
      let today = new Date();
      let monthObj = this.$dateFormat.monthStartEnd(today);
      this.date_from = monthObj.first;
      this.date_to = monthObj.last;
      //this.getDataFromApi();
    }

    setInterval(() => {
      if (this.$route.name == "alarm-view-customer-id") {
        this.getDataFromApi();
      }
    }, 1000 * 20);
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
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
    UpdateAlarmStatus(item, status) {
      if (status == 0) {
        if (confirm("Are you sure you want to TURN OFF the Alarm")) {
          let options = {
            params: {
              company_id: this.$auth.user.company_id,
              customer_id: this.customer_id,
              event_id: item.id,
              status: status,
              notes: "Alarm Closed by Customer",
            },
          };
          this.loading = true;
          this.$axios
            .post(`/update-device-alarm-event-status-off`, options.params)
            .then(({ data }) => {
              this.getDataFromApi();
              if (!data.status) {
                if (data.message == "undefined") {
                  this.response = "Try again. No connection available";
                } else this.response = "Try again. " + data.message;
                this.snackbar = true;

                return;
              } else {
                setTimeout(() => {
                  this.loading = false;
                  this.response = data.message;
                  this.snackbar = true;
                }, 2000);

                return;
              }
            })
            .catch((e) => console.log(e));
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
    getDataFromApi() {
      if (this.customer_id) {
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
          this.$axios.get(`get_alarm_events`, options).then(({ data }) => {
            this.items = data.data;

            this.totalRowsCount = data.total;
            this.loading = false;
          });
        } catch (e) {}
      }
    },
  },
};
</script>
