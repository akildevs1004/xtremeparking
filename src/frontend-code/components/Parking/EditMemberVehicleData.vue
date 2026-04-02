<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24" timeout="10000">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row>
      <v-col md="12" sm="12" cols="12" dense>
        <v-card class="elevation-0 p-2" style="padding: 5px">
          <v-row>
            <v-col cols="12">
              <v-row class="pt-0">
                <v-col cols="12" dense>
                  <v-text-field label="Vehicle Number" dense small outlined type="text" v-model="payload.vehicle_number"
                    hide-details :readonly="!editable" :filled="!editable"></v-text-field>
                  <span v-if="primary_errors && primary_errors.vehicle_number" class="text-danger mt-2">{{
                    primary_errors.vehicle_number[0]
                  }}</span>
                </v-col>
                <v-col cols="12" dense>
                  <v-text-field label="Parking Floor - Parking Number" dense small outlined type="text"
                    v-model="payload.parking_slot" hide-details :readonly="!editable"
                    :filled="!editable"></v-text-field>
                  <span v-if="primary_errors && primary_errors.parking_slot" class="text-danger mt-2">{{
                    primary_errors.parking_slot[0]
                  }}</span>
                </v-col>
                <v-col cols="12" dense>
                  <v-text-field label="First Name" dense small outlined type="text" v-model="payload.guest_first_name"
                    hide-details :readonly="!editable" :filled="!editable"></v-text-field>
                  <span v-if="primary_errors && primary_errors.guest_first_name" class="text-danger mt-2">{{
                    primary_errors.guest_first_name[0]
                  }}</span>
                </v-col><v-col cols="12" dense>
                  <v-text-field label="Last Name" dense small outlined type="text" v-model="payload.guest_last_name"
                    hide-details :readonly="!editable" :filled="!editable"></v-text-field>
                  <span v-if="primary_errors && primary_errors.guest_last_name" class="text-danger mt-2">{{
                    primary_errors.guest_last_name[0]
                  }}</span>
                </v-col><v-col cols="12" dense>
                  <v-text-field label="Address" dense small outlined type="text" v-model="payload.guest_address"
                    hide-details :readonly="!editable" :filled="!editable"></v-text-field>
                  <span v-if="primary_errors && primary_errors.guest_address" class="text-danger mt-2">{{
                    primary_errors.guest_address[0]
                  }}</span>
                </v-col><v-col cols="12" dense>
                  <v-text-field label="Location" dense small outlined type="text" v-model="payload.guest_location"
                    hide-details :readonly="!editable" :filled="!editable"></v-text-field>
                  <span v-if="primary_errors && primary_errors.guest_location" class="text-danger mt-2">{{
                    primary_errors.guest_location[0]
                  }}</span>
                </v-col>
                <v-col cols="12" dense>
                  <v-text-field label="Company Details" dense small outlined type="text"
                    v-model="payload.guest_company_details" hide-details :readonly="!editable"
                    :filled="!editable"></v-text-field>
                  <span v-if="primary_errors && primary_errors.guest_company_details" class="text-danger mt-2">{{
                    primary_errors.guest_company_details[0]
                  }}</span>
                </v-col>

              </v-row>




            </v-col>
          </v-row>
          <v-row v-if="errorMessage">
            <v-col cols="12" class="text-right" style="color:red">
              {{ errorMessage }}

            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" class="text-right">
              <v-btn v-if="editable" small :loading="loading" color="primary" @click="submit_primary">
                Submit
              </v-btn></v-col>
          </v-row>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  props: ["editId", "item", "editable", "memberId", "isMQTT"],
  data: () => ({
    errorMessage: null,
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
    payload: {

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
    qtyList: [],
  }),
  created() {
    this.primary_previewImage = null;
    this.payload = {};
    this.preloader = false;
    // this.getBranchesList();

    if (this.$store.state.storeAlarmControlPanel?.AddressTypes) {
      this.addressTypes = this.$store.state.storeAlarmControlPanel.AddressTypes;
    }

    // setTimeout(() => {
    //console.log(this.editAddressType);
    if (this.editId != "" && this.item) {
      this.payload.editId = this.editId;


      this.payload.vehicle_number = this.item.vehicle_number;
      this.payload.guest_first_name = this.item.guest_first_name;
      this.payload.guest_last_name = this.item.guest_last_name;
      this.payload.guest_address = this.item.guest_address;
      this.payload.guest_location = this.item.guest_location;
      this.payload.guest_company_details = this.item.guest_company_details;
      this.payload.parking_slot = this.item.parking_slot;







    }

  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },


    async submit_primary() {
      if (!this.memberId) {
        this.snackbar = true;
        this.color = "red";
        this.response = "Member is required.";
        return;
      }

      if (this.submitting) return; // double-submit guard
      this.submitting = true;

      const isEdit = !!this.editId;

      // --------- Build payload without mutating this.payload ----------
      const payload = {
        ...this.payload,
        company_id: this.$auth.user.company_id,
        member_id: this.memberId,
        ...(isEdit ? { id: this.editId } : {}),
      };

      // --------- Derive MQTT action + HTTP endpoint ----------
      const mqttAction = isEdit
        ? "parking_members_vehiclesList_update"
        : "parking_members_vehiclesList_create";

      const httpUrl = isEdit
        ? `/parking_members_vehiclesList/${this.editId}`
        : `/parking_members_vehiclesList`;

      const httpVerb = isEdit ? "put" : "post";

      // --------- Normalizer so both paths look identical ----------
      const normalize = (raw) => ({
        status: !!raw?.status,
        message: raw?.message ?? "",
        errors: raw?.errors ?? {},
        data: raw?.data ?? null,
        total: Number.isFinite(raw?.total) ? raw.total : 0,
      });



      this.errorMessage = null;
      this.primary_errors = [];
      this.isBackendRequestOpen = true;

      try {
        let result = null;
        let usedMQTT = false;

        // 1) Try MQTT if enabled
        if (this.isMQTT && typeof mqttRequestReply === "function") {
          try {
            const mqttResp = await mqttRequestReply({
              companyId: payload.company_id,
              action: mqttAction,
              payload,            // send full payload (company_id, member_id, etc.)
              timeoutMs: 8000,
            });

            // Expect standard envelope: { action, status, message, errors?, data?, total? }
            if (
              mqttResp &&
              mqttResp.action === mqttAction &&
              (mqttResp.status !== undefined || mqttResp.message || mqttResp.errors || mqttResp.data)
            ) {
              result = normalize(mqttResp);
              usedMQTT = true;
            } else {
              console.warn("[MQTT] Unexpected response shape:", mqttResp);
            }
          } catch (e) {
            console.warn("[MQTT] Failed; falling back to HTTP:", e?.message || e);
          }
        }
        else {
          // --------- Small helper for HTTP fallback ----------
          const doHttp = async () => {
            const { data } = await this.$axios[httpVerb](httpUrl, payload);
            return normalize(data);
          };
          result = await doHttp();
        }

        // // 2) Fallback to HTTP if needed
        // if (!result) {


        // }

        // 3) Handle success / errors
        if (!result.status) {
          this.primary_errors = result.errors || {};
          this.color = "red";
          this.snackbar = true;
          this.response = result.message || "Unable to save. Please check the form.";
          this.errorMessage = this.response;
          return;
        }

        // --- Success ---
        this.color = "background";
        this.snackbar = true;
        this.response = (usedMQTT ? "[MQTT] " : "") + (result.message || "Saved successfully.");

        // If you want to refresh a table/list after create/edit:
        // await this.fetchVehicleList?.();

        setTimeout(() => this.$emit("closeDialog"), 900);
      } catch (e) {
        // Normalize axios/network errors
        const resp = e?.response?.data || {};
        this.primary_errors = resp?.errors || {};
        this.color = "red";
        this.snackbar = true;
        this.response =
          resp?.message ||
          e?.response?.statusText ||
          e?.message ||
          "Request failed. Please try again.";
        this.errorMessage = this.response;
      } finally {
        this.submitting = false;
        this.isBackendRequestOpen = false;
      }


    },
    submit_primary_old() {
      if (this.editId) {


        this.payload.company_id = this.$auth.user.company_id;
        this.payload.member_id = this.memberId;


        this.errorMessage = null;
        this.$axios
          .put("/parking_members_vehiclesList/" + this.editId, this.payload)
          .then(({ data }) => {
            //this.loading = false;

            if (!data.status) {
              this.primary_errors = [];
              if (data.errors) this.primary_errors = data.errors;
              this.color = "red";

              this.snackbar = true;
              this.response = data.message;
              this.errorMessage = data.message;;

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

      }


      else {


        this.payload.company_id = this.$auth.user.company_id;
        this.payload.member_id = this.memberId;

        this.errorMessage = null;
        this.$axios
          .post("/parking_members_vehiclesList", this.payload)
          .then(({ data }) => {
            //this.loading = false;

            if (!data.status) {
              this.primary_errors = [];
              if (data.errors) this.primary_errors = data.errors;
              this.color = "red";

              this.snackbar = true;
              this.response = data.message;
              this.errorMessage = data.message;;

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

      }

    },
  },
};
</script>
