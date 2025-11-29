<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="dialogViewStartJob" width="700px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense style="color: black3333"
            >Start Job - Customer Contacs</span
          >
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="dialogViewStartJob = false"
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text style="padding-left: 10px; background-color: #e9e9e9">
          <PrimaryContactsInfo
            v-if="selectedCustomer"
            :key="key"
            :customer="selectedCustomer"
            :ticketId="editId"
            @closeDialogCall="closeDialogProcess()"
            :ticket="selectedTicket"
          />
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog
      v-model="dialogViewCustomer"
      width="1200px"
      height="700px"
      style="overflow: visible"
    >
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense>View Customer Information</span>
          <v-spacer></v-spacer>
          <v-icon @click="dialogViewCustomer = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text style="padding-left: 10px; background-color: #e9e9e9">
          <TechnicianCustomerTabsView
            v-if="selectedCustomer"
            :key="key"
            :_id="viewCustomerId"
            :selectedCustomer="selectedCustomer"
            :isPopup="true"
            :isEditable="false"
          />
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-card class="elevation-0 p-2" style="padding: 5px">
      <v-row>
        <v-col cols="8">
          <v-row>
            <v-col cols="12">
              <v-row class="pt-0">
                <v-col>
                  <h3>Subject: {{ payload_ticket.subject }}</h3>
                  <span style="color: #9ba5ca"
                    >Created On:
                    {{
                      $dateFormat.formatDateMonthYear(
                        payload_ticket.created_datetime
                      )
                    }}</span
                  >
                </v-col>
                <v-col cols="3">
                  <!-- <v-btn
                class="btn"
                color="primary"
                @click="viewCustomer(editItem)"
                small
                >Customer Info</v-btn
              > -->
                </v-col>
                <v-col
                  cols="3"
                  v-if="
                    editItem.status == 1 &&
                    editItem.job_start_datetime == null &&
                    isTechnician()
                  "
                  class="text-right"
                >
                  <!-- <v-btn class="btn" @click="startJob()" color="primary" small
                >Start Job</v-btn
              > -->
                </v-col>
                <v-col
                  cols="12"
                  dense
                  style="
                    height: 250px;
                    border-radius: 10px;
                    border: 1px solid #ddd;
                    overflow-y: scroll;
                  "
                >
                  <div
                    class="pharagrah-content"
                    v-html="payload_ticket.description"
                  ></div>
                </v-col>
              </v-row>
            </v-col>
          </v-row>

          <v-row
            style="
              margin-top: 15px;
              text-align: right;
              border-bottom: 1px solid #ddd;
            "
          >
            <v-col style="padding-bottom: 0px">
              <h4>
                <v-icon>mdi-paperclip</v-icon>({{
                  editItem?.attachments?.length ?? 0
                }})
              </h4>
              <div
                v-if="editItem?.attachments"
                v-for="(attachment, index) in editItem.attachments"
              >
                {{ ++index }}:
                <a
                  style="text-decoration: none"
                  title="Download Profile Picture"
                  :href="
                    getDonwloadLink(attachment.ticket_id, attachment.attachment)
                  "
                  >{{ attachment.title }}
                  <v-icon color="violet">mdi-arrow-down-bold-circle</v-icon></a
                >
              </div>
            </v-col>
          </v-row>
          <v-row
            v-if="editItem.status == 1"
            style="background-color: #184780; height: 35px; margin-bottom: 10px"
          >
            <v-col style="padding: 0px; padding-left: 10px; margin: auto">
              <h3
                @click="replyTicket = !replyTicket"
                style="color: #fff; cursor: pointer"
              >
                <v-icon color="#FFF">mdi-reply-circle</v-icon>
                Reply to Ticket
              </h3>
            </v-col>
          </v-row>
          <v-row v-if="replyTicket">
            <v-col>
              <ReplyToTicket
                :key="key"
                :editId="editItem.id"
                :editItem="editItem"
                :ticketId="editItem.id"
                :messageReply="true"
                :editorHeight="200"
                @close_dialog="ReloadTicketsList()"
              />
            </v-col>
          </v-row>

          <v-row style="background-color: #184780; height: 35px">
            <v-col style="padding: 0px; padding-left: 10px; margin: auto">
              <h3 style="color: #fff">
                Conversations({{ editItem.responses.length }})
              </h3>
            </v-col>
          </v-row>
          <v-row
            v-if="editItem.responses.length > 0"
            style="
              border: 1px solid #ddd;
              border-radius: 10px;
              border-radius: 0px 0px 10px 10px;
            "
          >
            <v-col>
              <v-row
                style="margin-bottom: 10px; border-top: 1px solid #ddd"
                v-for="(item, i) in editItem.responses"
                :key="i"
              >
                <v-col>
                  <v-row>
                    <v-col style="max-width: 100px"
                      ><v-img
                        style="
                          width: 50px;
                          max-height: 50px;

                          border-radius: 50%;
                          border: 1px solid #ddd;
                          margin: auto;
                        "
                        :src="
                          item.security
                            ? item.security.profile_picture
                            : item.customer
                            ? item.customer.profile_picture
                            : item.technician
                            ? item.technician.profile_picture
                            : $auth.user.company
                            ? $auth.user.company?.logo
                            : '/no-profile-image.jpg'
                        "
                      ></v-img
                    ></v-col>
                    <v-col>
                      <div
                        v-if="
                          item.customer_id &&
                          item.customer_id == $auth.user.customer?.id
                        "
                      >
                        (Me)
                        <v-chip color="#f9c9d2" style="height: 20px" label pill
                          >Customer</v-chip
                        >
                      </div>

                      <div
                        v-else-if="
                          item.security_id &&
                          item.security_id == $auth.user.security?.id
                        "
                      >
                        <v-chip color="#f9c9d2" style="height: 20px" label pill
                          >(Me)Operator</v-chip
                        >
                      </div>

                      <div v-else-if="item.security">
                        {{ item.security.first_name }}
                        {{ item.security.last_name }}
                        <v-chip color="#f9c9d2" style="height: 20px" label pill
                          >Operator</v-chip
                        >
                      </div>

                      <div v-else-if="item.customer">
                        {{ item.customer.building_name }}
                        <v-chip color="#f9c9d2" style="height: 20px" label pill
                          >Customer</v-chip
                        >
                      </div>
                      <div v-else-if="item.technician">
                        {{ item.technician.first_name }}
                        {{ item.technician.last_name }}
                        <v-chip color="#f9c9d2" style="height: 20px" label pill
                          >Technician</v-chip
                        >
                      </div>
                      <div v-else>
                        Admin
                        <!-- <v-chip color="#f9c9d2" style="height: 20px" label pill
                          >Admin</v-chip
                        > -->
                      </div>
                      <div style="color: #9ba5ca !important">
                        {{ $dateFormat.formatDateTime(item.created_datetime) }}
                      </div>
                    </v-col>
                  </v-row>
                  <v-row class="mt-0 pt-0">
                    <v-col style="max-width: 100px" class="mt-0 pt-0"> </v-col>
                    <v-col class="mt-0 pt-0"
                      ><div
                        style="text-align: left"
                        v-html="item.description"
                      ></div>

                      <v-row class="mt-0 pt-0" style="text-align: right">
                        <v-col
                          cols="12"
                          v-if="item?.attachments?.length > 0"
                          style="text-align: right"
                        >
                          <v-icon>mdi-paperclip</v-icon
                          >{{ item?.attachments?.length ?? 0 }})

                          <a
                            class="pr-5"
                            v-if="item?.attachments"
                            v-for="attachment in item.attachments"
                            title="Download Profile Picture"
                            :href="
                              getDonwloadLink(
                                attachment.ticket_response_id,
                                attachment.attachment
                              )
                            "
                            >{{ attachment.title }}
                            <v-icon color="violet"
                              >mdi-arrow-down-bold-circle</v-icon
                            ></a
                          >
                        </v-col>
                      </v-row></v-col
                    >
                  </v-row>
                </v-col>

                <!-- <div
                  style="
                    min-height: 25px;

                    color: #fff;
                  "
                >
                  <v-row :class="item.is_read ? '' : 'bold'">
                    <v-col>
                      <div
                        v-if="
                          item.customer_id &&
                          item.customer_id == $auth.user.customer?.id
                        "
                      >
                        You (Customer)
                      </div>
                      <div
                        v-else-if="
                          item.security_id &&
                          item.security_id == $auth.user.security?.id
                        "
                      >
                        You(Operator)
                      </div>
                      <div v-else-if="item.security">
                        Operator: {{ item.security.first_name }}
                        {{ item.security.last_name }}
                      </div>
                      <div v-else-if="item.customer">
                        Customer: {{ item.customer.building_name }}
                      </div>
                      <div v-else-if="item.technician">
                        Technician: {{ item.technician.first_name }}
                        {{ item.technician.last_name }}
                      </div>
                      <div v-else>Admin/Unknown</div>
                    </v-col>

                    <v-col style="max-width: 50px">
                      <v-icon v-if="item?.attachments?.length" color="#FFF"
                        >mdi-arrow-down-bold-circle</v-icon
                      ></v-col
                    >
                    <v-col style="text-align: right; max-width: 180px">
                      <v-icon size="20" color="#FFF"
                        >mdi-clock-time-four-outline</v-icon
                      >
                      {{ item.created_datetime }}</v-col
                    >
                  </v-row>
                </div>
                <div>
                  <div style="text-align: left" v-html="item.description"></div>

                  <v-row
                    ><v-col
                      ><v-row style="margin-top: 15px">
                        <v-col cols="12">
                          Attachments({{ item?.attachments?.length ?? 0 }})

                          <a
                            class="pr-5"
                            v-if="item?.attachments"
                            v-for="attachment in item.attachments"
                            title="Download Profile Picture"
                            :href="
                              getDonwloadLink(
                                attachment.ticket_response_id,
                                attachment.attachment
                              )
                            "
                            >{{ attachment.title }}
                            <v-icon color="violet"
                              >mdi-arrow-down-bold-circle</v-icon
                            ></a
                          >
                        </v-col>
                      </v-row></v-col
                    ></v-row
                  >
                </div> -->
              </v-row>
            </v-col>
          </v-row>
        </v-col>
        <v-col cols="4">
          <v-row style="border: 1px solid #ddd; border-radius: 10px">
            <v-col cols="12" style="line-height: 8px">
              <v-row>
                <v-col>
                  <h3>Ticket Information</h3>
                </v-col>
              </v-row>
              <v-row style="border-bottom: 1px solid #ddd">
                <v-col style="max-width: 130px">Ticket ID </v-col>
                <v-col cols="1">:</v-col
                ><v-col> #{{ editItem.id }}</v-col></v-row
              ><v-row style="border-bottom: 1px solid #ddd">
                <v-col style="max-width: 130px">Created By </v-col>
                <v-col cols="1">:</v-col
                ><v-col>
                  <div v-if="editItem.category_id > 0">Admin</div>
                  <div v-else>Customer</div>
                </v-col></v-row
              >
              <v-row style="border-bottom: 1px solid #ddd">
                <v-col style="max-width: 130px">Customer </v-col>
                <v-col cols="1">:</v-col
                ><v-col> {{ editItem.customer.building_name }}</v-col></v-row
              >
              <v-row style="border-bottom: 1px solid #ddd">
                <v-col style="max-width: 130px">Category </v-col>
                <v-col cols="1">:</v-col>
                <v-col> {{ editItem.category?.name || "---" }}</v-col>
              </v-row>
              <v-row style="border-bottom: 1px solid #ddd">
                <v-col style="max-width: 130px">Status </v-col>
                <v-col cols="1">:</v-col>
                <v-col>
                  <div v-if="editItem.status == 0" style="color: red">
                    <div label color="green" style="width: 60px; color: red">
                      Closed
                    </div>
                  </div>
                  <div
                    v-else-if="
                      editItem.status == 1 &&
                      editItem.job_start_datetime != null
                    "
                  >
                    <div
                      label
                      color="#a2b117"
                      style="width: 60px; color: #a2b117"
                    >
                      Process
                    </div>
                  </div>
                  <div
                    v-else-if="
                      editItem.status == 1 &&
                      editItem.job_start_datetime == null &&
                      editItem.responses.length > 0
                    "
                  >
                    <div
                      label
                      color="#1e71c3"
                      style="width: 60px; color: #1e71c3"
                    >
                      Open
                    </div>
                  </div>
                  <div
                    v-else-if="
                      editItem.status == 1 &&
                      editItem.job_start_datetime == null
                    "
                    style="color: green"
                  >
                    <div
                      label
                      color="#a70000"
                      style="
                        width: 60px;
                        color: #a70000;

                        text-align: center;
                      "
                    >
                      New
                    </div>
                  </div>
                </v-col>
              </v-row>
              <v-row style="border-bottom: 1px solid #ddd">
                <v-col style="max-width: 130px">Created Date </v-col>
                <v-col cols="1">:</v-col>
                <v-col>
                  {{
                    $dateFormat.formatDateMonthYear(editItem.created_datetime)
                  }}</v-col
                >
              </v-row>
              <v-row style="border-bottom: 1px solid #ddd">
                <v-col style="max-width: 130px">Job Started Date </v-col>
                <v-col cols="1">:</v-col>
                <v-col>
                  {{
                    editItem.job_start_datetime
                      ? $dateFormat.formatDateMonthYear(
                          editItem.job_start_datetime
                        )
                      : "---"
                  }}</v-col
                >
              </v-row>
              <v-row style="border-bottom: 0px solid #ddd">
                <v-col style="max-width: 130px">Closed Date </v-col>
                <v-col cols="1">:</v-col>
                <v-col>
                  {{
                    editItem.end_datetime
                      ? $dateFormat.formatDateMonthYear(editItem.end_datetime)
                      : "---"
                  }}</v-col
                >
              </v-row>
            </v-col>
          </v-row>
          <v-row
            style="border: 1px solid #ddd; border-radius: 10px"
            class="mt-5"
          >
            <v-col cols="12" v-if="selectedCustomer">
              <v-row>
                <v-col>
                  <h3>Customer Information</h3>
                </v-col>
              </v-row>
              <v-row class="mt-0 pa-0">
                <v-col>
                  <v-card style=""
                    ><v-card-text elevation="2">
                      <v-row>
                        <v-col class="p1-0" style="padding: 0px">
                          <v-img
                            style="
                              width: 100%;

                              border-radius: 5%;
                              border: 1px solid #ddd;
                              margin: auto;
                            "
                            :src="
                              selectedCustomer &&
                              selectedCustomer.profile_picture
                                ? selectedCustomer.profile_picture
                                : '/no-business_profile.png'
                            "
                          ></v-img>
                        </v-col>
                      </v-row>
                      <v-row>
                        <v-col style="font-weight: bold; text-align: center">
                          <div style="font-size: 16px; text-align: center">
                            {{
                              selectedCustomer && selectedCustomer.building_name
                                ? selectedCustomer.building_name
                                : "---"
                            }}
                          </div>
                        </v-col>
                      </v-row>

                      <v-row style="margin-top: 0px">
                        <v-col style="line-height: 30px; padding: 0px">
                          <div
                            style="border-top: 0px solid #ddd"
                            v-if="selectedCustomer"
                          >
                            {{ selectedCustomer.house_number }},
                            {{ selectedCustomer.street_number }},
                            {{ selectedCustomer.area }},
                            {{ selectedCustomer.city }}
                          </div>
                          <div
                            style="border-top: 1px solid #ddd"
                            v-if="selectedCustomer"
                          >
                            {{ selectedCustomer.state }},
                            {{ selectedCustomer.country }}
                          </div>
                          <div
                            style="border-top: 1px solid #ddd"
                            v-if="selectedCustomer"
                          >
                            Landmark:
                            {{ selectedCustomer.landmark }}
                          </div>

                          <div style="border-top: 1px solid #ddd">
                            Email:
                            {{
                              selectedCustomer
                                ? selectedCustomer.user?.email
                                : "---"
                            }}
                          </div>
                        </v-col>
                      </v-row>
                    </v-card-text></v-card
                  >
                </v-col>
              </v-row>
            </v-col>
          </v-row>
          <v-row
            style="border: 1px solid #ddd; border-radius: 10px"
            class="mt-5"
          >
            <v-col cols="12" v-if="selectedCustomer">
              <v-row>
                <v-col>
                  <h3>Primary Contact Information</h3>
                </v-col>
              </v-row>
              <v-row class="mt-0 pa-0">
                <v-col>
                  <v-card
                    ><v-card-text elevation="2">
                      <v-row style="margin-top: 0x">
                        <v-col class="p-0" style="padding: 0px">
                          <v-img
                            style="
                              width: 60%;

                              border-radius: 5%;
                              border: 1px solid #ddd;
                              margin: auto;
                            "
                            :src="
                              selectedCustomer &&
                              selectedCustomer.primary_contact
                                ? selectedCustomer.primary_contact
                                    ?.profile_picture
                                : '/no-profile-image.jpg'
                            "
                          ></v-img>
                        </v-col>
                        <v-col style="font-weight: bold; text-align: center">
                          <div style="font-size: 16px; text-align: center">
                            {{
                              selectedCustomer &&
                              selectedCustomer.primary_contact
                                ? selectedCustomer.primary_contact?.first_name +
                                  " " +
                                  selectedCustomer.primary_contact?.last_name
                                : "---"
                            }}
                          </div>
                          <div style="font-size: 10px">(Primary Contact)</div>
                        </v-col>
                      </v-row>

                      <v-row style="margin-top: 0px">
                        <v-col style="line-height: 30px">
                          <div style="border-top: 0px solid #ddd">
                            <v-icon size="13">mdi-phone</v-icon>
                            {{
                              selectedCustomer.primary_contact?.phone1 || "---"
                            }}
                          </div>
                          <div style="border-top: 1px solid #ddd">
                            <v-icon size="13">mdi-phone-classic</v-icon>
                            {{
                              selectedCustomer.primary_contact?.phone2 || "---"
                            }}
                          </div>
                          <div style="border-top: 1px solid #ddd">
                            <v-icon size="13">mdi-whatsapp</v-icon>
                            {{
                              selectedCustomer.primary_contact?.whatsapp ||
                              "---"
                            }}
                          </div>
                          <div style="border-top: 1px solid #ddd">
                            <v-icon size="13">mdi-at</v-icon>
                            {{
                              selectedCustomer.primary_contact?.email || "---"
                            }}
                          </div>
                        </v-col>
                      </v-row>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
            </v-col>
          </v-row>
        </v-col>
      </v-row>
    </v-card>
    <!-- <v-divider class="mt-5"></v-divider>
    <v-row>
      <v-col cols="12" class="text-right">
        <TicketResponses
          :expandPanels="true"
          v-if="editItem"
          :ticket="editItem"
          :key="key"
          @updateTicketReadStatus="updateTickets"
        />
      </v-col>
    </v-row> -->
  </div>
</template>

<script>
import PrimaryContactsInfo from "../Alarm/TechnicianDashboard/PrimaryContactsInfo.vue";
import TechnicianCustomerTabsView from "../Alarm/TechnicianDashboard/TechnicianCustomerTabsView.vue";
import ReplyToTicket from "./ReplyToTicket.vue";
import TicketResponses from "./TicketResponses.vue";

export default {
  props: ["editItem", "editId"],
  components: {
    TicketResponses,
    TechnicianCustomerTabsView,
    PrimaryContactsInfo,
    ReplyToTicket,
  },
  data: () => ({
    replyTicket: false,
    dialogViewCustomer: false,
    selectedCustomer: null,
    selectedTicket: null,
    viewCustomerId: null,
    key: 0,
    snack: false,
    snackColor: "",
    snackText: "",
    snackbar: false,
    response: "",
    dialogViewStartJob: false,
    //end editor
  }),
  created() {
    this.payload_ticket = { subject: "", description: "" };

    if (this.editId != "" && this.editItem) {
      this.payload_ticket.subject = this.editItem.subject;
      this.payload_ticket.description = this.editItem.description;
      this.payload_ticket.created_datetime = this.editItem.created_datetime;
      this.selectedCustomer = this.editItem.customer;
    }
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    closeDialogProcess() {
      this.key++;
      this.dialogViewStartJob = false;

      this.$emit("close_dialog");
    },

    isTechnician() {
      if (this.$auth.user.technician) return true;

      return false;
    },
    getDonwloadLink(ticket_id, file_name) {
      return (
        process.env.BACKEND_URL +
        "/download_ticket_atachment/" +
        ticket_id +
        "/" +
        file_name
      );
    },

    ReloadTicketsList() {
      this.replyTicket = !this.replyTicket;
      this.$emit("close_dialog");
    },
    updateTickets() {
      this.$emit("refreshTickets");
    },
    startJob() {
      this.key++;
      this.selectedCustomer = this.editItem.customer;
      this.selectedTicket = this.editItem;
      this.editId = this.editItem.id;
      this.dialogViewStartJob = true;
    },
    viewCustomer(item) {
      this.selectedCustomer = null;
      this.viewCustomerId = null;
      this.$axios
        .get(`customers`, { params: { customer_id: item.customer_id } })
        .then(({ data }) => {
          this.selectedCustomer = data.data[0];
          this.viewCustomerId = item.customer_id;
          this.dialogViewCustomer = true;
        });
    },
  },
};
</script>
