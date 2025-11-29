<template>
  <div max-width="100%">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
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
              name="AlarmForwardEvent"
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
    <v-card flat>
      <v-card-text style="padding: 0px">
        <v-row v-if="globalContactDetails">
          <v-col cols="6">
            <v-row>
              <v-col
                cols="4"
                style="padding-top: 5px"
                v-if="contact_type == 'primary' || contact_type == 'secondary'"
              >
                <v-img
                  style="
                    width: 200px;

                    border-radius: 5%;
                    border: 1px solid #ddd;
                  "
                  :src="
                    globalContactDetails?.profile_picture
                      ? globalContactDetails.profile_picture
                      : '/no-profile-image.jpg'
                  "
                ></v-img>
                <div style="" class="text-center">
                  {{ caps(contact_type) }} Contact
                </div>
              </v-col>
              <v-col
                :cols="
                  contact_type == 'primary' || contact_type == 'secondary'
                    ? 8
                    : 12
                "
                style="line-height: 20px; color: #000"
              >
                <v-row>
                  <v-col cols="12">
                    <v-text-field
                      readonly
                      class=""
                      label="Full Name"
                      dense
                      outlined
                      flat
                      :value="
                        globalContactDetails?.first_name
                          ? globalContactDetails.first_name +
                            ' ' +
                            globalContactDetails.last_name
                          : '---'
                      "
                      hide-details
                    >
                    </v-text-field>
                  </v-col>

                  <v-col cols="12">
                    <v-text-field
                      readonly
                      class=""
                      label="Phone 1"
                      dense
                      outlined
                      flat
                      v-model="globalContactDetails.phone1"
                      hide-details
                    >
                    </v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-text-field
                      readonly
                      class=""
                      label="Phone 2"
                      dense
                      outlined
                      flat
                      v-model="globalContactDetails.phone2"
                      hide-details
                    >
                    </v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-text-field
                      readonly
                      class=""
                      label="Office Phone"
                      dense
                      outlined
                      flat
                      v-model="globalContactDetails.office_phone"
                      hide-details
                    >
                    </v-text-field> </v-col
                  ><v-col cols="12">
                    <v-text-field
                      readonly
                      class=""
                      label="Email"
                      dense
                      outlined
                      flat
                      v-model="globalContactDetails.email"
                      hide-details
                    >
                    </v-text-field>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-col>
          <v-col cols="6" v-if="can('alarms_edit')">
            <!-- <v-row v-if="getUserType() == 'security'"> -->
            <v-row>
              <v-col cols="4">
                <div
                  style="
                    height: 160px;
                    width: 100%;
                    border: 1px solid rgb(157, 157, 157);
                    border-radius: 5px;
                    padding-left: 10px;
                  "
                >
                  <label class="div-border-label">Call Status</label>

                  <v-radio-group
                    class="radiogroup"
                    style="color: black; padding-top: 15px"
                    v-model="event_payload.call_status"
                    @change="updateCallStatus()"
                    @click="updateCallStatus()"
                  >
                    <v-radio
                      label="Answered"
                      value="Answered"
                      style="font-size: 10px"
                    ></v-radio>
                    <v-radio label="No Answer" value="No Answer"></v-radio>
                    <v-radio label="Busy" value="Busy"></v-radio>
                    <v-radio
                      label="Not Reachable "
                      value="Not Reachable "
                    ></v-radio>
                  </v-radio-group>
                </div>
              </v-col>

              <v-col cols="4">
                <div
                  style="
                    height: 160px;
                    width: 100%;
                    border: 1px solid rgb(157, 157, 157);
                    border-radius: 5px;
                    padding-left: 10px;
                  "
                >
                  <label class="div-border-label">Response</label>

                  <v-radio-group
                    style="padding-top: 15px"
                    v-model="event_payload.response"
                    class="radiogroup"
                  >
                    <v-radio
                      :disabled="!displayResponse"
                      label="False alarm"
                      value="False alarm"
                      style="font-size: 10px"
                    ></v-radio>
                    <v-radio
                      :disabled="!displayResponse"
                      label="True alarm"
                      value="True alarm"
                    ></v-radio>
                    <v-radio
                      :disabled="!displayResponse"
                      label="Not aware "
                      value="Not aware"
                    ></v-radio>
                    <v-radio
                      :disabled="!displayResponse"
                      label="Not in Town"
                      value="Not in Town"
                    ></v-radio>
                  </v-radio-group>
                </div>
              </v-col>
              <v-col cols="4">
                <div
                  style="
                    height: 160px;
                    width: 100%;
                    border: 1px solid rgb(157, 157, 157);
                    border-radius: 5px;
                    padding-left: 10px;
                  "
                >
                  <label class="div-border-label">Event satus</label>

                  <v-radio-group
                    style="padding-top: 15px"
                    v-model="event_payload.event_status"
                    class="radiogroup"
                  >
                    <v-radio
                      @click="displayForwardForm()"
                      label="Forwarded"
                      value="Forwarded"
                      style="font-size: 10px"
                    ></v-radio>

                    <v-radio label="Pending" value="Pending"></v-radio>
                    <!-- <v-radio
                      :disabled="!displayFalseAlarm"
                      label="Flase Alarm"
                      value="Flase Alarm"
                    ></v-radio> -->
                    <v-radio
                      :disabled="!displayCloseAlarm"
                      label="Closed"
                      value="Closed"
                    ></v-radio>
                  </v-radio-group>
                </div>
              </v-col>
            </v-row>
            <!-- <div v-if="getUserType() == 'security'"> -->
            <div>
              <v-row
                ><v-col cols="12">
                  <div>
                    <v-textarea
                      outlined
                      class="mt-1"
                      name="input-7-4"
                      label="Action Notes"
                      value=""
                      rows="2"
                      hide-details
                      v-model="event_payload.notes"
                    ></v-textarea>
                  </div> </v-col
              ></v-row>
              <v-row style="margin-top: 3px"
                ><v-col cols="4">
                  <div>
                    <v-text-field
                      :disabled="
                        event_payload.event_status == 'Closed' ? false : true
                      "
                      type="number"
                      min="0"
                      max="10"
                      class=""
                      label="Secret Code"
                      dense
                      outlined
                      flat
                      v-model="event_payload.pin_number"
                      hide-details
                      small
                    >
                    </v-text-field>
                  </div>
                </v-col>
                <v-col cols="4">
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
              </v-row>

              <div>
                <div style="color: green">{{ response }}</div>
                <div
                  v-if="errors && errors.pin_number"
                  class="text-danger mt-2"
                >
                  {{ errors.pin_number[0] }}
                </div>
                <div v-if="errors && errors.notes" class="text-danger mt-2">
                  {{ errors.notes[0] }}
                </div>
                <div
                  v-if="errors && errors.call_status"
                  class="text-danger mt-2"
                >
                  {{ errors.call_status[0] }}
                </div>
                <div
                  v-if="errors && errors.event_status"
                  class="text-danger mt-2"
                >
                  {{ errors.event_status[0] }}
                </div>
                <div v-if="errors && errors.response" class="text-danger mt-2">
                  {{ errors.response[0] }}
                </div>
              </div>
            </div>
          </v-col>
        </v-row>
        <div v-else>
          {{ caps(contact_type) }} contact details are not available
        </div>

        <v-divider class="mb-2 mt-4"></v-divider>

        <v-row>
          <v-col>
            <SecurityAlarmNotes
              v-if="globalContactDetails"
              :alarmId="alarmId"
              :customer="customer"
              :contact_id="globalContactDetails.id"
              :key="key"
            />
            <!-- <v-data-table
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
              <template v-slot:item.picture="{ item }">
                <v-row no-gutters>
                  <v-col
                    @click="viewPhoto(item)"
                    style="
                      padding: 5px;
                      padding-left: 0px;
                      width: 50px;
                      max-width: 50px;
                    "
                  >
                    <v-img
                      style="
                        border-radius: 10%;
                        height: auto;
                        width: 50px;
                        max-width: 50px;
                      "
                      :src="item.picture ? item.picture : '/noimage.png'"
                    >
                    </v-img>
                  </v-col>
                </v-row>
              </template>
              <template v-slot:item.date="{ item, index }">
                {{ $dateFormat.format4(item.created_datetime) }}
              </template>
              <template v-slot:item.security="{ item, index }">
                {{
                  item.security
                    ? item.security.first_name + " " + item.security.last_name
                    : "---"
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

              <template v-slot:item.notes="{ item, index }">
                <span
                  class="d-inline-block text-truncate"
                  style="max-width: 100px"
                  @click="displayNotes(item)"
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
            </v-data-table> -->
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import SecurityGoogleMap from "../../Alarm/SecurityDashboard/SecurityGoogleMap.vue";
import SecurityAlarmNotes from "../../Alarm/SecurityDashboard/SecurityAlarmNotes.vue";
import AlarmForwardEvent from "../AlarmForwardEvent.vue";

export default {
  components: { SecurityGoogleMap, SecurityAlarmNotes },
  props: ["alarmId", "customer", "contact_type"],
  data: () => ({
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
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
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

              this.response = "Alarm Notes Details Created successfully";
              this.snackbar = true;
              this.event_payload = {};
              // this.$emit("closeDialog");
              this.key += 1;
            }
          })
          .catch((e) => {
            if (e.response.data.errors) {
              this.errors = e.response.data.errors;
              this.color = "red";
              this.snackbar = true;
              this.response = "Some fileds are missing";
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
