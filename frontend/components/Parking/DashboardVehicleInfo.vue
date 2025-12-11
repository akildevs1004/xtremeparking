<template>
  <div class="vehicle-right-panel" :class="{ 'has-info': showInfo && selectedLog }">
    <!-- ========== VEHICLE IN/OUT LOG ========== -->
    <v-card class="panel-card mb-1 log-panel" flat>
      <v-card-title class="panel-title d-flex align-center">
        <span>Vehicle In/Out Log</span>

        <v-spacer />

        <!-- Small spinner while loading -->
        <v-progress-circular v-if="loading" indeterminate size="18" width="2" color="cyan" class="mr-2" />

        <v-icon small @click.stop="getDataFromApi()">mdi-reload</v-icon>
      </v-card-title>

      <v-divider class="mx-3" />

      <!-- VEHICLE LIST -->
      <v-list dense class="log-list" :class="{ disabled: loading }" ref="logListRef">
        <v-list-item v-for="(log, index) in items" :key="index" @click="!loading && selectLog(log, index)" :class="[
          'log-item',
          { 'log-item--selected': selectedIndex === index }
        ]">
          <v-list-item-content>
            <div class="log-row">
              <div class="log-left">
                <span class="lane">{{ index + 1 }}</span>

                <!-- Vehicle number as pill button -->
                <v-btn text small class="plate-btn" style="width: 100px;"
                  @click.stop="!loading && selectLog(log, index)">
                  <v-icon left x-small>mdi-car</v-icon>
                  {{ log.log_vehicle_number }}
                </v-btn>

                <span class="code" style="width: 80px;">
                  {{ log.raw_country_region }}
                </span>

                <span class="time" style="width: 150px;">
                  <v-icon x-small class="time-icon">mdi-clock-outline</v-icon>
                  {{ log.log_time }}
                </span>
              </div>

              <div class="log-right">
                <div :class="[
                  'direction-pill',
                  log.direction === 'OUT' ? 'out' : 'in'
                ]">
                  <!-- left icon for IN -->
                  <span style="width: 20px;">
                    <v-icon x-small class="mr-1" v-if="log.direction === 'IN'">
                      mdi-arrow-left-bold-outline
                    </v-icon>
                  </span>

                  <span>{{ log.direction === 'OUT' ? 'Out' : 'In' }}</span>

                  <!-- right icon for OUT -->
                  <span style="width: 20px;">
                    <v-icon x-small class="mr-1" v-if="log.direction === 'OUT'">
                      mdi-arrow-right-bold-outline
                    </v-icon>
                  </span>
                </div>
              </div>
            </div>
          </v-list-item-content>
        </v-list-item>

        <v-list-item v-if="!items.length && !loading">
          <v-list-item-content class="text-center grey--text text--lighten-1 text-caption">
            No vehicle movements yet.
          </v-list-item-content>
        </v-list-item>
      </v-list>
    </v-card>

    <!-- ========== VEHICLE INFORMATION ========== -->
    <v-card v-if="selectedLog && showInfo" class="panel-card" flat>
      <v-card-title class="panel-title d-flex align-center">
        <span>
          Vehicle Information
          <span v-if="selectedLog.membership_id">
            &nbsp;|&nbsp;
            {{ $utils.caps(selectedLog.parking_members?.member_type || selectedLog.member_type) }}
            -
            <span>
              Member
              {{ selectedLog.parking_members?.is_active ? "Active" : "In-Active" }}
            </span>
          </span>
          <span v-else class="guest-badge"> | GUEST</span>
        </span>
        <v-spacer />
        <span class="info-plate">
          <v-icon small class="mr-1" color="green">mdi-car</v-icon>
          {{ selectedLog.log_vehicle_number || '---' }}
        </span>
        <v-btn icon small class="ml-1" color="red" @click.stop="closeInfo">
          <v-icon small>mdi-close</v-icon>
        </v-btn>
      </v-card-title>

      <v-divider class="mx-3" />

      <v-card-text class="vehicle-info-body">
        <!-- Entry / Exit -->
        <div class="info-row">
          <div class="info-box">
            <div class="info-label">Entry</div>
            <div class="info-value">
              {{ entryTime }}
            </div>
          </div>
          <div class="info-box info-box--accent">
            <div class="info-label">Exit</div>
            <div class="info-value">
              {{ exitTime }}
            </div>
          </div>
        </div>

        <!-- Duration / Billing -->
        <div class="info-row mt-3">
          <div class="info-box">
            <div class="info-label">Duration</div>
            <div class="info-value">
              {{ durationValue }}
            </div>
          </div>

          <div class="info-box">
            <div class="info-label">Billing</div>
            <div class="info-value">
              <!-- FREE CASE -->
              <div v-if="selectedLog.total_amount === 0">
                <v-chip small color="green darken-2" text-color="white" label class="free-chip">
                  <v-icon left small>mdi-check-circle</v-icon>
                  FREE
                </v-chip>
                <div class="grey--text mt-1">
                  Membership / Guest FREE
                </div>
              </div>

              <!-- PAID CASE -->
              <div v-else-if="selectedLog.total_amount">
                <div class="billing-line">
                  <span class="billing-amount">
                    {{ selectedLog.total_amount }} AED
                    ({{ selectedLog.duration_per_hour_amount }} × {{ selectedLog.duration_in_hours }} h)
                  </span>
                </div>
              </div>

              <!-- NO DATA -->
              <div v-else>
                ---
              </div>
            </div>
          </div>
        </div>

        <!-- Fee row + payment buttons -->
        <div class="text-center mt-5">
          <v-row v-if="selectedLog.total_amount > 0" class="py-1111 align-center border-b"
            style="border-bottom: 1px solid #353538; font-size: 18px;">
            <v-col>
              <v-icon color="blue lighten-2">mdi-cash-100</v-icon>
              Charges
              <span v-if="selectedLog.total_amount === 0" class="free-text">
                FREE
              </span>
              <span v-else-if="selectedLog.total_amount">
                {{ selectedLog.total_amount }} AED
              </span>
              <span v-else>0 AED</span>
            </v-col>

            <v-col v-if="selectedLog.total_amount > 0 && !selectedLog.payment_mode" class="text-right font-weight-bold">
              <v-btn style="height: 25px;" @click="paymentProcess('cash', selectedLog.id)" color="green darken-2" dark>
                Cash
              </v-btn>

              <v-btn style="height: 25px;" @click="paymentProcess('card', selectedLog.id)" color="blue darken-2" dark>
                Card
              </v-btn>
            </v-col>
          </v-row>
        </div>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
export default {
  name: "VehicleRightPanel",

  props: {
    value: {
      type: Array,
      default: () => [],
    },
    mqttNewMessage: {
      type: Object,
      default: null,
    },
  },

  data() {
    return {
      items: [],
      selectedLog: null,
      selectedIndex: null, // index-based selection
      showInfo: false,
      page: 1,
      perPage: 20,
      cancelTokenSource: null,
      loading: false,
      error: null,
    };
  },

  created() {
    if (this.value && this.value.length) {
      this.items = this.value;
    } else {
      this.getDataFromApi();
    }
  },

  watch: {
    // When new MQTT message arrives:
    // - close info panel
    // - clear selection
    // - reload list
    mqttNewMessage: {
      handler() {
        this.showInfo = false;
        this.selectedLog = null;
        this.selectedIndex = null;
        this.getDataFromApi();
      },
      deep: false,
    },
  },

  computed: {
    record() {
      return this.selectedLog || null;
    },

    entryTime() {
      if (!this.record || !this.record.log_time_in) return "--:--";
      return this.record.log_time_in;
    },

    exitTime() {
      if (!this.record || !this.record.log_time_out) return "--:--";
      return this.record.log_time_out;
    },

    durationValue() {
      if (
        !this.record ||
        !this.record.log_time_out ||
        this.record.duration_in_minutes == null
      ) {
        return "--";
      }
      return `${this.record.duration_in_minutes} min`;
    },
  },

  methods: {
    paymentProcess(method, logId) {
      this.$emit("paymentProcess", method, logId);
    },

    selectLog(log, index) {
      if (this.loading) return;
      this.selectedLog = log;
      this.selectedIndex = index;
      this.showInfo = true;
      this.$emit("select", log);
    },

    closeInfo() {
      this.showInfo = false;
      // keep selectedIndex if you want row to stay highlighted,
      // or reset it here if you want full reset:
      // this.selectedIndex = null;
    },

    async getDataFromApi() {
      try {
        this.loading = true;
        this.error = null;

        const params = {
          page: this.page,
          perPage: this.perPage,
          pagination: true,
          company_id: this.$auth.user.company_id,
        };

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel("Operation canceled due to new request.");
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        const options = {
          params,
          cancelToken: this.cancelTokenSource.token,
        };

        const { data } = await this.$axios.get("parking_log_live", options);
        this.items = Array.isArray(data) ? data : data.data || [];
      } catch (err) {
        if (!this.$axios.isCancel(err)) {
          console.error("Error loading parking logs", err);
          this.error = "Failed to load vehicle logs.";
        }
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.vehicle-right-panel {
  display: flex;
  flex-direction: column;
  gap: 16px;
  height: 100%;
}

/* Cards */
.panel-card {
  background: #16181d;
  border-radius: 10px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.35);
  border: 1px solid rgba(255, 255, 255, 0.04);
}

/* Vehicle log card height behavior */
.log-panel {
  display: flex;
  flex-direction: column;
}

/* Full height (no vehicle info open) */
.vehicle-right-panel:not(.has-info) .log-panel {
  height: 720px;
}

/* Reduced height when info panel is open */
.vehicle-right-panel.has-info .log-panel {
  height: 420px;
}

.panel-title {
  font-size: 14px;
  font-weight: 600;
  color: #ffffff;
  padding-top: 10px;
  padding-bottom: 10px;
}

/* Log list */
.log-list {
  overflow-y: auto;
  flex: 1;
  padding-right: 4px;
}

.log-list::-webkit-scrollbar {
  width: 6px;
}

.log-item {
  cursor: pointer;
  padding: 8px 6px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

/* Row hover */
.log-item:hover {
  background: rgba(0, 170, 255, 0.08);
}

/* Row selected */
.log-item--selected {
  background: rgba(0, 200, 255, 0.15);
  border-left: 3px solid #00e5ff;
}

/* Layout inside row */
.log-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.log-left {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
}

.lane {
  color: #80858f;
  width: 18px;
  text-align: right;
}

/* Vehicle number pill */
.plate-btn {
  padding: 0 10px;
  border-radius: 999px;
  text-transform: none;
  font-weight: 600;
  font-size: 12px;
  min-width: auto;
  background: linear-gradient(135deg, #1294ff, #00d4ff);
  color: #ffffff !important;
}

.plate-btn ::v-deep .v-btn__content {
  letter-spacing: 0.3px;
}

.code {
  color: #9aa0af;
  font-size: 11px;
}

.time {
  color: #ffe8a3;
  font-size: 11px;
  display: inline-flex;
  align-items: center;
  gap: 3px;
}

.time-icon {
  color: #ffd36b;
}

/* Direction pill */
.direction-pill {
  padding: 3px 12px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  letter-spacing: 0.3px;
}

.direction-pill.in {
  background: rgba(0, 255, 110, 0.12);
  border: 1px solid rgba(0, 255, 110, 0.35);
  color: #18ff70;
}

.direction-pill.out {
  background: rgba(255, 70, 70, 0.12);
  border: 1px solid rgba(255, 70, 70, 0.35);
  color: #ff4c4c;
}

/* Vehicle information card */
.vehicle-info-body {
  padding-top: 12px;
  padding-bottom: 14px;
}

.info-plate {
  margin-left: 12px;
  font-size: 18px;
  font-weight: 800;
  color: #00eaff;
}

.guest-badge {
  color: #b0b0b0;
  font-size: 13px;
}

.info-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

.info-box {
  background: #101218;
  border-radius: 10px;
  padding: 10px;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.info-box--accent {
  border-color: rgba(244, 67, 54, 0.6);
}

.info-label {
  font-size: 11px;
  color: #8c93a3;
  margin-bottom: 4px;
}

.info-value {
  font-size: 13px;
  font-weight: 600;
  color: #ffffff;
}

/* FREE / billing styling */
.free-chip {
  font-weight: 600;
  letter-spacing: 0.5px;
  border-radius: 999px !important;
}

.billing-amount {
  font-size: 16px;
  font-weight: 700;
}

.free-text {
  color: #4caf50;
  font-weight: 700;
}

/* Payment buttons */
.payment-btn {
  border-radius: 999px;
  text-transform: none;
  font-size: 13px;
  font-weight: 600;
}
</style>
