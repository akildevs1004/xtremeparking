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
                  <v-textarea
                    label="Message"
                    v-model="message"
                    cols="30"
                    rows="3"
                    class="form-control"
                    hide-details
                    outlined
                    style="width: 100%; height: 100px"
                  ></v-textarea>

                  <span
                    v-if="primary_errors && primary_errors.message"
                    class="text-danger mt-2"
                    >{{ primary_errors.message[0] }}</span
                  >
                </v-col>
                <v-col cols="12" dense>
                  <v-select
                    label="Status"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="status"
                    hide-details
                    :items="['Open', 'Closed']"
                  ></v-select>
                </v-col>
              </v-row>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" class="text-right">
              <v-btn small :loading="loading" color="primary" @click="submit">
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
  props: ["item"],
  data: () => ({
    response: "",
    snackbar: false,
    color: "black",
    primary_errors: [],
    status: "Open",
    message: "",
    loading: false,
  }),
  created() {},
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    submit() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          quotation_id: this.item.id,
          message: this.message,
          status: this.status,
          user_id: this.$auth.user.id,
        },
      };

      this.$axios
        .post("/quotation_fallowup", options.params)
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
