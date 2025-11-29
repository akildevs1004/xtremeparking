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
          <v-row>
            <v-col cols="12">
              <v-row class="pt-0">
                <v-col cols="6" dense>
                  <v-select
                    label="Business Source"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="payload.business_source_id"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                    :items="business_source_list"
                    item-text="name"
                    item-value="id"
                  ></v-select>
                  <span
                    v-if="primary_errors && primary_errors.business_source_id"
                    class="text-danger mt-2"
                    >{{ primary_errors.business_source_id[0] }}</span
                  >
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    label="Source Name/Details"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="payload.business_source_info"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.business_source_info"
                    class="text-danger mt-2"
                    >{{ primary_errors.business_source_info[0] }}</span
                  >
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    label="First Name"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="payload.first_name"
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
                    v-model="payload.last_name"
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
                    clearable
                    autocomplete="off"
                    v-model="payload.contact_number"
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
                    label="Whatsapp Number"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.whatsapp_number"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.whatsapp_number"
                    class="text-danger mt-2"
                    >{{ primary_errors.whatsapp_number[0] }}</span
                  >
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    label="Email"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.email"
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
                    label="Address/Location"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.address"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.address"
                    class="text-danger mt-2"
                    >{{ primary_errors.address[0] }}</span
                  >
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    label="Business/Building Name"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.building_name"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.building_name"
                    class="text-danger mt-2"
                    >{{ primary_errors.building_name[0] }}</span
                  > </v-col
                ><v-col cols="6" dense>
                  <v-select
                    label="Type of Business/Customer"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.building_type_id"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                    :items="buildingTypes"
                    item-text="name"
                    item-value="id"
                  ></v-select>
                  <span
                    v-if="primary_errors && primary_errors.building_type_id"
                    class="text-danger mt-2"
                    >{{ primary_errors.building_type_id[0] }}</span
                  >
                </v-col>
                <v-col cols="6" dense>
                  <v-select
                    label="Alarm Type"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.device_type"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                    :items="device_type_list"
                    item-text="name"
                    item-value="id"
                  ></v-select>
                  <span
                    v-if="primary_errors && primary_errors.device_type"
                    class="text-danger mt-2"
                    >{{ primary_errors.device_type[0] }}</span
                  > </v-col
                ><v-col cols="6" dense>
                  <v-select
                    label="Expecting Sensor Count"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.sensor_count"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                    :items="[
                      1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17,
                      18, 19, 20,
                    ]"
                  ></v-select>
                  <span
                    v-if="primary_errors && primary_errors.sensor_count"
                    class="text-danger mt-2"
                    >{{ primary_errors.sensor_count[0] }}</span
                  >
                </v-col>
                <v-col cols="6" style="height: 150px">
                  <v-textarea
                    style="height: 20px; width: 100%"
                    label="Receiption Remarks"
                    dense
                    outlined
                    type="text"
                    v-model="payload.receiption_remarks"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-textarea>
                  <span
                    v-if="errors && errors.receiption_remarks"
                    class="text-danger mt-2"
                    >{{ errors.receiption_remarks[0] }}</span
                  > </v-col
                ><v-col cols="6" style="height: 150px">
                  <v-textarea
                    style="height: 20px; width: 100%"
                    label="Customer Request"
                    dense
                    outlined
                    type="text"
                    v-model="payload.customer_request"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-textarea>
                  <span
                    v-if="errors && errors.customer_request"
                    class="text-danger mt-2"
                    >{{ errors.customer_request[0] }}</span
                  >
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
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  props: ["editId", "item", "editable"],
  data: () => ({
    buildingTypes: [],
    business_source_list: [],
    device_type_list: [],

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
      this.payload = this.item;
      // this.payload.editId = this.editId;
      // this.payload.name = this.item.name;
      // this.payload.year_amount = this.item.year_amount;
      // this.payload.quarter_amount = this.item.quarter_amount;
      // this.payload.month_amount = this.item.month_amount;
      // this.payload.sensor_count = this.item.sensor_count;
    }

    for (let i = 1; i <= 100; i++) {
      this.qtyList.push(i);
    }

    this.getBusinessTypes();
    this.getAlarmTypes();
    this.getBuildingTypes();
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    changeStatus(status) {
      if (this.editable) this.web_login_access = status;
    },
    //primary
    // onpick_primary_attachment() {
    //   this.$refs.primary_attachment_input.click();
    // },
    // primary_attachment(e) {
    //   this.primary_upload.name = e.target.files[0] || "";

    //   let input = this.$refs.primary_attachment_input;
    //   let file = input.files;
    //   //console.log(file);
    //   if (file[0] && file[0].size > 1024 * 1024) {
    //     e.preventDefault();
    //     this.primary_errors["logo"] = [
    //       "File too big (> 1MB). Upload less than 1MB",
    //     ];
    //     return;
    //   }

    //   if (file && file[0]) {
    //     let reader = new FileReader();
    //     reader.onload = (e) => {
    //       this.primary_previewImage = e.target.result;
    //     };
    //     reader.readAsDataURL(file[0]);
    //     this.$emit("input", file[0]);
    //   }
    // },
    getBusinessTypes() {
      let options = { params: { company_id: this.$auth.user.company_id } };

      this.$axios.get("get_sales_business_types", options).then((data) => {
        this.business_source_list = data.data;
      });
    },
    getAlarmTypes() {
      let options = { params: { company_id: this.$auth.user.company_id } };

      this.$axios.get("device_types", options).then((data) => {
        this.device_type_list = data.data;
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
    submit_primary() {
      let customer = new FormData();
      this.loading = true;

      Object.entries(this.payload).forEach(([key, value]) => {
        if (value !== "" && value !== null) {
          customer.append(key, value);
        }
      });

      if (this.editId) customer.append("editId", this.editId);
      customer.append("company_id", this.$auth.user.company_id);

      this.$axios
        .post("/sales_inquiry", customer)
        .then(({ data }) => {
          this.loading = false;

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
          this.loading = false;

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
