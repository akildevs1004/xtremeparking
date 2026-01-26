<template>
  <v-card>
    <v-card-title class="popup_background_noviolet">
      <!-- <span>Import Members (CSV)</span> -->
      <v-spacer></v-spacer>

      <v-btn x-small text :disabled="busy" @click="downloadTemplate">
        <v-icon small class="mr-1">mdi-download</v-icon> Template
      </v-btn>


      <!-- <v-icon :disabled="busy" @click="close" outlined>mdi mdi-close-circle</v-icon> -->
    </v-card-title>

    <v-card-text>
      <!-- Upload + Defaults -->
      <v-row>
        <v-col cols="12" md="7">
          <v-file-input dense outlined accept=".csv,text/csv" label="Upload CSV  " v-model="file" :disabled="busy"
            append-icon="mdi-file-excel-box" />
          <!-- <div class="text-caption grey--text mt-1">
            Headers:
            <b>
              First Name, Last Name, Flat Number, Parking Floor Number, Parking Number,
              Email ID, Phone, Prefix, Plate Number, Vehicle Country Region, Vehicle Plate Color
            </b>
          </div> -->
        </v-col>

        <v-col cols="12" md="5">
          <!-- <v-row dense>
            <v-col cols="12" md="6">
              <v-select dense outlined :disabled="busy" label="Default Member Type" v-model="defaultMemberType"
                :items="memberTypeItems" item-text="text" item-value="value" />
            </v-col>

            <v-col cols="12" md="6">
              <v-select dense outlined :disabled="busy" label="Default Plate Type" v-model="defaultVehiclePlateType"
                :items="plateTypeItems" />
            </v-col>

            <v-col cols="12" md="6">
              <v-select dense outlined :disabled="busy" label="Default Plate Size" v-model="defaultPlateSize"
                :items="plateSizeItems" />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field dense outlined :disabled="busy" type="number" min="1" max="10" label="Concurrency (1-10)"
                v-model.number="concurrency" />
            </v-col>
          </v-row> -->

          <v-btn style="width:100px" color="primary" class="mt-2" :disabled="!file || busy" @click="previewCsv">
            <v-icon left>mdi-eye</v-icon>
            Preview
          </v-btn>

          <v-btn style="width:100px" color="success" class="mt-2" :disabled="!results.length || busy"
            @click="importMembers">
            <v-icon left>mdi-database-import</v-icon>
            Create
          </v-btn>
        </v-col>
      </v-row>

      <!-- Summary -->
      <v-alert v-if="results.length" type="info" dense outlined class="mt-2">
        Rows: <b>{{ results.length }}</b> |
        Success: <b>{{ summary.success }}</b> |
        Failed: <b>{{ summary.failed }}</b> |
        Pending: <b>{{ summary.pending }}</b>
      </v-alert>

      <!-- Status table -->
      <v-data-table dense fixed-header height="450" :headers="headers" :items="results" :items-per-page="50"
        class="elevation-0 mt-2">
        <template v-slot:item.status="{ item }">
          <v-chip x-small :color="statusColor(item.status)" text-color="white">
            {{ item.status }}
          </v-chip>
        </template>

        <template v-slot:item.message="{ item }">
          <span :style="item.status === 'error' ? 'color:#ef4444' : ''">
            {{ item.message || "-" }}
          </span>
        </template>
      </v-data-table>
    </v-card-text>

    <v-card-actions>
      <v-spacer></v-spacer>
      <v-btn color="warning" :disabled="busy" @click="reset">Reset</v-btn>
      <v-btn color="error" :disabled="busy" @click="close">Close</v-btn>
    </v-card-actions>

    <v-overlay :value="busy" opacity="0.12">
      <v-progress-circular indeterminate size="44" />
    </v-overlay>
  </v-card>

</template>

<script>
export default {
  name: "ImportMembersCsvPopup",
  props: {
    value: { type: Boolean, default: false }, // v-model


    // Backend preview endpoint that returns rows JSON from CSV
    endpointPreview: { type: String, default: "parking_members/import-csv/preview" },

    // Your existing create endpoint
    endpointCreate: { type: String, default: "/parking_members" },
  },

  data() {
    return {
      file: null,
      results: [],
      busy: false,
      previewing: false,
      importing: false,

      internalOpen: false,
      file: null,
      busy: false,
      concurrency: 1,

      // Defaults required by your validator but NOT in CSV
      defaultMemberType: "Tenant",
      defaultVehiclePlateType: "Standard",
      defaultPlateSize: "Small",

      memberTypeItems: [
        { text: "Tenant", value: "Tenant" },
        { text: "Membership", value: "Membership" },
      ],
      plateTypeItems: ["Standard", "Private", "Commercial", "Motorcycle", "Classic"],
      plateSizeItems: ["Small", "Medium", "Large"],

      results: [],

      headers: [
        { text: "#", value: "row_no", },
        { text: "First Name", value: "first_name", },
        { text: "Last Name", value: "last_name", },
        { text: "Email", value: "email", },
        { text: "Phone", value: "phone", },
        { text: "Plate", value: "plate_number", },
        { text: "Region", value: "vehicle_country_region", },
        { text: "Plate Color", value: "vehicle_plate_color", },
        { text: "Type", value: "member_type", },
        { text: "Status", value: "status", },
        { text: "Message", value: "message" },
      ],
    };
  },

  computed: {
    summary() {
      const success = this.results.filter((r) => r.status === "success").length;
      const failed = this.results.filter((r) => r.status === "error").length;
      const pending = this.results.filter((r) => ["pending", "running"].includes(r.status)).length;
      return { success, failed, pending };
    },
  },

  watch: {
    value: {
      immediate: true,
      handler(v) {
        this.internalOpen = !!v;
        if (!v) this.reset();
      },
    },
    internalOpen(v) {
      this.$emit("input", v);
      if (!v) this.reset();
    },
  },

  methods: {
    statusColor(s) {
      if (s === "success") return "green";
      if (s === "error") return "#ef4444";
      if (s === "running") return "blue";
      return "grey";
    },

    toast(color, text) {
      this.$emit("toast", { color, text });
    },

    close() {
      if (this.busy) return;
      this.internalOpen = false;
      this.$emit("close");
    },

    reset() {
      this.file = null;
      this.results = [];
      this.busy = false;
      this.concurrency = 3;
      this.defaultMemberType = "Tenant";
      this.defaultVehiclePlateType = "Standard";
      this.defaultPlateSize = "Small";
    },

    downloadTemplate() {
      const csv =
        "First Name,Last Name,Flat Number,Parking Floor Number,Parking Number,Email ID,Phone,Prefix,Plate Number,Vehicle Country Region,Vehicle Plate Color\n" +
        "Rahman,Abdul,2001,P2,104,rahman@gmail.com,971588888888,K,52565,DXB,white\n";

      const blob = new Blob([csv], { type: "text/csv;charset=utf-8" });
      const url = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = "parking_members_import_template.csv";
      a.click();
      URL.revokeObjectURL(url);
    },

    // supports snake_case from backend OR original CSV header keys
    pick(r, snakeKey, headerKey) {
      if (r && r[snakeKey] !== undefined && r[snakeKey] !== null) return String(r[snakeKey]).trim();
      if (r && r[headerKey] !== undefined && r[headerKey] !== null) return String(r[headerKey]).trim();
      return "";
    },

    normalizeRow(r) {
      return {
        first_name: this.pick(r, "first_name", "First Name"),
        last_name: this.pick(r, "last_name", "Last Name"),
        email: this.pick(r, "email", "Email ID") || this.pick(r, "email_id", "Email ID"),
        phone: this.pick(r, "phone", "Phone"),
        plate_number: this.pick(r, "plate_number", "Plate Number"),
        vehicle_country_region: this.pick(r, "vehicle_country_region", "Vehicle Country Region"),
        vehicle_plate_color: this.pick(r, "vehicle_plate_color", "Vehicle Plate Color"),
        prefix: this.pick(r, "prefix", "Prefix"),
        flat_number: this.pick(r, "flat_number", "Flat Number"),
        parking_floor_number: this.pick(r, "parking_floor_number", "Parking Floor Number"),
        parking_number: this.pick(r, "parking_number", "Parking Number"),
      };
    },

    // Build payload to satisfy your Laravel validator exactly
    buildCustomerPayload(rowRaw) {
      const n = this.normalizeRow(rowRaw);


      const today = new Date();

      // format YYYY-MM-DD
      const formatDate = (date) => date.toISOString().slice(0, 10);

      const membership_start = formatDate(today);

      const endDate = new Date(today);
      endDate.setFullYear(endDate.getFullYear() + 1);

      const membership_end = formatDate(endDate);


      return {
        company_id: this.$auth.user.company_id,
        editId: null,

        first_name: n.first_name,
        last_name: n.last_name,
        email: n.email,
        phone: n.phone,

        plate_number: n.plate_number,

        member_type: "Membership",
        membership_start: membership_start,
        membership_end: membership_end,
        parking_slot: n.parking_number,
        address: n.flat_number + " - " + n.parking_floor_number,

        vehicle_country_region: n.vehicle_country_region,
        vehicle_plate_type: this.defaultVehiclePlateType,
        vehicle_plate_color: n.vehicle_plate_color,

        plate_size: "small",
        vehicle_type: null,
        vehicle_color: null,

        blocked_reason: null,

        password: "password",
        confirm_password: "password",


      };
    },

    validateCustomer(customer) {
      const errs = [];
      const req = (k, label) => {
        if (!customer[k] || String(customer[k]).trim() === "") errs.push(label + " required");
      };

      req("first_name", "First Name");
      req("last_name", "Last Name");
      req("email", "Email");
      req("phone", "Phone");
      req("plate_number", "Plate Number");
      req("member_type", "Member Type");
      req("vehicle_country_region", "Vehicle Country Region");
      req("vehicle_plate_type", "Vehicle Plate Type");
      req("vehicle_plate_color", "Vehicle Plate Color");
      req("plate_size", "Plate Size");

      return errs;
    },

    async previewCsv() {
      if (!this.file) return;

      this.busy = true;
      try {
        const form = new FormData();
        form.append("file", this.file);
        form.append("company_id", this.$auth.user.company_id);

        const { data } = await this.$axios.post(this.endpointPreview, form, {
          headers: { "Content-Type": "multipart/form-data" },
        });

        const rows = data.rows || [];
        this.results = rows.map((r, i) => {
          const n = this.normalizeRow(r);
          return {
            row_no: i + 1,
            ...n,
            member_type: this.defaultMemberType,
            status: "pending",
            message: "Ready",
            __raw: r,
          };
        });

        if (!rows.length) this.toast("warning", "No rows found in CSV.");
      } catch (e) {
        const msg =
          (e && e.response && e.response.data && e.response.data.message) ||
          e.message ||
          "Preview failed";
        this.toast("error", msg);
      } finally {
        this.busy = false;
      }
    },

    async importMembers() {
      if (!this.results.length) return;

      this.busy = true;
      try {
        const queue = this.results.map((_, idx) => idx);
        const workers = Math.max(1, Math.min(10, Number(this.concurrency) || 3));

        const worker = async () => {
          while (queue.length) {
            const idx = queue.shift();
            const row = this.results[idx];

            this.$set(this.results, idx, { ...row, status: "running", message: "Creating..." });

            const customer = this.buildCustomerPayload(row.__raw);
            const vErr = this.validateCustomer(customer);
            if (vErr.length) {
              this.$set(this.results, idx, { ...row, status: "error", message: vErr.join(", ") });
              continue;
            }

            try {
              // YOUR EXACT CREATE STYLE
              const { data } = await this.$axios.post(this.endpointCreate, customer);

              if (!data || !data.status) {
                // API returns {status:false, message, errors}
                let msg = (data && data.message) || "Failed";
                if (data && data.errors) {
                  // Show the first error if it's an object/array
                  if (Array.isArray(data.errors)) msg = data.errors.join(", ");
                  else if (typeof data.errors === "object") {
                    const firstKey = Object.keys(data.errors)[0];
                    if (firstKey) {
                      const v = data.errors[firstKey];
                      msg = Array.isArray(v) ? v[0] : String(v);
                    } else {
                      msg = JSON.stringify(data.errors);
                    }
                  } else msg = String(data.errors);
                }
                this.$set(this.results, idx, { ...row, status: "error", message: msg });
              } else {
                this.$set(this.results, idx, { ...row, status: "success", message: data.message || "Created" });
              }
            } catch (e) {
              const msg =
                (e && e.response && e.response.data && e.response.data.message) ||
                e.message ||
                "Request failed";
              this.$set(this.results, idx, { ...row, status: "error", message: msg });
            }
          }
        };

        await Promise.all(Array.from({ length: workers }, () => worker()));

        this.toast("success", `Import completed. Success: ${this.summary.success}, Failed: ${this.summary.failed}`);
        this.$emit("imported", { success: this.summary.success, failed: this.summary.failed, results: this.results });
      } finally {
        this.busy = false;
      }
    },
  },
};
</script>
