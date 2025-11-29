<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row>
      <v-col cols="6">
        <div class="text-center mt-7">
          <v-img
            style="
              width: 150px;
              height: 150px;
              border-radius: 50%;
              margin: 0 auto;
            "
            :src="previewImage || '/no-business_profile.png'"
          ></v-img>
          <v-btn
            class="mt-2"
            style="width: 100%"
            small
            @click="onpick_attachment"
            >{{ !upload.name ? "Upload" : "Change" }}
            <v-icon right dark color="primary">mdi-cloud-upload</v-icon>
          </v-btn>

          <input
            required
            type="file"
            @change="attachment"
            style="display: none"
            accept="image/*"
            ref="attachment_input"
          />

          <span v-if="errors && errors.logo" class="text-danger mt-2">{{
            errors.logo[0]
          }}</span>
        </div>
      </v-col>

      <v-col cols="6" dense>
        <v-row>
          <v-col cols="12" dense>
            <v-text-field
              label="Notes Title"
              dense
              outlined
              type="text"
              v-model="event_payload.title"
              hide-details
            ></v-text-field>
            <span v-if="errors && errors.title" class="text-danger mt-2">{{
              errors.title[0]
            }}</span>
          </v-col>
          <v-col cols="12" dense>
            <v-textarea
              style="height: 60px; width: 100%"
              label="Notes/Description"
              dense
              outlined
              type="text"
              v-model="event_payload.notes"
              hide-details
            ></v-textarea>
            <span v-if="errors && errors.notes" class="text-danger mt-2">{{
              errors.notes[0]
            }}</span>
          </v-col>
        </v-row>
      </v-col>

      <v-col cols="12" class="text-right">
        <v-btn small :loading="loading" color="primary" @click="submit">
          {{ !customer_id ? "Submit" : "Update" }}
        </v-btn></v-col
      >
    </v-row>
  </div>
</template>

<script>
export default {
  props: ["customer_id", "alarmId", "editItem"],
  data: () => ({
    response: "",
    snackbar: false,
    show1: false,
    buildingTypes: [],
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
    event_payload: {},
    contact_payload: {},

    e1: 1,
    errors: [],
    previewImage: null,
  }),
  created() {
    if (this.editItem) {
      this.previewImage = this.editItem.picture;
      this.event_payload.title = this.editItem.title;
      this.event_payload.notes = this.editItem.notes;
      this.event_payload.alarm_id = this.editItem.alarm_id;
    }
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
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
          this.event_payload.branch_id = this.branchesList[0].id;
          //this.branch_id = this.$auth.user.branch_id || "";
        });
    },
    getBuildingTypes() {
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

      for (const key in this.event_payload) {
        if (this.event_payload[key] != "")
          customer.append(key, this.event_payload[key]);
      }
      customer.append("company_id", this.$auth.user.company_id);

      if (this.editItem) customer.append("notes_id", this.editItem.id);

      customer.append("customer_id", this.customer_id);

      customer.append("alarm_id", this.alarmId);

      if (this.upload.name) {
        customer.append("attachment", this.upload.name);
      }
      if (this.customer_id) {
        this.$axios
          .post("/customer_add_event_notes", customer)
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
              this.response = "Customer Details Updated successfully";

              this.$emit("closeDialog");
            }
          })
          .catch((e) => {
            if (e.response.data.errors) {
              this.errors = e.response.data.errors;
              this.color = "red";

              this.snackbar = true;
              this.response = e.response.data.message;
            }
          });
      } else {
        this.$axios
          .post("/customer_update_event_notes", customer)
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
              this.response = "Customer Details Created successfully";

              this.$emit("closeDialog");
            }
          })
          .catch((e) => {
            if (e.response.data.errors) {
              this.errors = e.response.data.errors;
              this.color = "red";

              this.snackbar = true;
              this.response = e.response.data.message;
            }
          });
      }
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
