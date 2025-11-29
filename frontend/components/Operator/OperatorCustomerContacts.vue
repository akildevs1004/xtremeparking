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
    <v-card flat>
      <v-card-text style="margin-top: 25px">
        <v-row>
          <v-col style="margin: auto; max-width: 80px">
            <v-img
              style="width: 80px; border-radius: 50%; border: 1px solid #ddd"
              :src="
                globalContactDetails?.profile_picture
                  ? globalContactDetails.profile_picture
                  : '/no-profile-image.jpg'
              "
            ></v-img>
          </v-col>
          <v-col style="font-size: 12px; padding: 0px; overflow: hidden">
            <div style="font-weight: bold">
              <v-icon size="13" style="border: 0px solid #ddd"
                >mdi-account</v-icon
              >
              {{
                globalContactDetails?.first_name
                  ? globalContactDetails.first_name +
                    " " +
                    globalContactDetails.last_name
                  : "---"
              }}
            </div>
            <div>
              <v-icon style="border: 0px solid #ddd" size="13"
                >mdi-phone</v-icon
              >
              {{ globalContactDetails.phone1 }}
            </div>
            <div>
              <v-icon style="border: 0px solid #ddd" size="13"
                >mdi-phone-classic</v-icon
              >
              {{ globalContactDetails.phone2 }}
            </div>
            <div>
              <v-icon style="border: 0px solid #ddd" size="13"
                >mdi-cellphone-basic</v-icon
              >
              {{ globalContactDetails.whatsapp }}
            </div>
            <div>
              <v-icon style="border: 0px solid #ddd" size="13">mdi-at</v-icon>
              {{ globalContactDetails.email }}
            </div>
          </v-col>
          <v-col cols="4" style="padding-left: 0px; padding-top: 0px">
            <div
              style="
                overflow: auto;
                height: 100px;
                width: 100%;
                border: 1px solid rgb(157, 157, 157);
                border-radius: 5px;
                padding-left: 0px;
              "
            >
              <label
                style="
                  position: absolute;
                  margin-top: -12px;
                  margin-left: 12px;
                  background: #fff;
                  padding: 0px 5px;
                  font-size: 12px;
                "
                >Customer Notes</label
              >

              <div style="padding-top: 10px">
                {{ globalContactDetails.notes }}
              </div>
            </div>
          </v-col>
        </v-row>

        <v-row style="font-size: 12px; margin-top: 10px" class="radio-group">
          <v-col cols="4" style="padding-right: 0px">
            <div
              style="
                height: 160px;
                width: 100%;
                border: 1px solid rgb(157, 157, 157);
                border-radius: 5px;
                padding-left: 0px;
              "
            >
              <label
                style="
                  position: absolute;
                  margin-top: -12px;
                  margin-left: 12px;
                  background: #fff;
                  padding: 0px 5px;
                "
                >Call Status
              </label>

              <v-radio-group
                class="radiogroup radiogroup-small"
                style="padding-top: 10px"
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

          <v-col cols="4" style="padding-right: 0px">
            <div
              style="
                height: 160px;
                width: 100%;
                border: 1px solid rgb(157, 157, 157);
                border-radius: 5px;
                padding-left: 0px;
              "
            >
              <label
                style="
                  position: absolute;
                  margin-top: -12px;
                  margin-left: 12px;
                  background: #fff;
                  padding: 0px 5px;
                "
                >Response</label
              >

              <v-radio-group
                v-model="event_payload.response"
                class="radiogroup radiogroup-small"
              >
                <v-radio
                  :disabled="!displayResponse"
                  label="False alarm"
                  value="False alarm"
                  style="font-size: 10px; padding-top: 10px"
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
                padding-left: 0px;
              "
            >
              <label
                style="
                  position: absolute;
                  margin-top: -12px;
                  margin-left: 12px;
                  background: #fff;
                  padding: 0px 5px;
                "
                >Event satus</label
              >

              <v-radio-group
                v-model="event_payload.event_status"
                class="radiogroup radiogroup-small"
              >
                <v-radio
                  label="Forwarded"
                  value="Forwarded"
                  style="font-size: 10px; padding-top: 10px"
                ></v-radio>

                <v-radio label="Pending" value="Pending"></v-radio>

                <v-radio
                  :disabled="!displayCloseAlarm"
                  label="Closed"
                  value="Closed"
                ></v-radio>
              </v-radio-group>
            </div>
          </v-col>
        </v-row>

        <v-row>
          <v-col>
            <div
              style="
                width: 100%;
                border: 1px solid rgb(157, 157, 157);
                border-radius: 5px;
                padding-left: 0px;
              "
            >
              <label
                style="
                  position: absolute;
                  margin-top: -12px;
                  margin-left: 12px;
                  background: #fff;
                  padding: 0px 5px;
                "
                >Select to Event Forward
              </label>
              <v-row style="margin-top: 10px; padding-bottom: 10px">
                <v-col
                  :key="'customercontacts' + index + 20"
                  cols="3"
                  style="padding-bottom: 0px; padding-top: 0px"
                  v-if="
                    contact.address_type.toLowerCase() != 'primary' &&
                    contact.address_type.toLowerCase() != 'secondary' &&
                    contact.address_type.toLowerCase() != 'security'
                  "
                  v-for="(contact, index) in customer.contacts"
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

        <v-row
          ><v-col>
            <div
              style="
                width: 100%;
                border: 1px solid rgb(157, 157, 157);
                border-radius: 5px;
              "
            >
              <v-row
                style="height: 160px; overflow-y: auto; overflow-x: hidden"
              >
                <v-col cols="8" style="padding: 30px">
                  <v-row
                    v-if="filteredContactInfo"
                    :key="filteredContactInfo.id"
                  >
                    <!-- <v-col cols="5" style="margin: auto">
                      <v-img
                        style="
                          width: 100%;

                          border-radius: 5%;
                          border: 1px solid #ddd;
                        "
                        :src="
                          filteredContactInfo?.profile_picture
                            ? filteredContactInfo.profile_picture
                            : '/no-profile-image.jpg'
                        "
                      ></v-img>
                    </v-col> -->
                    <v-col
                      cols="12"
                      style="font-size: 12px; padding: 0px; overflow: hidden"
                    >
                      <div style="font-weight: bold">
                        <v-icon size="13" style="border: 0px solid #ddd"
                          >mdi-account</v-icon
                        >
                        {{
                          filteredContactInfo?.first_name
                            ? filteredContactInfo.first_name +
                              " " +
                              filteredContactInfo.last_name
                            : "---"
                        }}
                      </div>
                      <div>
                        <v-icon style="border: 0px solid #ddd" size="13"
                          >mdi-phone</v-icon
                        >
                        {{ filteredContactInfo.phone1 }}
                      </div>
                      <div>
                        <v-icon style="border: 0px solid #ddd" size="13"
                          >mdi-phone-classic</v-icon
                        >
                        {{ filteredContactInfo.phone2 }}
                      </div>
                      <div>
                        <v-icon style="border: 0px solid #ddd" size="13"
                          >mdi-whatsapp</v-icon
                        >
                        {{ filteredContactInfo.whatsapp }}
                      </div>
                      <div>
                        <v-icon style="border: 0px solid #ddd" size="13"
                          >mdi-at</v-icon
                        >
                        {{ filteredContactInfo.email }}
                      </div>
                    </v-col>
                  </v-row>
                </v-col>
                <v-col cols="4" style="text-align: right">
                  <v-btn-toggle
                    class="buttongroup-small"
                    v-model="selectContactButton"
                    vertical
                    style="display: inline-grid !important"
                  >
                    <v-btn
                      :key="'address_type' + index + 20"
                      height="20px"
                      plain
                      small
                      style="
                        margin-top: 5px;
                        border: 0px;
                        border-bottom: 1px solid #ddd;
                      "
                      dense
                      color="black"
                      :value="contact.id"
                      @click="displayContactInfoById(contact.id)"
                      v-if="
                        contact.address_type.toLowerCase() != 'primary' &&
                        contact.address_type.toLowerCase() != 'secondary' &&
                        contact.address_type.toLowerCase() != 'security'
                      "
                      v-for="(contact, index) in customer.contacts"
                    >
                      {{ contact.address_type }}
                    </v-btn>
                  </v-btn-toggle>
                </v-col>
              </v-row>
            </div>
          </v-col></v-row
        >

        <v-row>
          <v-col>
            <v-textarea
              outlined
              class="mt-1"
              label="Operator Comments"
              value=""
              height="100px"
              hide-details
              v-model="event_payload.notes"
            ></v-textarea>
          </v-col>
        </v-row>

        <v-row v-if="globalContactDetails">
          <v-col cols="6" class="pr-1">
            <v-text-field
              :disabled="event_payload.event_status == 'Closed' ? false : true"
              type="number"
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
              style="width: 130px; float: right"
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
        </v-row>

        <div>
          <div style="color: green">{{ response }}</div>
          <div v-if="errors && errors.pin_number" class="text-danger mt-2">
            {{ errors.pin_number[0] }}
          </div>
          <div v-if="errors && errors.notes" class="text-danger mt-2">
            {{ errors.notes[0] }}
          </div>
          <div v-if="errors && errors.call_status" class="text-danger mt-2">
            {{ errors.call_status[0] }}
          </div>
          <div v-if="errors && errors.event_status" class="text-danger mt-2">
            {{ errors.event_status[0] }}
          </div>
          <div v-if="errors && errors.response" class="text-danger mt-2">
            {{ errors.response[0] }}
          </div>
        </div>

        <v-row>
          <v-col> </v-col>
        </v-row>

        <!-- <div v-else>
            {{ caps(contact_type) }} contact details are not available
          </div> -->
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import SecurityAlarmNotes from "../Alarm/SecurityDashboard/SecurityAlarmNotes.vue";
import AlarmForwardEvent from "../Alarm/AlarmForwardEvent.vue";

export default {
  components: { SecurityAlarmNotes },
  props: ["alarm_id", "customer", "contact_type"],
  data: () => ({
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

    displayContactInfoById(contactId) {
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
              // this.$emit("closeDialog");
              this.key += 1;
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
