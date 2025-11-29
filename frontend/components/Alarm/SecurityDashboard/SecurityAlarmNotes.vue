<template>
  <div max-width="100%">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="dialogNotes" max-width="700px" :key="key">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense style="color: black3333"
            >Alarm Notes - ID: {{ selectedItem?.alarm_id || "---" }}</span
          >
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="dialogNotes = false"
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <v-row v-if="selectedItem" class="mt-2">
            <!-- <v-col cols="6">
              <v-text-field
                readonly
                class=""
                label="Event ID"
                dense
                outlined
                flat
                :value="selectedItem.alarm_id"
                hide-details
              >
              </v-text-field>
            </v-col> -->
            <v-col cols="6">
              <v-text-field
                readonly
                class=""
                label="Date"
                dense
                outlined
                flat
                :value="selectedItem.created_datetime"
                hide-details
              >
              </v-text-field> </v-col
            ><v-col cols="6">
              <v-text-field
                readonly
                class=""
                label="Security Login Name"
                dense
                outlined
                flat
                :value="
                  selectedItem.security
                    ? selectedItem.security.first_name +
                      ' ' +
                      selectedItem.security?.last_name
                    : '---'
                "
                hide-details
              >
              </v-text-field> </v-col
            ><v-col cols="6">
              <v-text-field
                readonly
                class=""
                label="Closed PIN Customer Name"
                dense
                outlined
                flat
                :value="
                  selectedItem.contact
                    ? selectedItem.contact.first_name +
                      ' ' +
                      selectedItem.contact?.last_name
                    : '---'
                "
                hide-details
              >
              </v-text-field> </v-col
            ><v-col cols="6">
              <v-text-field
                readonly
                class=""
                label="Closed PIN Customer Type"
                dense
                outlined
                flat
                :value="selectedItem.contact?.address_type || '---'"
                hide-details
              >
              </v-text-field> </v-col
            ><v-col cols="6">
              <v-text-field
                readonly
                class=""
                label="Phone"
                dense
                outlined
                flat
                :value="selectedItem.contact?.phone1 || '---'"
                hide-details
              >
              </v-text-field> </v-col
            ><v-col cols="6">
              <v-text-field
                readonly
                class=""
                label="Call Status"
                dense
                outlined
                flat
                :value="selectedItem.call_status"
                hide-details
              >
              </v-text-field> </v-col
            ><v-col cols="6">
              <v-text-field
                readonly
                class=""
                label="Response"
                dense
                outlined
                flat
                :value="selectedItem.response"
                hide-details
              >
              </v-text-field>
            </v-col>
            <v-col cols="6">
              <v-text-field
                readonly
                class=""
                label="Event/Alarm Status"
                dense
                outlined
                flat
                :value="selectedItem.event_status"
                hide-details
              >
              </v-text-field>
            </v-col>

            <v-col cols="12">
              <v-textarea
                outlined
                class="mt-2"
                name="input-7-4"
                label="Action Notes"
                value=""
                rows="2"
                hide-details
                v-model="selectedItem.notes"
              ></v-textarea>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-card flat>
      <v-card-text style="padding: 0px">
        <v-row>
          <v-col cols="12" class="text-right" style="padding-top: 0px">
            <v-row>
              <v-col></v-col>
              <v-col class="text-right">
                <v-row>
                  <v-col class="mt-2">
                    <v-icon @click="getDataFromApi()">mdi-refresh</v-icon>
                  </v-col>
                  <v-col
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
                  <v-col>
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
              <!--<v-col cols="1" class="text-left pt-5 pl-0">
                <v-menu bottom right>
                  <template v-slot:activator="{ on, attrs }">
                    <span v-bind="attrs" v-on="on" style="font-size: 16px">
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
              </v-col>-->
            </v-row>
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <v-data-table
              :style="
                contact_id
                  ? 'height: 300px; overflow: auto'
                  : 'height: 600px; overflow: auto'
              "
              :headers="headers"
              :items="items"
              :server-items-length="totalRowsCount"
              :loading="loading"
              :options.sync="options"
              :footer-props="{
                itemsPerPageOptions: [100, 500, 1000],
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

              <template v-slot:item.date="{ item, index }">
                {{ $dateFormat.format4(item.created_datetime) }}
              </template>
              <template v-slot:item.security="{ item, index }">
                {{
                  item.security
                    ? item.security.first_name + " " + item.security.last_name
                    : "Admin"
                }}
              </template>
              <template v-slot:item.customer="{ item, index }">
                {{
                  item.contact
                    ? item.contact.first_name + " " + item.contact.last_name
                    : "---"
                }}
                <div class="secondary-value">
                  {{ item.contact ? caps(item.contact.address_type) : "---" }}
                </div>
              </template>
              <template v-slot:item.phone="{ item, index }">
                {{ item.contact ? item.contact.phone1 : "---" }}
              </template>
              <template v-slot:item.response="{ item, index }">
                {{
                  item.response != "null" &&
                  item.response != "" &&
                  item.response != null
                    ? item.response
                    : "---"
                }}
              </template>
              <template v-slot:item.call_status="{ item, index }">
                {{
                  item.call_status != "null" &&
                  item.call_status != "" &&
                  item.call_status != null
                    ? item.call_status
                    : "---"
                }}
              </template>
              <template v-slot:item.notes="{ item, index }">
                <span
                  class="d-inline-block text-truncate"
                  style="max-width: 100px"
                  >{{ item.notes }}</span
                >
              </template>

              <template v-slot:item.event_status="{ item, index }">
                <div style="color: red" v-if="item.event_status != 'Closed'">
                  {{ item.event_status }}
                </div>
                <div style="color: green" v-else>
                  {{ item.event_status }}
                </div>
              </template>
              <template v-slot:item.action="{ item, index }">
                <v-icon @click="displayNotes(item)" color="black"
                  >mdi mdi-information-slab-circle</v-icon
                >
              </template>
            </v-data-table>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
export default {
  components: {},
  props: ["alarmId", "customer", "contact_type", "contact_id"],
  data: () => ({
    snackbar: false,

    dialogNotes: false,
    selectedItem: null,
    loading: false,
    tab: "",
    event_payload: {},
    error_message: "",
    errors: [],
    response: "",
    options: { perPage: 10 },
    cumulativeIndex: 1,
    perPage: 10,
    currentPage: 1,
    totalRowsCount: 0,
    headers: [
      { text: "#", value: "sno", sortable: false },
      { text: "Event Status", value: "event_status", sortable: false },
      { text: "Call satus", value: "call_status", sortable: false },

      { text: "Response", value: "response", sortable: false },

      { text: "Notes", value: "notes", sortable: false },
      { text: "Operator ID", value: "security", sortable: false },
      { text: "Contacted", value: "customer", sortable: false },
      { text: "Phone", value: "phone", sortable: false },
      { text: "Actions", value: "action", sortable: false },
      { text: "Date", value: "date", sortable: false },
      // { text: "Status", value: "status", sortable: false },
    ],
    items: [],
    globalContactDetails: null,
    key: 1,
    date_from: "",
    date_to: "",
    commonSearch: "",
  }),
  computed: {},
  mounted() {},
  created() {
    let today = new Date();
    let monthObj = this.$dateFormat.monthStartEnd(today);
    this.date_from = monthObj.first;
    this.date_to = monthObj.last;
  },
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  methods: {
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    displayNotes(item) {
      this.key += 1;
      this.selectedItem = item;
      this.dialogNotes = true;
    },
    filterAttr(data) {
      this.date_from = data.from;
      this.date_to = data.to;

      this.getDataFromApi();
    },
    downloadOptions(option) {
      let url = process.env.BACKEND_URL;
      if (option == "print") url += "/security_alarm_notes_print_pdf";
      if (option == "excel") url += "/security_alarm_notes_export_excel";
      if (option == "download") url += "/security_alarm_notes_download_pdf";
      url += "?company_id=" + this.$auth.user.company_id;
      url += "&date_from=" + this.date_from;
      url += "&date_to=" + this.date_to;
      url += "&alarm_id=" + this.alarmId;
      if (this.commonSearch) url += "&common_search=" + this.commonSearch;

      //  url += "&alarm_status=" + this.filterAlarmStatus;

      window.open(url, "_blank");
    },
    getDataFromApi() {
      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";
      if (itemsPerPage) this.perPage = itemsPerPage;
      if (page) this.currentPage = page;

      this.loading = true;
      let options = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          perPage: itemsPerPage,
          pagination: true,
          company_id: this.$auth.user.company_id,
          customer_id: this.customer.customer_id,
          //contact_id: this.contact_id,
          alarm_id: this.alarmId,
          date_from: this.date_from,
          date_to: this.date_to,
          common_search: this.commonSearch,
        },
      };

      // try {
      this.$axios.get(`get_alarm_events_notes`, options).then(({ data }) => {
        //this.key += 1;
        this.items = data.data;
        this.totalRowsCount = data.total;

        this.loading = false;
      });
      //   } catch (e) {}
    },
    submit() {
      let customer = new FormData();

      for (const key in this.event_payload) {
        if (this.event_payload[key] != "")
          customer.append(key, this.event_payload[key]);
      }
      if (this.$auth.user.security?.id)
        customer.append("security_id", this.$auth.user.security.id);
      customer.append("company_id", this.$auth.user.company_id);
      customer.append("customer_id", this.customer.id);
      customer.append("contact_id", this.globalContactDetails.id);
      customer.append("alarm_id", this.alarmId);
      customer.append("contact_type", this.contact_type);

      if (this.customer.id) {
        this.$axios
          .post("/customer_add_event_notes", customer)
          .then(({ data }) => {
            this.response = "";
            //this.loading = false;

            if (!data.status) {
              this.errors = [];
              if (data.errors) this.errors = data.errors;
              this.color = "red";
            } else {
              this.error_message = "";
              this.color = "background";
              this.errors = [];
              this.snackbar = true;
              this.response = "Alarm Notes Details Created successfully";

              this.event_payload = {};
              // this.$emit("closeDialog");
              //if (this.globalContactDetails)

              this.getDataFromApi();
            }
          })
          .catch((e) => {
            if (e.response.data.errors) {
              this.errors = e.response.data.errors;
              this.color = "red";

              this.snackbar = true;
              //this.response = e.response.data.message;
              if (this.errors.message) {
                this.color = "red";
                this.snackbar = true;
                this.response = this.errors.messag;
                this.error_message = this.errors.message;
              }

              // this.errors.forEach((element) => {
              //   console.log(element);
              //   this.color = "red";
              //   this.snackbar = true;
              //   this.response = element[0];
              //   this.error_message = element[0];
              //   return false;
              // });
            }
          });
      }
    },
  },
};
</script>
