<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <!-- <v-card elevation="2">
      <v-card-text> -->
    <div v-if="customer?.contacts.length == 0">No Contacts are available</div>

    <v-tabs
      v-if="customer?.contacts"
      right
      show-arrows
      class="tabswidthalignment"
    >
      <v-tab
        @click="clearFrom()"
        style="font-size: 10px; min-width: 50px !important"
        v-if="
          contact.address_type.toLowerCase() == 'primary' ||
          contact.address_type.toLowerCase() == 'secondary'
        "
        v-for="(contact, index) in customer.contacts"
        :key="contact.id"
      >
        {{ contact.address_type }}
      </v-tab>

      <v-tab-item
        v-if="
          contact.address_type.toLowerCase() == 'primary' ||
          contact.address_type.toLowerCase() == 'secondary'
        "
        v-for="(contact, index) in customer.contacts"
        :key="contact.id + 50"
        name="index+50"
      >
        <v-row>
          <v-col style="margin: auto; padding-top: 0px; max-width: 200px">
            <div style="margin: auto; text-align: center">
              {{ $utils.caps(contact.address_type) }}
            </div>
            <v-img
              style="width: 150px; border: 1px solid #ddd; margin: auto"
              :src="
                contact?.profile_picture
                  ? contact.profile_picture
                  : '/no-profile-image.jpg'
              "
            ></v-img>
          </v-col>
          <v-col>
            <table
              class="eventcustomertab"
              style="width: 100%; line-height: 30px"
            >
              <tr style="font-weight: bold">
                <td style="width: 50px">
                  <v-icon color="#2038c0" size="20">mdi-account-tie</v-icon>
                </td>
                <td>
                  {{
                    contact?.first_name
                      ? $utils.caps(contact.first_name) +
                        " " +
                        $utils.caps(contact.last_name)
                      : "---"
                  }}
                </td>
              </tr>
              <tr>
                <td style="width: 50px">
                  <v-icon color="#2038c0" size="20">mdi-map-marker</v-icon>
                </td>
                <td>
                  {{
                    contact?.address && contact?.address != "null"
                      ? contact.address
                      : "---"
                  }}
                </td>
              </tr>
              <tr>
                <td style="width: 50px">
                  <v-icon color="#2038c0" size="20">mdi-phone-classic</v-icon>
                </td>
                <td>
                  {{ contact?.office_phone ? contact.office_phone : "---" }}
                </td>
              </tr>
              <tr>
                <td style="width: 50px">
                  <v-icon color="#2038c0" size="20">mdi-cellphone-basic</v-icon>
                </td>
                <td>
                  <!-- Mobile : -->
                  {{ contact?.phone1 ? contact.phone1 : "---" }}
                </td>
              </tr>
              <tr>
                <td style="width: 50px">
                  <v-icon color="#2038c0" size="20">mdi-cellphone</v-icon>
                </td>
                <td>
                  <!-- Office : -->
                  {{ contact?.phone2 ? contact.phone2 : "---" }}
                </td>
              </tr>
              <tr>
                <td style="width: 50px">
                  <v-icon color="#2038c0" size="20">mdi-whatsapp</v-icon>
                </td>
                <td>
                  <!-- Whatsapp : -->
                  {{ contact?.whatsapp ? contact.whatsapp : "---" }}
                </td>
              </tr>
              <tr>
                <td style="width: 50px">
                  <v-icon color="#2038c0" size="20">mdi-at</v-icon>
                </td>
                <td>
                  <!-- E-Mail : -->
                  {{ contact?.email ? contact.email : "---" }}
                </td>
              </tr>
              <tr>
                <td style="width: 50px">
                  <v-icon color="#2038c0" size="20"
                    >mdi-message-bulleted</v-icon
                  >
                </td>
                <td>
                  Notes :
                  {{ contact?.notes ? contact.notes : "---" }}
                </td>
              </tr>
            </table>
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <v-card class="mt-5"
          ><v-card-text :key="contact.id" style="padding: 0px">
            <!-- <v-divider></v-divider> -->

            <v-row class="mt-1">
              <v-col class="text-right">
                <label>
                  Enter
                  <span class="bold">{{
                    contact.address_type?.toLowerCase() == "primary"
                      ? "Primary"
                      : "Secondary"
                  }}</span>
                  Contact Secret Code</label
                ></v-col
              >
              <v-col
                style="max-width: 160px; padding-left: 0px; padding-right: 0px"
              >
                <v-text-field
                  label="Contact Secret Code"
                  min="0"
                  max="10"
                  class="employee-schedule-search-box font10"
                  dense
                  outlined
                  flat
                  height="20px"
                  v-model="pin_number"
                  hide-details
                  small
                  style="
                    font-size: 11px;

                    padding-top: 0px;
                  "
                  max-length="10"
                  type="number"
                >
                </v-text-field>
              </v-col>
              <v-col style="width: 90px" class="pt-3 text-right">
                <v-btn
                  :loading="loading"
                  @click="resendSecretCode(contact)"
                  small
                  >Re-send Code to Customer</v-btn
                >
              </v-col>
              <v-col cols="12" class="text-right">
                <v-btn
                  :loading="loading"
                  @click="submit(contact.id, contact.address_type)"
                  small
                  color="primary"
                  >Verify and Submit</v-btn
                ></v-col
              >
            </v-row>

            <div style="color: red; text-align: center">
              {{ errors && errors.pin_number ? errors.pin_number[0] : "" }}
            </div>
          </v-card-text></v-card
        >
      </v-tab-item>
    </v-tabs>
    <!-- </v-card-text>
    </v-card> -->
  </div>
</template>

<script>
import TechnicianSensorsTesting from "./TechnicianSensorsTesting.vue";

export default {
  components: { TechnicianSensorsTesting },
  props: ["customer", "ticketId", "close_ticket", "ticket"],
  data: () => ({
    responseList: "",
    pin_number: null,
    error_message: "",
    color: "background",
    errors: [],
    response: "",
    snackbar: false,
    key: 0,
    loading: false,
  }),
  computed: {},
  mounted() {},
  created() {},

  methods: {
    clearFrom() {
      this.pin_number = "";
      this.response = "";
      this.errors = [];
    },
    resendSecretCode(contact) {
      let options = { params: { contact_id: contact.id } };

      this.$axios
        .get("contact_resend_verification_code", options)
        .then(({ data }) => {
          this.response = "Mail sent successfully";
        });
    },
    submit(contact_id, address_type) {
      if (this.close_ticket) {
        if (!confirm("Are you sure want to close the Job?")) {
          return false;
        }
      }

      this.loading = true;
      // console.log(
      //   "this.$auth.user.technician.id",
      //   this.$auth.user.technician.id
      // );

      if (this.$auth.user.technician?.id) {
        let options = {
          params: {
            company_id: this.$auth.user.company_id,
            technician_id: this.$auth.user.technician.id,
            customer_id: this.customer.id,
            pin_verified_by_id: contact_id,
            tikcet_id: this.ticketId,
            contact_type: address_type,
            pin_number: this.pin_number,
            close_ticket: this.close_ticket,
          },
        };

        {
          this.$axios
            .post("/technician_start_job", options.params)
            .then(({ data }) => {
              this.loading = false;

              this.response = "";

              if (!data.status) {
                this.errors = [];
                if (data.errors) this.errors = data.errors;
                this.color = "red";

                this.snackbar = true;
                //this.response = data.errors.pin_number[0];
                const firstErrorField = Object.keys(this.errors)[0];

                if (firstErrorField && this.errors[firstErrorField]?.length) {
                  this.response = this.errors[firstErrorField][0]; // First error message
                  //console.log(`Error in ${firstErrorField}:`, this.response);
                } else {
                  //this.response = "An unknown error occurred.";
                }
              } else {
                this.error_message = "";
                this.color = "background";
                this.errors = [];
                this.snackbar = true;
                this.response = "Job Details Updated successfully";
                setTimeout(() => {
                  this.$emit("closeDialogCall");
                }, 1000 * 1);
              }
            })
            .catch((e) => {
              // console.log("e.response", e.response);

              if (e.response?.data?.errors) {
                this.errors = e.response.data.errors;
                this.color = "red";
                this.snackbar = true;
                this.response = e.response.data.message;

                if (this.errors.message) {
                  this.response = this.errors.message; // Fixed typo
                  this.error_message = this.errors.message;
                }
              } else if (e.response?.data) {
                this.snackbar = true;
                this.response = e.response.data.message;

                if (e.response.data.errors) {
                  e.response.data.errors.forEach((element) => {
                    console.log(element);
                    this.response = element;
                  });
                }
              } else {
                // Handle cases where response is completely missing
                this.snackbar = true;
                this.response = "An unexpected error occurred.";
                console.log("Error:", e);
              }
            });
        }
      }
    },
  },
};
</script>
