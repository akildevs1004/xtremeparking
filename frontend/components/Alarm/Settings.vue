<template>
  <div>
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row>
      <v-col cols="12">
        <v-tabs v-model="tab">
          <v-tab
            ><v-icon color="green">mdi-invoice-clock-outline</v-icon>
            Subscription
          </v-tab>
          <v-tab
            ><v-icon color="green">mdi-home-clock-outline</v-icon> Store Timings
          </v-tab>
          <v-tab v-if="isEditable">
            <v-icon color="green">mdi-key</v-icon>Password
          </v-tab>
        </v-tabs>
        <v-tabs-items v-model="tab">
          <v-tab-item>
            <v-row>
              <v-col>
                <v-card>
                  <v-card-text>
                    <h3>Subscription Package</h3>
                    <br />
                    <v-row>
                      <v-col style="max-width: 300px">Package Name</v-col>
                      <v-col class="secondary-text-color"
                        >:
                        {{
                          invoicePackageData
                            ? invoicePackageData.device_product_service.name
                            : "---"
                        }}</v-col
                      >
                    </v-row>
                    <v-row>
                      <v-col style="max-width: 300px">Max Sensor Count</v-col>
                      <v-col class="secondary-text-color">
                        :
                        {{
                          invoicePackageData
                            ? invoicePackageData.device_product_service
                                .sensor_count
                            : "---"
                        }}</v-col
                      >
                    </v-row>
                    <v-row>
                      <v-col style="max-width: 300px"
                        >Invoice Price(After Discount)</v-col
                      >
                      <v-col class="secondary-text-color">
                        : $
                        {{
                          invoicePackageData ? invoicePackageData.amount : "---"
                        }}</v-col
                      >
                    </v-row>
                    <v-row>
                      <v-col style="max-width: 300px">Start Date</v-col>
                      <v-col class="secondary-text-color">
                        :
                        {{
                          invoicePackageData
                            ? invoicePackageData.start_date
                            : "---"
                        }}</v-col
                      > </v-row
                    ><v-row>
                      <v-col style="max-width: 300px">End Date</v-col>
                      <v-col class="secondary-text-color">
                        :
                        {{
                          invoicePackageData
                            ? invoicePackageData.end_date
                            : "---"
                        }}</v-col
                      >
                    </v-row>
                    <v-row>
                      <v-col style="max-width: 300px">Created Date</v-col>
                      <v-col class="secondary-text-color">
                        :
                        {{
                          invoicePackageData
                            ? invoicePackageData.created_datetime
                            : "---"
                        }}</v-col
                      >
                    </v-row>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </v-tab-item>

          <v-tab-item>
            <v-card>
              <v-card-text>
                <v-row>
                  <v-col cols="6">
                    <v-row>
                      <v-col cols="12">
                        <v-form autocomplete="off">
                          <v-col md="12" sm="12" cols="12" dense>
                            <!-- <label class="col-form-label"
                          >Current Password
                          <span class="text-danger">*</span></label
                        > -->
                            <!-- Email:
                            <span style="font-weight: bold">{{
                              payload.email
                            }}</span> -->
                            <!-- <v-text-field
                  readonly
                  disabled
                  autocomplete="off"
                  label="Login Email Id"
                  dense
                  outlined
                  :hide-details="!errors.email"
                  v-model="payload.email"
                  class="input-group--focused"
                  :error="errors.email"
                  :error-messages="errors && errors.email ? errors.email : ''"
                ></v-text-field> -->
                          </v-col>
                          <v-col md="12" sm="12" cols="12" dense>
                            <v-row>
                              <v-col md="6" sm="6" cols="6" dense>
                                <v-select
                                  :disabled="!isEditable"
                                  :items="timmingArray"
                                  label="Store Close Time(Armed)"
                                  dense
                                  outlined
                                  hide-details
                                  v-model="payload.close_time"
                                  class="input-group--focused"
                                  :error="errors.close_time"
                                  :error-messages="
                                    errors && errors.close_time
                                      ? errors.close_time[0]
                                      : ''
                                  "
                                  append-icon="mdi-lock"
                                ></v-select>
                              </v-col>
                              <v-col md="6" sm="6" cols="6" dense>
                                <v-select
                                  :disabled="!isEditable"
                                  :items="timmingArray"
                                  label="Store Open Time(Disarm)"
                                  dense
                                  outlined
                                  hide-details
                                  v-model="payload.open_time"
                                  class="input-group--focused"
                                  :error="errors.open_time"
                                  :error-messages="
                                    errors && errors.open_time
                                      ? errors.open_time[0]
                                      : ''
                                  "
                                  append-icon="mdi-lock-open-variant"
                                ></v-select>
                              </v-col>
                            </v-row>
                          </v-col>
                        </v-form>
                      </v-col>
                    </v-row>
                    <v-row class="pl-5" v-if="isEditable">
                      <v-col class="pt-5" style="max-width: 100px"
                        >Login Status
                      </v-col>

                      <v-col class="pl-0 pt-1" style="max-width: 80px">
                        <div
                          style="cursor: pointer"
                          v-if="web_login_access == 0"
                        >
                          <v-img
                            class="ele1"
                            src="/off.png"
                            style="width: 60px"
                            @click="web_login_access = 1"
                          >
                          </v-img>
                        </div>
                        <div
                          style="cursor: pointer"
                          v-if="web_login_access == 1"
                        >
                          <v-img
                            class="ele1"
                            src="/on.png"
                            style="width: 60px"
                            @click="web_login_access = 0"
                          >
                          </v-img>
                        </div>
                      </v-col>
                    </v-row>
                  </v-col>
                </v-row>

                <v-col cols="6" class="align-right">
                  <v-row v-if="isEditable">
                    <v-col cols="12" class="text-right"
                      ><v-btn
                        v-if="can('setting_company_change_password_access')"
                        dark
                        small
                        :loading="loading_password"
                        color="primary"
                        @click="update_setting"
                      >
                        Update
                      </v-btn></v-col
                    >
                  </v-row>
                </v-col>
              </v-card-text>
            </v-card>
          </v-tab-item>
          <v-tab-item>
            <v-card>
              <v-card-text>
                <v-card>
                  <v-card-text>
                    <v-row>
                      <v-col style="max-width: 400px">
                        <v-row>
                          <v-col cols="12">
                            <v-form autocomplete="off">
                              <v-col md="12" sm="12" cols="12" dense>
                                <!-- <label class="col-form-label"
                          >Current Password
                          <span class="text-danger">*</span></label
                        > -->
                                Email:
                                <span style="font-weight: bold">{{
                                  payload.email
                                }}</span>
                                <!-- <v-text-field
                  readonly
                  disabled
                  autocomplete="off"
                  label="Login Email Id"
                  dense
                  outlined
                  :hide-details="!errors.email"
                  v-model="payload.email"
                  class="input-group--focused"
                  :error="errors.email"
                  :error-messages="errors && errors.email ? errors.email : ''"
                ></v-text-field> -->
                              </v-col>

                              <v-col
                                md="12"
                                sm="12"
                                cols="12"
                                dense
                                v-if="isEditable"
                              >
                                <!-- <label class="col-form-label"
                          >Password <span class="text-danger">*</span></label
                        > -->
                                <v-text-field
                                  label="Change Password"
                                  dense
                                  outlined
                                  :hide-details="!errors.password"
                                  :append-icon="
                                    show_password ? 'mdi-eye' : 'mdi-eye-off'
                                  "
                                  :type="show_password ? 'text' : 'password'"
                                  v-model="payload.password"
                                  class="input-group--focused"
                                  @click:append="show_password = !show_password"
                                  :error="errors.password"
                                  :error-messages="
                                    errors && errors.password
                                      ? errors.password[0]
                                      : ''
                                  "
                                ></v-text-field>
                              </v-col>
                              <v-col
                                md="12"
                                sm="12"
                                cols="12"
                                dense
                                v-if="isEditable"
                              >
                                <!-- <label class="col-form-label"
                          >Password <span class="text-danger">*</span></label
                        > -->
                                <v-text-field
                                  label="Password Confirmation"
                                  dense
                                  outlined
                                  :append-icon="
                                    show_password_confirm
                                      ? 'mdi-eye'
                                      : 'mdi-eye-off'
                                  "
                                  :type="
                                    show_password_confirm ? 'text' : 'password'
                                  "
                                  v-model="payload.password_confirmation"
                                  class="input-group--focused"
                                  @click:append="
                                    show_password_confirm =
                                      !show_password_confirm
                                  "
                                  :error="errors.password_confirmation"
                                  :error-messages="
                                    errors && errors.password_confirmation
                                      ? errors.password_confirmation[0]
                                      : ''
                                  "
                                ></v-text-field>
                                <span>{{
                                  errors && errors.password_confirmation
                                    ? errors.password_confirmation[0]
                                    : ""
                                }}</span>
                              </v-col>
                            </v-form>
                          </v-col>
                        </v-row>
                        <v-row v-if="isEditable">
                          <v-col cols="12" class="text-right"
                            ><v-btn
                              v-if="
                                can('setting_company_change_password_access')
                              "
                              dark
                              small
                              :loading="loading_password"
                              color="primary"
                              @click="update_setting"
                            >
                              Update
                            </v-btn></v-col
                          >
                        </v-row>
                      </v-col>
                      <v-col></v-col>
                    </v-row>

                    <v-col cols="6" class="align-right"> </v-col>
                  </v-card-text>
                </v-card>
              </v-card-text>
            </v-card>
          </v-tab-item>
        </v-tabs-items>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  props: ["customer", "isEditable"],
  data() {
    return {
      timmingArray: null,
      snackbar: false,
      response: "",
      item: { is_over_time: true },
      show_password_confirm: false,
      current_password_show: false,
      show_password: false,
      loading_password: false,
      user_payload: {
        password: "",
        password_confirmation: "",
      },
      web_login_access: 0,
      payload: {
        email: "",

        company_id: "",
        customer_id: "",
      },
      tab: 0,
      e1: 1,
      errors: [],
      invoicePackageData: null,
    };
  },
  mounted() {
    if (this.customer) {
      this.payload.email = this.customer.user.email;
      //this.payload.web_login_access = this.customer.user.web_login_access;
      this.web_login_access = this.customer.user?.web_login_access || false;
      //this.payload.account_status = this.customer.account_status;
      this.payload.password = "";
      this.payload.password_confirmation = "";

      this.payload.close_time = this.customer.close_time ?? "00:00";
      this.payload.open_time = this.customer.open_time ?? "00:00";
    }

    this.timmingArray = this.generateTimingArray();
  },
  created() {
    if (this.customer) this.getSensorLimitFromPayments();
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    generateTimingArray() {
      const times = [];
      for (let h = 0; h < 24; h++) {
        for (let m = 0; m < 60; m += 60) {
          const hour = h.toString().padStart(2, "0");
          const minute = m.toString().padStart(2, "0");
          times.push(`${hour}:${minute}`);
        }
      }
      return times;
    },
    getSensorLimitFromPayments() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          customer_id: this.customer.id,
        },
      };
      this.$axios
        .get(`/get_customer_sensor_payment_package_details`, options)
        .then(({ data }) => {
          this.invoicePackageData = data;
        });
    },
    update_setting() {
      this.payload.company_id = this.$auth.user.company_id;
      this.payload.customer_id = this.customer.id;
      this.payload.web_login_access = this.web_login_access;
      this.payload.user_id = this.customer.user_id;
      this.errors = [];

      if (this.payload.password === "") {
        delete this.payload.password;
        delete this.payload.password_confirmation;
      }

      this.$axios
        .post("/update_customer_settings", this.payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          }

          this.snackbar = data.status;
          this.response = data.message;

          this.$emit("closeDialog");
        })
        .catch((e) => {
          if (e.response.data.errors) {
            this.snackbar = true;
            this.response = e.response.data.message;
          }
        });
    },
  },
};
</script>
