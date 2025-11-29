<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <TechnicianSensorsTesting
      :customer_id="customer.id"
      :ticket_id="ticketId"
    />
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
              console.log("e.response", e.response);

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
