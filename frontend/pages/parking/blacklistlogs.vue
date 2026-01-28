<template>
  <div>
    <!-- Snackbar -->
    <div class="text-center">
      <v-snackbar v-model="snackbar" top color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <!-- Image Preview Dialog -->
    <v-dialog v-model="dialogImagePreview" max-width="80%">
      <v-card>
        <v-card-title dense class="popup_background">
          Image Preview
          <v-spacer></v-spacer>
          <v-btn icon @click="dialogImagePreview = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="d-flex justify-center">
          <v-img :src="dialogImageUrl" contain style="max-width: 80%; max-height: 80vh;" />
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Parking Info Dialog (optional) -->
    <v-dialog v-model="dialogTabViewParking" width="1000px" persistent scrollable eager :retain-focus="false">
      <v-card>
        <v-card-title dense class="popup_background">
          Parking Info
          <v-spacer></v-spacer>
          <v-btn icon @click="dialogTabViewParking = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text class="background">
          <!-- If you have ParkingInfo component, keep it. Otherwise remove this block -->
          <ParkingInfo v-if="selectedParking" @close="dialogTabViewParking = false; getDataFromApi(0)"
            :parking="selectedParking" />
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Header + Filters -->
    <v-row class="p-0" style="padding-top: 0px">
      <v-col cols="12" class="text-right" style="padding-top: 0px; z-index: 9; padding-right: 0px">
        <v-card class="mt-3" elevation="0">
          <v-card-text>
            <v-row>
              <v-col class="text-left mt-1" cols="4">
                <h3 style="font-weight: normal">Blocked Logs - History</h3>
              </v-col>

              <v-col style="width: 600px; padding: 0px">
                <v-row>
                  <v-col style="margin: auto">
                    <v-icon @click="getDataFromApi(0)" class="mt-2 mr-2">mdi-reload</v-icon>

                    <v-text-field style="padding-top: 7px; float: right; width: 250px" height="20"
                      class="employee-schedule-search-box" v-model="commonSearch" label="Search"
                      placeholder="Plate / Device / Camera / Reason..." dense outlined append-icon="mdi-magnify"
                      clearable hide-details />
                  </v-col>



                  <v-col style="max-width: 200px; margin: auto">
                    <!-- Your existing date range component -->
                    <CustomFilter style="float: left; padding-top: 5px; z-index: 999" @filter-attr="filterAttr"
                      :default_date_from="date_from" :default_date_to="date_to" :defaultFilterType="1"
                      :height="'30px'" />
                  </v-col>

                  <v-col style="max-width: 100px; padding-top: 18px; margin: auto">
                    <v-btn dense small color="primary" @click="getDataFromApi(0)">Submit</v-btn>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>

            <!-- Table -->
            <v-row style="margin-top: 0px">
              <v-col cols="12" style="margin-top: 0px">
                <v-card flat>
                  <v-card-text style="padding: 0px">
                    <v-data-table :height="tableHeight" v-if="showTable" :headers="headers" :items="items"
                      :server-items-length="totalRowsCount" :loading="loading" :options.sync="options"
                      :footer-props="{ itemsPerPageOptions: [10, 50, 100, 500, 1000] }"
                      class="elevation-0 table-header-color">
                      <!-- Serial No -->
                      <template v-slot:item.sno="{ index }">
                        {{ getSno(index) }}
                      </template>

                      <!-- Plate -->
                      <template v-slot:item.plate_number="{ item }">
                        <div v-if="item.plate_number">{{ item.plate_number }}</div>
                        <div v-else>---</div>
                      </template>

                      <!-- Capture Time -->
                      <template v-slot:item.raw_capture_time="{ item }">
                        <div v-if="item.raw_capture_time">{{ item.raw_capture_time }}</div>
                        <div v-else>---</div>
                      </template>
                      <template v-slot:item.first_name="{ item }">
                        <div v-if="item.parking_members">{{ item.parking_members.first_name }} {{
                          item.parking_members.last_name }}</div>
                        <div v-else>---</div>
                      </template>


                      <!-- Action -->
                      <template v-slot:item.action="{ item }">
                        <v-chip v-if="item.action" :color="item.action === 'blocked' ? 'red' : 'green'" small label>
                          {{ $utils?.caps ? $utils.caps(item.action) : item.action }}
                        </v-chip>
                        <div v-else>---</div>
                      </template>

                      <!-- Reason -->
                      <template v-slot:item.reason="{ item }">
                        <div style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                          {{ item.reason || '---' }}
                        </div>
                      </template>

                      <!-- Photo (if you have path/file fields; adjust based on your backend response) -->
                      <template v-slot:item.plate_photo="{ item }">
                        <v-img v-if="item.out_time == null && item.in_background_file_name"
                          @click="openImagePreview(item.parking_image_path + '/' + item.in_background_file_name.replace('_BACKGROUND', '_PLATE'))"
                          :src="item.parking_image_path + '/' + item.in_background_file_name.replace('_BACKGROUND', '_PLATE')"
                          max-width="100" max-height="30"></v-img>

                        <v-img v-else-if="item.out_background_file_name"
                          @click="openImagePreview(item.parking_image_path + '/' + item.out_background_file_name.replace('_BACKGROUND', '_PLATE'))"
                          :src="item.parking_image_path + '/' + item.out_background_file_name.replace('_BACKGROUND', '_PLATE')"
                          max-width="100" max-height="30"></v-img>
                      </template>

                      <!-- Options -->
                      <template v-slot:item.options="{ item }">
                        <v-menu bottom left>
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn icon v-bind="attrs" v-on="on">
                              <v-icon>mdi-dots-vertical</v-icon>
                            </v-btn>
                          </template>
                          <v-list width="160" dense>
                            <v-list-item @click="viewParkinginfo(item)">
                              <v-list-item-title style="cursor: pointer">
                                <v-icon color="secondary" small>mdi-eye</v-icon>
                                Details
                              </v-list-item-title>
                            </v-list-item>
                          </v-list>
                        </v-menu>
                      </template>

                      <template v-slot:no-data>
                        <v-alert type="info" dense>No data available</v-alert>
                      </template>
                    </v-data-table>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>

          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
// If you use MQTT path as in your reference:
import { mqttRequestReply } from "@/utils/mqttRequestReplyClient.js";

// If you have ParkingInfo component, import it; else remove in template
// import ParkingInfo from "@/components/ParkingInfo.vue";

export default {
  components: {
    // ParkingInfo,
  },

  props: [
    "memberId",
    "isMQTT",
  ],

  data() {
    return {
      // UI
      snackbar: false,
      response: "",
      loading: false,
      showTable: true,

      dialogImagePreview: false,
      dialogImageUrl: null,

      dialogTabViewParking: false,
      selectedParking: null,

      tableHeight: 750,

      // Filters
      commonSearch: "",
      filterAction: "All",
      date_from: "",
      date_to: "",

      allActionOptionsList: [
        { id: null, name: "All" },
        { id: 1, name: "Blocked" },
        { id: 2, name: "Allowed" },
      ],

      // Server pagination
      options: { page: 1, itemsPerPage: 10, sortBy: [], sortDesc: [] },
      totalRowsCount: 0,
      items: [],
      perPage: 10,
      currentPage: 1,

      // request control
      cancelTokenSource: null,
      _fetchVersion: 0,
      searchTimer: null,

      // Table headers (ALL RAW DUMP fields)
      headers: [
        { text: "S.No", value: "sno", sortable: false },

        // Primary fields
        { text: "Plate No", value: "plate_number", sortable: false },
        { text: "Action", value: "action", sortable: false },
        { text: "Reason", value: "reason", sortable: false },

        { text: "Member Name", value: "first_name", sortable: false },
        { text: "Date", value: "created_datetime", sortable: false },


        // Raw dump columns
        // { text: "Device No", value: "raw_device_no", sortable: false },
        // { text: "Capture Time", value: "raw_capture_time", sortable: false },
        // { text: "Raw Plate", value: "raw_plate_no", sortable: false },
        // { text: "Vehicle Color", value: "raw_vehicle_color", sortable: false },
        // { text: "Vehicle Type", value: "raw_vehicle_type", sortable: false },
        // { text: "Brand", value: "raw_vehicle_brand", sortable: false },
        // { text: "Direction", value: "raw_moving_direction", sortable: false },
        // { text: "Validity", value: "raw_validity", sortable: false },
        // { text: "Country/Region", value: "raw_country_region", sortable: false },
        // { text: "Plate Color", value: "raw_plate_color", sortable: false },
        // { text: "Plate Size", value: "raw_plate_size", sortable: false },
        // { text: "Plate Type", value: "raw_plate_type", sortable: false },
        // { text: "Province", value: "raw_province", sortable: false },
        // { text: "Camera No", value: "raw_camera_no", sortable: false },

        // optional image
        { text: "Photo", value: "plate_photo", sortable: false },

        { text: "Options", value: "options", sortable: false },
      ],
    };
  },

  watch: {
    // server pagination trigger
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },

    // filter triggers
    commonSearch() {
      clearTimeout(this.searchTimer);
      this.searchTimer = setTimeout(() => {
        this.getDataFromApi(0);
      }, 500);
    },
    filterAction() {
      this.getDataFromApi(0);
    },
    date_from() {
      this.getDataFromApi(0);
    },
    date_to() {
      this.getDataFromApi(0);
    },
  },

  mounted() {
    this.tableHeight = window.innerHeight - 210;
    window.addEventListener("resize", () => {
      this.tableHeight = window.innerHeight - 210;
    });

    // initial load
    this.getDataFromApi(0);
  },

  methods: {
    // Serial number for server pagination
    getSno(localIndex) {
      const page = Number(this.currentPage || 1);
      const per = Number(this.perPage || 10);
      return (page - 1) * per + (localIndex + 1);
    },

    openImagePreview(url) {
      this.dialogImageUrl = url;
      this.dialogImagePreview = true;
    },

    viewParkinginfo(item) {
      this.selectedParking = item;
      this.dialogTabViewParking = true;
    },

    filterAttr(data) {
      this.date_from = data.from;
      this.date_to = data.to;
    },

    async getDataFromApi(custompage = 1) {
      // Reset pagination if asked
      if (custompage === 0) this.options = { ...this.options, page: 1, itemsPerPage: this.options.itemsPerPage || 10 };

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;
      const sortedBy = Array.isArray(sortBy) ? sortBy[0] : "";
      const sortedDesc = Array.isArray(sortDesc) ? sortDesc[0] : false;

      if (!(page > 0)) return;

      // request versioning (ignore stale)
      this._fetchVersion = (this._fetchVersion || 0) + 1;
      const myFetchVersion = this._fetchVersion;

      this.loading = true;

      // Build params
      const params = {
        pagination: true,
        company_id: this.$auth.user.company_id,
        page,
        perPage: itemsPerPage,

        date_from: this.date_from,
        date_to: this.date_to,
        common_search: this.commonSearch,

        filter_action: this.filterAction, // All / Blocked / Allowed
        member_id: this.memberId ?? null,

        sorted_by: sortedBy || null,
        sorted_desc: sortedDesc || false,
      };

      // Axios cancel token
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel("Canceled due to new request.");
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      // Normalizer (supports both MQTT and HTTP)
      const normalize = (raw) => ({
        data: Array.isArray(raw?.data) ? raw.data : [],
        total: Number.isFinite(raw?.total) ? raw.total : (Array.isArray(raw?.data) ? raw.data.length : 0),
        status: raw?.status ?? true,
        message: raw?.message ?? "",
      });

      try {
        let result = null;

        // MQTT path (optional)
        if (this.isMQTT && typeof mqttRequestReply === "function") {
          try {
            const mqttResp = await mqttRequestReply({
              companyId: params.company_id,
              action: "parking_blocked_logs", // IMPORTANT: your backend MQTT action name
              payload: params,
              timeoutMs: 8000,
            });

            // Expect shape: { action: "parking_blocked_logs", data: { data: [...], total: N } }
            if (mqttResp && mqttResp.action === "parking_blocked_logs") {
              result = normalize(mqttResp.data);
            }
          } catch (e) {
            // fallback to HTTP below
          }
        }

        // HTTP fallback
        if (!result) {
          const { data } = await this.$axios.get("parking_blocked_logs", {
            params,
            cancelToken: this.cancelTokenSource.token,
          });
          result = normalize(data);
        }

        // Ignore stale responses
        if (myFetchVersion !== this._fetchVersion) return;

        // Apply results
        this.items = result.data;
        this.totalRowsCount = result.total;
        this.showTable = true;

      } catch (error) {
        if (this.$axios.isCancel?.(error)) {
          // silent cancel
        } else {
          console.error("Error fetching logs:", error);
          this.snackbar = true;
          this.response =
            error?.response?.data?.message ||
            error?.message ||
            "Failed to load blocked logs.";
        }
      } finally {
        if (myFetchVersion !== this._fetchVersion) return;

        this.loading = false;

        // IMPORTANT: keep these synced for S.No
        this.currentPage = page;
        this.perPage = itemsPerPage;
      }
    },
  },
};
</script>
