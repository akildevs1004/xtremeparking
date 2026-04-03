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
                <v-col cols="12" dense>
                  <v-text-field label="Product/Service Name" dense small outlined type="text" v-model="payload.name"
                    hide-details :readonly="!editable" :filled="!editable"></v-text-field>
                  <span v-if="primary_errors && primary_errors.name" class="text-danger mt-2">{{ primary_errors.name[0]
                  }}</span>
                </v-col>
                <v-col cols="12" dense>
                  <v-text-field label="Yearly Amount" dense small outlined type="text" v-model="payload.year_amount"
                    hide-details :readonly="!editable" :filled="!editable"></v-text-field>
                  <span v-if="primary_errors && primary_errors.year_amount" class="text-danger mt-2">{{
                    primary_errors.year_amount[0] }}</span>
                </v-col>
                <v-col cols="12" dense>
                  <v-text-field label="Quarter Amount" dense small outlined type="text" v-model="payload.quarter_amount"
                    hide-details :readonly="!editable" :filled="!editable"></v-text-field>
                  <span v-if="primary_errors && primary_errors.quarter_amount" class="text-danger mt-2">{{
                    primary_errors.quarter_amount[0] }}</span>
                </v-col>
                <v-col cols="12" dense>
                  <v-text-field label="Monthly Amount" dense small outlined clearable autocomplete="off"
                    v-model="payload.month_amount" hide-details :readonly="!editable"
                    :filled="!editable"></v-text-field>
                  <span v-if="primary_errors && primary_errors.month_amount" class="text-danger mt-2">{{
                    primary_errors.month_amount[0] }}</span>
                </v-col>
                <!-- <v-col cols="12" dense>
                  <v-select
                    label="Max Sensor Count"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload.sensor_count"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                    :items="qtyList"
                  ></v-select>

                  <span
                    v-if="primary_errors && primary_errors.sensor_count"
                    class="text-danger mt-2"
                    >{{ primary_errors.sensor_count[0] }}</span
                  >
                </v-col> -->
              </v-row>
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
  props: ["editId", "item", "editable"],
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
    this.payload = { sensor_count: 0 };
    this.preloader = false;
    // this.getBranchesList();

    if (this.$store.state.storeAlarmControlPanel?.AddressTypes) {
      this.addressTypes = this.$store.state.storeAlarmControlPanel.AddressTypes;
    }

    // setTimeout(() => {
    //console.log(this.editAddressType);
    if (this.editId != "" && this.item) {
      this.payload.editId = this.editId;
      this.payload.name = this.item.name;
      this.payload.year_amount = this.item.year_amount;
      this.payload.quarter_amount = this.item.quarter_amount;
      this.payload.month_amount = this.item.month_amount;
      this.payload.sensor_count = 0;//this.item.sensor_count;
    }

    for (let i = 1; i <= 100; i++) {
      this.qtyList.push(i);
    }
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
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

      for (const key in this.payload) {
        if (this.payload[key] != "")
          if (this.payload[key] != null)
            customer.append(key, this.payload[key]);
      }

      customer.append("company_id", this.$auth.user.company_id);

      this.$axios
        .post("/device_product_services", customer)
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
