<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row>
      <v-col md="12" sm="12" cols="12" dense>
        <v-card class="elevation-0 p-2" style="padding: 5px">
          <v-row>
            <v-col cols="12">
              <v-row class="pt-0">
                <v-col cols="3" dense v-if="!isNewInvoice">
                  <v-text-field
                    label="First Name"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="payload.first_name"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.first_name"
                    class="text-danger mt-2"
                    >{{ primary_errors.first_name[0] }}</span
                  >
                </v-col>
                <v-col cols="3" dense v-if="!isNewInvoice">
                  <v-text-field
                    label="Last Name"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="payload.last_name"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.last_name"
                    class="text-danger mt-2"
                    >{{ primary_errors.last_name[0] }}</span
                  >
                </v-col>
                <v-col cols="3" dense v-if="!isNewInvoice">
                  <v-text-field
                    label="Business/Building Name"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.building_name"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.building_name"
                    class="text-danger mt-2"
                    >{{ primary_errors.building_name[0] }}</span
                  >
                </v-col>
                <v-col cols="3" dense v-if="!isNewInvoice">
                  <v-select
                    label="Type of Business/Customer"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.building_type_id"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                    :items="buildingTypes"
                    item-text="name"
                    item-value="id"
                  ></v-select>
                  <span
                    v-if="primary_errors && primary_errors.building_type_id"
                    class="text-danger mt-2"
                    >{{ primary_errors.building_type_id[0] }}</span
                  >
                </v-col>
                <v-col cols="3" dense v-if="!isNewInvoice">
                  <v-text-field
                    label="House Number"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.house_numner"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.house_numner"
                    class="text-danger mt-2"
                    >{{ primary_errors.house_numner[0] }}</span
                  >
                </v-col>
                <v-col cols="3" dense v-if="!isNewInvoice">
                  <v-text-field
                    label="Street Number"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.street_number"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.street_number"
                    class="text-danger mt-2"
                    >{{ primary_errors.street_number[0] }}</span
                  >
                </v-col>
                <v-col cols="3" dense v-if="!isNewInvoice">
                  <v-text-field
                    label="Area"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.area"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.area"
                    class="text-danger mt-2"
                    >{{ primary_errors.area[0] }}</span
                  > </v-col
                ><v-col cols="3" dense v-if="!isNewInvoice">
                  <v-text-field
                    label="City"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.city"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.city"
                    class="text-danger mt-2"
                    >{{ primary_errors.city[0] }}</span
                  > </v-col
                ><v-col cols="3" dense v-if="!isNewInvoice">
                  <v-text-field
                    label="State"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.state"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.state"
                    class="text-danger mt-2"
                    >{{ primary_errors.state[0] }}</span
                  > </v-col
                ><v-col cols="3" dense v-if="!isNewInvoice">
                  <v-text-field
                    label="Country"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.country"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.country"
                    class="text-danger mt-2"
                    >{{ primary_errors.country[0] }}</span
                  >
                </v-col>
                <v-col cols="3" dense v-if="!isNewInvoice">
                  <v-text-field
                    label="Contact Number"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.contact_number"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.contact_number"
                    class="text-danger mt-2"
                    >{{ primary_errors.contact_number[0] }}</span
                  >
                </v-col>
                <v-col cols="3" dense v-if="!isNewInvoice">
                  <v-text-field
                    label="Whatsapp Number"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.whatsapp_number"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.whatsapp_number"
                    class="text-danger mt-2"
                    >{{ primary_errors.whatsapp_number[0] }}</span
                  >
                </v-col>
                <v-col cols="3" dense v-if="!isNewInvoice">
                  <v-text-field
                    label="Email"
                    type="email"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.email"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.email"
                    class="text-danger mt-2"
                    >{{ primary_errors.email[0] }}</span
                  >
                </v-col>
                <v-col cols="3" dense v-if="!isNewInvoice">
                  <v-text-field
                    label="Password(New customer)"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.password"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.password"
                    class="text-danger mt-2"
                    >{{ primary_errors.password[0] }}</span
                  >
                </v-col>
                <v-col cols="3" dense v-if="isNewInvoice">
                  <v-select
                    label="Customer"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.customer_id"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                    :items="customersList"
                    item-text="building_name"
                    item-value="id"
                  ></v-select>
                  <span
                    v-if="primary_errors && primary_errors.customer_id"
                    class="text-danger mt-2"
                    >{{ primary_errors.customer_id[0] }}</span
                  >
                </v-col>
                <!-- <v-col cols="3" dense>
                  <v-select
                    label="Alarm Type"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.device_type"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                    :items="device_type_list"
                    item-text="name"
                    item-value="id"
                  ></v-select>
                  <span
                    v-if="primary_errors && primary_errors.device_type"
                    class="text-danger mt-2"
                    >{{ primary_errors.device_type[0] }}</span
                  > </v-col
                ><v-col cols="3" dense>
                  <v-select
                    label="Required Sensor Count"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.sensor_count"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                    :items="[
                      1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17,
                      18, 19, 20,
                    ]"
                  ></v-select>
                  <span
                    v-if="primary_errors && primary_errors.sensor_count"
                    class="text-danger mt-2"
                    >{{ primary_errors.sensor_count[0] }}</span
                  >
                </v-col> -->
                <v-col md="3" sm="12" cols="12">
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
                        label="Licence Start Date"
                        hide-details
                        v-model="payload.start_date"
                        persistent-hint
                        append-icon="mdi-calendar"
                        readonly
                        outlined
                        dense
                        v-bind="attrs"
                        v-on="on"
                        :disabled="!editable"
                      ></v-text-field>
                      <span
                        v-if="errors && errors.start_date"
                        class="text-danger mt-2"
                        >{{ errors.start_date[0] }}</span
                      >
                    </template>
                    <v-date-picker
                      style="min-height: 320px"
                      v-model="payload.start_date"
                      no-title
                      @input="startDateMenuOpen = false"
                    ></v-date-picker>
                  </v-menu>
                </v-col>
                <v-col cols="3" dense>
                  <v-select
                    dense
                    small
                    outlined
                    clearable
                    hide-details
                    label="Total Years"
                    v-model="payload.licence_duration_years"
                    :items="[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]"
                    @change="updateCartPrice()"
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-select>
                </v-col>
                <v-col cols="3" dense>
                  <v-select
                    dense
                    small
                    outlined
                    clearable
                    hide-details
                    @click="updateCartPrice()"
                    label="Payment Mode"
                    v-model="payment_type"
                    :items="['Yearly', 'Quarter', 'Monthly']"
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-select>
                </v-col>
              </v-row>
            </v-col>
          </v-row>
          <v-row>
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
                      <v-radio
                        :readonly="!editable"
                        :filled="!editable"
                        :value="service"
                      ></v-radio>
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
                  >Product
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
              <v-row>
                <v-col><hr /></v-col>
              </v-row>
              <v-row>
                <v-col>
                  <v-select
                    height="20"
                    class="employee-schedule-search-box"
                    label="Discount Type"
                    outlined
                    dense
                    small
                    @change="updateCartPrice()"
                    v-model="discount_type"
                    :items="['Percentage', 'Amount']"
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-select>
                </v-col>
                <v-col style="max-width: 130px">
                  <v-text-field
                    class="small-custom-textbox"
                    :label="
                      'Discount ' + (discount_type == 'Percentage' ? '%' : '')
                    "
                    outlined
                    dense
                    small
                    v-model="product_discount_price"
                    @keyup="updateCartPrice()"
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field> </v-col
                ><v-col
                  style="
                    max-width: 100px;
                    color: red;
                    text-align: right;
                    padding-right: 25px;
                  "
                >
                  - $ {{ product_discount_price_amount.toFixed(2) }}
                </v-col>
              </v-row>
              <hr />
              <v-row style="font-weight: bold">
                <v-col> </v-col>
                <v-col style="text-align: right">Final Amount</v-col>
                <v-col
                  style="
                    max-width: 100px;
                    text-align: right;
                    padding-right: 25px;
                  "
                  >$ {{ product_final_price.toFixed(2) }}</v-col
                >
              </v-row>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="9" class="text-right" style="color: red">{{
              response
            }}</v-col>
            <v-col cols="3" class="text-right">
              <v-btn
                v-if="editable"
                small
                :loading="loading"
                color="primary"
                @click="submit_primary"
              >
                Submit
              </v-btn>
            </v-col>
          </v-row>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  props: ["editId", "item", "editable", "inquiry_to_quotation", "isNewInvoice"],
  data: () => ({
    data: [],
    customersList: [],
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
    discount_type: "Amount",
    product_discount_price_amount: 0,
    payment_type: "Yearly",

    options: { perPage: 10 },

    buildingTypes: [],
    business_source_list: [],
    device_type_list: [],

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
    payload: {
      attachment: "",
      title: "",
      display_order: "",
      licence_duration_years: 1,
      password: "password",
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
    web_login_access: 1,
    qtyList: [],
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
  async created() {
    this.primary_previewImage = null;
    this.payload = { password: "welcome" };
    this.payload.licence_duration_years = 1;
    this.preloader = false;

    // this.getBranchesList();

    if (this.$store.state.storeAlarmControlPanel?.AddressTypes) {
      this.addressTypes = this.$store.state.storeAlarmControlPanel.AddressTypes;
    }

    // setTimeout(() => {
    //console.log(this.editAddressType);
    if (this.editId != "" && this.item) {
      this.payload = this.item;

      this.payload.password = "welcome";

      this.payment_type = this.item.payment_type;

      this.discount = parseFloat(this.item.discount);

      this.payload.licence_duration_years = this.item.total_years;
      this.product_discount_price_amount = parseFloat(this.item.discount);
      this.product_discount_price = parseFloat(this.item.discount);

      this.product_price =
        parseFloat(this.item.amount) + parseFloat(this.item.discount);
      this.product_final_price = parseFloat(this.item.amount);
    }

    if (this.inquiry_to_quotation) {
      this.payload = this.inquiry_to_quotation;
      this.payload.licence_duration_years = 1;

      this.payload.inquiry_id = this.inquiry_to_quotation.id;
    }

    for (let i = 1; i <= 100; i++) {
      this.qtyList.push(i);
    }
    this.selectedItem = null;
    await this.getDataFromApi();
    await this.getBusinessTypes();
    await this.getAlarmTypes();
    await this.getBuildingTypes();
    this.payload.start_date = new Date().toISOString().slice(0, 10);
    await this.getCustomersList();
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    changeStatus(status) {
      if (this.editable) this.web_login_access = status;
    },
    //primary
    // onpick_primary_attachment() {
    //   this.$refs.primary_attachment_input.click();
    // },
    // primary_attachment(e) {
    //   this.primary_upload.name = e.target.files[0] || "";

    //   let input = this.$refs.primary_attachment_input;
    //   let file = input.files;
    //   //console.log(file);
    //   if (file[0] && file[0].size > 1024 * 1024) {
    //     e.preventDefault();
    //     this.primary_errors["logo"] = [
    //       "File too big (> 1MB). Upload less than 1MB",
    //     ];
    //     return;
    //   }

    //   if (file && file[0]) {
    //     let reader = new FileReader();
    //     reader.onload = (e) => {
    //       this.primary_previewImage = e.target.result;
    //     };
    //     reader.readAsDataURL(file[0]);
    //     this.$emit("input", file[0]);
    //   }
    // },

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
          const endDate = new Date(this.payload.start_date);
          endDate.setDate(
            endDate.getDate() + 365 * this.licence_duration_years
          );
          this.end_date = formatDate(endDate);
        } else if (this.payment_type == "Monthly") {
          this.product_price = this.selectedItem.month_amount;

          this.total_invoice_count = this.licence_duration_years * 12;
          const endDate = new Date(this.payload.start_date);
          endDate.setDate(
            endDate.getDate() + 365 * this.licence_duration_years
          );
          this.end_date = formatDate(endDate);
        } else if (this.payment_type == "Quarter") {
          this.product_price = this.selectedItem.quarter_amount;
          this.total_invoice_count = this.licence_duration_years * 4;
          const endDate = new Date(this.payload.start_date);
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

      this.product_final_price =
        parseFloat(this.product_price) - parseFloat(discount);

      if (this.product_final_price < 0) {
        alert("Invalid Invoice amount");
        this.product_discount_price = 0;
        this.updateCartPrice();
      }
    },
    async getDataFromApi(url = "", filter_column = "", filter_value = "") {
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

          if (this.item?.device_product_service_id)
            this.selectedItem = this.data.find(
              (e) => e.id == this.item.device_product_service_id
            );
        });
      } catch (e) {
        console.log(e);
        this.loading = false;
      }
    },
    async getBusinessTypes() {
      let options = { params: { company_id: this.$auth.user.company_id } };

      this.$axios.get("get_sales_business_types", options).then((data) => {
        this.business_source_list = data.data;
      });
    },
    async getAlarmTypes() {
      let options = { params: { company_id: this.$auth.user.company_id } };

      this.$axios.get("device_types", options).then((data) => {
        this.device_type_list = data.data;
      });
    },
    async getBuildingTypes() {
      this.$axios
        .get(`building_types`, {
          params: {
            company_id: this.$auth.user.company_id,
          },
        })
        .then(({ data }) => {
          this.buildingTypes = data;
        });
    },
    async getCustomersList() {
      this.payloadOptions = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      try {
        this.$axios
          .get("customers-all", this.payloadOptions)
          .then(({ data }) => {
            this.customersList = data;
          });
      } catch (e) {}
    },
    submit_primary() {
      this.loading = true;
      if (this.isNewInvoice) {
        this.generateCusotmerInvoices(this.payload.customer_id);
        this.loading = false;

        return false;
      }

      let customer = new FormData();

      for (const key in this.payload) {
        if (this.payload[key] != "")
          if (this.payload[key] != null)
            customer.append(key, this.payload[key]);
      }
      if (this.editId) customer.append("editId", this.editId);
      customer.append("company_id", this.$auth.user.company_id);
      customer.append("quotation_id", this.item.id);

      // customer.append("payment_type", this.payment_type);
      // customer.append("device_service_id", this.selectedItem.id);
      // customer.append("start_date", this.payload.start_date);
      // customer.append("end_date", this.end_date);

      // customer.append(
      //   "product_discount_price",
      //   this.product_discount_price_amount
      // );
      // customer.append("product_final_price", this.product_final_price);
      // customer.append(
      //   "product_price",
      //   parseFloat(this.product_final_price) +
      //     parseFloat(this.product_discount_price_amount)
      // );

      this.$axios
        .post("/customers", customer)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.primary_errors = [];
            if (data.errors) this.primary_errors = data.errors;
            this.color = "red";

            this.snackbar = true;
            this.response = data.message;
          } else {
            this.color = "background";
            this.primary_errors = [];
            this.snackbar = true;
            this.response = data.message;

            let new_customer_id = data.record.id;
            //generate Invoices
            this.loading = true;

            let invoiceStatus = this.generateCusotmerInvoices(new_customer_id);

            console.log("invoiceStatus", invoiceStatus);

            // setTimeout(() => {
            //   this.$emit("closeDialog");

            //   if (this.inquiry_to_quotation) {
            //     this.$router.push("/alarm/quotations");
            //   }
            // }, 1000);
          }
        })
        .catch((e) => {
          this.loading = false;

          if (e.response.data.errors) {
            this.primary_errors = e.response.data.errors;
            this.color = "red";

            this.snackbar = true;
            this.response = e.response.data.message;
          }
        });
    },

    generateCusotmerInvoices(customer_id) {
      this.loading = true;

      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          customer_id: customer_id,
          device_service_id: this.selectedItem.id,
          product_price: this.product_price,
          product_discount_price: this.product_discount_price,
          product_final_price: this.product_final_price,
          payment_type: this.payment_type,
          start_date: this.payload.start_date,
          end_date: this.end_date,
          licence_duration_years: this.licence_duration_years,
          total_invoice_count: this.total_invoice_count,
          quotation_id: this.item?.id,
        },
      };

      this.$axios
        .post("customer_product_invoice_submition", options.params)
        .then((data) => {
          this.loading = false;

          if (data.status) {
            this.snackbar = true;
            this.response = "Invoices Created Successfully";
            setTimeout(() => {
              this.$emit("closeDialog");
            }, 1000);
            return true;
          } else {
            this.snackbar = true;
            this.response = "Error Occured";

            return false;
          }
        })
        .catch((e) => {
          this.loading = false;

          if (e.response.data.errors) {
            this.primary_errors = e.response.data.errors;
            this.color = "red";

            this.snackbar = true;
            this.response = e.response.data.message;
          }
        });
    },
  },
};
</script>
