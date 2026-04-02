<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-dialog v-model="dialogGoogleMap" width="600px">
      <v-card>
        <v-card-title dense class="popup_background">
          <span>Google Map Location</span>
          <v-spacer></v-spacer>
          <v-icon color="primary" @click="dialogGoogleMap = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text class="background">
          <v-container style="min-height: 100px">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d14433.170866542543!2d55.291463!3d25.2607368!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e5f43145ef11097%3A0x78e0d1eb3fed1225!2sAkil%20Security%20%26%20Alarm%20System%20Dubai%20LLC!5e0!3m2!1sen!2sae!4v1719239839580!5m2!1sen!2sae"
              width="100%"
              height="450"
              style="border: 0"
              allowfullscreen=""
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-dialog v-model="dialogEditEmergency" width="800px">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span style="color: black111">Contacts </span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="dialogEditEmergency = false"
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text class="background">
          <v-container style="min-height: 100px">
            <!-- <AlarmEditEmergencyContact
              :key="key"
              :customer_id="customer_id"
              :customer_contacts="customer_contacts"
              :customer="customer"
              @closeDialog="closeDialog"
              :editId="editId"
            /> -->

            <CompCustomersEditContact
              @callrefreshData="reloadContent()"
              :customer_id="customer_id"
              :customer="customer"
              :isMapviewOnly="isMapviewOnly"
              :isEditable="isEditable"
              :key="key"
              :customer_contacts="customer_contacts"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <div
      style="height: 50px"
      v-if="!customer_contacts || customer_contacts.length == 0"
      class="text-center"
    >
      No Contacts Available
    </div>

    <v-row>
      <v-col></v-col>
      <v-col v-if="isEditable" class="text-center" style="max-width: 150px">
        <v-btn
          color="primary"
          @click="
            editId = null;
            key = key + 1;

            dialogEditEmergency = true;
          "
          dense
          x-small
        >
          + Add
        </v-btn>
      </v-col>
    </v-row>

    <v-row>
      <v-col>
        <v-tabs right show-arrows class="tabswidthalignment1">
          <v-tab v-for="(item, index) in customer_contacts" :key="item.id">
            <v-icon
              color="red"
              v-if="item.address_type.toLowerCase() == 'primary'"
              >mdi-account-star</v-icon
            >
            <v-icon
              color="red"
              v-else-if="item.address_type.toLowerCase() == 'fire'"
              >mdi-fire-circle</v-icon
            >
            <v-icon
              color="red"
              v-else-if="item.address_type.toLowerCase() == 'medical'"
              >mdi-ambulance</v-icon
            >
            <v-icon
              color="red"
              v-else-if="item.address_type.toLowerCase() == 'hospital'"
              >mdi-hospital-building</v-icon
            >
            <v-icon
              color="red"
              v-else-if="item.address_type.toLowerCase() == 'police'"
              >mdi-car-emergency</v-icon
            >
            <v-icon v-else>mdi-card-account-phone</v-icon>
            {{ item.address_type }}</v-tab
          >
          <v-tab-item
            v-for="(item, index) in customer_contacts"
            :key="item.id + 50"
            name="index+50"
          >
            <v-card class="elevation-1">
              <CompCustomersEditContact
                @callrefreshData="reloadContent()"
                :customer_id="item.customer_id"
                :contact="item"
                :customer="customer"
                :isMapviewOnly="isMapviewOnly"
                :isEditable="isEditable"
                :key="item.id"
                :contact_id="item.id"
                :customer_contacts="customer_contacts"
              />
              <!-- <v-row>
                  <v-col cols="10">
                    <h3 style="">{{ item.address_type }}</h3>
                  </v-col>

                  <v-col cols="2" class="text-right">
                    <v-menu bottom left v-if="!isMapviewOnly && isEditable">
                      <template v-slot:activator="{ on, attrs }">
                        <v-btn dark-2 icon v-bind="attrs" v-on="on">
                          <v-icon>mdi-dots-vertical</v-icon>
                        </v-btn>
                      </template>
                      <v-list width="120" dense>
                        <v-list-item
                          v-if="can('device_notification_contnet_view')"
                          @click="editContactDetails(item.id)"
                        >
                          <v-list-item-title style="cursor: pointer">
                            <v-icon color="secondary" small> mdi-eye </v-icon>
                            Edit
                          </v-list-item-title>
                        </v-list-item>

                        <v-list-item
                          v-if="can('device_notification_contnet_delete')"
                          @click="deleteContactDetails(item.id)"
                        >
                          <v-list-item-title style="cursor: pointer">
                            <v-icon color="error" small> mdi-delete </v-icon>
                            Delete
                          </v-list-item-title>
                        </v-list-item>
                      </v-list>
                    </v-menu>
                  </v-col>
                </v-row>
                <v-divider></v-divider>

                <v-row class="pt-5 color-black">
                  <v-col cols="6" dense>
                    <v-text-field
                      readonly
                      label="Address"
                      dense
                      small
                      outlined
                      type="text"
                      v-model="item.address"
                      hide-details
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6" dense>
                    <v-text-field
                      readonly
                      label="Office Phone"
                      dense
                      small
                      outlined
                      type="text"
                      v-model="item.office_phone"
                      hide-details
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6" dense>
                    <v-text-field
                      readonly
                      label="Person First  Name"
                      dense
                      small
                      outlined
                      type="text"
                      v-model="item.first_name"
                      hide-details
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6" dense>
                    <v-text-field
                      readonly
                      label="Person Last Name"
                      dense
                      small
                      outlined
                      type="text"
                      v-model="item.last_name"
                      hide-details
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6" dense>
                    <v-text-field
                      readonly
                      label="Phone 1"
                      dense
                      small
                      outlined
                      type="text"
                      v-model="item.phone1"
                      hide-details
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6" dense>
                    <v-text-field
                      readonly
                      label="Phone 2"
                      dense
                      small
                      outlined
                      type="text"
                      v-model="item.phone2"
                      hide-details
                    ></v-text-field>
                  </v-col>

                  <v-col cols="6" dense>
                    <v-text-field
                      readonly
                      label="Email"
                      dense
                      small
                      outlined
                      type="email"
                      v-model="item.email"
                      hide-details
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6" dense>
                    <v-text-field
                      readonly
                      label="Whatsapp"
                      dense
                      small
                      outlined
                      type="text"
                      v-model="item.whatsapp"
                      hide-details
                    ></v-text-field>
                  </v-col>

                  <v-col cols="6" dense>
                    <v-text-field
                      readonly
                      label="Working Hours"
                      dense
                      small
                      outlined
                      type="text"
                      v-model="item.working_hours"
                      hide-details
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6" dense>
                    <v-text-field
                      readonly
                      label="Distance From Building"
                      dense
                      small
                      outlined
                      type="text"
                      v-model="item.distance"
                      hide-details
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6" dense>
                    <v-text-field
                      readonly
                      label="Latitude"
                      dense
                      small
                      outlined
                      type="text"
                      v-model="item.latitude"
                      hide-details
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6" dense>
                    <v-text-field
                      readonly
                      label="Longitude"
                      dense
                      small
                      outlined
                      type="text"
                      v-model="item.longitude"
                      hide-details
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" dense>
                    <v-text-field
                      readonly
                      label="Notes"
                      dense
                      small
                      outlined
                      type="text"
                      v-model="item.notes"
                      hide-details
                    ></v-text-field>
                  </v-col>
                </v-row> -->
            </v-card>
          </v-tab-item>
        </v-tabs>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import AlarmEditEmergencyContact from "../../components/Alarm/EditEmergencyContact.vue";
import CompCustomersEditContact from "./CompCustomersEditContact.vue";

export default {
  components: { AlarmEditEmergencyContact, CompCustomersEditContact },
  props: [
    "customer",
    "customer_id",
    "customer_contacts",
    "isMapviewOnly",
    "isEditable",
  ],
  data: () => ({
    slider: [
      "red",
      "green",
      "orange",
      "blue",
      "pink",
      "purple",
      "indigo",
      "cyan",
      "deep-purple",
      "light-green",
      "deep-orange",
      "blue-grey",
    ],
    key: 1,
    dialogEditEmergency: false,
    dialogGoogleMap: false,
    branchesList: [],
    startDateMenuOpen: "",
    endDateMenuOpen: "",
    preloader: false,
    loading: false,
    upload: {
      name: "",
    },
    start_date: "",
    end_date: "",
    editId: "",
    customer_payload: {
      branch_id: "",
      building_type_id: "",
      first_name: "",
      last_name: "",
      building_name: "",
      house_number: "",
      street_number: "",
      city: "",
      state: "",
      country: "",
      landmark: "",
      latitude: "",
      longitude: "",
      contact_number: "",
      start_date: "",
      end_date: "",
    },
    contact_payload: {
      name: "",
      number: "",
      position: "",
      whatsapp: "",
    },
    // location: "",
    geographic_payload: {
      location: "",
      lat: "",
      lon: "",
    },
    e1: 1,
    errors: [],
    previewImage: null,
    snackbar: false,
    response: "",
  }),
  created() {
    this.preloader = false;
    this.getBranchesList();
  },
  computed: {
    columns() {
      if (this.$vuetify.breakpoint.xl) {
        return 4;
      }

      if (this.$vuetify.breakpoint.lg) {
        return 3;
      }

      if (this.$vuetify.breakpoint.md) {
        return 2;
      }

      return 1;
    },
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    reloadContent() {
      console.log("reloadContent");
      this.dialogEditEmergency = false;
      this.$emit("callrefreshData");
    },
    deleteContactDetails(id) {
      if (confirm("Are you sure you wish to delete ?")) {
        this.$axios
          .delete(`customer_contact/${id}`)

          .then(({ data }) => {
            this.color = "background";
            this.errors = [];
            this.snackbar = true;
            this.response = "Contact Details are deleted successfully";

            this.$emit("closeDialog");
            //this.branch_id = this.$auth.user.branch_id || "";
          });
      }
    },
    editContactDetails(editId) {
      this.key = this.key + 1;
      this.editId = editId;
      this.dialogEditEmergency = true;
    },
    getBranchesList() {
      this.$axios
        .get(`branches_list`, {
          params: {
            company_id: this.$auth.user.company_id,
          },
        })
        .then(({ data }) => {
          this.branchesList = data;
          this.customer_payload.branch_id = this.branchesList[0].id;
          //this.branch_id = this.$auth.user.branch_id || "";
        });
    },
    onpick_attachment() {
      this.$refs.attachment_input.click();
    },
    attachment(e) {
      this.upload.name = e.target.files[0] || "";

      let input = this.$refs.attachment_input;
      let file = input.files;

      if (file[0].size > 1024 * 1024) {
        e.preventDefault();
        this.errors["logo"] = ["File too big (> 1MB). Upload less than 1MB"];
        return;
      }

      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
          //croppedimage step6
          this.previewImage = e.target.result;

          // this.selectedFile = event.target.result;

          // this.$refs.cropper.replace(this.selectedFile);
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);

        // this.dialogCropping = true;
      }
    },
    submit() {
      let customer = new FormData();

      for (const key in this.customer_payload) {
        customer.append(key, fields[key]);
      }
      if (this.upload.name) {
        customer.append("logo", this.upload.name);
      }

      this.$axios
        .post("/customer", customer)
        .then(({ data }) => {
          //this.loading = false;

          if (!data.status) {
            this.errors = [];
            if (data.errors) this.errors = data.errors;
            this.color = "red";

            this.snackbar = true;
            this.response = data.message;
          } else {
            this.color = "background";
            this.errors = [];
            this.snackbar = true;
            this.response = "Address Details Updated successfully";

            this.$emit("closeDialog");
          }
        })
        .catch((e) => console.log(e));
    },
    closeDialog() {
      //this.key = this.key + 1;
      this.$emit("closeDialog");
      this.dialogEditEmergency = false;
      // location.reload();
      //  this.dialogEditEmergency = false;
    },
    update() {
      let branch = new FormData();
      branch.append("company_id", this.$auth.user.company_id);
      branch.append("branch_name", this.branch.branch_name);
      branch.append("user_id", this.branch.user_id);

      branch.append("licence_number", this.branch.licence_number);
      branch.append(
        "licence_issue_by_department",
        this.branch.licence_issue_by_department
      );
      branch.append("licence_expiry", this.branch.licence_expiry);
      branch.append("lat", this.branch.lat);
      branch.append("lon", this.branch.lon);
      branch.append("address", this.branch.address);

      if (this.upload.name) {
        branch.append("logo", this.upload.name);
      }

      this.$axios
        .post(`/branch/${this.branch.id}`, branch)
        .then(({ data }) => {
          //this.loading = false;

          if (!data.status) {
            this.errors = [];
            if (data.errors) this.errors = data.errors;
            this.color = "red";

            this.snackbar = true;
            this.response = data.message;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Branch updated successfully";
            this.getDataFromApi();
            this.branchDialog = false;
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
