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
                label="Camera Title"
                dense
                small
                outlined
                type="text"
                v-model="payload_camera.title"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.title"
                class="text-danger mt-2"
                >{{ primary_errors.title[0] }}</span
              >
            </v-col>
            <v-col cols="6" dense>
              <v-text-field
                label="Display Order"
                dense
                small
                outlined
                type="number"
                v-model="payload_camera.display_order"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.display_order"
                class="text-danger mt-2"
                >{{ primary_errors.display_order[0] }}</span
              >
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" dense>
              <v-text-field
                label="Camera URL"
                dense
                small
                outlined
                v-model="payload_camera.camera_url"
                hide-details
              ></v-text-field>
              <span
                v-if="primary_errors && primary_errors.camera_url"
                class="text-danger mt-2"
                >{{ primary_errors.camera_url[0] }}</span
              >
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" class="text-right">
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
  props: ["customer_id", "editId", "item", "building_cameras"],
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
    payload_camera: {
      attachment: "",
      title: "",
      display_order: "",
      camera_url: process.env.CAMERA_RTMP,
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
    this.payload_camera = {
      camera_url:
        process.env.CAMERA_RTMP + "" + (this.building_cameras.length + 1),
    };
    this.preloader = false;
    // this.getBranchesList();

    if (this.$store.state.storeAlarmControlPanel?.AddressTypes) {
      this.addressTypes = this.$store.state.storeAlarmControlPanel.AddressTypes;
    }

    // setTimeout(() => {
    //console.log(this.editAddressType);
    if (this.editId != "" && this.item) {
      this.payload_camera.editId = this.editId;
      this.payload_camera.display_order = this.item.display_order;
      this.payload_camera.title = this.item.title;
      this.payload_camera.camera_url = this.item.camera_url;
    }
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
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
      console.log(file);
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

      for (const key in this.payload_camera) {
        if (this.payload_camera[key] != "")
          customer.append(key, this.payload_camera[key]);
      }

      // if (this.primary_upload.name) {
      //   customer.append("attachment", this.primary_upload.name);
      // }

      customer.append("company_id", this.$auth.user.company_id);
      customer.append("customer_id", this.customer_id);
      // if (this.editAddressType != "") customer.append("editAddressType", true);

      if (this.editId != "") {
        customer.append("editId", this.editId);
      }

      this.$axios
        .post("/customers_building_cameras", customer)
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
            this.response = "Camera Details Updated successfully";
            setTimeout(() => {
              this.$emit("closeDialog");
            }, 1000);
          }
        })
        .catch((e) => {
          if (e.response.data.primary_errors) {
            this.primary_errors = e.response.data.primary_errors;
            this.color = "red";

            this.snackbar = true;
            this.response = e.response.data.message;
          }
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
