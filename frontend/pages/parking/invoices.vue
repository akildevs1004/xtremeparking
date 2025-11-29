<template>
  <NoAccess v-if="!can('operators_view')" />

  <div v-else class="mt-0 pt-0">
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="dialogQuotationToInvoice" max-width="1000px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense> New Invoice - Member</span>
          <v-spacer></v-spacer>
          <v-icon @click="dialogQuotationToInvoice = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <Invoice :key="key" :editId="null" :item="null" :editable="true" :isNewInvoice="true"
            @closeDialog="closeDialog()" />
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogEditAutomation" width="600px">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span style="color: black111">Payment Information </span>
          <v-spacer></v-spacer>
          <v-icon style="color: black3333" @click="
            closeDialog();
          dialogEditAutomation = false;
          " outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text>
          <v-container style="min-height: 100px">
            <EditPayments :key="key" :member_id="member_id" @closeDialog="closeDialog" :editId="editId"
              :editItem="editItemobject" />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-row class="mt-0 pt-0">
      <v-col class="mt-0 pt-0">
        <v-row class="mt-0 pt-0">
          <v-col cols="12" class="text-right" style="width: 600px">
            <v-card elevation="2" style="z-index: 9"><v-card-text>
                <v-row>
                  <v-col class="text-left">
                    <h3>Member Invoices/Payments</h3>
                  </v-col>
                  <v-col style="max-width: 30px"><v-icon @click="getDataFromApi()" loading="true"
                      class="mr-2">mdi-reload</v-icon></v-col>
                  <v-col style="max-width: 250px">
                    <v-text-field style="float: right" height="20" class="employee-schedule-search-box"
                      v-model="commonSearch" label="Invoice Number" placeholder="Invoice Number" dense outlined
                      type="text" append-icon="mdi-magnify" clearable hide-details></v-text-field></v-col>
                  <v-col style="max-width: 250px">
                    <v-autocomplete class="employee-schedule-search-box" label="All Members" dense small outlined
                      clearable autocomplete="off" v-model="filter_member_id" hide-details :items="customersList"
                      :item-text="memberFullName" item-value="id"></v-autocomplete>
                    <!-- <v-autocomplete class="employee-schedule-search-box" style="
                        z-index: 999;

                        min-width: 100%;
                      " height="20px" outlined v-model="filter_member_id" dense :items="[
                        { first_name: 'All Members', id: null },
                        ...customersList,
                      ]" item-text="first_name" item-value="id" hide-details></v-autocomplete> -->
                  </v-col>
                  <v-col style="max-width: 150px">
                    <v-select label="Mode" class="employee-schedule-search-box" style="
                        z-index: 999;

                        min-width: 100%;
                      " height="20px" outlined v-model="filter_mode_of_payment" dense :items="[
                        { value: null, text: 'All' },
                        { value: 'Online', text: 'Online' },
                        { value: 'Cheque', text: 'Cheque' },
                        { value: 'Cash', text: 'Cash' },
                      ]" item-text="text" item-value="value" hide-details></v-select>
                  </v-col>
                  <v-col style="max-width: 150px">
                    <v-select label="Status" class="employee-schedule-search-box" style="
                        z-index: 999;

                        min-width: 100%;
                      " height="20px" outlined v-model="filter_status" dense :items="[
                        { value: null, text: 'All' },
                        { value: 'Received', text: 'Received' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'Cancelled', text: 'Cancelled' },
                      ]" item-text="text" item-value="value" hide-details></v-select>
                  </v-col>

                  <v-col style="max-width: 210px">
                    <CustomFilter style="float: left; z-index: 999" @filter-attr="filterAttr"
                      :default_date_from="date_from" :default_date_to="date_to" :defaultFilterType="1"
                      :height="'30px'" />
                  </v-col>
                  <v-col style="
                      max-width: 80px;

                      text-align: center;
                    ">
                    <v-btn small dense color="primary" @click="getDataFromApi(0)">Submit</v-btn>
                  </v-col>

                  <v-col style="max-width: 50px; text-align: left"><v-btn title="Add" x-small :ripple="false" text
                      @click="addItem()">
                      <v-icon class="">mdi mdi-plus-circle</v-icon>
                    </v-btn></v-col>
                  <!-- <v-col
                    style="
                      margin-top: 10px;
                      margin-left: -16px;
                      max-width: 100px;
                    "
                  >
                    <v-menu bottom right style="z-index: 9999">
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
        <v-col cols="5"><img style="padding-top: 5px" src="/icons/icon_print.png" class="iconsize" /></v-col>
        <v-col cols="7" style="padding-left: 0px; padding-top: 19px">
          Print
        </v-col>
      </v-row>
    </v-list-item-title>
  </v-list-item>
  <v-list-item @click="downloadOptions('download')">
    <v-list-item-title style="cursor: pointer">
      <v-row>
        <v-col cols="5"><img style="padding-top: 5px" src="/icons/icon_pdf.png" class="iconsize" /></v-col>
        <v-col cols="7" style="padding-left: 0px; padding-top: 19px">
          PDF
        </v-col>
      </v-row>
    </v-list-item-title>
  </v-list-item>

  <v-list-item @click="downloadOptions('excel')">
    <v-list-item-title style="cursor: pointer">
      <v-row>
        <v-col cols="5"><img style="padding-top: 5px" src="/icons/icon_excel.png" class="iconsize" /></v-col>
        <v-col cols="7" style="padding-left: 0px; padding-top: 19px">
          EXCEL
        </v-col>
      </v-row>
    </v-list-item-title>
  </v-list-item>
</v-list>
</v-menu>
</v-col> -->
                </v-row>
              </v-card-text></v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
    <v-row class="mt-0">
      <v-col class="mt-0">
        <v-card elevation="2" class="mt-0"><v-card-text class="mt-0 pa-0">
            <v-data-table :height="browserHeight - 230" :headers="headers" :items="items"
              :server-items-length="totalRowsCount" :loading="loading" :options.sync="options" :footer-props="{
                itemsPerPageOptions: [10, 13, 20, 50, 100, 500, 1000],
              }" class="elevation-1">
              <template v-slot:item.sno="{ item, index }">
                {{
                  currentPage
                    ? (currentPage - 1) * perPage +
                    (cumulativeIndex + items.indexOf(item))
                    : "-"
                }}
              </template>
              <template v-slot:item.invoice_number="{ item, index }">
                {{ item.invoice_number }}
              </template>


              <template v-slot:item.duration="{ item, index }">
                {{ item.customer_product_services.payment_type == 'Monthly' ? ' Month ' :
                  item.customer_product_services.payment_type == 'Yearly' ? ' Year ' :
                    item.invoice_count + ' Day ' }}
              </template>
              <template v-slot:item.amount="{ item, index }">
                <div style="text-align: right; padding-right: 40px; width: 150px">
                  {{ getCurrency() }}
                  {{
                    item.tax_amount !== null && item.tax_amount !== undefined
                      ? parseFloat(
                        parseFloat(item.tax_amount) + parseFloat(item.amount)
                      ).toFixed(2)
                      : parseFloat(item.amount).toFixed(2)
                  }}
                </div>
              </template>


              <template v-slot:item.status="{ item, index }">
                <v-chip v-if="item.status == 'Received'" color="green" small text-color="white" class="ma-1"
                  style="font-size: 12px;text-align: center;  height:20px; padding-top: 0px; padding-bottom: 0px;width:80px;">
                  {{ item.status }}
                </v-chip>
                <v-chip v-else-if="item.status == 'Pending'" color="red" small text-color="white" class="ma-1"
                  style="font-size: 12px;text-align: center; height:20px; padding-top: 0px; padding-bottom: 0px;width:80px;">
                  {{ item.status }}
                </v-chip> <v-chip v-else-if="item.status == 'Cancelled'" color="orange" small text-color="white"
                  class="ma-1"
                  style="font-size: 12px;text-align: center; height:20px; padding-top: 0px; padding-bottom: 0px;width:80px;">
                  {{ item.status }}
                </v-chip> <v-chip v-else color="red" small text-color="white" class="ma-1"
                  style="font-size: 12px;text-align: center; height:20px; padding-top: 0px; padding-bottom: 0px;width:80px;">
                  {{ item.status }}
                </v-chip>
              </template>
              <template v-slot:item.received_date="{ item, index }">
                {{ item.received_date || "---" }}
              </template><template v-slot:item.mode_of_payment="{ item, index }">
                {{ item.mode_of_payment || "---" }}
              </template>
              <template v-slot:item.member="{ item, index }">
                {{ item.member ? item.member.first_name + ' ' + item.member.last_name : "---" }}
              </template>

              <template v-slot:item.options="{ item }">
                <v-menu bottom left v-if="isEditable">
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn dark-2 icon v-bind="attrs" v-on="on">
                      <v-icon>mdi-dots-vertical</v-icon>
                    </v-btn>
                  </template>
                  <v-list width="120" dense>
                    <v-list-item v-if="item.status == 'Pending'" @click="editItem(item)">
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" size="22">
                          mdi-pencil
                        </v-icon>
                        Edit
                      </v-list-item-title>
                    </v-list-item>
                    <!-- <v-list-item @click="sendReminderMail(item)">
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="blue" size="22">
                          mdi-email-outline
                        </v-icon>
                        Mail to Cust
                      </v-list-item-title>
                    </v-list-item> -->
                    <v-list-item @click="invoicePrint(item.id, 'print')">
                      <v-list-item-title style="cursor: pointer">
                        <v-row>
                          <v-col cols="3"><img style="padding-top: 5px" src="/icons/icon_print.png"
                              class="iconsize" /></v-col>
                          <v-col cols="9" style="padding-left: 10px; padding-top: 19px">
                            Print
                          </v-col>
                        </v-row>
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="invoicePrint(item.id, 'download')">
                      <v-list-item-title style="cursor: pointer">
                        <v-row>
                          <v-col cols="3"><img style="padding-top: 5px" src="/icons/icon_pdf.png"
                              class="iconsize" /></v-col>
                          <v-col cols="9" style="padding-left: 10px; padding-top: 19px">
                            PDF
                          </v-col>
                        </v-row>
                      </v-list-item-title>
                    </v-list-item>
                    <!-- <v-list-item
                  v-if="!item.received_date"
                  @click="deleteItem(item.id)"
                >
                  <v-list-item-title style="cursor: pointer">
                    <v-icon color="red" small> mdi-delete </v-icon>
                    Delete
                  </v-list-item-title>
                </v-list-item> -->
                  </v-list>
                </v-menu>
              </template>
            </v-data-table>
          </v-card-text></v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
// import QuotationToInvoice from "../../components/Alarm/QuotationToInvoice.vue";
import EditPayments from "../../components/Parking/EditPayments.vue";
import Invoice from "../../components/Parking/Invoice.vue";

// import AlarmEditPayments from "../../../components/Alarm/EditPayments.vue";

export default {
  components: { Invoice, EditPayments },

  data() {
    return {
      dialogQuotationToInvoice: false,
      browserHeight: 900,

      customer: null,
      member_id: null,
      filter_member_id: null,
      customersList: [],
      filterAlarmStatus: null,
      filter_mode_of_payment: null,
      filter_status: null,
      isEditable: true,
      snackbar: false,
      response: "",
      key: 1,
      editId: "",
      editItemobject: null,
      dialogEditAutomation: false,
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
        { text: "Invoice #", value: "invoice_number", sortable: false },
        { text: "Date", value: "invoice_date", sortable: false },
        { text: "Duration", value: "duration", sortable: false },


        { text: "Member", value: "member", sortable: false },

        { text: "Amount", value: "amount", sortable: false, width: "200px" },

        { text: "Status", value: "status", sortable: false },

        { text: "Created", value: "created_datetime", sortable: false },
        // { text: "Notes", value: "cancel_notes", sortable: false },
        { text: "Received On", value: "received_date", sortable: false },
        { text: "Mode", value: "mode_of_payment", sortable: false },
        // { text: "Mail Sent", value: "mail_sent_datetime", sortable: false },
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
    let today = new Date();
    // let monthObj = this.$dateFormat.monthStartEnd(today);
    // this.date_from = monthObj.first;
    // this.date_to = monthObj.last;
    //this.getDataFromApi();
    this.getCustomersList();
    try {
      if (window) this.browserHeight = window.innerHeight;
    } catch (e) { }
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    memberFullName(item) {
      return `${item.first_name} ${item.last_name}`;
    },
    addItem() {
      this.key++;
      this.dialogQuotationToInvoice = true;
    },
    getCustomersList() {
      this.payloadOptions = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      try {
        this.$axios
          .get("members_all", this.payloadOptions)
          .then(({ data }) => {
            this.customersList = data;
          });
      } catch (e) { }
    },
    getCurrency() {
      return this.$auth.user.company.currency;
    },
    sendReminderMail(item) {
      this.snackbar = true;
      this.response = "Invoice mail is processing....";
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          invoice_id: item.id,
        },
      };

      this.$axios
        .post("customer_invoice_reminder_mail", options.params)
        .then((data) => {
          this.snackbar = true;
          this.response = "Invoice Sent to client successfully";
        });
    },
    closeDialog() {
      this.dialogEditAutomation = false;
      this.dialogQuotationToInvoice = false;
      this.getDataFromApi();
    },
    filterAttr(data) {
      this.date_from = data.from;
      this.date_to = data.to;

      // this.getDataFromApi();
    },
    editItem(item) {
      this.key = this.key + 1;
      this.editItemobject = item;
      this.dialogEditAutomation = true;
    },
    invoicePrint(invoice_id, option) {
      //let option = "print";

      let url = process.env.BACKEND_URL;
      if (option == "print") url += "/invoice_print_pdf";
      if (option == "excel") url += "/invoice_print_pdf";
      if (option == "download") url += "/invoice_print_pdf";
      url += "?company_id=" + this.$auth.user.company_id;

      url += "&invoice_id=" + invoice_id;
      url += "&type=" + option;

      window.open(url, "_blank");
    },
    deleteItem(id) {
      if (confirm("Are you sure want to delete Payment  ?")) {
        this.loading = true;
        let options = {
          params: {
            company_id: this.$auth.user.company_id,
            id: id,
          },
        };

        try {
          this.$axios.delete(`delete-payment`, options).then(({ data }) => {
            this.snackbar = true;
            this.response = "Payment Deleted Successfully";
            this.getDataFromApi();
            this.loading = false;
          });
        } catch (e) { }
      }
    },
    getDataFromApi() {
      this.loading = true;
      if (this.browserHeight > 600) this.options.itemsPerPage = 13;

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";
      this.perPage = itemsPerPage;
      this.currentPage = page;
      if (!page > 0) return false;
      let options = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          perPage: itemsPerPage,
          pagination: true,
          company_id: this.$auth.user.company_id,
          member_id: this.member_id,
          date_from: this.date_from || null,
          date_to: this.date_to || null,
          common_search: this.commonSearch,
          filter_status: this.filter_status,
          filter_mode_of_payment: this.filter_mode_of_payment,
          filter_member_id: this.filter_member_id,
        },
      };

      try {
        this.$axios.get(`customer_payments`, options).then(({ data }) => {
          this.items = data.data;

          this.totalRowsCount = data.total;
          this.loading = false;
        });
      } catch (e) { }
    },
  },
};
</script>
