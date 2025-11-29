<template>
  <div>
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="dialogEditAutomation" width="600px">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span style="color: black111">Payment Information </span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="
              closeDialog();
              dialogEditAutomation = false;
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text>
          <v-container style="min-height: 100px">
            <AlarmEditPayments
              :key="key"
              :customer_id="customer_id"
              @closeDialog="closeDialog"
              :editId="editId"
              :editItem="editItemobject"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-row>
      <v-col cols="12" class="text-right">
        <v-row>
          <v-col
            cols="4"
            style="
              font-size: 14px;
              font-weight: bold;
              padding-top: 25px;
              text-align: left;
            "
          >
            <span style="color: green">
              Start Date :
              {{
                $dateFormat.format_date_month_name_year(
                  filter_start_date ? filter_start_date : customer?.start_date
                )
              }}
            </span>
            <br />
            <span style="color: #f52f2f"
              >End Date:
              {{
                $dateFormat.format_date_month_name_year(
                  filter_end_date ? filter_end_date : customer?.end_date
                )
              }}
            </span></v-col
          >
          <v-col cols="8" class="text-right" style="width: 550px">
            <v-row>
              <v-col></v-col>
              <v-col style="max-width: 250px"
                ><v-text-field
                  style="padding-top: 7px; width: 200px; padding-right: 10px"
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
              <v-col style="max-width: 200px">
                <CustomFilter
                  style="float: right; padding-top: 5px; z-index: 9999"
                  @filter-attr="filterAttr"
                  :default_date_from="date_from"
                  :default_date_to="date_to"
                  :defaultFilterType="1"
                  :height="'40px'"
                />
              </v-col>
              <!-- <v-col
                v-if="isEditable"
                cols="1"
                style="padding-left: 0px; width: 50px"
                class="text-left"
              >
                <v-btn
                  small
                  dense
                  color="primary"
                  @click="
                    key = key + 1;
                    editItemobject = null;
                    dialogEditAutomation = true;
                  "
                  class="ma-2"
                >
                  New
                </v-btn>
              </v-col> -->
              <!-- <v-col cols="1" style="width: 50px">
              <v-btn icon small dense color="primary" class="ma-2">
                  <v-icon>mdi mdi-download</v-icon>
                </v-btn>
              </v-col> -->
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

          <template v-slot:item.received_date="{ item, index }">
            {{ item.received_date ? item.received_date : "---" }}
          </template>

          <template v-slot:item.mode_of_payment="{ item, index }">
            {{ item.received_date ? item.mode_of_payment : "---" }}
          </template>

          <template v-slot:item.invoice_number="{ item, index }">
            {{ item.invoice_number }}
            <div class="secondary-value">
              {{ item.invoice_date }}
            </div>
          </template>
          <template v-slot:item.amount="{ item, index }">
            {{ getCurrency() }}
            {{
              item.tax_amount !== null && item.tax_amount !== undefined
                ? parseFloat(
                    parseFloat(item.tax_amount) + parseFloat(item.amount)
                  ).toFixed(2)
                : parseFloat(item.amount).toFixed(2)
            }}
          </template>

          <template v-slot:item.options="{ item }">
            <v-menu bottom left v-if="isEditable">
              <template v-slot:activator="{ on, attrs }">
                <v-btn dark-2 icon v-bind="attrs" v-on="on">
                  <v-icon>mdi-dots-vertical</v-icon>
                </v-btn>
              </template>
              <v-list width="120" dense>
                <v-list-item v-if="!item.received_date" @click="editItem(item)">
                  <v-list-item-title style="cursor: pointer">
                    <v-icon color="secondary" small> mdi-pencil </v-icon>
                    Edit
                  </v-list-item-title>
                </v-list-item>
                <!-- <v-list-item @click="sendReminderMail(item)">
                  <v-list-item-title style="cursor: pointer">
                    <v-row>
                      <v-col cols="5" style="padding-top: 19px">
                        <v-icon color="blue" small> mdi-clock </v-icon>
                      </v-col>
                      <v-col
                        cols="7"
                        style="padding-left: 0px; padding-top: 19px"
                        >Send Mail to Cust</v-col
                      ></v-row
                    >
                  </v-list-item-title>
                </v-list-item> -->
                <v-list-item @click="invoicePrint(item.id, 'print')">
                  <v-list-item-title style="cursor: pointer">
                    <v-row>
                      <v-col cols="3"
                        ><img
                          style="padding-top: 5px"
                          src="/icons/icon_print.png"
                          class="iconsize"
                      /></v-col>
                      <v-col
                        cols="9"
                        style="padding-left: 10px; padding-top: 19px"
                      >
                        Print
                      </v-col>
                    </v-row>
                  </v-list-item-title>
                </v-list-item>
                <v-list-item @click="invoicePrint(item.id, 'download')">
                  <v-list-item-title style="cursor: pointer">
                    <v-row>
                      <v-col cols="3"
                        ><img
                          style="padding-top: 5px"
                          src="/icons/icon_pdf.png"
                          class="iconsize"
                      /></v-col>
                      <v-col
                        cols="9"
                        style="padding-left: 10px; padding-top: 19px"
                      >
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
            <span v-else>---</span>
          </template>
        </v-data-table>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import AlarmEditPayments from "../../components/Alarm/EditPayments.vue";

export default {
  components: { AlarmEditPayments },
  props: [
    "customer_id",
    "customer",
    "isEditable",
    "filter_start_date",
    "filter_end_date",
  ],
  data() {
    return {
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
        { text: "#", value: "sno" },
        { text: "Invoice #", value: "invoice_number" },
        { text: "Amount", value: "amount" },
        { text: "Received On", value: "received_date" },
        { text: "Mode", value: "mode_of_payment" },

        { text: "Status", value: "status" },

        { text: "Options", value: "options" },
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
    if (this.customer) {
      let today = new Date();
      let monthObj = this.$dateFormat.monthStartEnd(today);

      if (this.filter_start_date) {
        if (this.customer?.start_date == null)
          this.date_from = this.filter_start_date;
        else this.date_from = this.customer?.start_date;
        this.date_to = this.filter_end_date;
      } else if (this.customer?.start_date) {
        this.date_from = this.customer?.start_date;
        this.date_to = this.customer?.end_date;
      } else {
        this.date_from = monthObj.first;
        this.date_to = monthObj.last;
      }
      //this.getDataFromApi();

      this.getDevicesList();
    }
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    closeDialog() {
      this.getDataFromApi();
      this.dialogEditAutomation = false;
    },
    getCurrency() {
      return this.$auth.user.company.currency;
    },
    filterAttr(data) {
      this.date_from = data.from;
      this.date_to = data.to;

      this.getDataFromApi();
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
    getDevicesList() {
      this.$axios
        .get(`device-list`, {
          params: {
            company_id: this.$auth.user.company_id,
            customer_id: this.customer_id,
          },
        })
        .then(({ data }) => {
          this.devicesList = data;
        });
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
            this.response = "Notification Deleted Successfully";
            this.getDataFromApi();
            this.loading = false;
          });
        } catch (e) {}
      }
    },
    sendReminderMail(item) {
      this.snackbar = true;
      this.response = "Invoice mail is processing....";
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          invoice_id: item.invoice_id,
        },
      };

      this.$axios
        .post("customer_invoice_reminder_mail", options.params)
        .then((data) => {
          this.snackbar = true;
          this.response = "Invoice Sent to client successfully";
        });
    },
    getDataFromApi() {
      if (this.customer_id) {
        this.loading = true;

        let { sortBy, sortDesc, page, itemsPerPage } = this.options;

        let sortedBy = sortBy ? sortBy[0] : "";
        let sortedDesc = sortDesc ? sortDesc[0] : "";
        this.perPage = itemsPerPage;
        this.currentPage = page;

        let options = {
          params: {
            page: page,
            sortBy: sortedBy,
            sortDesc: sortedDesc,
            perPage: itemsPerPage,
            pagination: true,
            company_id: this.$auth.user.company_id,
            customer_id: this.customer_id,
            date_from: this.date_from,
            date_to: this.date_to,
            common_search: this.commonSearch,
          },
        };

        try {
          this.$axios.get(`customer_payments`, options).then(({ data }) => {
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
