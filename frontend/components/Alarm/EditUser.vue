<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row>
      <v-col md="12" sm="12" cols="12" dense>
        <v-tabs v-model="tab">
          <v-tab>Primary</v-tab>
          <v-tab>Emergency Contacts</v-tab>
          <v-tab>Documents</v-tab>
        </v-tabs>

        <v-tabs-items v-model="tab">
          <v-tab-item>
            <v-card class="elevation-0 p-2" style="padding: 5px">
              <v-row>
                <v-col cols="4">
                  <div class="text-center mt-0">
                    <v-img
                      style="
                        height: auto;
                        min-height: 200px;
                        width: 200px;
                        border-radius: 10%;
                        margin: 0 auto;
                      "
                      :src="primary_previewImage || '/no-business_profile.png'"
                    ></v-img>
                    <v-btn
                      class="mt-2"
                      style="width: 50%"
                      small
                      @click="onpick_primary_attachment"
                      >{{ !editId ? "Upload" : "Change" }}
                      <v-icon right dark color="primary"
                        >mdi-cloud-upload</v-icon
                      >
                    </v-btn>

                    <input
                      required
                      type="file"
                      @change="primary_attachment"
                      style="display: none"
                      accept="image/*"
                      ref="primary_attachment_input"
                    />

                    <span
                      v-if="primary_errors && primary_errors.logo"
                      class="text-danger mt-2"
                      >{{ primary_errors.logo[0] }}</span
                    >
                  </div>
                </v-col>

                <v-col cols="8">
                  <v-row class="pt-0">
                    <v-col cols="6" dense>
                      <v-text-field
                        label="First Name"
                        dense
                        small
                        outlined
                        type="text"
                        v-model="payload_user.first_name"
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
                    <v-col cols="6" dense>
                      <v-text-field
                        label="Last Name"
                        dense
                        small
                        outlined
                        type="text"
                        v-model="payload_user.last_name"
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
                    <v-col cols="6" dense>
                      <v-text-field
                        label="Contact Number"
                        dense
                        small
                        outlined
                        type="number"
                        v-model="payload_user.contact_number"
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
                    <v-col cols="6" dense>
                      <v-text-field
                        label="Email ID(Login)"
                        dense
                        small
                        outlined
                        clearable
                        autocomplete="off"
                        v-model="payload_user.email"
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
                    <v-col cols="6" dense>
                      <v-text-field
                        label="Login Password"
                        :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
                        :type="show1 ? 'text' : 'password'"
                        dense
                        outlined
                        clearable
                        v-model="payload_user.password"
                        hide-details
                        @click:append="show1 = !show1"
                        :readonly="!editable"
                        :filled="!editable"
                      ></v-text-field>
                      <span
                        v-if="errors && errors.password"
                        class="text-danger mt-2"
                        >{{ errors.password[0] }}</span
                      >
                    </v-col>
                    <v-col cols="6" dense>
                      <v-text-field
                        label="Confirm Password"
                        :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
                        dense
                        small
                        outlined
                        clearable
                        :type="show1 ? 'text' : 'password'"
                        v-model="payload_user.confirm_password"
                        hide-details
                        :readonly="!editable"
                        :filled="!editable"
                      ></v-text-field>
                      <span
                        v-if="primary_errors && primary_errors.confirm_password"
                        class="text-danger mt-2"
                        >{{ primary_errors.confirm_password[0] }}</span
                      >
                    </v-col>

                    <v-col cols="6">
                      <v-select
                        :readonly="!editable"
                        :filled="!editable"
                        label="User Role"
                        dense
                        outlined
                        v-model="payload_user.role_id"
                        class="form-select"
                        placeholder="Select Role"
                        :items="[{ id: 0, name: 'None' }, ...roles]"
                        item-value="id"
                        item-text="name"
                      >
                      </v-select>
                    </v-col>
                    <v-col cols="6">
                      <div style="cursor: pointer" v-if="web_login_access == 0">
                        <v-img
                          class="ele1"
                          src="/off.png"
                          style="width: 60px"
                          @click="changeStatus(1)"
                        >
                        </v-img>
                      </div>
                      <div style="cursor: pointer" v-if="web_login_access == 1">
                        <v-img
                          class="ele1"
                          src="/on.png"
                          style="width: 60px"
                          @click="changeStatus(0)"
                        >
                        </v-img>
                      </div>
                    </v-col>
                  </v-row>
                </v-col>
              </v-row>

              <v-row>
                <v-col cols="12" class="text-right">
                  <v-btn
                    v-if="editable"
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
          </v-tab-item>
          <v-tab-item>
            <UserContacts :user_id="item.id" :editable="editable"
          /></v-tab-item>
          <v-tab-item>
            <UserDocuments :user_id="item.id" :editable="editable" />
          </v-tab-item>
        </v-tabs-items>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  props: ["customer_id", "editId", "item", "editable"],
  data: () => ({
    tab: 0,
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
    payload_user: {
      attachment: "",
      title: "",
      display_order: "",
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
    roles: [],
  }),
  created() {
    this.payload_user.role_id = 0;
    this.getRoles();
    this.primary_previewImage = null;
    this.payload_user = {};
    this.preloader = false;
    // this.getBranchesList();

    if (this.$store.state.storeAlarmControlPanel?.AddressTypes) {
      this.addressTypes = this.$store.state.storeAlarmControlPanel.AddressTypes;
    }

    // setTimeout(() => {
    //console.log(this.editAddressType);
    if (this.editId != "" && this.item) {
      this.payload_user.editId = this.editId;
      this.payload_user.user_id = this.item.id;
      this.payload_user.first_name = this.item.first_name;
      this.payload_user.last_name = this.item.last_name;
      this.payload_user.contact_number = this.item.contact_number;
      this.payload_user.email = this.item.email;
      this.payload_user.role_id = this.item.role_id;
      this.web_login_access = this.item.web_login_access || 0;
      this.payload_user.password = null;

      this.primary_previewImage = this.item.profile_picture;
    }
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    getRoles() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`role`, options).then(({ data }) => {
        this.roles = data.data;
      });
    },
    changeStatus(status) {
      if (this.editable) this.web_login_access = status;
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

      for (const key in this.payload_user) {
        if (this.payload_user[key] != "")
          if (this.payload_user[key] != null)
            customer.append(key, this.payload_user[key]);
      }

      if (this.primary_upload.name) {
        customer.append("attachment", this.primary_upload.name);
      }

      customer.append("company_id", this.$auth.user.company_id);

      // if (this.editAddressType != "") customer.append("editAddressType", true);

      if (this.editId) {
        customer.append("editId", this.editId);
        customer.append("web_login_access", this.web_login_access);
      }

      this.$axios
        .post("/create_user", customer)
        .then(({ data }) => {
          //this.loading = false;

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
            setTimeout(() => {
              this.$emit("closeDialog");
            }, 1000);
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
  },
};
</script>
