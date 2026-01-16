<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-form ref="form" lazy-validation>
      <v-row>
        <v-col cols="12" md="6">
          <v-text-field label="Camera Name" outlined dense v-model="form.name" :disabled="!editable"
            :rules="[v => !!v || 'Name is required']" />
        </v-col>

        <!-- <v-col cols="12" md="6">
          <v-text-field label="Node Server IP" outlined dense v-model="form.node_server_ip" :disabled="!editable" />
        </v-col> -->

        <v-col cols="12" md="6">
          <v-text-field label="Camera RTSP URL" outlined dense v-model="form.rtsp_url" :disabled="!editable"
            :rules="[v => !!v || 'RTSP URL is required']" />
        </v-col>
      </v-row>

      <v-divider class="my-3"></v-divider>

      <div class="d-flex justify-end">
        <v-btn text @click="$emit('closeDialog')">Close</v-btn>

        <v-btn v-if="editable" class="ml-2" color="primary" :loading="saving" @click="save">
          Save
        </v-btn>
      </div>
    </v-form>
  </div>
</template>

<script>
export default {
  props: {
    editId: { type: [Number, String], default: null },
    item: { type: Object, default: null },
    editable: { type: Boolean, default: false },
  },

  data() {
    return {
      saving: false,
      snackbar: false,
      response: "",
      form: {
        name: "",
        rtsp_url: "",
        // node_server_ip: "",
      },
      endpoint: "cameraslist", // must match page endpoint
    };
  },

  mounted() {
    // If opening from table item (view/edit)
    if (this.item) {
      this.form = {
        name: this.item.name || "",
        rtsp_url: this.item.rtsp_url || "",
        // node_server_ip: this.item.node_server_ip || "",
      };
      return;
    }

    // If only editId provided, load from backend
    if (this.editId) this.fetchOne();
  },

  methods: {
    async fetchOne() {
      try {
        const { data } = await this.$axios.get(`${this.endpoint}/${this.editId}`);
        const cam = data?.data || data; // support both shapes
        this.form = {
          name: cam.name || "",
          rtsp_url: cam.rtsp_url || "",
          // node_server_ip: cam.node_server_ip || "",
        };
      } catch (e) {
        // silent (parent snackbar will show on list refresh)
      }
    },

    async save() {
      const ok = this.$refs.form.validate();
      if (!ok) return;

      this.saving = true;
      this.snackbar = false;
      this.response = "";

      try {
        let res;

        if (this.editId) {
          // Not required, but harmless:
          // this.form.id = this.editId;

          res = await this.$axios.put(`${this.endpoint}/${this.editId}`, this.form);
        } else {
          res = await this.$axios.post(`${this.endpoint}`, this.form);
        }

        // Accept any 2xx response (200 update, 201 create)
        if (res.status >= 200 && res.status < 300) {
          this.snackbar = true;
          this.response = res?.data?.message || "Saved successfully";
          this.$emit("closeDialog");
        } else {
          this.snackbar = true;
          this.response = "Save failed. Try again";
        }
      } catch (e) {
        this.snackbar = true;

        // Laravel validation support
        const data = e?.response?.data;
        if (data?.message) {
          this.response = data.message;
        } else if (data?.errors) {
          const firstKey = Object.keys(data.errors)[0];
          this.response = data.errors[firstKey]?.[0] || "Validation failed";
        } else {
          this.response = "Save failed";
        }
      } finally {
        this.saving = false;
      }
    }
  },

};
</script>
