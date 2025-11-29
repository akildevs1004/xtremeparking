<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row v-if="messageReply">
      <v-col md="12" sm="12" cols="12" dense>
        <v-card class="elevation-0 p-2 pa-2" style="border: 1px solid #ddd">
          <v-row>
            <v-col cols="12" style="">
              <v-row class="pt-0">
                <v-col>
                  <h3>Subject: {{ payload_ticket.subject }}</h3>
                </v-col>
                <v-col cols="12" dense>
                  <!-- <v-textarea
                    style="width: 100%"
                    label="Description"
                    dense
                    outlined
                    type="text"
                    v-model="payload_ticket.description"
                    hide-details
                    rows="4"
                  ></v-textarea> -->
                  <ClientOnly style="height: 600px; overflow: scroll">
                    <tiptap-vuetify
                      class="tiptap-icon ma-1"
                      v-model="payload_ticket.description"
                      :extensions="extensions"
                      :height="450"
                      :style="
                        'height:' +
                        setEditorHeight +
                        'px;overflow: auto;border: 1px solid black'
                      "
                      v-scroll.self="onScroll"
                      :toolbar-attributes="{
                        color: 'background red--text',
                      }"
                    />
                    <template #placeholder> Loading... </template>
                  </ClientOnly>
                  <!-- <ClientOnly style-="height:600px">
                    <tiptap-vuetify
                      class="tiptap-icon"
                      v-model="payload_ticket.description"
                      :extensions="extensions"
                      v-scroll.self="onScroll"
                      :toolbar-attributes="{
                        color: 'background red--text',
                      }"
                    />
                    <template #placeholder> Loading... </template>
                  </ClientOnly> -->
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
            <v-col cols="6"> <h3>Attachments</h3></v-col>
            <v-col cols="6">
              <div style="float: right"></div>
            </v-col>
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
                        accept=".jpg,.png,.pdf"
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
            <v-col>
              <!-- <v-checkbox
                class="text-right pb-5 mt-0"
                style="margin-top: 0px"
                v-model="payload_ticket.status"
                label="Close Ticket"
              ></v-checkbox> -->
            </v-col>
            <v-col cols="6" class="text-right pb-5">
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

    <!-- <v-row>
      <v-col cols="12" class="text-right">
        <TicketResponses v-if="editItem" :ticket="editItem" />
      </v-col>
    </v-row> -->
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
import TicketResponses from "./TicketResponses.vue";
export default {
  props: ["editItem", "editId", "messageReply", "editorHeight"],
  components: {
    TiptapVuetify,
    TicketResponses,
  },
  data: () => ({
    TitleRules: [(v) => !!v || "Title is required"],
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
        console.log("File value:", value); // Debugging

        try {
          if (!value) return true; // Allow empty file (if not required)
          if (Array.isArray(value) && value.length === 0) return true; // Handle empty array case

          const file = Array.isArray(value) ? value[0] : value; // Handle single or multiple file uploads

          if (!(file instanceof File)) return "Invalid file format!";
          if (file.size > 200000)
            return "File size should be less than 200 KB!";

          return true;
        } catch (e) {
          console.error("File validation error:", e);
          return "File validation failed!";
        }
      },
    ],
    setEditorHeight: 450,
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
      status: "",
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

    //end editor
  }),
  created() {
    this.primary_previewImage = null;
    this.payload_ticket = { subject: "", description: "" };
    this.preloader = false;

    if (this.editId != "" && this.editItem) {
      this.payload_ticket.subject = this.editItem.subject;
      //this.payload_ticket.description = this.editItem.description;
    }
    if (this.editorHeight) this.setEditorHeight = this.editorHeight;
  },

  methods: {
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
      if (!this.editId) alert("Ticket Id is missing");

      // if (
      //   this.$auth.user.user_type == "company" ||
      //   this.customer_id ||
      //   this.$auth?.user.technician.id
      // ) {
      // } else {
      //   this.snackbar = true;
      //   this.response = "Operator or Customer Details are not available";

      //   return false;
      // }
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
      payload.append(`ticket_id`, this.editId);
      payload.append(`company_id`, this.$auth?.user?.company?.id);
      if (this.$auth?.user.customer)
        payload.append(`customer_id`, this.$auth?.user.customer.id);
      if (this.$auth?.user.security)
        payload.append(`security_id`, this.$auth?.user.security.id);
      if (this.$auth?.user.technician)
        payload.append(`technician_id`, this.$auth?.user.technician.id);
      payload.append(`subject`, this.payload_ticket.subject);
      payload.append(`description`, this.payload_ticket.description);
      payload.append(`status`, this.payload_ticket.status ? 0 : 1);

      this.$axios
        .post(`tickets_responses`, payload, options)
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
