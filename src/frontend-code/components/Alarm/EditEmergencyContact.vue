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
          <v-row class="pt-0">
            <v-col md="12" cols="12" sm="12" dense>
              <!-- <v-autocomplete
                v-model="selectedItem"
                :items="items"
                label="Select or add an item"
                @change="addNewItem"
                :hide-no-data="true"
              ></v-autocomplete> -->

              <v-select
                @change="loadAddressContent(false)"
                label="Contact  Type"
                :items="addressTypes"
                item-text="name"
                item-value="name"
                v-model="payload_primary.address_type"
                dense
                outlined
                hide-details
                small
              ></v-select>
              <span
                v-if="errors && errors.address_type"
                class="text-danger mt-2"
                >{{ errors.address_type[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Address"
                dense
                small
                outlined
                type="text"
                v-model="payload_primary.address"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.address"
                class="text-danger mt-2"
                >{{ primary_errors.address[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Office Phone"
                dense
                small
                outlined
                type="text"
                v-model="payload_primary.office_phone"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.office_phone"
                class="text-danger mt-2"
                >{{ primary_errors.office_phone[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Person First  Name"
                dense
                small
                outlined
                type="text"
                v-model="payload_primary.first_name"
                hide-details
              ></v-text-field
              ><span
                v-if="primary_errors && primary_errors.first_name"
                class="text-danger mt-2"
                >{{ primary_errors.first_name[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Person Last Name"
                dense
                small
                outlined
                type="text"
                v-model="payload_primary.last_name"
                hide-details
              ></v-text-field
              ><span
                v-if="primary_errors && primary_errors.last_name"
                class="text-danger mt-2"
                >{{ primary_errors.last_name[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Phone 1"
                dense
                small
                outlined
                type="text"
                v-model="payload_primary.phone1"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.phone1"
                class="text-danger mt-2"
                >{{ primary_errors.phone1[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Phone 2"
                dense
                small
                outlined
                type="text"
                v-model="payload_primary.phone2"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.phone2"
                class="text-danger mt-2"
                >{{ primary_errors.phone2[0] }}</span
              >
            </v-col>

            <v-col cols="6" dense>
              <v-text-field
                label="Email"
                dense
                small
                outlined
                type="email"
                v-model="payload_primary.email"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.email"
                class="text-danger mt-2"
                >{{ primary_errors.email[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Whatsapp"
                dense
                small
                outlined
                type="text"
                v-model="payload_primary.whatsapp"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.whatsapp"
                class="text-danger mt-2"
                >{{ primary_errors.whatsapp[0] }}</span
              >
            </v-col>

            <v-col cols="6" dense>
              <v-text-field
                label="Working Hours"
                dense
                small
                outlined
                type="text"
                v-model="payload_primary.working_hours"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.working_hours"
                class="text-danger mt-2"
                >{{ primary_errors.working_hours[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Distance From Building"
                dense
                small
                outlined
                type="text"
                v-model="payload_primary.distance"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.distance"
                class="text-danger mt-2"
                >{{ primary_errors.distance[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Latitude"
                dense
                small
                outlined
                type="text"
                v-model="payload_primary.latitude"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.latitude"
                class="text-danger mt-2"
                >{{ primary_errors.latitude[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Longitude"
                dense
                small
                outlined
                type="text"
                v-model="payload_primary.longitude"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.longitude"
                class="text-danger mt-2"
                >{{ primary_errors.longitude[0] }}</span
              >
            </v-col>
            <v-col cols="12" dense>
              <v-text-field
                label="Notes"
                dense
                small
                outlined
                type="text"
                v-model="payload_primary.notes"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.notes"
                class="text-danger mt-2"
                >{{ primary_errors.address[0] }}</span
              >
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" class="text-right">
              <!-- <v-btn
                small
                :loading="loading"
                color="primary"
                @click="$emit('closeDialog')"
              >
                Close
              </v-btn> -->
              <v-btn
                small
                :loading="loading"
                color="primary"
                @click="submit_primary"
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
  props: [
    "customer_id",
    "customer_contacts",
    "customer",
    "editId",
    "isMapviewOnly",
  ],
  data: () => ({
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
    payload_primary: {
      attachment: "",

      phone1: "",
      phone2: "",
      office_phone: "",
      email: "",
      whatsapp: "",
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
  }),
  created() {
    this.payload_primary = {};
    this.preloader = false;
    // this.getBranchesList();

    if (this.$store.state.storeAlarmControlPanel?.AddressTypes) {
      this.addressTypes = this.$store.state.storeAlarmControlPanel.AddressTypes;
    }

    // setTimeout(() => {
    //console.log(this.editAddressType);
    if (this.editId != "") {
      this.payload_primary.editId = this.editId;
      this.loadAddressContent(true);
    }
    // }, 1000);

    // if (this.customer) {
    //   this.payload_primary = this.customer.primary_contact;
    //   this.primary_previewImage = this.payload_primary.profile_picture;

    //   this.payload_secondary = this.customer.secondary_contact;
    //   this.secondary_previewImage = this.payload_secondary.profile_picture;
    // }
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    addNewItem(value) {
      // Check if the value is already in the items list
      if (!this.items.includes(value)) {
        // Add the new value to the items list
        this.items.push(value);
      }
    },
    loadAddressContent(is_address_type) {
      // let _payload_primary = this.customer_contacts.filter(
      //   (el) => el.address_type == this.payload_primary.address_type
      // );
      let _payload_primary = [];
      if (this.editId != "") {
        _payload_primary = this.customer_contacts.filter(
          (el) => el.id == this.payload_primary.editId
        );
      }

      if (_payload_primary[0]) {
        if (is_address_type)
          this.payload_primary.address_type = _payload_primary[0].address_type;
        this.payload_primary.first_name = _payload_primary[0].first_name;
        this.payload_primary.last_name = _payload_primary[0].last_name;
        this.payload_primary.address = _payload_primary[0].address;
        this.payload_primary.phone1 = _payload_primary[0].phone1;
        this.payload_primary.phone2 = _payload_primary[0].phone2;
        this.payload_primary.office_phone = _payload_primary[0].office_phone;
        this.payload_primary.email = _payload_primary[0].email;
        this.payload_primary.whatsapp = _payload_primary[0].whatsapp;
        this.payload_primary.latitude = _payload_primary[0].latitude;
        this.payload_primary.longitude = _payload_primary[0].longitude;
        this.payload_primary.working_hours = _payload_primary[0].working_hours;
        this.payload_primary.distance = _payload_primary[0].distance;
        this.payload_primary.notes = _payload_primary[0].notes;
        this.payload_primary.editId = _payload_primary[0].id;
      }
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
          this.payload_primary.branch_id = this.branchesList[0].id;
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
    //primary
    onpick_primary_attachment() {
      this.$refs.primary_attachment_input.click();
    },
    primary_attachment(e) {
      this.primary_upload.name = e.target.files[0] || "";

      let input = this.$refs.primary_attachment_input;
      let file = input.files;
      //console.log(file);
      if (file[0] && file[0].size > 1024 * 1024) {
        e.preventDefault();
        this.primary_errors["logo"] = [
          "File too big (> 1MB). Upload less than 1MB",
        ];
        return;
      }

      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
          this.primary_previewImage = e.target.result;
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);
      }
    },

    submit_primary() {
      let customer = new FormData();

      for (const key in this.payload_primary) {
        if (this.payload_primary[key] != "")
          customer.append(key, this.payload_primary[key]);
      }
      let address_type = "";
      if (typeof this.payload_primary["address_type"] === "object") {
        address_type = this.payload_primary["address_type"].name;
      } else {
        address_type = this.payload_primary.address_type;
      }
      customer.append("address_type", address_type);
      customer.append("company_id", this.$auth.user.company_id);
      customer.append("customer_id", this.customer_id);
      // if (this.editAddressType != "") customer.append("editAddressType", true);

      if (this.editId != "" && this.editId != null && this.editId != "null") {
        customer.append("editId", this.editId);
      } else {
        customer.delete("editId");
      }

      console.log(customer);

      this.$axios
        .post("/customers_contact_update", customer)
        .then(({ data }) => {
          //this.loading = false;

          if (!data.status) {
            this.primary_errors = [];
            if (data.primary_errors) this.primary_errors = data.primary_errors;
            this.color = "red";

            this.snackbar = true;
            this.response = data.message;
          } else {
            this.color = "background";
            this.primary_errors = [];
            this.snackbar = true;
            this.response = "Address Details Updated successfully";

            this.$emit("closeDialog");
          }
        })
        .catch((e) => {
          if (e.response.data.errors) {
            this.primary_errors = e.response.data.errors;
            this.color = "red";

            this.snackbar = true;
            this.response = e.response.data.message;
          }
        });
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

      if (this.primary_upload.name) {
        branch.append("logo", this.primary_upload.name);
      }

      this.$axios
        .post(`/branch/${this.branch.id}`, branch)
        .then(({ data }) => {
          //this.loading = false;

          if (!data.status) {
            this.primary_errors = [];
            if (data.primary_errors) this.primary_errors = data.primary_errors;
            this.color = "red";

            this.snackbar = true;
            this.response = data.message;
          } else {
            this.primary_errors = [];
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
