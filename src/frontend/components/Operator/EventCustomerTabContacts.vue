<template>
  <div max-width="100%">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" :color="color" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="dialogOperatorNotes" max-width="400px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense style="color: black3333"
            >Operator Comments #{{ alarmId }}</span
          >
          <v-spacer></v-spacer>
          <v-icon @click="dialogOperatorNotes = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <v-row>
            <v-col>
              <v-row>
                <v-col>
                  <table class="eventcustomertabselect" style="width: 100%">
                    <tr>
                      <!-- <td>Call Status</td> -->
                      <td style="width: 60%; padding-top: 5px">
                        <label>Call Status</label>
                        <v-select
                          class="employee-schedule-search-box font10"
                          height="20px"
                          outlined
                          v-model="event_payload.call_status"
                          dense
                          hide-details
                          @change="updateResponseList()"
                          :items="[
                            null,
                            'Answered',
                            'No Answer',
                            'Busy',
                            'Not Reachable',
                          ]"
                        ></v-select>
                      </td>
                    </tr>
                    <tr>
                      <!-- <td>Response</td> -->
                      <td style="padding-top: 5px">
                        <label>Response</label>
                        <v-select
                          class="employee-schedule-search-box font10"
                          height="20px"
                          outlined
                          v-model="event_payload.response"
                          dense
                          hide-details
                          :items="responseList"
                          @change="updateAlarmStatusList()"
                        ></v-select>
                      </td>
                    </tr>

                    <tr>
                      <!-- <td>Alarm Status</td> -->
                      <td style="padding-top: 5px">
                        <label>Alarm Status</label>
                        <v-select
                          class="employee-schedule-search-box font10"
                          height="20px"
                          outlined
                          v-model="event_payload.event_status"
                          dense
                          hide-details
                          :items="alarmStatusList"
                        ></v-select>
                      </td>
                    </tr>
                    <tr v-if="event_payload.event_status == 'Closed'">
                      <!-- <td>Contact Secret Code</td> -->
                      <td style="padding-top: 5px">
                        <label class="bold">
                          {{
                            contact_type && contact_type == "primary"
                              ? "Primary"
                              : "Secondary"
                          }}
                          Contact Secret Code</label
                        >
                        <v-text-field
                          :disabled="
                            event_payload.event_status == 'Closed'
                              ? false
                              : true
                          "
                          min="0"
                          max="10"
                          class="employee-schedule-search-box font10"
                          dense
                          outlined
                          flat
                          height="20px"
                          v-model="event_payload.pin_number"
                          hide-details
                          small
                          style="
                            margin-right: -10px;
                            font-size: 11px;

                            padding-top: 0px;
                          "
                        >
                        </v-text-field>
                      </td>
                    </tr>
                  </table>
                </v-col>
              </v-row>
              <v-row v-if="event_payload.event_status == 'Forwarded'">
                <v-col>
                  <div
                    style="
                      width: 100%;
                      border: 0px solid rgb(157, 157, 157);
                      border-radius: 5px;
                      padding-left: 0px;
                    "
                  >
                    <label style="font-size: 11px"> Forward Event to.. </label>
                    <v-row style="margin-top: 0px; padding-bottom: 0px">
                      <v-col
                        :key="'customercontacts' + index + 20"
                        cols="3"
                        style="padding-bottom: 0px; padding-top: 0px"
                        v-if="
                          customer &&
                          contact.address_type.toLowerCase() != 'primary' &&
                          contact.address_type.toLowerCase() != 'secondary' &&
                          contact.address_type.toLowerCase() != 'security'
                        "
                        v-for="(contact, index) in customer?.contacts"
                      >
                        <v-checkbox
                          class="radiogroup radiogroup-small"
                          style="font-size: 12px"
                          v-model="contact.forwarded"
                          :label="contact.address_type"
                        ></v-checkbox
                      ></v-col>
                    </v-row>
                  </div>
                </v-col>
              </v-row>
              <v-row>
                <v-col class="pr-0">
                  <label>Operator Comments</label>

                  <v-textarea
                    outlined
                    class="mt-0 white-color1 font13label"
                    value=""
                    height="100px"
                    hide-details
                    v-model="event_payload.notes"
                    style="font-size: 12px"
                  ></v-textarea>
                </v-col>
              </v-row>
              <v-row>
                <v-col class="text-center"
                  ><v-btn
                    @click="dialogOperatorNotes = false"
                    class="mt-1"
                    small
                    color="#203864"
                    style="
                      margin: auto;
                      margin-top: -10px;
                      width: 100px;
                      color: black;
                    "
                    >Cancel</v-btn
                  ></v-col
                ><v-col class="text-center"
                  ><v-btn
                    @click="submit"
                    class="mt-1"
                    small
                    color="#203864"
                    style="
                      margin: auto;
                      margin-top: -10px;
                      width: 100px;
                      color: #fff;
                      background-color: #007d1f;
                    "
                    >Submit</v-btn
                  ></v-col
                >
              </v-row>
              <!--<v-row>
                <v-col>
                  <v-row v-if="globalContactDetails" class="mt-1">
                    <v-col cols="6" class="pr-1">
                       <v-text-field
                        v-if="event_payload.event_status == 'Closed'"
                        :disabled="
                          event_payload.event_status == 'Closed' ? false : true
                        "
                        min="0"
                        max="10"
                        class="input-small-fieldset1 mt-1"
                        label="Secret Code"
                        dense
                        outlined
                        flat
                        v-model="event_payload.pin_number"
                        hide-details
                        small
                        style="
                          width: 130px;
                          float: right;
                          margin-right: -10px;
                          font-size: 11px;
                          height: 20px;
                          padding-top: 0px;
                        "
                      >
                      </v-text-field>
                    </v-col>
                    <v-col cols="6" class="pl-0">
                      <v-btn
                        @click="submit"
                        class="mt-1"
                        small
                        color="#203864"
                        style="
                          margin: auto;
                          margin-top: -10px;
                          width: 100px;
                          color: #00b8fb;
                        "
                        >Submit</v-btn
                      >
                    </v-col>
                  </v-row> -->

              <div>
                <div v-if="response != ''" style="color: green">
                  {{ response }}
                </div>
                <div
                  v-else-if="errors && errors.pin_number"
                  class="text-danger mt-2"
                >
                  {{ errors.pin_number[0] }}
                </div>
                <div
                  v-else-if="errors && errors.notes"
                  class="text-danger mt-2"
                >
                  {{ errors.notes[0] }}
                </div>
                <div
                  v-else-if="errors && errors.call_status"
                  class="text-danger mt-2"
                >
                  {{ errors.call_status[0] }}
                </div>
                <div
                  v-else-if="errors && errors.event_status"
                  class="text-danger mt-2"
                >
                  {{ errors.event_status[0] }}
                </div>
                <div
                  v-else-if="errors && errors.response"
                  class="text-danger mt-2"
                >
                  {{ errors.response[0] }}
                </div>
              </div>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogNotes" max-width="700px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense style="color: black3333">Notes</span>
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
          {{ selectedItem ? selectedItem.notes : "---" }}
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogForwardEventDetails" width="800px">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span>Alarm - Forward Details #{{ eventId }}</span>
          <v-spacer></v-spacer>
          <v-icon @click="dialogForwardEventDetails = false" outlined>
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
              @closeDialog="closeDialog()"
              :alarm_id="eventId"
              :popupEventText="'Test'"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-card elevation="6">
      <v-card-text
        :style="
          'margin-top: 5px;height:' + parseInt(browserHeight - 380) + 'px'
        "
      >
        <!-- <div v-if="!globalContactDetails">
          Primary or Secondary Contact Details are not available
        </div> -->
        <v-row>
          <v-col
            style="
              margin: auto;
              padding-top: 0px;
              max-width: 60px;
              padding: 0px;
            "
          >
            <v-img
              style="
                border: 1px solid #ddd;
                margin: auto;
                border-radius: 50%;
                width: 60px;
                border: 1px solid #ddd;
              "
              :src="
                globalContactDetails?.profile_picture
                  ? globalContactDetails.profile_picture
                  : '/no-profile-image.jpg'
              "
            ></v-img>
          </v-col>
          <v-col style="margin: auto; padding-top: 0px">
            <div class="bold">
              {{
                globalContactDetails?.first_name
                  ? $utils.caps(globalContactDetails.first_name) +
                    " " +
                    $utils.caps(globalContactDetails.last_name)
                  : "---"
              }}
            </div>
            <div>{{ contact_type }}</div>
          </v-col>
        </v-row>
        <v-row>
          <v-col style="padding: 0px; padding-top: 10px">
            <table class="eventcustomertab" style="width: 100%">
              <!-- <tr style="font-weight: bold">
                <td>
                  <v-icon color="2038c011111" size="16" class="pr-6"
                    >mdi-account-tie</v-icon
                  >

                  {{
                    globalContactDetails?.first_name
                      ? $utils.caps(globalContactDetails.first_name) +
                        " " +
                        $utils.caps(globalContactDetails.last_name)
                      : "---"
                  }}
                </td>
              </tr> -->
              <tr>
                <td>
                  <v-icon color="2038c011111" size="16" class="pr-6"
                    >mdi-cellphone-basic</v-icon
                  >
                  <!-- Mobile -->
                  <!-- </td>
                <td> -->
                  {{
                    globalContactDetails?.phone1 &&
                    globalContactDetails.phone1 != "null"
                      ? globalContactDetails.phone1
                      : "---"
                  }}
                </td>
              </tr>
              <tr>
                <td>
                  <v-icon color="2038c011111" size="16" class="pr-6"
                    >mdi-phone-classic</v-icon
                  >
                  <!-- Phone -->
                  <!-- </td>
                <td> -->
                  {{
                    globalContactDetails?.phone2 &&
                    globalContactDetails.phone2 != "null"
                      ? globalContactDetails.phone2
                      : "---"
                  }}
                </td>
              </tr>
              <tr>
                <td>
                  <v-icon color="2038c011111" size="16" class="pr-6"
                    >mdi-whatsapp</v-icon
                  >
                  <!-- Whatsapp -->
                  <!-- </td>
                <td> -->
                  {{
                    globalContactDetails?.whatsapp &&
                    globalContactDetails.whatsapp != "null"
                      ? globalContactDetails.whatsapp
                      : "---"
                  }}
                </td>
              </tr>
              <tr>
                <td>
                  <v-icon color="2038c011111" size="16" class="pr-6"
                    >mdi-at</v-icon
                  >
                  <!-- E-Mail -->
                  <!-- </td>
                <td> -->
                  {{
                    globalContactDetails?.email &&
                    globalContactDetails.email != "null"
                      ? globalContactDetails.email
                      : "---"
                  }}
                </td>
              </tr>
              <tr>
                <td>
                  <v-icon color="2038c011111" size="16" class="pr-6"
                    >mdi-message-bulleted</v-icon
                  >
                  <!-- Notes -->
                  <!-- </td>
                <td> -->
                  {{
                    globalContactDetails?.notes &&
                    globalContactDetails.notes != "null"
                      ? globalContactDetails.notes
                      : "---"
                  }}
                </td>
              </tr>
            </table>
          </v-col>
        </v-row>

        <v-row v-if="qrcode == null">
          <v-col class="text-center">
            <v-btn
              @click="dialogOperatorNotes = true"
              class="mt-4"
              dense
              small
              color="#203864"
              style="margin: auto; font-size: 12px; width: 100px; color: #fff"
              :disabled="alarm && alarm.alarm_status == 0"
            >
              Comments</v-btn
            ></v-col
          >
        </v-row>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import AlarmForwardEvent from "../Alarm/AlarmForwardEvent.vue";

export default {
  components: {},
  props: [
    "alarmId",
    "alarm",
    "customer",
    "contact_type",
    "browserHeight",
    "qrcode",
  ],
  data: () => ({
    responseList: [
      null,
      "False alarm",
      // 'No Answer',
      "Busy",
      "Not Reachable",
    ],
    alarmStatusList: [null, "Forwarded", "Closed", "Pending", "Not in Town"],
    dialogOperatorNotes: false,
    selectContactButton: null,
    filteredContactInfo: [],
    dialogForwardEventDetails: false,
    eventId: null,
    customer_id: null,
    displayCloseAlarm: false,
    displayFalseAlarm: true,
    displayResponse: true,
    snackbar: false,
    key: 1,
    dialogNotes: false,
    selectedItem: null,
    loading: false,
    tab: "",
    event_payload: {
      event_status: null,
      call_status: null,
      response: null,
    },
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
      { text: "Date", value: "date", sortable: false },
      { text: "Security", value: "security", sortable: false },
      { text: "Customer", value: "customer", sortable: false },
      { text: "Phone", value: "phone", sortable: false },
      { text: "Call satus", value: "call_status", sortable: false },

      { text: "Response", value: "response", sortable: false },

      { text: "Notes", value: "notes", sortable: false },
      { text: "Event Status", value: "event_status", sortable: false },
      // { text: "Status", value: "status", sortable: false },
    ],
    items: [],
    globalContactDetails: null,
    color: "",
  }),
  computed: {},
  mounted() {},
  created() {
    if (this.customer) {
      if (this.contact_type == "primary") {
        this.globalContactDetails = this.customer.primary_contact;
      } else if (this.contact_type == "secondary") {
        this.globalContactDetails = this.customer.secondary_contact;
      } else {
        let Contacts = this.customer.contacts.filter(
          (e) => e.address_type.toLowerCase() == this.contact_type.toLowerCase()
        );
        if (Contacts.length > 0) {
          this.globalContactDetails = Contacts[0];
        }
      }
      let filterContacts = [];
      if (this.customer.contacts.length > 0)
        filterContacts = this.customer.contacts.filter(
          (contact) =>
            contact.address_type.toLowerCase() != "primary" &&
            contact.address_type.toLowerCase() != "secondary" &&
            contact.address_type.toLowerCase() != "security"
        );
      if (filterContacts.length > 0) {
        this.selectContactButton = filterContacts[0].id;

        this.displayContactInfoById(this.selectContactButton);
      }
    }
  },
  watch: {
    // options: {
    //   handler() {
    //     if (this.globalContactDetails) this.getDataFromApi();
    //   },
    //   deep: true,
    // },
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
    updateAlarmStatusList() {
      if (
        this.event_payload.response == "False alarm" ||
        this.event_payload.response == "True Alarm" ||
        this.event_payload.response == "Manually Closed Alarm" ||
        this.event_payload.response == "Not in Town"
      ) {
        this.alarmStatusList = [null, "Closed", "Pending", "Forwarded"];
      }
    },
    updateResponseList() {
      if (this.event_payload.call_status == "Answered") {
        this.responseList = [
          null,
          "False alarm",
          "True Alarm",
          "Manually Closed Alarm",
          "Not in Town",
        ];

        this.alarmStatusList = [null, "Forwarded", "Closed", "Pending"];
      } else {
        this.responseList = [this.event_payload.call_status];
        this.event_payload.response = this.event_payload.call_status;

        this.alarmStatusList = ["Pending", "Forwarded"];
        this.event_payload.event_status = "Pending";
      }
    },
    displayContactInfoById(contactId) {
      if (this.customer)
        this.filteredContactInfo = this.customer.contacts.find(
          (contact) => contact.id == contactId
        );
    },
    closeDialog() {
      this.key++;
    },
    updateCallStatus() {
      this.displayResponse = true;
      this.displayFalseAlarm = true;
      this.displayFalseAlarm = true;
      this.displayCloseAlarm = true;

      if (this.event_payload.call_status != "Answered") {
        this.event_payload.response = null;
        this.event_payload.event_status = null;

        this.displayFalseAlarm = false;
        this.displayCloseAlarm = false;

        this.displayResponse = false;
        this.displayFalseAlarm = false;
      }
    },
    getUserType() {
      if (this.$auth.user) {
        const user = this.$auth.user;
        const userType = user.user_type;

        return userType;
      }
      return "";
    },
    displayNotes(item) {
      this.selectedItem = item;
      this.dialogNotes = true;
    },
    displayForwardForm() {
      this.eventId = this.alarmId;
      this.customer_id = this.customer.id;
      this.dialogForwardEventDetails = true;
    },
    // getDataFromApi() {
    //   let { sortBy, sortDesc, page, itemsPerPage } = this.options;

    //   let sortedBy = sortBy ? sortBy[0] : "";
    //   let sortedDesc = sortDesc ? sortDesc[0] : "";
    //   if (itemsPerPage) this.perPage = itemsPerPage;
    //   if (page) this.currentPage = page;

    //   this.loading = true;
    //   let options = {
    //     params: {
    //       page: page,
    //       sortBy: sortedBy,
    //       sortDesc: sortedDesc,
    //       perPage: itemsPerPage,
    //       pagination: true,
    //       company_id: this.$auth.user.company_id,
    //       customer_id: this.customer.customer_id,
    //       contact_id: this.globalContactDetails.id,
    //       alarm_id: this.alarmId,
    //     },
    //   };

    //   try {
    //     this.$axios.get(`get_alarm_events_notes`, options).then(({ data }) => {
    //       this.items = data.data;
    //       this.totalRowsCount = data.total;

    //       this.loading = false;
    //     });
    //   } catch (e) {}
    // },
    submit() {
      let customer = new FormData();

      if (this.event_payload.call_status == null) {
        alert("Select Call Status");
        return false;
      }
      if (this.event_payload.event_status == null) {
        alert("Select Alarm Status");
        return false;
      }
      if (this.event_payload.response == null) {
        alert("Select Response");
        return false;
      }
      if (this.event_payload.notes == null) {
        alert("Operator Comments information is required");
        return false;
      }

      for (const key in this.event_payload) {
        if (this.event_payload[key] != "" && this.event_payload[key] != null)
          customer.append(key, this.event_payload[key]);
      }
      let filterEventForwardSelected = this.customer.contacts.filter(
        (event) => event.forwarded == true
      );

      let selectedForwardContactIds = filterEventForwardSelected.map(
        (event) => event.id
      );

      if (this.$auth.user.security?.id)
        customer.append("security_id", this.$auth.user.security.id);
      customer.append("company_id", this.$auth.user.company_id);
      customer.append("customer_id", this.customer.id);
      customer.append("contact_id", this.globalContactDetails.id);
      customer.append("alarm_id", this.alarmId);
      customer.append("contact_type", this.contact_type);
      selectedForwardContactIds.forEach((element) => {
        customer.append("selected_forward_contact_ids[]", element);
      });

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

              this.response = "Alarm Notes Details Created successfully";
              this.snackbar = true;
              this.event_payload = {};
              //this.$emit("closeDialog");
              this.key += 1;

              setTimeout(() => {
                this.dialogOperatorNotes = false;

                this.$emit("emitreloadEventNotesStep1");
              }, 1000 * 1);

              setTimeout(() => {
                this.$emit("emitreloadEventNotesStep1");
              }, 1000 * 2);
            }
          })
          .catch((e) => {
            if (e.response.data.errors) {
              this.errors = e.response.data.errors;
              this.color = "red";
              this.snackbar = true;
              //this.response = "Some fileds are missing";
              this.snackbar = true;
              this.response = e.response.data.message;
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
