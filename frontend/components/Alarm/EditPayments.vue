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
            <v-col cols="6" dense>
              <v-text-field
                label="Invoice #"
                dense
                small
                outlined
                type="text"
                v-model="payload_primary.invoice_number"
                hide-details
                :disabled="editItem ? true : false"
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.invoice_number"
                class="text-danger mt-2"
                >{{ primary_errors.invoice_number[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-menu
                style="z-index: 9999"
                v-model="invoice_date_menu"
                :close-on-content-click="false"
                :nudge-left="145"
                transition="scale-transition"
                offset-y
                min-width="auto"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-text-field
                    style="z-index: 9999; height: 10px"
                    outlined
                    v-model="payload_primary.invoice_date"
                    v-bind="attrs"
                    v-on="on"
                    dense
                    x-small
                    hide-details
                    class="custom-text-box shadow-none"
                    label="Invoice Date"
                    :disabled="editItem ? true : false"
                  ></v-text-field>
                </template>
                <v-date-picker
                  no-title
                  scrollable
                  v-model="payload_primary.invoice_date"
                  @input="invoice_date_menu = false"
                ></v-date-picker>
              </v-menu>
              <span
                v-if="primary_errors && primary_errors.invoice_date"
                class="text-danger mt-2"
                >{{ primary_errors.invoice_date[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Amount"
                dense
                small
                outlined
                type="number"
                v-model="payload_primary.amount"
                hide-details
                :disabled="editItem ? true : false"
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.amount"
                class="text-danger mt-2"
                >{{ primary_errors.amount[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-select
                label="Payment Status"
                :items="['Pending', 'Received', 'Cancelled']"
                v-model="payload_primary.status"
                dense
                outlined
                hide-details
                small
                @change="updateStatus(payload_primary.status)"
              ></v-select>
              <span
                v-if="primary_errors && primary_errors.status"
                class="text-danger mt-2"
                >{{ primary_errors.status[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense v-if="payload_primary_status != 'Cancelled'">
              <v-menu
                style="z-index: 9999"
                v-model="from_menu"
                :close-on-content-click="false"
                :nudge-left="145"
                transition="scale-transition"
                offset-y
                min-width="auto"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-text-field
                    style="z-index: 9999; height: 10px"
                    outlined
                    v-model="payload_primary.received_date"
                    v-bind="attrs"
                    v-on="on"
                    dense
                    x-small
                    hide-details
                    class="custom-text-box shadow-none"
                    label="Received Date"
                  ></v-text-field>
                </template>
                <v-date-picker
                  no-title
                  scrollable
                  v-model="payload_primary.received_date"
                  @input="from_menu = false"
                ></v-date-picker>
              </v-menu>
              <span
                v-if="primary_errors && primary_errors.received_date"
                class="text-danger mt-2"
                >{{ primary_errors.received_date[0] }}</span
              >
            </v-col>

            <v-col cols="6" dense v-if="payload_primary_status != 'Cancelled'">
              <v-select
                label="Payment Mode"
                :items="['Cash', 'Online', 'Cheque']"
                v-model="payload_primary.mode_of_payment"
                dense
                outlined
                hide-details
                small
              ></v-select>
              <span
                v-if="primary_errors && primary_errors.mode_of_payment"
                class="text-danger mt-2"
                >{{ primary_errors.mode_of_payment[0] }}</span
              >
            </v-col>

            <v-col cols="12" dense v-if="payload_primary_status == 'Cancelled'">
              <v-text-field
                label="Cancel Notes"
                dense
                small
                outlined
                v-model="payload_primary.cancel_notes"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.cancel_notes"
                class="text-danger mt-2"
                >{{ primary_errors.cancel_notes[0] }}</span
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
  props: ["editItem", "editId", "customer_id"],
  data: () => ({
    from_menu: false,
    invoice_date_menu: false,

    show1: false,
    devicesList: [],
    zonesList: [],
    //branchesList: [],
    startDateMenuOpen: "",
    endDateMenuOpen: "",
    preloader: false,
    loading: false,
    primary_upload: {
      name: "",
    },

    start_date: "",
    end_date: "",
    payload_primary: { received_date: "" },

    e1: 1,
    primary_errors: [],

    response: "",
    snackbar: false,
    errors: [],
    selectedItem: null,
    items: ["Apple", "Banana", "Orange"],
    payload_primary_status: null,
  }),
  created() {
    this.payload_primary = { received_date: "" };
    this.preloader = false;

    // this.getBranchesList();

    if (this.$store.state.storeAlarmControlPanel?.AddressTypes) {
      this.addressTypes = this.$store.state.storeAlarmControlPanel.AddressTypes;
    }

    if (this.editItem) {
      this.payload_primary = { received_date: "" };
      this.payload_primary.editId = this.editItem.id;

      this.payload_primary.invoice_number = this.editItem.invoice_number;
      this.payload_primary.amount = this.editItem.amount;
      this.payload_primary.received_date = this.editItem.received_date;
      this.payload_primary.invoice_date = this.editItem.invoice_date;

      this.payload_primary.mode_of_payment = this.editItem.mode_of_payment;
      this.payload_primary.status = this.editItem.status;
      this.payload_primary.customer_id = this.editItem.customer_id;
    }
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    updateStatus(status) {
      console.log(status);
      this.payload_primary_status = status;
    },
    submit_primary() {
      this.payload_primary.company_id = this.$auth.user.company_id;
      if (this.customer_id) this.payload_primary.customer_id = this.customer_id;

      if (this.editId) {
        this.$axios
          .put("/customer_payments/" + this.editId, this.payload_primary)
          .then(({ data }) => {
            if (!data.status) {
              this.primary_errors = data.errors;
              return;
            }
            console.log("data", data);

            this.snackbar = data.status;
            this.response = data.message;

            this.loading = false;
            setTimeout(() => {
              this.$emit("closeDialog");
            }, 500);
          })
          .catch((e) => console.log(e));
      } else {
        this.$axios
          .post("/customer_payments", this.payload_primary)
          .then(({ data }) => {
            this.loading = false;

            if (!data.status) {
              this.primary_errors = data.errors;
              return;
            }
            console.log("data", data);

            this.snackbar = data.status;
            this.response = data.message;
            //this.$emit("closeDialog");
            setTimeout(() => {
              this.$emit("closeDialog");
            }, 500);
          })
          .catch((e) => {
            if (e.response.data.errors) {
              this.primary_errors = e.response.data.errors;
              this.color = "red";

              this.snackbar = true;
              this.response = e.response.data.message;
            }
          });
      }
    },
  },
};
</script>
