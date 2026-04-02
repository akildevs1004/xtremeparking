<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row>
      <v-col md="12" sm="12" cols="12" dense>
        <v-row style="margin-top: 15px">
          <v-col> <h3>Create New Customer Invoice</h3></v-col>

          <v-col style="max-width: 170px">
            <v-menu
              v-model="startDateMenuOpen"
              :close-on-content-click="false"
              transition="scale-transition"
              offset-y
              max-width="290px"
              min-width="auto"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field
                  v-model="start_date"
                  label="Licence Start Date"
                  class="small-custom-textbox"
                  outlined
                  dense
                  readonly
                  hide-details
                  persistent-hint
                  v-bind="attrs"
                  v-on="on"
                  :disabled="!isEditable"
                  append-icon=""
                >
                  <template v-slot:append>
                    <v-icon color="green">mdi-calendar</v-icon>
                  </template>
                </v-text-field>
              </template>

              <v-date-picker
                style="min-height: 320px"
                v-model="start_date"
                no-title
                @input="
                  updateEnddate();
                  startDateMenuOpen = false;
                "
              ></v-date-picker>
            </v-menu>
          </v-col>

          <v-col style="max-width: 170px">
            <v-menu
              v-model="endDateMenuOpen"
              :close-on-content-click="false"
              transition="scale-transition"
              offset-y
              max-width="290px"
              min-width="auto"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field
                  class="small-custom-textbox"
                  label="Licence End Date"
                  hide-details
                  v-model="end_date"
                  persistent-hint
                  readonly
                  outlined
                  dense
                  v-bind="attrs"
                  v-on="on"
                  disabled
                  append-icon=""
                >
                  <template v-slot:append>
                    <v-icon color="red">mdi-calendar</v-icon>
                  </template>
                </v-text-field>
              </template>
              <v-date-picker
                :min="start_date"
                style="min-height: 320px"
                v-model="end_date"
                no-title
                @input="endDateMenuOpen = false"
              ></v-date-picker>
            </v-menu>
          </v-col>
          <v-col style="max-width: 120px">
            <v-select
              height="20"
              class="employee-schedule-search-box"
              label="Total Years"
              outlined
              dense
              v-model="licence_duration_years"
              :items="[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]"
              @change="updateCartPrice()"
            ></v-select>
          </v-col>

          <v-col style="max-width: 160px">
            <v-select
              height="20"
              class="employee-schedule-search-box"
              label="Payment Type"
              outlined
              dense
              v-model="payment_type"
              :items="['Yearly', 'Quarter', 'Monthly']"
            ></v-select
          ></v-col>
          <v-col cols="12" class="text-right mt-0 pt-0">
            <table id="table-services">
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Sensor Count</th>
                <th>{{ payment_type }} - Price</th>
                <th>Options</th>
              </tr>
              <tr v-for="(service, index) in data">
                <td>{{ ++index }}</td>
                <td>{{ service.name }}</td>
                <td>{{ service.sensor_count }}</td>
                <td>
                  <div v-if="payment_type == 'Yearly'">
                    ${{ service.year_amount }}
                  </div>
                  <div v-else-if="payment_type == 'Monthly'">
                    ${{ service.month_amount }}
                  </div>
                  <div v-else-if="payment_type == 'Quarter'">
                    ${{ service.quarter_amount }}
                  </div>
                </td>

                <td style="">
                  <v-radio-group v-model="selectedItem">
                    <v-radio :value="service"></v-radio>
                  </v-radio-group>
                </td>
              </tr>
            </table>
          </v-col>
        </v-row>

        <v-row>
          <v-col> </v-col>
          <v-col>
            <v-row>
              <v-col style="text-align: right"
                >Invoice
                <span style="font-weight: bold">{{ payment_type }}</span>
                Amount</v-col
              >
              <v-col
                style="
                  max-width: 100px;
                  text-align: right;
                  padding-right: 25px;
                  font-weight: bold;
                "
                >$ {{ product_price }}</v-col
              >
            </v-row>
            <!-- <v-row>
              <v-col><hr /></v-col>
            </v-row> -->
            <v-row>
              <v-col></v-col>
              <v-col style="max-width: 250px">
                <v-select
                  style="width: 150px"
                  height="20"
                  class="employee-schedule-search-box"
                  label="Discount Type"
                  outlined
                  dense
                  small
                  @change="updateCartPrice()"
                  v-model="discount_type"
                  :items="['Percentage', 'Amount']"
                ></v-select>
              </v-col>
              <v-col style="max-width: 100px">
                <v-text-field
                  style="width: 100px"
                  class="small-custom-textbox"
                  label="Discount"
                  outlined
                  dense
                  small
                  v-model="product_discount_price"
                  @keyup="updateCartPrice()"
                ></v-text-field> </v-col
              ><v-col
                style="
                  max-width: 150px;
                  color: red;
                  text-align: right;
                  padding-right: 25px;
                "
              >
                - $ {{ product_discount_price_amount.toFixed(2) }}
              </v-col>
            </v-row>
            <!-- <hr /> -->
            <v-row style="font-weight: bold">
              <v-col> </v-col>
              <v-col style="text-align: right">Final Amount</v-col>
              <v-col
                style="max-width: 150px; text-align: right; padding-right: 25px"
                >$ {{ product_final_price.toFixed(2) }}</v-col
              >
            </v-row>
          </v-col>
        </v-row>
        <v-row>
          <v-col
            class="pull-right text-right align-right"
            style="color: yellow; padding-right: 20px"
          >
            Note: Total Invoice will be generate Count :
            {{ total_invoice_count }}
          </v-col>
        </v-row>
        <v-row>
          <v-col class="pull-right text-right align-right">
            <v-btn
              primary
              fill
              color="primary"
              dense
              small
              @click="generateInvoices()"
              >Create Invoice
            </v-btn>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  props: ["customer", "isEditable"],

  data: () => ({
    licence_duration_years: 1,
    total_invoice_count: 0,
    startDateMenuOpen: "",
    endDateMenuOpen: "",
    start_date: "",
    end_date: "",

    product_price: 0,
    product_discount_price: 0,
    product_final_price: 0,
    product_service_id: null,
    discount_type: "Percentage",

    product_discount_price_amount: 0,

    payment_type: "Yearly",
    tableHeight: 600,
    options: { perPage: 10 },
    totalRowsCount: 0,
    data: [],
    perPage: 10,
    cumulativeIndex: 1,
    currentPage: 1,
    headersUpdated: [],
    headers: [
      {
        text: "#",
        value: "sno",
      },
      {
        text: "Name",
        value: "name",
      },
      {
        text: "Max Sensor Count",
        value: "sensor_count",
      },
      {
        text: "Yearly Amount",
        value: "year_amount",
      },
      {
        text: "Quarter Amount",
        value: "quarter_amount",
      },
      {
        text: "Monthly Amount",
        value: "month_amount",
      },
      {
        text: "Optioins",
        value: "options",
      },
    ],

    category_id: null,
    TitleRules: [(v) => !!v || "Title is required"],
    servicesList: [],
    filter_customer_id: null,
    categoryList: [],
    extensions: [],
    documents: false,

    Document: {
      items: [{ title: "", file: "" }],
    },
    document_list: [],

    FileRules: [
      (value) => {
        console.log("File value:", value); // Debugging to see the value being validated
        if (!value || typeof value !== "object") return true;
        if (value.size < 1000 * 500) return true;
        return "File size should be less than 200 KB!";
      },
    ],

    show1: false,
    contactTypes: [],
    branchesList: [],
    startDateMenuOpen: "",
    endDateMenuOpen: "",
    preloader: false,
    loading: false,
    primary_upload: {
      name: "",
    },
    secondary_upload: {
      name: "",
    },
    building_upload: {
      name: "",
    },
    start_date: "",
    end_date: "",
    payload_ticket: {
      description: "",
      subject: "",
    },

    e1: 1,
    primary_errors: [],
    primary_previewImage: null,
    secondary_errors: [],
    secondary_previewImage: null,
    building_errors: [],
    building_previewImage: null,
    response: "",
    snackbar: false,
    errors: [],
    selectedItem: null,
    items: ["Apple", "Banana", "Orange"],
    web_login_access: 1, //editor
    datatable_search_textbox: "",
    filter_employeeid: "",
    snack: false,
    snackColor: "",
    snackText: "",
    is_admin: false,
    qtyList: [],
    service_id: null,
    //end editor

    plancolorarray: ["basic", "standard", "premium"],
  }),
  watch: {
    payment_type: {
      handler(newValue) {
        this.updateCartPrice();
      },
      immediate: true, // Optional: Runs on component mount
    },
    selectedItem: {
      handler(newValue) {
        this.updateCartPrice();
      },
      immediate: true, // Optional: Runs on component mount
    },
  },

  mounted() {
    if (window) this.tableHeight = window.innerHeight - 270;
  },
  created() {
    this.getDataFromApi();

    // // Get today's date
    let today = new Date();

    // Get the end date after 365 days
    let nextYear = new Date();
    nextYear.setDate(today.getDate() + 364);

    // Format dates as "DD-MM-YYYY"
    let formatDate = (date) => {
      let dd = String(date.getDate()).padStart(2, "0");
      let mm = String(date.getMonth() + 1).padStart(2, "0"); // Months are 0-based
      let yyyy = date.getFullYear();
      return `${yyyy}-${mm}-${dd}`;
    };
    this.start_date = formatDate(today);
    this.end_date = formatDate(nextYear);

    // console.log("Today's Date:", formatDate(today));
    // console.log("End Date (365 Days Later):", formatDate(nextYear));

    // if (this.payment_type == "Monthly") {
    //   this.headersUpdated = this.headers.filter(
    //     (h) => h.value != "year_amount" && h.value != "quarter_amount"
    //   );
    // } else if (this.payment_type == "Yearly") {
    //   this.headersUpdated = this.headers.filter(
    //     (h) => h.value != "month_amount" && h.value != "quarter_amount"
    //   );
    // } else if (this.payment_type == "Quarter") {
    //   this.headersUpdated = this.headers.filter(
    //     (h) => h.value != "month_amount" && h.value != "year_amount"
    //   );
    // }
  },

  methods: {
    generateInvoices() {
      if (confirm("Are you sure want to submit?")) {
        if (!parseFloat(this.product_final_price)) {
          this.snackbar = true;
          this.response = "Product Price is invalid";
          return false;
        }
        let options = {
          params: {
            company_id: this.$auth.user.company_id,
            customer_id: this.customer.id,
            device_service_id: this.selectedItem.id,
            product_price: this.product_price,
            product_discount_price: this.product_discount_price,
            product_final_price: this.product_final_price,
            payment_type: this.payment_type,
            start_date: this.start_date,
            end_date: this.end_date,
            licence_duration_years: this.licence_duration_years,
            total_invoice_count: this.total_invoice_count,
          },
        };

        this.$axios
          .post("customer_product_invoice_submition", options.params)
          .then((data) => {
            if (data.status) {
              this.snackbar = true;
              this.response = "Invoices Created Successfully";

              setTimeout(() => {
                this.$emit("callInvoiceTab", this.start_date, this.end_date);
              }, 1000);
            } else {
              this.snackbar = true;
              this.response = "Error Occured";
            }
          });
      }
    },
    updateEnddate() {
      let today = new Date(this.start_date);

      console.log(this.start_date);

      // Get the end date after 365 days
      let nextYear = new Date(this.start_date);
      nextYear.setDate(today.getDate() + 364);

      console.log(nextYear);

      let formatDate = (date) => {
        let dd = String(date.getDate()).padStart(2, "0");
        let mm = String(date.getMonth() + 1).padStart(2, "0"); // Months are 0-based
        let yyyy = date.getFullYear();
        return `${yyyy}-${mm}-${dd}`;
      };

      this.end_date = formatDate(nextYear);

      console.log(this.end_date);
    },
    updateCartPrice() {
      let formatDate = (date) => {
        let dd = String(date.getDate()).padStart(2, "0");
        let mm = String(date.getMonth() + 1).padStart(2, "0"); // Months are 0-based
        let yyyy = date.getFullYear();
        return `${yyyy}-${mm}-${dd}`;
      };
      if (this.selectedItem) {
        if (this.payment_type == "Yearly") {
          this.product_price = this.selectedItem.year_amount;

          this.total_invoice_count = this.licence_duration_years;
          const endDate = new Date(this.start_date);
          endDate.setDate(
            endDate.getDate() + 365 * this.licence_duration_years
          );
          this.end_date = formatDate(endDate);
        } else if (this.payment_type == "Monthly") {
          this.product_price = this.selectedItem.month_amount;

          this.total_invoice_count = this.licence_duration_years * 12;
          const endDate = new Date(this.start_date);
          endDate.setDate(
            endDate.getDate() + 365 * this.licence_duration_years
          );
          this.end_date = formatDate(endDate);
        } else if (this.payment_type == "Quarter") {
          this.product_price = this.selectedItem.quarter_amount;
          this.total_invoice_count = this.licence_duration_years * 4;
          const endDate = new Date(this.start_date);
          endDate.setDate(
            endDate.getDate() + 365 * this.licence_duration_years
          );
          this.end_date = formatDate(endDate);
        }
      }

      //discount
      let discount = 0;

      if (this.discount_type === "Percentage") {
        const discountPrice = parseInt(this.product_discount_price, 10);
        const price = parseFloat(this.product_price);

        if (!isNaN(discountPrice) && !isNaN(price)) {
          discount = (discountPrice * price) / 100;
        }
      } else if (this.discount_type === "Amount") {
        const discountPrice = parseFloat(this.product_discount_price);
        if (!isNaN(discountPrice)) {
          discount = discountPrice;
        }
      } else {
        this.product_discount_price = 0;
      }

      this.product_discount_price_amount = discount;

      this.product_final_price = this.product_price - discount;

      if (this.product_final_price < 0) {
        alert("Invalid Invoice amount");
        this.product_discount_price = 0;
        this.updateCartPrice();
      }
    },
    getDataFromApi(url = "", filter_column = "", filter_value = "") {
      if (this.isBackendRequestOpen) return false;
      this.isBackendRequestOpen = true;

      url = "device_product_services";

      this.newCustomerDialog = false;

      const { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";

      this.payloadOptions = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          company_id: this.$auth.user.company_id,
          common_search: this.commonSearch,
          // branch_id: this.branch_id,
          ...this.payload,
        },
      };
      if (filter_column != "")
        this.payloadOptions.params[filter_column] = filter_value;
      this.loading = true;

      this.currentPage = page;
      this.perPage = itemsPerPage;
      try {
        this.$axios.get(url, this.payloadOptions).then(({ data }) => {
          this.isBackendRequestOpen = false;
          this.data = data.data;

          if (this.data[0]) this.selectedItem = this.data[0];
          this.total = data.total;
          this.loading = false;
          this.totalRowsCount = data.total;
        });
      } catch (e) {
        console.log(e);
        this.loading = false;
      }
    },
  },
};
</script>
