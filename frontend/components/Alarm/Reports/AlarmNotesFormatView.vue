<template>
  <div>
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row v-if="alarm">
      <v-col>
        <v-row no-gutters>
          <v-col
            style="
              padding: 5px;
              padding-left: 0px;
              width: 60px;
              max-width: 60px;
            "
          >
            <v-img
              style="
                border-radius: 50%;
                height: 60px;
                min-height: 60px;
                width: 60px;
                max-width: 60px;
              "
              :src="
                alarm.device?.customer?.profile_picture
                  ? alarm.device?.customer?.profile_picture
                  : '/no-business_profile.png'
              "
            >
            </v-img>
          </v-col>
          <v-col
            style="
              padding: 10px;
              padding: 10px;
              line-height: 16px;
              padding-left: 15px;
            "
          >
            <div style="font-size: 13px; font-weight: bold">
              {{ alarm.device?.customer.building_name || "---" }}
              <span style="font-size: 10px"
                >({{ alarm.device?.customer.buildingtype.name || "---" }})</span
              >
            </div>

            <div style="font-size: 12px">
              {{ alarm.device?.customer.house_number || "---" }},
              {{ alarm.device?.customer.street_number || "---" }},
            </div>
            <div style="font-size: 12px">
              {{ alarm.device?.customer.area || "---" }},
              {{ alarm.device?.customer.city || "---" }}
            </div>
            <div style="font-size: 12px">
              <v-icon size="15">mdi-at</v-icon
              >{{ alarm.device?.customer.user.email || "---" }},
            </div>
            <div style="font-size: 12px">
              <v-icon size="15">mdi-cellphone-basic</v-icon
              >{{ alarm.device?.customer.contact_number || "---" }}
            </div>
          </v-col>
        </v-row></v-col
      >
      <v-col style="border-left: 1px solid #ddd">
        <v-row no-gutters>
          <v-col
            style="
              padding: 5px;
              padding-left: 0px;
              width: 60px;
              max-width: 60px;
            "
          >
            <v-img
              style="
                border-radius: 50%;
                height: 60px;
                min-height: 60px;
                width: 60px;
                max-width: 60px;
              "
              :src="
                alarm.device?.company.logo
                  ? alarm.device?.company.logo
                  : '/no-business_profile.png'
              "
            >
            </v-img>
          </v-col>
          <v-col
            style="
              padding: 10px;
              padding: 10px;
              line-height: 16px;
              padding-left: 15px;
            "
          >
            <div style="font-size: 13px; font-weight: bold">
              {{ alarm.device?.company.name || "---" }}
            </div>
            <div style="font-size: 12px">
              {{ alarm.device?.company.location || "---" }}
            </div>

            <div style="font-size: 12px">
              <v-icon size="15">mdi-at</v-icon
              >{{ alarm.device?.company.user?.email || "---" }}
            </div>
            <div style="font-size: 12px">
              <v-icon size="15">mdi-cellphone-basic</v-icon
              >{{ alarm.device?.company.contact_number || "---" }}
            </div>
          </v-col>
        </v-row>
      </v-col>
      <v-col style="border-left: 1px solid #ddd">
        <v-row no-gutters>
          <v-col
            style="
              padding: 5px;
              padding-left: 0px;
              width: 60px;
              max-width: 60px;
            "
          >
            <v-img
              style="
                border-radius: 50%;
                height: 60px;
                min-height: 60px;
                width: 60px;
                max-width: 60px;
              "
              :src="
                alarm.security?.picture
                  ? alarm.security.picture
                  : '/no-profile-image.png'
              "
            >
            </v-img>
          </v-col>
          <v-col
            style="
              padding: 10px;
              padding: 10px;
              line-height: 16px;
              padding-left: 15px;
            "
          >
            <div style="font-size: 13px; font-weight: bold">
              {{ alarm.security?.first_name || "---" }}
              {{ alarm.security?.last_name || "---" }}
            </div>
            <div style="font-size: 12px">
              <v-icon size="15">mdi-at</v-icon
              >{{ alarm.security?.email || "---" }}
            </div>

            <div style="font-size: 12px">
              <v-icon size="15">mdi-cellphone-basic</v-icon
              >{{ alarm.security?.contact_number || "---" }}
            </div>
          </v-col>
        </v-row>
      </v-col>
      <v-col style="min-width: 400px; border-left: 1px solid #ddd">
        <v-row>
          <v-col
            style="
              margin: auto;
              text-align: center;
              font-size: 12px;
              line-height: 23px;
              padding-top: 0px;
              padding-bottom: 0px;
            "
          >
            <img
              :title="alarm.alarm_type"
              style="width: 30px"
              :src="getAlarmColorObject(alarm).image + '?4=3'"
            />
            <div>
              {{ alarm.alarm_type ?? "---" }},{{
                alarm.zone_data?.location ?? "---"
              }}
            </div>
            <div style="color: red">
              <!-- {{ alarm.zone ?? "---" }} -->
              {{ alarm.zone_data?.sensor_type ?? "---" }}

              , {{ alarm.zone_data?.sensor_name ?? "---" }}
            </div>
          </v-col>

          <v-col
            style="
              padding: 0px;
              font-size: 12px;
              padding-left: 10px;
              line-height: 15px;
            "
          >
            <table
              style="
                font-size: 12px;
                line-height: 23px;
                padding-left: 10px;
                border-collapse: collapse;
              "
            >
              <tbody>
                <tr style="border-bottom: 1px solid #ddd">
                  <td style="color: red; padding: 0 10px">Event Id</td>
                  <td style="color: red; padding: 0 10px">#{{ alarm.id }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #ddd">
                  <td style="padding: 0 10px">Status</td>
                  <td style="padding: 0 10px">
                    <span
                      v-if="alarm.forwarded == true && alarm.alarm_status == 1"
                      >Forwarded</span
                    >
                    <span v-else-if="alarm.alarm_status == 1">Open</span>
                    <span v-else-if="alarm.alarm_status == 0">Closed</span>
                    <v-icon
                      v-if="alarm.forwarded === true && alarm.alarm_status == 1"
                      title="Forwarded"
                      color="orange"
                      style="color: red"
                      >mdi-lock-open-variant-outline</v-icon
                    >

                    <v-icon
                      v-else-if="alarm.alarm_status == 1"
                      title="Open"
                      colo="red"
                      style="color: red"
                      >mdi-lock-open-outline</v-icon
                    >

                    <v-icon
                      v-else-if="alarm.alarm_status == 0"
                      title="Closed"
                      color="green"
                      style="color: red"
                      >mdi-lock-outline</v-icon
                    >
                  </td>
                </tr>
                <tr style="border-bottom: 1px solid #ddd">
                  <td style="padding: 0 10px">Category</td>
                  <td style="padding: 0 10px">
                    <div v-if="alarm.alarm_category == 1">Critical</div>
                    <div v-if="alarm.alarm_category == 2">Medium</div>
                    <div v-if="alarm.alarm_category == 3">Low</div>
                  </td>
                </tr>
                <tr style="border-bottom: 1px solid #ddd">
                  <td style="padding: 0 10px">Start</td>
                  <td style="padding: 0 10px">
                    {{
                      $dateFormat.formatDateMonthYear(
                        alarm.alarm_start_datetime
                      )
                    }}
                  </td>
                </tr>
                <tr style="border-bottom: 1px solid #ddd">
                  <td style="padding: 0 10px">End</td>
                  <td style="padding: 0 10px">
                    <div v-if="alarm.alarm_end_datetime != ''">
                      {{
                        $dateFormat.formatDateMonthYear(
                          alarm.alarm_end_datetime
                        )
                      }}
                    </div>
                    <div v-else>---</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
    <v-divider style="margin-top: 13px"></v-divider>
    <div
      style="text-align: center; padding: 7%"
      v-if="alarm.notes.length == 0 && alarm.alarm_status == 1"
    >
      Operator Notes are not available
    </div>

    <v-row>
      <v-col class="alarm-notes-timeline">
        <template>
          <v-timeline v-if="alarm.notes.length > 0 || alarm.alarm_end_datetime">
            <v-timeline-item
              fill-dot
              color="white"
              v-for="(note, index) in alarm.notes"
              :key="'alarmnotes' + index"
              right
            >
              <template v-slot:opposite style="">
                <div style="padding-right: 10px">
                  {{ $dateFormat.formatDateMonthYear(note.created_datetime) }}
                </div>
                <div
                  v-if="note.contact"
                  style="
                    float: right;
                    width: 140px;
                    margin-right: -25px;
                    font-size: 10px;
                  "
                >
                  <div style="text-align: left">
                    <div class="bold">
                      {{ note.contact?.first_name }}
                      {{ note.contact?.last_name }}
                    </div>
                    <div>
                      <v-icon size="15">mdi-cellphone-basic</v-icon
                      >{{ note.contact.phone1 }}
                    </div>
                    <div>
                      <v-icon size="15">mdi-cellphone-basic</v-icon
                      >{{ note.contact.phone2 }}
                    </div>
                    <div>
                      <v-icon size="15">mdi-at</v-icon>{{ note.contact.email }}
                    </div>
                    <div>
                      <v-icon size="15">mdi-whatsapp</v-icon
                      >{{ note.contact.whatsapp }}
                    </div>
                  </div>
                </div>
              </template>
              <template v-slot:icon>
                <v-avatar>
                  <v-icon
                    v-if="note.event_status == 'Forwarded'"
                    title="Forwarded"
                    color="orange"
                    style="color: red"
                    >mdi-lock-open-variant-outline</v-icon
                  >
                  <v-img
                    v-else-if="note.contact?.profile_picture"
                    style="
                      border-radius: 50%;
                      height: 40px;
                      min-height: 40px;
                      width: 40px;
                      max-width: 40px;
                    "
                    :src="
                      note.contact?.profile_picture
                        ? note.contact?.profile_picture
                        : '/no-business_profile.png'
                    "
                  >
                  </v-img>
                  <v-icon v-else color="red">mdi-account-tie</v-icon>
                </v-avatar>
              </template>
              <v-card class="elevation-0" style="border: 1px solid #ddd">
                <v-card-title style="font-size: 14px">
                  <div v-if="note.event_status == 'Forwarded'">Forwarded</div>
                  <div v-else class="bold">
                    {{ caps(note.contact?.address_type) || "---" }}
                  </div>
                </v-card-title>

                <v-card-text
                  ><span class="bold">Notes: </span>{{ note.notes }}
                  <v-divider
                    v-if="note.event_status != 'Forwarded'"
                  ></v-divider>

                  <v-row v-if="note.event_status != 'Forwarded'">
                    <v-col class="bold">Call Status</v-col>
                    <v-col>: {{ note.call_status || "---" }}</v-col
                    ><v-col class="bold">Call Response</v-col>
                    <v-col>: {{ note.response || "---" }}</v-col
                    ><v-col class="bold">Event Status </v-col>
                    <v-col>: {{ note.event_status || "---" }}</v-col>
                  </v-row>
                </v-card-text>
              </v-card>
            </v-timeline-item>
            <v-timeline-item
              class="close-alarm"
              v-if="alarm.alarm_status == 0"
              fill-dot
              color="white"
              right
            >
              <template v-slot:opposite>
                <div style="padding-right: 10px; color: red">
                  {{
                    $dateFormat.formatDateMonthYear(alarm.alarm_end_datetime)
                  }}
                </div>
              </template>
              <template v-slot:icon>
                <v-avatar>
                  <v-icon color="red">mdi-circle-slice-8</v-icon>
                </v-avatar>
              </template>
              <v-card class="elevation-0" style="border: 1px solid #ddd">
                <v-card-title style="font-size: 14px">
                  Alarm Event Closed at
                  {{
                    $dateFormat.formatDateMonthYear(alarm.alarm_end_datetime)
                  }}
                </v-card-title>

                <v-card-text>
                  <div v-if="alarm.alarm_end_manually == 1">
                    Operator Verified PIN with {{ alarm.pin_verified_by }}
                    <span class="bold">
                      {{
                        alarm.pinverifiedby
                          ? alarm.pinverifiedby.first_name +
                            " " +
                            alarm.pinverifiedby.last_name
                          : "---"
                      }}
                    </span>
                  </div>
                  <div v-else>Auto Closed by Disarm Event</div>
                </v-card-text>
              </v-card>
            </v-timeline-item>
          </v-timeline>
        </template>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  components: {},
  props: ["alarm"],
  data() {
    return {
      timeLineItems: [
        {
          color: "red lighten-2",
          icon: "mdi-star",
        },
        {
          color: "purple darken-1",
          icon: "mdi-book-variant",
        },
        {
          color: "green lighten-1",
          icon: "mdi-airballoon",
        },
        {
          color: "indigo",
          icon: "mdi-buffer",
        },
      ],

      colorcodes: [],
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
    this.colorcodes = this.$utils.getAlarmIcons();
    //this.getDataFromApi();

    if (this.alarm) console.log(this.alarm);
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
    getAlarmColorObject(alarm, customer = null) {
      if (alarm) {
        if (this.colorcodes[alarm.alarm_type.toLowerCase()])
          return this.colorcodes[alarm.alarm_type.toLowerCase()];
        if (alarm.alarm_status == 1) {
          return this.colorcodes.alarm;
        }
      }
      // else if (alarm.alarm_status == 0) {
      //   return this.colorcodes.closed;
      // }
      //if (
      //   alarm.customer &&
      //   this.findanyArmedDevice(alarm.customer.devices)
      // ) {
      //   return this.colorcodes.armed;
      // }
      else if (customer) {
        if (this.findAnyDeviceisOffline(customer.devices) > 0) {
          return this.colorcodes.offline;
        } else if (this.findanyArmedDevice(customer.devices)) {
          return this.colorcodes.armed;
        } else if (this.findanyDisamrDevice(customer.devices) > 0) {
          return this.colorcodes.disarm;
        }
      }
      // console.log(
      //   "findAnyDeviceisOffline",
      //   this.findAnyDeviceisOffline(item.devices)
      // );
      // console.log(alarm.alarm_status);

      return this.colorcodes.offline;
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
