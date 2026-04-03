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
          <v-row style="margin-top: 15px">
            <v-col cols="12"> <h3></h3></v-col>
            <v-col cols="12" class="text-right mt-0 pt-0">
              <v-tabs centered color="primary">
                <v-tab> Yearly </v-tab>
                <v-tab> Quarterly </v-tab>
                <v-tab> Monthly </v-tab>

                <v-tab-item>
                  <v-card flat>
                    <v-card-text style="text-align: center">
                      <div class="pricing-container">
                        <div
                          v-if="service"
                          :class="'pricing-card ' + plancolorarray[index]"
                          v-for="(service, index) in servicesList"
                        >
                          <div class="header">
                            {{ service.name }}
                          </div>
                          <div class="price">${{ service.year_amount }}</div>
                          <ul class="features">
                            <li>
                              <span class="check">{{
                                service.sensor_count
                              }}</span>
                              Maximum Sensors
                            </li>
                            <li>
                              <span class="check">✔</span> Email Notificaitons
                            </li>
                            <li>
                              <span class="check">✔</span> SMS NOtifications
                            </li>
                            <li>
                              <span class="check">✔</span> Phone Call
                              Notifications
                            </li>
                            <li>
                              <span class="check">✔</span> Whatsapp
                              Notifications
                            </li>
                            <li>
                              <span class="check">✔</span> Floor Plan sensor
                              Mapping
                            </li>
                          </ul>
                          <button class="buy-button">BUY NOW</button>
                        </div>
                      </div>
                    </v-card-text>
                  </v-card>
                </v-tab-item>
                <v-tab-item>
                  <v-card flat>
                    <v-card-text style="text-align: center">
                      <div class="pricing-container">
                        <div
                          v-if="service"
                          :class="'pricing-card ' + plancolorarray[index]"
                          v-for="(service, index) in servicesList"
                        >
                          <div class="header">
                            {{ service.name }}
                          </div>
                          <div class="price">${{ service.quarter_amount }}</div>
                          <ul class="features">
                            <li>
                              <span class="check">{{
                                service.sensor_count
                              }}</span>
                              Maximum Sensors
                            </li>
                            <li>
                              <span class="check">✔</span> Email Notificaitons
                            </li>
                            <li>
                              <span class="check">✔</span> SMS NOtifications
                            </li>
                            <li>
                              <span class="check">✔</span> Phone Call
                              Notifications
                            </li>
                            <li>
                              <span class="check">✔</span> Whatsapp
                              Notifications
                            </li>
                            <li>
                              <span class="check">✔</span> Floor Plan sensor
                              Mapping
                            </li>
                          </ul>
                          <button class="buy-button">BUY NOW</button>
                        </div>
                      </div>
                    </v-card-text>
                  </v-card>
                </v-tab-item>
                <v-tab-item>
                  <v-card flat>
                    <v-card-text style="text-align: center">
                      <div class="pricing-container">
                        <div
                          v-if="service"
                          :class="'pricing-card ' + plancolorarray[index]"
                          v-for="(service, index) in servicesList"
                        >
                          <div class="header">
                            {{ service.name }}
                          </div>
                          <div class="price">${{ service.month_amount }}</div>
                          <ul class="features">
                            <li>
                              <span class="check">{{
                                service.sensor_count
                              }}</span>
                              Maximum Sensors
                            </li>
                            <li>
                              <span class="check">✔</span> Email Notificaitons
                            </li>
                            <li>
                              <span class="check">✔</span> SMS NOtifications
                            </li>
                            <li>
                              <span class="check">✔</span> Phone Call
                              Notifications
                            </li>
                            <li>
                              <span class="check">✔</span> Whatsapp
                              Notifications
                            </li>
                            <li>
                              <span class="check">✔</span> Floor Plan sensor
                              Mapping
                            </li>
                          </ul>
                          <button class="buy-button">BUY NOW</button>
                        </div>
                      </div>
                    </v-card-text>
                  </v-card>
                </v-tab-item>
              </v-tabs>

              <!-- <v-form class="mt-0" ref="form" method="post" lazy-validation>
                <v-row style="text-align: left; font-weight: bold">
                  <v-col> Products/Services </v-col>
                  <v-col style="text-align: center; max-width: 100px">
                    Qty </v-col
                  ><v-col
                    class="greenborder"
                    style="
                      text-align: center;
                      max-width: 100px;
                      background-color: #5b57e9;
                      border: 0px;
                      border-radius: 5%;
                      color: #fff;
                    "
                  >
                    Yearly </v-col
                  ><v-col
                    class="yellowborder"
                    style="
                      border: 0px;
                      text-align: center;
                      max-width: 100px;
                      background-color: #ffc501;
                      border-radius: 5%;
                      color: #fff;
                    "
                  >
                    Quarterly </v-col
                  ><v-col
                    class="blackborder"
                    style="
                      border: 0px;
                      text-align: center;
                      max-width: 100px;
                      background-color: #fd3c7f;
                      border-radius: 5%;
                      color: #fff;
                    "
                  >
                    Monthly </v-col
                  ><v-col style="text-align: center; max-width: 80px"> </v-col>
                </v-row>
                <v-row v-for="(d, index) in Document.items" :key="index">
                  <v-col>
                    <v-select
                      small
                      type="text"
                      class="smalltextbox"
                      label="Name"
                      dense
                      outlined
                      v-model="d.title"
                      :items="['1111', '222', '333', '4444', '555']"
                      hide-details
                    ></v-select>
                  </v-col>
                  <v-col style="max-width: 100px">
                    <v-select
                      small
                      class="smalltextbox"
                      label="Qty"
                      dense
                      outlined
                      v-model="d.title"
                      :items="qtyList"
                      hide-details
                    ></v-select>
                  </v-col>
                  <v-col style="max-width: 100px" class="greenborder">
                    Yearly
                  </v-col>
                  <v-col style="max-width: 100px" class="yellowborder">
                    Quarterly
                  </v-col>
                  <v-col style="max-width: 100px" class="blackborder">
                    Monhtly
                  </v-col>
                  <v-col style="max-width: 80px">
                    <div class="form-group">
                      <v-icon
                        v-if="Document.items.length - 1 == index"
                        class="mt-1"
                        size="20"
                        color="primary"
                        @click="addDocumentFile"
                        >mdi-plus-circle</v-icon
                      >

                      <v-icon
                        class="mt-1"
                        size="20"
                        @click="removeItem(index)"
                        v-else
                        >mdi-delete</v-icon
                      >
                    </div>
                  </v-col>
                </v-row>
              </v-form> -->
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" class="text-right">
              <v-btn
                small
                :loading="loading"
                color="primary"
                @click="save_documents"
              >
                Submit
              </v-btn></v-col
            >
          </v-row>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  props: ["customer", "isEditable"],

  data: () => ({
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
    //end editor

    plancolorarray: ["basic", "standard", "premium"],
  }),
  created() {
    this.primary_previewImage = null;
    this.payload_ticket = { subject: "", description: "" };
    this.getServicesList();

    this.getCategoriesList();
    this.preloader = false;

    if (this.$auth.user.user_type == "company") this.is_admin = true;

    for (let i = 1; i <= 100; i++) {
      this.qtyList.push(i);
    }
  },

  methods: {
    async getServicesList() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios
        .get(`/device_product_services_group`, options)
        .then(({ data }) => {
          this.servicesList = data;
        });
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
    onScroll() {
      this.scrollInvoked++;
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    changeStatus(status) {
      if (this.editable) this.web_login_access = status;
    },
    addDocumentFile() {
      this.Document.items.push({
        title: "",
        file: "",
      });
    },
    removeItem(index) {
      if (confirm("Are you sure want to delete?"))
        this.Document.items.splice(index, 1);
      //this.displayForm = false;
    },
    save_documents() {
      //if (!this.$auth?.user?.customer) return false;

      if (this.customer_id || this.security_id) {
      } else {
        this.snackbar = true;
        this.response = "Operator or Customer Details are not available";
      }
      this.errors = {};
      if (!this.$refs.form.validate()) {
        alert("Enter required fields!");
        return;
      }
      this.loading = true;
      let options = {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      };
      let payload = new FormData();

      this.Document.items.forEach((e) => {
        if (e.title) {
          payload.append(`items[][title]`, e.title);
          payload.append(`items[][file]`, e.file || {});
        }
      });

      payload.append(`company_id`, this.$auth?.user?.company?.id);
      if (this.$auth?.user.customer)
        payload.append(`customer_id`, this.$auth?.user.customer.id);
      else if (this.$auth?.user.security)
        payload.append(`security_id`, this.$auth?.user.security.id);

      payload.append(`customer_id`, this.filter_customer_id);
      if (this.category_id) payload.append(`category_id`, this.category_id);

      payload.append(`subject`, this.payload_ticket.subject);
      payload.append(`description`, this.payload_ticket.description);

      this.$axios
        .post(`tickets`, payload, options)
        .then(({ data }) => {
          this.dialogUploadDocuments = false;
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = data.message;
            //this.getDocumentInfo(this.employeeId);

            // this.close_document_info();
            // this.displayForm = false;
            this.loading = false;

            setTimeout(() => {
              this.$emit("close_dialog");
            }, 1000);
          }
        })
        .catch((e) => {
          this.loading = false;
          if (e.response.data.errors) {
            this.errors = e.response.data.errors;
            this.color = "red";

            this.snackbar = true;
            this.response = e.response.data.message;
          }
        });
    },
  },
};
</script>
