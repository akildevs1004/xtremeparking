<template>
  <div>
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
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
        <v-card-text style="">
          <PrimaryContactsInfo
            v-if="selectedCustomer && selectedTicket"
            :key="key"
            :customer="selectedCustomer"
            :ticketId="selectedTicket.id"
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
          <span dense style="color: black3333"
            >Ticket Customer Information</span
          >
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="dialogViewCustomer = false"
            outlined
          >
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
    <v-dialog v-model="dialogCloseJob" width="900px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense style="color: black3333"
            >Close Ticket - Customer Contacs
          </span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="dialogCloseJob = false"
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text style="padding-left: 10px; background-color: #e9e9e9">
          <PrimaryContactsInfo
            v-if="selectedCustomer"
            :key="selectedCustomerCounter"
            :customer="selectedCustomer"
            :ticketId="editId"
            @closeDialogCall="closeDialogProcess()"
            @savedResults="savedResults()"
            :close_ticket="true"
          />
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogTestingJob" width="1000px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense style="color: black3333">Ticket - Testing Sensors </span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="dialogTestingJob = false"
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text style="padding-left: 10px">
          <!-- <TicketTestingSensor
            v-if="selectedCustomer"
            :key="selectedCustomerCounter"
            :customer="selectedCustomer"
            :ticketId="editId"
            @closeDialogCall="closeDialogProcess()"
            @savedResults="savedResults()"
            :close_ticket="true"
          /> -->

          <TechnicianSensorsTesting
            v-if="selectedCustomer"
            :customer_id="selectedCustomer.id"
            :ticket_id="editId"
          />
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog
      v-model="dialogTicketResponsesList"
      max-width="700px"
      style="z-index: 9999"
    >
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense>Ticket History</span>
          <v-spacer></v-spacer>
          <v-icon @click="dialogTicketResponsesList = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <TicketResponses :ticket="editItem" />
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog
      v-model="dialogNewTicket"
      max-width="1000px"
      style="z-index: 9999"
    >
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense> New Ticket</span>
          <v-spacer></v-spacer>
          <v-icon @click="dialogNewTicket = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <NewTicket
            :security_id="security_id"
            :customer_id="customer_id"
            :key="key"
            @close_dialog="close_dialog_reaction"
          />
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogReply" max-width="1000px" style="z-index: 9999">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense> Reply to Ticket </span>
          <v-spacer></v-spacer>
          <v-icon @click="dialogReply = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <ReplyToTicket
            :key="key"
            :editId="editId"
            :editItem="editItem"
            :ticketId="ticketId"
            :messageReply="messageReply"
            @close_dialog="close_dialog_reaction"
          />
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogViewTicket" width="1200px" style="z-index: 9999">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense> Technician - Ticket Information</span>
          <v-spacer></v-spacer>
          <v-icon @click="dialogViewTicket = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <ViewTicket
            :key="key"
            :editItem="editItem"
            :editId="editItem?.id"
            @close_dialog="close_dialog_reaction"
            @refreshTickets="getDataFromApi"
          />
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-dialog
      v-model="dialogAssignTechnician"
      width="300px"
      style="overflow: visible"
    >
      <v-card :key="key">
        <v-card-title dark class="popup_background_noviolet">
          <span dense style="color: black3333"> Assign Technician</span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="
              dialogAssignTechnician = false;
              closeDialogProcess();
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <v-select
            v-model="assignTechnicianId"
            label="Select Technician"
            height="20"
            class="employee-schedule-search-box11111111 pt-8"
            outlined
            dense
            :items="techniciansList"
            item-text="full_name"
            item-value="id"
            clearable
          >
          </v-select>

          <v-col cols="12" class="text-center pt-5"
            ><v-btn
              dense
              class="primary"
              @click="updateTicketTechnicianId()"
              small
              >Submit</v-btn
            ></v-col
          >
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-card elevation="3" class="mt-1">
      <v-card-text>
        <v-row>
          <v-col class="pl-4"><h3>Tickets</h3></v-col>

          <v-col style="max-width: 40px; padding-top: 20px">
            <v-icon @click="getDataFromApi()">mdi-refresh</v-icon>
          </v-col>
          <v-col style="max-width: 300px"
            ><v-text-field
              style="padding-top: 7px; width: 250px"
              height="20"
              class="employee-schedule-search-box"
              @input="getDataFromApi()"
              v-model="commonSearch"
              label="Common Search"
              dense
              outlined
              type="text"
              append-icon="mdi-magnify"
              clearable
              hide-details
            ></v-text-field
          ></v-col>
          <v-col style="max-width: 200px; padding-right: 20px">
            <CustomFilter
              v-if="displayDateFilter"
              style="float: right; padding-top: 5px; z-index: 9"
              @filter-attr="filterAttr"
              :default_date_from="date_from"
              :default_date_to="date_to"
              :defaultFilterType="1"
              :height="'40px'"
          /></v-col>

          <v-col style="max-width: 200px" class="pt-5">
            <v-autocomplete
              @change="getDataFromApi()"
              clearable
              style="width: 200px"
              class="reports-events-autocomplete"
              v-model="filter_customer_id"
              :items="customersList"
              dense
              placeholder="Customers"
              outlined
              item-value="id"
              item-text="building_name"
              hide-details
            >
            </v-autocomplete>
          </v-col>
          <v-col style="max-width: 200px" class="pt-5">
            <v-select
              @change="getDataFromApi()"
              clearable
              style="width: 200px"
              v-model="filterCategoryId"
              :items="[{ id: null, name: 'All Categories' }, ...categoryList]"
              dense
              placeholder="Category"
              outlined
              item-value="id"
              item-text="name"
              height="20px"
              class="employee-schedule-search-box"
              hide-details
            >
            </v-select>
          </v-col>
          <v-col style="max-width: 100px" class="pt-5">
            <v-select
              @change="getDataFromApi()"
              :items="[
                { text: 'All', value: '' },
                { text: 'Operator', value: 'security' },
                { text: 'Customer', value: 'customer' },
              ]"
              v-model="filterRequestfrom"
              outlined
              dense
              height="20px"
              class="employee-schedule-search-box"
              hide-details
            >
            </v-select>
          </v-col>
          <v-col
            v-if="technician_id == null"
            class="pt-5"
            style="max-width: 50px"
          >
            <v-btn
              title="Add Ticket"
              x-small
              :ripple="false"
              text
              @click="addItem()"
            >
              <v-icon class="">mdi mdi-plus-circle</v-icon>
            </v-btn>
          </v-col>
          <!--<v-col style="margin-top: 10px">
                  <v-menu bottom right>
                    <template v-slot:activator="{ on, attrs }">
                      <span v-bind="attrs" v-on="on">
                        <v-icon dark-2 icon color="violet" small
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
      </v-card-text>
    </v-card>

    <v-card elevation="3" class="mt-3" :height="browserHeight - 250">
      <v-card-text>
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
              <template v-slot:item.created_datetime="{ item }">
                <div :class="getIsReadStatus(item) ? '' : 'bold'">
                  {{ $dateFormat.formatDateMonthYear(item.created_datetime) }}
                </div>
              </template>
              <template v-slot:item.subject="{ item }">
                <div
                  :class="getIsReadStatus(item) ? '' : 'bold'"
                  :title="item.subject"
                >
                  <!-- {{ item.subject.slice(0, 10) }} -->
                  {{ item.subject }}
                </div>
              </template>
              <template v-slot:item.customer="{ item }">
                <div
                  :class="getIsReadStatus(item) ? '' : 'bold'"
                  v-if="item.category_id > 0"
                >
                  Admin
                  <div class="secondary-value">
                    <!-- Customer <br /> -->
                    For {{ item.customer.building_name }}
                  </div>
                </div>
                <div
                  :class="getIsReadStatus(item) ? '' : 'bold'"
                  v-else-if="item.customer"
                >
                  {{ item.customer.building_name }}
                  <div class="secondary-value">Customer</div>
                </div>
                <!-- <div
                :class="getIsReadStatus(item) ? '' : 'bold'"
                v-else-if="item.security"
              >
                {{ item.security.first_name }} {{ item.security.last_name }}

                <div class="secondary-value">Operator</div>
              </div>
              <div
                :class="getIsReadStatus(item) ? '' : 'bold'"
                v-else-if="item.technician"
              >
                Technician
                <div class="secondary-value">
                  {{ item.technician.first_name }}
                  {{ item.technician.last_name }}
                </div>
              </div> -->
              </template>
              <template v-slot:item.ticket_responses="{ item }">
                <div
                  :class="getIsReadStatus(item) ? '' : 'bold'"
                  @click="responses(item)"
                >
                  {{ item.responses?.length || 0 }}
                </div>
              </template>

              <template v-slot:item.category_id="{ item }">
                {{ item.category?.name || "---" }}
              </template>
              <template v-slot:item.last_active_datetime="{ item }">
                <div :class="getIsReadStatus(item) ? '' : 'bold'">
                  {{
                    $dateFormat.formatDateMonthYear(item.last_active_datetime)
                  }}
                </div>
              </template>

              <template v-slot:item.status="{ item }">
                <div :class="getIsReadStatus(item) ? '' : 'bold'">
                  <div v-if="item.status == 0" style="color: red">
                    <v-chip
                      label
                      color="green"
                      style="
                        width: 60px;
                        color: #fff;
                        height: 18px;
                        text-align: center;
                      "
                      >Closed</v-chip
                    >
                  </div>
                  <div
                    v-else-if="
                      item.status == 1 && item.job_start_datetime != null
                    "
                  >
                    <v-chip
                      label
                      color="#a2b117"
                      style="
                        width: 60px;
                        color: #fff;
                        height: 18px;
                        text-align: center;
                      "
                      >Process</v-chip
                    >
                  </div>
                  <div
                    v-else-if="
                      item.status == 1 &&
                      item.job_start_datetime == null &&
                      item.responses.length > 0
                    "
                  >
                    <v-chip
                      label
                      color="#1e71c3"
                      style="
                        width: 60px;
                        color: #fff;
                        height: 18px;
                        text-align: center;
                      "
                      >Open</v-chip
                    >
                  </div>
                  <div
                    v-else-if="
                      item.status == 1 && item.job_start_datetime == null
                    "
                    style="color: green"
                  >
                    <v-chip
                      label
                      color="#a70000"
                      style="
                        width: 60px;
                        color: #fff;
                        height: 18px;
                        text-align: center;
                      "
                      >New</v-chip
                    >
                  </div>
                </div>
              </template>

              <template v-slot:item.technician="{ item }">
                <div>
                  {{ item.technician?.first_name }}
                  {{ item.technician?.last_name || "---" }}
                </div>
              </template>
              <template v-slot:item.closed_datetime="{ item }">
                <div
                  v-if="item.status == 0"
                  :class="getIsReadStatus(item) ? '' : 'bold'"
                  style="color: green"
                >
                  {{
                    $dateFormat.formatDateMonthYear(item.last_active_datetime)
                  }}
                </div>
                <div v-else>---</div>
              </template>
              <template v-slot:item.job_start_datetime="{ item }">
                <div
                  v-if="item.job_start_datetime != null"
                  style="color: #4b9eff"
                >
                  {{ $dateFormat.formatDateMonthYear(item.job_start_datetime) }}
                </div>
                <div v-else>---</div>
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
                      @click="addReply(item)"
                      v-if="item.status != 0"
                    >
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-reply</v-icon>
                        Reply
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="viewTicket(item)">
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small>
                          mdi-information</v-icon
                        >
                        View Ticket
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="viewCustomer(item)">
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="blue" small> mdi-home-circle</v-icon>
                        Customer
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      v-if="userType == 'company' && item.status == 1"
                      @click="showTichnician(item)"
                    >
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="green" small> mdi-account-tie</v-icon>
                        Technician
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      @click="startJob(item)"
                      v-if="
                        userType == 'technician' &&
                        item.status == 1 &&
                        item.job_start_datetime == null
                      "
                    >
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="green" small> mdi-button-pointer</v-icon>
                        Start Job
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      @click="testSensors(item)"
                      v-if="
                        userType == 'technician' &&
                        item.status == 1 &&
                        item.job_start_datetime != null
                      "
                    >
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="green" small> mdi-button-pointer</v-icon>
                        Testing
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      v-if="
                        userType == 'technician' &&
                        item.status == 1 &&
                        item.job_start_datetime != null
                      "
                      @click="closeTicket(item)"
                    >
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="red" small> mdi-close-box</v-icon>
                        Close Job
                      </v-list-item-title>
                    </v-list-item>
                    <!-- <v-list-item @click="responses(item)">
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-view-list</v-icon>
                        History
                      </v-list-item-title>
                    </v-list-item> -->
                    <!-- <v-list-item
                      v-if="can('branch_view')"
                      @click="editTicket(item)"
                    >
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-pencil </v-icon>
                        Edit
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      v-if="can('branch_edit')"
                      @click="deleteNotes(item.id)"
                    >
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="red" small> mdi-delete </v-icon>
                        Delete
                      </v-list-item-title>
                    </v-list-item>-->
                  </v-list>
                </v-menu>
              </template>
            </v-data-table>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import NewTicket from "../../components/Tickets/NewTicket.vue";
import ReplyToTicket from "../../components/Tickets/ReplyToTicket.vue";
import ViewTicket from "../../components/Tickets/TechnicianViewTicket.vue";
import PrimaryContactsInfo from "../Alarm/TechnicianDashboard/PrimaryContactsInfo.vue";
import TechnicianCustomerTabsView from "../Alarm/TechnicianDashboard/TechnicianCustomerTabsView.vue";
import TechnicianSensorsTesting from "../Alarm/TechnicianDashboard/TechnicianSensorsTesting.vue";
import TicketTestingSensor from "../Alarm/TechnicianDashboard/TicketTestingSensor.vue";
import TicketResponses from "./TicketResponses.vue";

export default {
  components: {
    NewTicket,
    ReplyToTicket,
    ViewTicket,
    TicketResponses,
    PrimaryContactsInfo,
    TechnicianCustomerTabsView,
    TicketTestingSensor,
    TechnicianSensorsTesting,
  },
  props: [
    "canReply",
    "customer_id",
    "security_id",
    "technician_id",
    "status",
    "filterWord",
  ],
  data() {
    return {
      techniciansList: [],
      dialogAssignTechnician: false,
      assignTechnicianId: false,
      customersList: [],
      filter_customer_id: null,
      dialogViewCustomer: false,
      dialogViewStartJob: false,
      selectedCustomerCounter: 0,
      selectedTicket: null,
      selectedCustomer: null,
      dialogCloseJob: false,
      dialogTestingJob: false,
      filterCategoryId: null,
      categoryList: [],
      dialogTicketResponsesList: false,
      messageReply: true,
      filterRequestfrom: "",
      displayDateFilter: false,
      dialogViewTicket: false,
      editId: null,
      editItem: null,
      ticketId: null,
      dialogReply: false,
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
        { text: "Ticket ID", value: "id", sortable: false },

        { text: "Subject", value: "subject", sortable: false },
        { text: "Created By", value: "customer", sortable: false },

        { text: "Category", value: "category_id", sortable: false },

        // { text: "Reply Count", value: "ticket_responses", sortable: false },
        // { text: "Closed Date", value: "closed_datetime", sortable: false },
        {
          text: "Last Activity",
          value: "last_active_datetime",
          sortable: false,
        },

        { text: "Status", value: "status", sortable: false },

        { text: "Job Date", value: "job_start_datetime", sortable: false },
        { text: "Technician", value: "technician", sortable: false },

        { text: "Requested Date", value: "created_datetime", sortable: false },
        { text: "Closed Date", value: "closed_datetime", sortable: false },
        { text: "Options", value: "options", sortable: false },
      ],
      items: [],
      dialogNewTicket: false,
      isPageload: true,
      is_admin: false,
      userType: "",
      viewCustomerId: null,
      browserHeight: 900,
    };
  },
  watch: {
    options: {
      handler() {
        if (!this.isPageload) this.getDataFromApi();
      },
      deep: true,
    },
  },
  async created() {
    // let today = new Date();
    // let monthObj = this.$dateFormat.monthStartEnd(today);
    // this.date_from = null; //monthObj.first;
    // this.date_to = null; // monthObj.last;

    await this.getDataFromApi();
    await this.getCategoriesList();
    if (this.$auth.user.user_type == "company") this.is_admin = true;

    this.userType = this.$auth.user.user_type;

    // setInterval(() => {
    //   if (this.dialogViewTicket == false && this.dialogReply == false)
    //     this.getDataFromApi();
    // }, 1000 * 30);

    try {
      if (window) this.browserHeight = window.innerHeight;
    } catch (e) {}
    await this.getCustomersList();
    await this.getTechniciansList();
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    savedResults(status) {
      this.status;
    },
    closeDialogProcess() {
      this.dialogViewStartJob = false;
      this.dialogCloseJob = false;
      this.getDataFromApi();
      //this.$emit("close_dialog");

      this.key++;
    },
    closeTicket(ticket) {
      this.key++;
      this.selectedCustomerCounter++;
      this.selectedCustomer = ticket.customer;
      this.selectedTicket = ticket;
      this.editId = ticket.id;
      this.dialogCloseJob = true;
    },
    async getCustomersList() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`/customers_list`, options).then(({ data }) => {
        this.customersList = [
          { id: null, building_name: "All Customers" },
          ...data,
        ];
      });
    },
    async getTechniciansList() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`/technicians_list`, options).then(({ data }) => {
        this.techniciansList = data;
      });
    },
    testSensors(ticket) {
      this.key++;
      this.selectedCustomerCounter++;
      this.selectedCustomer = ticket.customer;
      this.selectedTicket = ticket;
      this.editId = ticket.id;

      this.dialogTestingJob = true;
    },

    verifyCanReply() {
      if (this.canReply != null) {
        return this.canReply;
      }
      return true;
    },
    responses(item) {
      this.editItem = item;
      this.key += 1;

      this.dialogTicketResponsesList = true;
    },
    async getCategoriesList() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`/ticket_categories`, options).then(({ data }) => {
        this.categoryList = data;
      });
    },

    updateTicketTechnicianId() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          technician_id: this.assignTechnicianId,
          ticket_id: this.editItem.id,
        },
      };

      this.$axios
        .post("ticket_update_technician", options.params)
        .then((data) => {
          this.getDataFromApi();
          this.dialogAssignTechnician = false;
          this.snackbar = true;
          this.response = "Technican is updated successfully";
        });
    },
    getIsReadStatus(item) {
      if (this.$auth.user.user_type == "technician") {
        return item.is_technician_read;
      }
      if (this.$auth.user.user_type == "security") {
        return item.is_security_read;
      }
      if (this.$auth.user.user_type == "customer") {
        return item.is_customer_read;
      }

      return true;
    },
    viewTicket(item) {
      this.editItem = item;
      this.key += 1;
      this.dialogViewTicket = true;
    },
    showTichnician(item) {
      this.assignTechnicianId = item.technician_id;
      this.editItem = item;
      this.key += 1;
      this.dialogAssignTechnician = true;
    },
    close_dialog_reaction() {
      this.getDataFromApi();
      this.key += 1;
      this.dialogReply = false;
      this.dialogNewTicket = false;
      this.dialogViewTicket = false;
    },

    addReply(item, message = true) {
      this.key += 1;
      this.editItem = item;
      this.editId = item.id;
      this.messageReply = message;
      this.dialogReply = true;
    },
    editTicket(item) {
      this.editId = item.id;
      this.editItem = item;
      this.key += 1;
      this.dialogNewTicket = true;
    },
    closeDialog() {
      this.dialogAddCustomerNotes = false;
      this.isPageload = true;
      this.displayDateFilter = true;
      this.getDataFromApi();
      this.$emit("closeDialog");
    },
    filterAttr(data) {
      this.date_from = data.from;
      this.date_to = data.to;

      this.getDataFromApi();
    },
    viewCustomer(item) {
      this.selectedCustomer = null;
      this.viewCustomerId = null;
      this.dialogViewCustomer = true;
      this.$axios
        .get(`customers`, { params: { customer_id: item.customer_id } })
        .then(({ data }) => {
          this.selectedCustomer = data.data[0];
          this.viewCustomerId = item.customer_id;
        });
    },
    // closeDialogProcess() {
    //   this.key++;
    //   this.dialogViewStartJob = false;

    //   // this.$emit("close_dialog");
    // },
    startJob(item) {
      this.key++;
      this.selectedCustomer = item.customer;
      this.selectedTicket = item;
      this.dialogViewStartJob = true;
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

    async getDataFromApi() {
      // console.log("dialogViewStartJob");

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";
      this.perPage = itemsPerPage;
      this.currentPage = page;
      // if (!page > 0) return false;

      this.loading = true;
      let options = {
        params: {
          page: page,
          //sortBy: sortedBy,
          sortDesc: sortedDesc,
          perPage: itemsPerPage,
          pagination: true,
          company_id: this.$auth.user.company_id,
          customer_id: this.customer_id,
          security_id: this.security_id,
          date_from: this.date_from,
          date_to: this.date_to,
          common_search: this.commonSearch,
          category_id: this.filterCategoryId,
          sortBy: "alarm_start_datetime",
          filterRequestfrom:
            this.filterRequestfrom == "" ? null : this.filterRequestfrom,

          //status: this.status ?? null,
          filterWord: this.filterWord ?? null,
          filter_customer_id: this.filter_customer_id,
        },
      };

      this.$axios.get(`tickets`, options).then(({ data }) => {
        this.items = data.data;

        this.totalRowsCount = data.total;

        setTimeout(() => {
          this.loading = false;
        }, 1000);

        if (this.displayDateFilter == false && this.isPageload) {
          let today = new Date();
          if (this.items.length > 0) {
            today = new Date(this.items[0].created_datetime);
          }

          let monthObj = this.$dateFormat.monthStartEnd(today);
          this.date_from = monthObj.first;
          this.date_to = monthObj.last;
          this.displayDateFilter = true;
          this.isPageload = false;
        }
      });
    },
    addItem() {
      this.key += 1;

      this.dialogNewTicket = true;
    },
  },
};
</script>
