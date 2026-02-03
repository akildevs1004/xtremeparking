<template>
  <v-card>
    <v-card-title class="popup_background_noviolet">
      <v-spacer></v-spacer>

      <v-btn x-small text :disabled="busy" @click="downloadTemplate">
        <v-icon small class="mr-1">mdi-download</v-icon> Template
      </v-btn>
    </v-card-title>

    <v-card-text>
      <v-row>
        <v-col cols="12" md="5">
          <v-file-input dense outlined accept=".csv,text/csv" label="Upload CSV" v-model="file" :disabled="busy"
            append-icon="mdi-file-excel-box" />
        </v-col>

        <v-col cols="12" md="5" style="margin-top:-7px">
          <v-btn style="width:100px" color="primary" class="mt-2" :disabled="!file || busy" @click="previewCsv">
            <v-icon left>mdi-eye</v-icon> Preview
          </v-btn>

          <v-btn style="width:100px;margin-left:30px" color="success" class="mt-2" :disabled="!results.length || busy"
            @click="importMembers">
            <v-icon left>mdi-database-import</v-icon> Create
          </v-btn>
        </v-col>
      </v-row>

      <v-alert v-if="results.length" type="info" dense outlined class="mt-2">
        Rows: <b>{{ results.length }}</b> |
        Success: <b>{{ summary.success }}</b> |
        Failed: <b>{{ summary.failed }}</b> |
        Pending: <b>{{ summary.pending }}</b>
      </v-alert>

      <v-data-table dense fixed-header height="450" :headers="headers" :items="results" :items-per-page="50"
        class="elevation-0 mt-2">
        <template v-slot:item.status="{ item }">
          <v-chip x-small :color="statusColor(item.status)" text-color="white">
            {{ item.status }}
          </v-chip>
        </template>
        <template v-slot:item.parking_floor_number="{ item }">

          {{ item.parking_floor_number }}-{{ item.parking_number }}

        </template>



      </v-data-table>
    </v-card-text>

    <v-overlay :value="busy" opacity="0.12">
      <v-progress-circular indeterminate size="44" />
    </v-overlay>
  </v-card>
</template>
<script>export default {
  name: "ImportMembersCsvPopup",

  data() {
    return {
      file: null,
      results: [],
      busy: false,
      concurrency: 3,

      endpointPreview: "parking_members/import-csv/preview",
      endpointCreate: "/parking_members",

      defaultVehiclePlateType: "Standard",

      headers: [
        { text: "#", value: "row_no" },
        { text: "Unit", value: "flat_number" },
        { text: "Parking Floor", value: "parking_floor_number" },
        { text: "Vehicle Plate", value: "plate_number" },
        { text: "Email", value: "email" },
        { text: "Name", value: "first_name" },
        { text: "Status", value: "status" },
        { text: "Message", value: "message" },
      ],
    };
  },

  computed: {
    summary() {
      return {
        success: this.results.filter(r => r.status === "success").length,
        failed: this.results.filter(r => r.status === "error").length,
        pending: this.results.filter(r => ["pending", "running"].includes(r.status)).length,
      };
    },
  },

  methods: {
    /* -------------------------------- HELPERS -------------------------------- */

    pick(r, key) {
      return r && r[key] ? String(r[key]).trim() : "";
    },

    statusColor(s) {
      if (s === "success") return "green";
      if (s === "error") return "red";
      if (s === "running") return "blue";
      return "grey";
    },

    toast(color, text) {
      this.$emit("toast", { color, text });
    },

    /* ------------------------------ PARSERS ------------------------------ */

    parseParkingBays(value) {
      if (!value) return [];
      return String(value)
        .split(",")
        .map(v => v.trim())
        .filter(Boolean)
        .map(v => {
          const [floor, number] = v.split("-");
          return { floor, number };
        });
    },

    parseVehicles(row) {
      return ["vehicle# 1", "vehicle# 2", "vehicle# 3"]
        .map(k => this.pick(row, k))
        .filter(Boolean);
    },

    parseRemarks(value) {
      if (!value) return null;
      const v = value.toLowerCase();
      if (v.includes("owner")) return "Owner";
      if (v.includes("tenant")) return "Tenant";
      if (v.includes("short")) return "ShortTerm";
      return "Tenant";
    },
    // normalizeRowArray(row) {
    //   return {
    //     first_name: (row[2] || "").split(" ")[0],      // Name F/L
    //     last_name: (row[2] || "").split(" ").slice(1).join(" "),
    //     email: row[5] || "",                            // Emails Add
    //     phone: row[4] || "",                            // Phone #
    //     flat_number: row[0] || "",                      // Unit
    //     remarks: this.parseRemarks(row[11] || ""),      // Remarks
    //     vehicle_plate_color: row[10] || "White",        // Color
    //     vehicle_country_region: "",
    //   };
    // },
    normalizeRow(r) {
      const fullName = this.pick(r, "name f/l");





      return {
        first_name: fullName.split(" ")[0] || "-",
        last_name: fullName.split(" ").slice(1).join(" "),
        email: this.pick(r, "emails add"),
        phone: this.pick(r, "phone #"),
        flat_number: this.pick(r, "unit"),
        remarks: this.parseRemarks(this.pick(r, "remarks")),
        vehicle_plate_color: this.pick(r, "color") || "White",
        vehicle_country_region: "",
      };
    },

    /* ------------------------------ PREVIEW ------------------------------ */

    cleanPlateNumber(plate) {
      if (!plate) return "";

      // Remove words like Dubai, Abu Dhabi, etc.
      plate = plate.replace(/\b(Dubai|Abu Dhabi|Sharjah|UAE)\b/gi, "");

      // Remove everything in parentheses
      plate = plate.replace(/\(.*?\)/g, "");

      plate = plate.replace("?", "");


      // Remove dashes
      plate = plate.replace(/-/g, "");

      // Remove all spaces
      plate = plate.replace(/\s+/g, "");

      return plate.toUpperCase();
    },
    cleanEmailId(emailid, base) {



      if (!emailid) return `${base.first_name.replace(/\s+/g, "").toLowerCase()}.${base.last_name.replace(/\s+/g, "").toLowerCase()}@gmail.com`;

      emailid = emailid.replace("?", "");
      emailid = emailid.replace("`", "");

      emailid = emailid.replace(/\s+/g, "");


      return emailid.toLowerCase();
    },



    async previewCsv() {
      if (!this.file) return;
      this.busy = true;

      try {
        const form = new FormData();
        form.append("file", this.file);
        form.append("company_id", this.$auth.user.company_id);

        const { data } = await this.$axios.post(this.endpointPreview, form);
        const rows = data.rows || [];

        // console.log("Preview rows:", rows);
        const expanded = [];

        rows.forEach(r => {
          const base = this.normalizeRow(r);

          const parkings = this.parseParkingBays(this.pick(r, "parking bay no."));
          const vehicles = this.parseVehicles(r);
          //console.log("vehicles", vehicles);
          //console.log("base.flat_number", base, base.flat_number);



          parkings.forEach(p => {
            vehicles.forEach(v => {
              expanded.push({
                row_no: expanded.length + 1,
                ...base,
                email: this.cleanEmailId(base.email, base),
                flat_number: base.flat_number,
                parking_floor_number: p.floor,
                parking_number: p.number,
                plate_number: this.cleanPlateNumber(v),
                member_type: "Tenant",//base.remarks,
                remarks: base.remarks,
                status: "pending",
                message: base.remarks,
                __raw: r,
              });
            });
          });
        });

        this.results = expanded;


      } catch (e) {
        this.toast("error", "Preview failed");
      } finally {
        this.busy = false;
      }
    },

    /* ------------------------------ IMPORT ------------------------------ */

    async importMembers() {
      this.busy = true;

      try {
        for (let i = 0; i < this.results.length; i++) {
          const row = this.results[i];
          this.$set(this.results, i, { ...row, status: "running" });

          try {
            const payload = {
              company_id: this.$auth.user.company_id,
              first_name: row.first_name || "-",
              last_name: row.last_name || "-",
              email: row.email,
              phone: row.phone,
              plate_number: row.plate_number,
              member_type: "Tenant",//row.remarks,
              remarks: row.remarks,

              parking_slot: `${row.parking_floor_number}-${row.parking_number}`,
              vehicle_country_region: row.vehicle_country_region,
              vehicle_plate_type: this.defaultVehiclePlateType,
              vehicle_plate_color: row.vehicle_plate_color,
              plate_size: "Small",
              password: "password",
              confirm_password: "password",

              is_import_from_csv: true,
            };

            const { data } = await this.$axios.post(this.endpointCreate, payload);

            this.$set(this.results, i, {
              ...row,
              status: data?.status ? "success" : "error",
              message: data?.message || "Failed",
            });
          } catch (e) {
            this.$set(this.results, i, { ...row, status: "error", message: "Request failed" });
          }
        }

        this.toast("success", "Import completed");
      } finally {
        this.busy = false;
      }
    },

    downloadTemplate() {
      const csv =
        "PARKING ALLOCATION,,,,,,,,,,,,,,,,,,,\nS.No., Unit, Parking Bay No., Name F / L, Phone #, Emails Add, Vehicle# 1, Model, Vehicle# 2, Model, Vehicle# 3, Model, Color, Remarks\n";


      const blob = new Blob([csv], { type: "text/csv;charset=utf-8" });
      const url = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = "parking_members_import_template.csv";
      a.click();
      URL.revokeObjectURL(url);
    },
  },
};
</script>
