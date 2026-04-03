<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <div
      style="height: 50px"
      v-if="!customer_contacts || customer_contacts.length == 0"
      class="text-center"
    >
      No Contacts Available
    </div>

    <v-row>
      <v-col
        v-if="isEditable"
        cols="12"
        class="text-right"
        style="padding-top: 0px"
      >
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
          Add
        </v-btn>
      </v-col>

      <v-row>
        <v-col cols="12">
          <v-tabs right class="customerEmergencyContactTabs" show-arrows>
            <v-tab v-for="(item, index) in customer_contacts" :key="item.id">
              {{ item.address_type }}</v-tab
            >
            <v-tab-item
              v-for="(item, index) in customer_contacts"
              :key="item.id + 50"
              name="index+50"
            >
              <v-card class="elevation-1">
                <SecurityContactInfo
                  :alarmId="alarmId"
                  v-if="customer"
                  :customer="customer"
                  :contact_type="item.address_type"
                  :key="item.address_type"
                />
              </v-card>
            </v-tab-item>
          </v-tabs>
        </v-col>
      </v-row>

      <!-- <v-col
        v-if="1 == 8"
        v-for="(item, index) in customer_contacts"
        :key="item.id"
        cols="4"
        class="mt-3"
        style="line-height: 35px; border-right: #ddd 0px solid"
      >
        <v-card class="elevation-1 pl-1">2222222222222222 </v-card>
      </v-col> -->
    </v-row>
  </div>
</template>

<script>
import AlarmEditEmergencyContact from "../../components/Alarm/EditEmergencyContact.vue";
import SecurityContactInfo from "./SecurityDashboard/SecurityContactInfo.vue";

export default {
  components: { AlarmEditEmergencyContact, SecurityContactInfo },
  props: [
    "alarmId",
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
