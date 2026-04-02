<template>
  <NoAccess v-if="!can('operators_view')" />
  <div v-else>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-dialog v-model="newProductDialog" max-width="400px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense>
            {{ editId ? (editable ? "Update" : "View") : "New" }} Guest/Member
            Vehicle
          </span>
          <v-spacer></v-spacer>
          <v-icon @click="newProductDialog = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <EditMemberVehicleData
            :isMQTT="isMQTT"
            :key="key"
            :editId="editId"
            :memberId="memberId"
            :item="item"
            :editable="editable"
            @closeDialog="closeProductDialog"
          />
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-card elevation="0" class="mt-0 popup_background_noviolet">
      <v-toolbar class="mb-2" dense flat>
        <v-toolbar-title> <span> Vehicles List</span></v-toolbar-title>
        <!-- <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }"> -->
        <v-btn
          title="Reload"
          dense
          class="ma-0 px-0"
          x-small
          :ripple="false"
          @click="getDataFromApi"
          text
        >
          <v-icon class="ml-2" dark>mdi mdi-reload</v-icon>
        </v-btn>
        <!-- </template>
<span>Reload</span>
</v-tooltip> -->

        <v-spacer></v-spacer>
        <span style="width: 180px"
          ><v-text-field
            style="padding-top: 7px"
            height="20"
            class="employee-schedule-search-box"
            @input="getDataFromApi()"
            v-model="commonSearch"
            label="Search (min 3)"
            dense
            outlined
            type="text"
            append-icon="mdi-magnify"
            clearable
            hide-details
          ></v-text-field
        ></span>

        <v-btn
          v-if="can('operators_create')"
          title="Change Request"
          x-small
          :ripple="false"
          text
          @click="addItem()"
        >
          <v-icon class="">mdi mdi-plus-circle</v-icon>
        </v-btn>
      </v-toolbar>

      <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
        {{ snackText }}

        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
        </template>
      </v-snackbar>
      <v-data-table
        dense
        :headers="headers"
        :items="data"
        :loading="loading"
        :options.sync="options"
        :footer-props="{
          itemsPerPageOptions: [10, 50, 100, 500, 1000],
        }"
        class="elevation-1"
        :server-items-length="totalRowsCount"
        fixed-header
        :height="tableHeight"
        :disable-sort="true"
      >
        <template v-slot:item.sno="{ item, index }">
          {{
            currentPage
              ? (currentPage - 1) * perPage +
                (cumulativeIndex + data.indexOf(item))
              : ""
          }}
        </template>

        <template v-slot:item.guest_first_name="{ item, index }">
          {{ $utils.caps(item.guest_first_name) }}
          {{ $utils.caps(item.guest_last_name) }}
        </template>
        <template v-slot:item.guest_address="{ item, index }">
          {{ item.guest_address }},
          {{ item.guest_location }}
        </template>

        <template v-slot:item.options="{ item }">
          <v-menu bottom left>
            <template v-slot:activator="{ on, attrs }">
              <v-btn dark-2 icon v-bind="attrs" v-on="on">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </template>
            <v-list width="120" dense>
              <v-list-item v-if="can('operators_view')" @click="viewItem(item)">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-eye </v-icon>
                  View
                </v-list-item-title>
              </v-list-item>

              <v-list-item @click="editItem(item)" v-if="can('operators_edit')">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-pencil </v-icon>
                  Edit
                </v-list-item-title>
              </v-list-item>
              <v-list-item
                v-if="can('operators_delete')"
                @click="deleteItem(item)"
              >
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="error" small> mdi-delete </v-icon>
                  Delete
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>

<script>
import EditMemberVehicleData from "../../components/Parking/EditMemberVehicleData.vue";
import { mqttRequestReply } from "@/utils/mqttRequestReplyClient.js"; // adjust path

export default {
  props: ["memberId", "isMQTT"],
  components: {
    EditMemberVehicleData,
  },
  data: () => ({
    dialogSecurityCustomers: false,
    editId: null,
    item: null,
    editable: false,
    key: 1,
    viewCustomerId: null,
    commonSearch: "",
    perPage: 10,
    cumulativeIndex: 1,
    currentPage: 1,
    branchesList: [],
    isCompany: true,
    tableHeight: 750,
    id: "",

    newProductDialog: false,
    dialogViewCustomer: false,
    totalRowsCount: 0,

    snack: false,
    snackColor: "",
    snackText: "",
    departments: [],
    Model: "Log",
    security_id: null,
    endpoint: "parking_members_vehiclesList",
    payload: {},
    loading: true,
    browserHeight: 700,

    data: [],
    headers: [
      {
        text: "#",
        value: "sno",
      },
      {
        text: "Vehicle Number",
        value: "vehicle_number",
      },
      {
        text: "Parking Floor/Number",
        value: "parking_slot",
      },
      {
        text: "Name",
        value: "guest_first_name",
      },
      {
        text: "Address",
        value: "guest_address",
      },
      {
        text: "Company",
        value: "guest_company_details",
      },
      {
        text: "Options",
        value: "options",
      },
    ],
    ids: [],

    data: [],
    devices: [],
    total: 0,
    pagination: {
      current: 1,
      total: 0,
      itemsPerPage: 1000,
    },
    payloadOptions: {},
    options: { perPage: 10 },
    errors: [],
    snackbar: false,
    branchesList: [],
    branch_id: "",

    responseStatusColor: "",
    response: "",
    buildingTypes: [],
    _id: null,
    isBackendRequestOpen: false,
    companyId: 0,
  }),
  computed: {},
  mounted() {
    this.companyId = this.$route?.query?.id;
    this.tableHeight = window.innerHeight - 230;
    window.addEventListener("resize", () => {
      this.tableHeight = window.innerHeight - 230;
    });

    this.getDataFromApi();
  },
  created() {
    this._id = 4; //this.$route.params.id;
    this.loading = true;

    if (this.$auth.user.branch_id) {
      this.branch_id = this.$auth.user.branch_id;
      this.isCompany = false;
      return;
    }
    try {
      if (window) this.browserHeight = window.innerHeight - 70;
    } catch (e) {}
  },
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  methods: {
    can(per) {
      return true;
      return this.$pagePermission.can(per, this);
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

    closeProductDialog() {
      this.newProductDialog = false;
      this.dialogSecurityCustomers = false;

      this.getDataFromApi();
    },

    addItem() {
      this.editId = null;
      this.editable = true;
      this.key += 1;
      this.item = null;
      this.viewCustomerId = null;
      this.newProductDialog = true;
    },
    viewItem(item) {
      this.editId = item.id;
      this.editable = false;
      this.viewCustomerId = item.id;
      this.key += 1;
      this.item = item;
      this.newProductDialog = true;
    },
    // viewItem2(item) {
    //   this.$router.push("/alarm/view-customer/" + item.id);
    // },
    editItem(item) {
      this.editable = true;
      this.editId = item.id;
      this.key += 1;
      this.item = item;
      this.newProductDialog = true;
    },
    async deleteItem(item) {
      if (!item?.id) return;

      this.loading = true;
      this.snackbar = false;
      this.response = "";
      const companyId = this.$auth.user?.company_id || this.companyId; // fallback company id

      try {
        let result = null;
        let usedMQTT = false;

        // ---------- 1) Try MQTT ----------
        if (this.isMQTT && typeof mqttRequestReply === "function") {
          try {
            const mqttResponse = await mqttRequestReply({
              companyId,
              action: "parking_members_vehiclesList_delete",
              payload: { id: item.id },
              timeoutMs: 8000,
            });

            // Expected MQTT reply structure:
            // { action:"parking_members_vehiclesList_delete", status:true, message:"Deleted" }
            if (
              mqttResponse &&
              mqttResponse.action === "parking_members_vehiclesList_delete" &&
              mqttResponse.status !== undefined
            ) {
              result = mqttResponse;
              usedMQTT = true;
            } else {
              console.warn("[MQTT] Unexpected response:", mqttResponse);
            }
          } catch (err) {
            console.warn(
              "[MQTT] Delete failed; fallback to HTTP:",
              err?.message || err
            );
          }
        } else {
          // ---------- 2) Fallback to HTTP ----------

          const { data } = await this.$axios.delete(
            `/parking_members_vehiclesList/${item.id}`
          );
          result = data;
        }

        // ---------- 3) Handle result ----------
        if (result?.status === false) {
          this.color = "red";
          this.snackbar = true;
          this.response = result?.message || "Failed to delete vehicle.";
          this.loading = false;
          return;
        }

        // Success
        this.color = "background";
        this.snackbar = true;
        this.response =
          (usedMQTT ? "[MQTT] " : "") +
          (result?.message || "Member vehicle deleted successfully.");

        await this.getDataFromApi(); // refresh list
      } catch (e) {
        console.error("Delete error:", e);
        this.color = "red";
        this.snackbar = true;
        this.response =
          e?.response?.data?.message ||
          e?.message ||
          "Error deleting vehicle. Please try again.";
      } finally {
        this.loading = false;
      }
    },

    // deleteItem(item) {
    //   if (confirm("Are you sure want to delete  ?")) {
    //     this.loading = true;
    //     let options = {
    //       params: {
    //         company_id: this.$auth.user.company_id,
    //         id: item.id,
    //       },
    //     };

    //     this.$axios
    //       .delete(`parking_members_vehiclesList/${item.id}`)
    //       .then(({ data }) => {
    //         this.snackbar = true;
    //         this.response = "Member Vehicle is  Deleted Successfully";
    //         this.getDataFromApi();
    //         this.loading = false;
    //       });

    //   }
    // },

    async getDataFromApi(url = "", filter_column = "", filter_value = "") {
      if (this.isBackendRequestOpen) return false;
      this.isBackendRequestOpen = true;

      url = this.endpoint;

      this.newCustomerDialog = false;

      const { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";

      this.payloadOptions = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          company_id: this.$auth.user.company_id,
          common_search: this.commonSearch,
          member_id: this.memberId,
          // branch_id: this.branch_id,
          ...this.payload,
        },
      };
      if (filter_column != "")
        this.payloadOptions.params[filter_column] = filter_value;
      this.loading = true;

      this.currentPage = page;
      this.perPage = itemsPerPage;
      let dataFinal = null;

      if (this.isMQTT) {
        // --- MQTT Request/Reply Flow ---
        const mqttResponse = await mqttRequestReply({
          companyId: this.companyId,
          action: "parking_members_vehiclesList",
          payload: this.payloadOptions.params,
          timeoutMs: 8000,
        });

        // Check if the action matches expected type
        if (
          mqttResponse &&
          mqttResponse.action === "parking_members_vehiclesList"
        ) {
          dataFinal = mqttResponse.data || null;
        } else {
          console.warn("Unexpected MQTT response:", mqttResponse);
        }
      } else {
        // // --- HTTP Request (Axios) ---
        const response = await this.$axios.get(url, this.payloadOptions);
        dataFinal = response?.data ?? null;
      }

      // --- Final Data Handling ---
      if (dataFinal) {
        this.isBackendRequestOpen = false;
        this.data = dataFinal.data || [];
        this.total = dataFinal.total || 0;
        this.loading = false;
        this.totalRowsCount = dataFinal.total || 0;
      } else {
        console.warn("No data received from either MQTT or backend");
        this.loading = false;
      }
    },
  },
};
</script>
