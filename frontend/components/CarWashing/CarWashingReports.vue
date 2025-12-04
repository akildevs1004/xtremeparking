<template>
  <div>
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="dialogImagePreview" max-width="80%">
      <v-card>
        <v-card-title dense class="popup_background">
          Image Preview
          <v-spacer></v-spacer>
          <v-btn icon @click="dialogImagePreview = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="d-flex justify-center">
          <v-img :src="dialogImageUrl" contain style="max-width: 80%; max-height: 80vh;"></v-img>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogTabViewParking" width="1000px" persistence scrollable eager :retain-focus="false"><v-card>
        <v-card-title dense class="popup_background">
          Parking Info
          <v-spacer></v-spacer>
          <v-btn icon @click="dialogTabViewParking = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text class="background">

          <CarWashingInfo @close="dialogTabViewParking = false; getDataFromApi(0)" :parking="selectedParking">
          </CarWashingInfo>
          <!-- <v-img :src="dialogImageUrl" @click:outside="dialogTabViewParking = false"></v-img> -->
        </v-card-text></v-card>
    </v-dialog>

    <v-row class="p-0" style="padding-top: 0px">
      <v-col cols="12" class="text-right" style="padding-top: 0px; z-index: 9; padding-right: 0px">
        <v-card class="mt-3" elevation="0">
          <v-card-text>
            <v-row>
              <v-col class="text-left mt-1" :cols="sensorItems.length > 1 ? 4 : 4">
                <h3 style="font-weight: normal"> Logs - History</h3>

                <!-- <v-icon @click="getDataFromApi()">mdi-reload</v-icon> -->
              </v-col>

              <v-col style="width: 600px; padding: 0px">

                <v-row>
                  <v-col style="margin: auto">
                    <v-icon loading="true" @click="getDataFromApi(0)" class="mt-2 mr-2">mdi-reload</v-icon>

                    <v-text-field style="padding-top: 7px; float: right; width: 250px" height="20"
                      class="employee-schedule-search-box" v-model="commonSearch" label="Search"
                      placeholder="Vehicle Number etc.." dense outlined type="text" append-icon="mdi-magnify" clearable
                      hide-details></v-text-field></v-col>
                  <v-col style="max-width: 200px; padding-right: 0px; margin: auto">


                    <v-select class="employee-schedule-search-box" style="
                        padding-top: 7px;
                        z-index: 999;
                        min-width: 100%;
                        width: 200px;
                      " height="25px" outlined v-model="filterDuration" dense :items="allDurationOptionsList"
                      item-text="name" item-value="name" hide-details></v-select>
                  </v-col>
                  <!-- <v-col style="max-width: 200px; padding-right: 0px; margin: auto">


                    <v-select class="employee-schedule-search-box" style="
                        padding-top: 7px;
                        z-index: 999;
                        min-width: 100%;
                        width: 200px;
                      " height="25px" outlined v-model="filterPayment" dense :items="allPaymentOptionsList"
                      item-text="name" item-value="name" hide-details></v-select>
                  </v-col> -->
                  <v-col style="max-width: 200px; margin: auto">
                    <CustomFilter style="float: left; padding-top: 5px; z-index: 999" @filter-attr="filterAttr"
                      :default_date_from="date_from" :default_date_to="date_to" :defaultFilterType="1"
                      :height="'30px'" />
                  </v-col>
                  <v-col style="max-width: 100px; padding-top: 18px; margin: auto">
                    <v-btn desne small color="primary" @click="getDataFromApi(0)">Submit</v-btn>
                  </v-col>
                  <v-col style="max-width: 50px"></v-col>

                </v-row>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <v-row v-if="sensorItems?.length > 0" style="margin-top: 0px">
          <v-col cols="12" style="margin-top: 0px">
            <v-tabs v-if="sensorItems.length > 1" v-model="tab" background-color="transparent" color="red" right bold>
              <v-tab @click="showTabContent()" v-for="(item, index) in sensorItems" :key="item.id"
                style="font-weight: bold">
                {{ item }}
              </v-tab>
            </v-tabs>

            <v-tabs-items v-model="tab">
              <v-tab-item v-for="(item, index) in sensorItems" :key="item.id">
                <v-card color="basil" flat>
                  <v-card-text style="padding: 0px">
                    <v-data-table :height="tableHeight" :name="'table' + index" v-if="showTable" :headers="headers"
                      :items="items" :server-items-length="totalRowsCount" :loading="loading" :options.sync="options"
                      :footer-props="{
                        itemsPerPageOptions: [10, 50, 100, 500, 1000],
                      }" class="elevation-0 table-header-color">

                      <template v-slot:item.sno="{ item, index }">
                        {{
                          currentPage
                            ? (currentPage - 1) * perPage +
                            (cumulativeIndex + items.indexOf(item))
                            : ""
                        }}
                      </template>
                      <template v-slot:item.total_amount="{ item }">
                        <div style="width:50px;">
                          <div style="text-align: right;" v-if="item.total_amount">{{ item.total_amount }}</div>
                          <div style="text-align: center;" v-else>---</div>
                        </div>
                      </template>
                      <template v-slot:item.in_time="{ item }">
                        <div v-if="item.in_time">{{ item.in_time }}</div>

                        <div v-else>----</div>
                      </template>
                      <template v-slot:item.membership_id="{ item }">
                        <v-chip @click="viewParkinginfo(item)" style="min-width: 80px; " v-if="item.membership_id"
                          color="green" label>{{
                            $utils.caps(item.parking_members.member_type)

                          }}
                          {{ item.member_guest_vehicle_id ? ' - Guest'
                            : '' }}


                        </v-chip>
                        <div v-else><v-chip style="min-width: 80px; " v-if="!item.membership_id" color="red"
                            label>GUEST</v-chip></div>
                      </template>
                      <template v-slot:item.payment_mode="{ item }">
                        <div v-if="item.payment_mode && item.out_time"> <v-chip style="min-width: 80px; "
                            v-if="item.payment_mode" color="green" label>{{
                              $utils.caps(item.payment_mode)
                            }}</v-chip></div>

                        <v-chip style="min-width: 80px; " v-else-if="item.out_time && item.membership_id" color="green"
                          outlined label>Completed</v-chip>
                        <div v-else>--- </div>
                      </template>
                      <template v-slot:item.duration_in_hours="{ item }">
                        <div v-if="item.duration_in_minutes">{{ $dateFormat.minutesToHHMM(item.duration_in_minutes) }}
                        </div>

                        <div v-else>----</div>
                      </template>
                      <template v-slot:item.out_time="{ item }">
                        <div v-if="item.out_time">{{ item.out_time }}</div>

                        <div v-else>----</div>
                      </template>

                      <template v-slot:item.plate_photo="{ item }">





                        <v-img v-if="item.out_time == null && item.in_background_file_name"
                          @click="openImagePreview(item.parking_image_path + '/' + item.in_background_file_name.replace('_BACKGROUND', '_PLATE'))"
                          :src="item.parking_image_path + '/' + item.in_background_file_name.replace('_BACKGROUND', '_PLATE')"
                          max-width="100" max-height="30"></v-img>

                        <v-img v-else-if="item.out_background_file_name"
                          @click="openImagePreview(item.parking_image_path + '/' + item.out_background_file_name.replace('_BACKGROUND', '_PLATE'))"
                          :src="item.parking_image_path + '/' + item.out_background_file_name.replace('_BACKGROUND', '_PLATE')"
                          max-width="100" max-height="30"></v-img>
                      </template>

                      <template v-slot:item.options="{ item }">
                        <v-menu bottom left>
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn dark-2 icon v-bind="attrs" v-on="on">
                              <v-icon>mdi-dots-vertical</v-icon>
                            </v-btn>
                          </template>
                          <v-list width="120" dense>

                            <v-list-item @click="viewParkinginfo(item)">
                              <v-list-item-title style="cursor: pointer">
                                <v-icon color="secondary" small>
                                  mdi-eye
                                </v-icon>
                                Info
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
import { mqttRequestReply } from '@/utils/mqttRequestReplyClient.js'; // adjust path
import CarWashingInfo from './CarWashingInfo.vue';
import { filter } from 'core-js/core/array';

export default {

  components: {
    CarWashingInfo
  },
  props: [
    "popup",
    "showFilters",
    "showTabs",
    "eventFilter",
    "filter_customer_id",

    "compfilterPayment",
    "compFilterSupervisor",
    "filter_date",
    "filter_alarm_type",
    "hide_customer_details", "memberId", "isMQTT"
  ],
  data() {
    return {


      perPage: 10,
      cumulativeIndex: 1,
      currentPage: 1,





      dialogImagePreview: false,
      dialogImageUrl: null,
      tableHeight: 750,
      allDurationOptionsList: [],
      allPaymentOptionsList: [],
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
      filterPayment: "All",
      filterDuration: "All Duration",
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
        { text: "Number", value: "log_vehicle_number", sortable: false },


        { text: "Entry Time", value: "in_time", sortable: false },
        { text: "Exit Time", value: "out_time", sortable: false },
        { text: "Hours", value: "duration_in_hours", sortable: false, },
        { text: "State", value: "raw_country_region", sortable: false },
        // { text: "Payment", value: "total_amount", sortable: false },
        // { text: "Member", value: "membership_id", sortable: false },



        // { text: "Payment Status", value: "payment_mode", sortable: false },

        // { text: "Plate", value: "raw_plate_no", sortable: false },

        { text: "Photo", value: "plate_photo", sortable: false },


        { text: "Options", value: "options", sortable: false },
      ],
      items: [],
      selectedAlarm: null,
      AlarmTypeNotificationIcons: [],
      filterAlarmType: "All",
      selectedParking: null,
      dialogTabViewParking: false
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

    // let options = {
    //   params: {
    //     company_id: this.$auth.user.company_id,
    //   },
    // };
    // this.$axios.get(`alarm_notification_icons`, options).then(({ data }) => {
    //   this.AlarmTypeNotificationIcons = data;
    //   const excludedKeys = ["AC_off", "DC_off"];

    //   const array = Object.entries(this.AlarmTypeNotificationIcons)

    //     .filter(([key]) => !excludedKeys.includes(key)) // Exclude specific keys
    //     .map(([key, value]) => ({ notification_type: key, image: value }));

    //   this.AlarmTypeNotificationIcons = [
    //     { notification_type: "All", image: "All" },
    //     ...array,
    //   ];
    // });
  },
  created() {
    this.allPaymentOptionsList = [];

    this.allPaymentOptionsList = [
      { id: null, name: "All" },
      { id: 1, name: "Cash" },

      { id: 2, name: "Online" },
      { id: 3, name: "Pending" },


    ];
    this.allDurationOptionsList = [];

    this.allDurationOptionsList = [
      { id: null, name: "All Duration" },
      { id: 1, name: "More Than 1 Hour" },

      { id: 2, name: "Less Than 1 Hour" },



    ];


    // if (this.showTabs) {
    //   setTimeout(() => {
    //     if (this.sensorItems.length == 0) {
    //       this.$axios
    //         .get(`sensor_types`, {
    //           params: {
    //             company_id: this.$auth.user.company_id,
    //           },
    //         })
    //         .then(({ data }) => {
    //           this.sensorItems = ["All", ...data];
    //         });
    //     }
    //   }, 2000);
    // } else {
    //   this.sensorItems = ["All"];
    // }
    // this.getDataFromApi(0);


    // if (this.hide_customer_details) {
    //   this.headers = this.headers.filter(
    //     (header) =>
    //       !["Customer", "Property", "Options", "Priority"].includes(header.text)
    //   );
    // }
  },

  methods: {
    openImagePreview(url) {
      this.dialogImageUrl = url;
      this.dialogImagePreview = true;
    },
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
    // showTabContent() {
    //   this.showTable = false;

    //   this.getDataFromApi(0);
    // },
    // closeCustomerDialog() {
    //   this.dialogTabViewCustomer = false;
    // },
    // viewAlarminfo(alarm) {
    //   this.key++;
    //   this.selectedAlarm = alarm;
    //   this.dialogViewAlarmFormat = true;
    // },

    viewParkinginfo(item) {
      this.key++;
      this.selectedParking = item;
      this.dialogTabViewParking = true;
    },
    // viewCustomerinfo(item) {
    //   if (item.device) {
    //     this.popupEventText =
    //       "#" +
    //       item.id +
    //       " -    " +
    //       item.alarm_type +
    //       " ,  " +
    //       "   Time " +
    //       item.alarm_start_datetime +
    //       " -  Priority " +
    //       item.category.name;
    //     this.key += 1;

    //     this.viewCustomerId = item.customer_id;
    //     this.eventId = item.id;
    //     this.selectedAlarm = item;
    //     this.dialogTabViewCustomer = true;
    //   }
    // },

    // eventForward(item) {
    //   this.popupEventText =
    //     "#" +
    //     item.id +
    //     " -    " +
    //     item.alarm_type +
    //     " ,  " +
    //     "   Time " +
    //     item.alarm_start_datetime +
    //     " -  Priority " +
    //     item.category.name;
    //   this.key += 1;
    //   this.viewCustomerId = item.customer_id;
    //   this.eventId = item.id;
    //   this.customer = item.device.customer;
    //   this.dialogForwardEventDetails = true;
    // },
    // viewLogs(item) {
    //   this.popupEventText =
    //     "#" +
    //     item.id +
    //     " -    " +
    //     item.alarm_type +
    //     " ,  " +
    //     "   Time " +
    //     item.alarm_start_datetime +
    //     " -  Priority " +
    //     item.category.name;
    //   this.key += 1;
    //   this.viewCustomerId = item.customer_id;
    //   this.eventId = item.id;
    //   this.customer = item.device.customer;
    //   this.dialogViewLogs = true;
    // },
    // viewNotes(item) {
    //   this.key += 1;
    //   this.eventId = item.id;
    //   this.customer_id = item.customer_id;
    //   this.dialogNotesList = true;
    // },
    // getSensorsList() {
    //   if (this.$store.state.storeAlarmControlPanel?.SensorTypes) {
    //     // this.sensorItems = this.$store.state.storeAlarmControlPanel.SensorTypes;
    //     this.sensorItems = [
    //       "All",
    //       ...this.$store.state.storeAlarmControlPanel.SensorTypes,
    //     ];
    //   }

    //   if (this.eventFilter) {
    //     this.sensorItems = [this.eventFilter];
    //   }
    // },
    // addNotes(item) {
    //   this.eventId = item.id;
    //   this.dialogAddCustomerNotes = true;
    // },
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
    // UpdateAlarmStatus(item, status) {
    //   if (status == 0) {
    //     if (confirm("Are you sure you want to TURN OFF the Alarm")) {
    //       this.customer_id = item.customer_id;
    //       this.eventId = item.id;
    //       this.dialogCloseAlarm = true;
    //     }
    //   }
    // },
    // deleteEvent(id) {
    //   if (confirm("Are you sure want to delete Alarm Event notes?")) {
    //     this.loading = true;
    //     let options = {
    //       params: {
    //         company_id: this.$auth.user.company_id,
    //         id: id,
    //       },
    //     };

    //     try {
    //       this.$axios.delete(`delete-event`, options).then(({ data }) => {
    //         this.snackbar = true;
    //         this.response = "Event Note Deleted Successfully";
    //         this.getDataFromApi();
    //         this.loading = false;
    //       });
    //     } catch (e) { }
    //   }
    // },

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
      if (this.filterPayment)
        url += "&alarm_status=" + this.filterPayment;

      if (this.filterDuration)
        url += "&filter_duration=" + this.filterDuration;



      if (filterSensorname != "null" && filterSensorname)
        url += "&filterSensorname=" + filterSensorname;
      if (this.filterResponseInMinutes)
        url += "&filterResponseInMinutes=" + this.filterResponseInMinutes;
      url += "&tab=" + this.tab;
      //  url += "&alarm_status=" + this.filterPayment;
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
      // Prevent request if loading and no search criteria
      if (this.loading && this.commonSearch == null) return false;

      // Reset pagination if asked
      if (custompage === 0) this.options = { perPage: 10, page: 1 };

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;
      const sortedBy = Array.isArray(sortBy) ? sortBy[0] : "";
      const sortedDesc = Array.isArray(sortDesc) ? sortDesc[0] : "";

      // Prevent invalid page request
      if (!(page > 0)) return false;

      // Track the latest fetch to ignore stale results
      this._fetchVersion = (this._fetchVersion || 0) + 1;
      const myFetchVersion = this._fetchVersion;

      this.loading = true;
      this.isBackendRequestOpen = true;

      // Prepare filters
      let filterSensorname = this.tab > 0 ? this.sensorItems[this.tab] : null;
      if (this.eventFilter) filterSensorname = this.eventFilter;

      if (this.filterAlarmType === "All") this.filterAlarmType = null;

      // Build the params/payload once (used by both MQTT & HTTP)
      const params = {
        page,
        perPage: itemsPerPage,
        pagination: true,
        company_id: this.$auth.user.company_id,
        date_from: this.date_from,
        date_to: this.date_to,
        common_search: this.commonSearch,
        tab: this.tab,
        filter_payment: this.filterPayment,
        filter_duration: this.filterDuration,
        member_id: this.memberId ?? null,
        filter_date: this.filter_date,
        sorted_by: sortedBy || null,
        sorted_desc: sortedDesc || false,
        filter_sensorname: filterSensorname,
        // add any additional filters here...
      };

      // Axios cancel token (HTTP path only)
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel("Operation canceled due to new request.");
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      // Normalizer so both paths look identical
      const normalize = (raw) => ({
        data: Array.isArray(raw?.data) ? raw.data : [],
        total: Number.isFinite(raw?.total) ? raw.total : (Array.isArray(raw?.data) ? raw.data.length : 0),
        status: raw?.status ?? true, // assume true if not provided
        message: raw?.message ?? "",
      });



      try {
        let result = null;
        let usedMQTT = false;

        // 1) Try MQTT first (if enabled)
        if (this.isMQTT && typeof mqttRequestReply === "function") {
          try {

            console.log(params.member_id);

            const mqttResp = await mqttRequestReply({
              companyId: params.company_id,
              action: "parking_camera_logs",
              payload: params,          // mirror the HTTP query as payload
              timeoutMs: 8000,
            });

            // Expect: { action: "parking_camera_logs_list", data: [...], total: N, status?, message? }
            if (
              mqttResp &&
              mqttResp.action === "parking_camera_logs") {
              result = normalize(mqttResp.data);

              usedMQTT = true;
            } else {
              console.warn("[MQTT] Unexpected response shape:", mqttResp);
            }
          } catch (e) {
            console.warn("[MQTT] Failed; falling back to HTTP:", e?.message || e);
          }
        }
        else {
          // Helper: HTTP fallback
          const doHttp = async () => {
            const options = { params, cancelToken: this.cancelTokenSource.token };
            const { data } = await this.$axios.get(`parking_camera_logs`, options);
            return normalize(data);
          };
          result = await doHttp();
        }



        // Ignore stale responses
        if (myFetchVersion !== this._fetchVersion) return;

        // 3) Apply results
        this.items = result.data;
        this.totalRowsCount = result.total;
        this.showTable = true;

        // Optional: toast/debug
        // if (usedMQTT) {
        //   this.snackbar = true;
        //   this.color = "background";
        //   this.response = "[MQTT] Data loaded.";
        // }
      } catch (error) {
        if (this.$axios.isCancel?.(error)) {
          console.log("Request canceled:", error.message);
        } else {
          console.error("Error fetching data:", error);
          this.snackbar = true;
          this.color = "red";
          this.response =
            error?.response?.data?.message ||
            error?.message ||
            "Failed to load data. Please try again.";
        }
      } finally {
        // Ignore stale
        if (myFetchVersion !== this._fetchVersion) return;
        this.loading = false;
        this.isBackendRequestOpen = false;
        this.currentPage = page;
        this.perPage = itemsPerPage;
      }
    }


    // async getDataFromApi(custompage = 1) {
    //   // Prevent request if loading or no search criteria
    //   if (this.loading && this.commonSearch == null) return false;

    //   // Reset pagination if custompage is 0
    //   if (custompage == 0) this.options = { perPage: 10, page: 1 };

    //   let { sortBy, sortDesc, page, itemsPerPage } = this.options;
    //   let sortedBy = sortBy ? sortBy[0] : "";
    //   let sortedDesc = sortDesc ? sortDesc[0] : "";

    //   this.perPage = itemsPerPage;
    //   this.currentPage = page;

    //   // Prevent invalid page request
    //   if (!(page > 0)) return false;

    //   this.loading = true;

    //   // Prepare filter data
    //   let filterSensorname = this.tab > 0 ? this.sensorItems[this.tab] : null;
    //   if (this.eventFilter) {
    //     filterSensorname = this.eventFilter;
    //   }

    //   // Cancel previous request if it exists
    //   if (this.cancelTokenSource) {
    //     this.cancelTokenSource.cancel("Operation canceled due to new request.");
    //   }

    //   // Create a new cancel token for this request
    //   this.cancelTokenSource = this.$axios.CancelToken.source();

    //   if (this.filterAlarmType == "All") this.filterAlarmType = null;
    //   // if (this.filterPayment == "All") this.filterPayment = null;

    //   let options = {
    //     params: {
    //       page: page,
    //       perPage: itemsPerPage,
    //       pagination: true,
    //       company_id: this.$auth.user.company_id,
    //       date_from: this.date_from,
    //       date_to: this.date_to,
    //       common_search: this.commonSearch,
    //       // customer_id: this.filter_customer_id,
    //       tab: this.tab,
    //       filter_payment: this.filterPayment,
    //       member_id: this.memberId ?? null,

    //       filter_date: this.filter_date,

    //     },
    //     cancelToken: this.cancelTokenSource.token, // Assign the cancel token
    //   };
    //   this.currentPage = page;
    //   this.perPage = itemsPerPage;
    //   try {
    //     const { data } = await this.$axios.get(`parking_camera_logs`, options);

    //     // Process the response
    //     this.items = data.data;
    //     this.totalRowsCount = data.total;
    //     this.showTable = true;
    //     this.loading = false;
    //   } catch (error) {
    //     if (this.$axios.isCancel(error)) {
    //       console.log("Request canceled:", error.message);
    //     } else {
    //       console.error("Error fetching data:", error);
    //       this.loading = false;
    //     }
    //   }
    // },
  },
};
</script>
