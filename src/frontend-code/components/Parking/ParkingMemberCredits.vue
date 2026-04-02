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
                  <v-text-field type="number" label="Add Balance" dense small outlined
                    v-model="payload.guest_parking_hours_count" hide-details :readonly="!editable"
                    :filled="!editable"></v-text-field>
                  <span v-if="primary_errors && primary_errors.guest_parking_hours_count" class="text-danger mt-2">{{
                    primary_errors.guest_parking_hours_count[0]
                  }}</span>
                </v-col>
                <v-col cols="12" dense>
                  <v-text-field label="Notes" dense small outlined type="text" v-model="payload.notes" hide-details
                    :readonly="!editable" :filled="!editable"></v-text-field>
                  <span v-if="primary_errors && primary_errors.notes" class="text-danger mt-2">{{
                    primary_errors.notes[0]
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
  props: ["memberId", "editable"],
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



  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },


    submit_primary() {
      if (this.memberId) {


        this.payload.company_id = this.$auth.user.company_id;
        this.payload.member_id = this.memberId;




        this.errorMessage = null;
        this.$axios
          .post("/parking_members_add_balance/", this.payload)
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
