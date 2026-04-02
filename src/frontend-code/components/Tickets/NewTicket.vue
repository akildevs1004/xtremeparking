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
                <v-col :cols="is_admin ? 6 : 12" dense>
                  <v-text-field
                    label="Subject"
                    dense
                    small
                    outlined
                    type="text"
                    class="smalltextbox"
                    v-model="payload_ticket.subject"
                    hide-details
                  ></v-text-field>
                  <span
                    v-if="errors && errors.subject"
                    class="text-danger mt-2"
                    >{{ errors.subject[0] }}</span
                  >
                </v-col>

                <v-col cols="3" v-if="is_admin"
                  ><v-autocomplete
                    clearable
                    style="width: 200px"
                    class="reports-events-autocomplete"
                    v-model="filter_customer_id"
                    :items="customersList"
                    dense
                    placeholder="Select Customer"
                    outlined
                    item-value="id"
                    item-text="building_name"
                    hide-details
                  >
                  </v-autocomplete
                ></v-col>
                <v-col cols="3" v-if="is_admin">
                  <v-autocomplete
                    clearable
                    style="width: 200px"
                    class="reports-events-autocomplete"
                    v-model="category_id"
                    :items="categoryList"
                    dense
                    placeholder="Category"
                    outlined
                    item-value="id"
                    item-text="name"
                    hide-details
                  >
                  </v-autocomplete
                ></v-col>
                <v-col cols="12" dense>
                  <ClientOnly style="height: 600px; overflow: scroll">
                    <tiptap-vuetify
                      class="tiptap-icon ma-1"
                      v-model="payload_ticket.description"
                      :extensions="extensions"
                      height="450"
                      style="
                        height: 450px;
                        overflow: auto;
                        border: 1px solid black;
                      "
                      v-scroll.self="onScroll"
                      :toolbar-attributes="{
                        color: 'background red--text',
                      }"
                    />
                    <template #placeholder> Loading... </template>
                  </ClientOnly>
                  <!-- <v-textarea
                    style="width: 100%"
                    label="Description"
                    dense
                    outlined
                    type="text"
                    v-model="payload_ticket.description"
                    hide-details
                    rows="8"
                  ></v-textarea> -->
                </v-col>
              </v-row>

              <span
                v-if="errors && errors.description"
                class="text-danger mt-2"
                >{{ errors.description[0] }}</span
              >
            </v-col>
          </v-row>

          <v-row style="margin-top: 15px">
            <v-col cols="12"> <h3>Attachments</h3></v-col>
            <v-col cols="12" class="text-right mt-0 pt-0">
              <v-form class="mt-0" ref="form" method="post" lazy-validation>
                <v-row v-for="(d, index) in Document.items" :key="index">
                  <v-col cols="5">
                    <v-text-field
                      small
                      type="text"
                      class="smalltextbox"
                      label="Title"
                      dense
                      outlined
                      v-model="d.title"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="5">
                    <div class="form-group">
                      <v-file-input
                        label="Attachment"
                        dense
                        outlined
                        class="smalltextbox"
                        v-model="d.file"
                        placeholder="Upload your file"
                        :rules="FileRules"
                      >
                        <template v-slot:selection="{ text }">
                          <v-chip v-if="text" small label color="primary">
                            {{ text }}
                          </v-chip>
                        </template>
                      </v-file-input>

                      <span
                        v-if="errors && errors.attachment"
                        class="text-danger mt-2"
                        >{{ errors.attachment[0] }}</span
                      >
                    </div>
                  </v-col>
                  <v-col cols="2">
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
              </v-form>
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
                Create Ticket
              </v-btn></v-col
            >
          </v-row>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import {
  TiptapVuetify,
  Heading,
  Bold,
  Italic,
  Strike,
  Underline,
  Paragraph,
  BulletList,
  OrderedList,
  ListItem,
  Blockquote,
  History,
} from "tiptap-vuetify";
export default {
  props: ["customer_id", "security_id", "editId", "editItem", "editable"],
  components: { TiptapVuetify },
  data: () => ({
    category_id: null,
    TitleRules: [(v) => !!v || "Title is required"],
    customersList: [],
    filter_customer_id: null,
    categoryList: [],
    extensions: [
      History,
      Blockquote,
      Underline,
      Strike,
      Italic,
      ListItem,
      BulletList,
      OrderedList,
      [
        Heading,
        {
          options: {
            levels: [1, 2, 3],
          },
        },
      ],
      Bold,
      Paragraph,
    ],
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
    //end editor
  }),
  created() {
    this.primary_previewImage = null;
    this.payload_ticket = { subject: "", description: "" };
    this.getCustomersList();

    this.getCategoriesList();
    this.preloader = false;

    if (this.$auth.user.user_type == "company") this.is_admin = true;
  },

  methods: {
    async getCustomersList() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`/customers_list`, options).then(({ data }) => {
        this.customersList = data;
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
      this.Document.items.splice(index, 1);
      //this.displayForm = false;
    },
    save_documents() {
      //if (!this.$auth?.user?.customer) return false;

      if (this.customer_id || this.security_id || this.filter_customer_id) {
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
    // //primary
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

    // submit_primary() {
    //   let customer = new FormData();

    //   for (const key in this.payload_ticket) {
    //     if (this.payload_ticket[key] != "")
    //       if (this.payload_ticket[key] != null)
    //         customer.append(key, this.payload_ticket[key]);
    //   }

    //   if (this.primary_upload.name) {
    //     customer.append("attachment", this.primary_upload.name);
    //   }

    //   customer.append("company_id", this.$auth.user.company_id);

    //   // if (this.editAddressType != "") customer.append("editAddressType", true);

    //   if (this.editId) {
    //     customer.append("editId", this.editId);
    //     customer.append("web_login_access", this.web_login_access);
    //   }

    //   this.$axios
    //     .post("/technicians", customer)
    //     .then(({ data }) => {
    //       //this.loading = false;

    //       if (!data.status) {
    //         this.primary_errors = [];
    //         if (data.errors) this.primary_errors = data.errors;
    //         this.color = "red";

    //         this.snackbar = true;
    //         this.response = data.message;
    //       } else {
    //         this.color = "background";
    //         this.primary_errors = [];
    //         this.snackbar = true;
    //         this.response = data.message;
    //         setTimeout(() => {
    //           this.$emit("closeDialog");
    //         }, 1000);
    //       }
    //     })
    //     .catch((e) => {
    //       if (e.response.data.errors) {
    //         this.primary_errors = e.response.data.errors;
    //         this.color = "red";

    //         this.snackbar = true;
    //         this.response = e.response.data.message;
    //       }
    //     });
    // },
  },
};
</script>
