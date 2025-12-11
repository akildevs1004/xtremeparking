<template>
  <div class="vehicle-right-panel" :class="{ 'has-info': showInfo && selectedLog }">
    <!-- ========== VEHICLE IN/OUT LOG ========== -->
    <v-card class="panel-card mb-4 log-panel" flat>
      <v-card-title class="panel-title d-flex align-center">
        <span>Vehicle In/Out Log</span>

        <v-spacer />

        <!-- Small spinner while loading -->
        <v-progress-circular v-if="loading" indeterminate size="18" width="2" color="cyan" class="mr-2" />

        <v-icon small @click.stop="getDataFromApi()">mdi-reload</v-icon>
      </v-card-title>

      <v-divider class="mx-3" />

      <!-- VEHICLE LIST -->
      <v-list dense class="log-list" :class="{ disabled: loading }">
        <v-list-item v-for="(log, index) in items" :key="log.id || index" @click="!loading && selectLog(log)" :class="[
          'log-item',
          { 'log-item--selected': selectedLog && selectedLog.id === log.id }
        ]">
          <v-list-item-content>
            <div class="log-row">
              <div class="log-left">
                <span class="lane">{{ index + 1 }}</span>

                <!-- Vehicle number as text button -->
                <v-btn text small class="plate-btn" color="cyan lighten-2" @click.stop="!loading && selectLog(log)">
                  <v-icon left x-small>mdi-car</v-icon>
                  {{ log.log_vehicle_number }}
                </v-btn>

                <span class="code">
                  {{ log.raw_country_region }}
                </span>

                <span class="time">
                  <v-icon x-small class="time-icon">mdi-clock-outline</v-icon>
                  {{ log.log_time }}
                </span>
              </div>

              <div class="log-right">
                <div :class="[
                  'direction-pill',
                  log.direction === 'OUT' ? 'out' : 'in'
                ]">
                  <v-icon x-small class="mr-1">
                    {{ log.direction === "OUT" ? 'mdi-arrow-right-circle' : 'mdi-arrow-left-circle' }}
                  </v-icon>
                  <span>{{ log.direction === "OUT" ? 'Out' : 'In' }}</span>
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
        <span>Vehicle Information</span>
        <v-spacer />
        <span class="info-plate">
          {{ selectedLog.log_vehicle_number || '---' }}
        </span>
        <v-btn icon x-small class="ml-1" @click.stop="closeInfo">
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
                  </span>
                </div>
                <div class="grey--text mt-1">
                  {{ selectedLog.duration_per_hour_amount }} ×
                  {{ selectedLog.duration_in_hours }} h
                </div>
              </div>

              <!-- NO DATA -->
              <div v-else>
                ---
              </div>
            </div>
          </div>
        </div>

        <!-- Guest / Member etc. -->
        <div class="text-center mt-5">
          <div v-if="selectedLog.membership_id">
            {{ $utils.caps(selectedLog.parking_members?.member_type || selectedLog.member_type) }}
            -
            <span>
              Member
              {{ selectedLog.parking_members?.is_active ? "Active" : "In-Active" }}
            </span>
          </div>
          <div v-else>GUEST</div>

          <!-- Fee row + payment buttons -->
          <div>
            <v-row class="py-1111 align-center border-b" style="border-bottom: 1px solid #353538; font-size: 18px;">
              <v-col class="shrink">
                <v-icon color="blue lighten-2">mdi-cash-100</v-icon>
              </v-col>
              <v-col>Fee/Charges</v-col>

              <!-- Amount / FREE label in row -->
              <v-col class="text-right font-weight-bold">
                <span v-if="selectedLog.total_amount === 0" class="free-text">
                  FREE
                </span>
                <span v-else-if="selectedLog.total_amount">
                  {{ selectedLog.total_amount }} AED
                </span>
                <span v-else>0 AED</span>
              </v-col>

              <!-- PAYMENT BUTTONS ONLY WHEN PAID & NOT YET PAID -->
              <v-col v-if="selectedLog.total_amount > 0 && !selectedLog.payment_mode"
                class="text-right font-weight-bold">
                <v-btn @click="paymentProcess('cash', selectedLog.id)" width="110px" height="34px" elevation="3"
                  color="green darken-2" dark class="mr-2 payment-btn">
                  <v-icon left small>mdi-cash-100</v-icon>
                  Cash
                </v-btn>

                <v-btn @click="paymentProcess('card', selectedLog.id)" width="140px" height="34px" elevation="3"
                  color="blue darken-2" dark class="payment-btn">
                  <v-icon left small>mdi-credit-card-outline</v-icon>
                  Card/Online
                </v-btn>
              </v-col>
            </v-row>
          </div>
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
      showInfo: false, // info hidden by default
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
    // - reload list (spinner only in header)
    mqttNewMessage: {
      handler() {
        this.showInfo = false;
        this.selectedLog = null;
        this.loading = true;
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

    selectLog(log) {
      if (this.loading) return;
      this.selectedLog = log;
      this.showInfo = true; // open info, height reduces via class
      this.$emit("select", log);
    },

    closeInfo() {
      this.showInfo = false; // hide info, list returns to full height
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
  background: #181b20;
  border-radius: 10px;
  box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.03);
}

/* Vehicle log card height behavior */
.log-panel {
  display: flex;
  flex-direction: column;
  transition: height 0.2s ease;
}

/* Full height (no vehicle info open) */
.vehicle-right-panel:not(.has-info) .log-panel {
  height: 700px;
  /* full height when info closed */
}

/* Reduced height when info panel is open */
.vehicle-right-panel.has-info .log-panel {
  height: 420px;
  /* adjust as you like */
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
}

/* Disable clicks on list while loading */
.log-list.disabled {
  pointer-events: none;
  opacity: 0.6;
}

.log-item {
  cursor: pointer;
  padding-top: 6px;
  padding-bottom: 6px;
}

.log-item--selected {
  background: rgba(255, 255, 255, 0.04);
}

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
  color: #777b86;
  width: 18px;
  text-align: right;
}

/* Vehicle number button */
.plate-btn {
  padding: 0 10px;
  border-radius: 999px;
  text-transform: none;
  font-weight: 600;
  font-size: 12px;
  min-width: auto;
}

/* Make default text color slightly softer (Vuetify will handle actual color) */
.plate-btn ::v-deep .v-btn__content {
  letter-spacing: 0.3px;
}

.code {
  color: #9aa0af;
  font-size: 11px;
}

.time {
  color: #ffcc80;
  font-size: 11px;
  display: inline-flex;
  align-items: center;
  gap: 3px;
}

.time-icon {
  opacity: 0.8;
}

.log-right {
  display: flex;
  align-items: center;
}

/* Direction pill */
.direction-pill {
  padding: 2px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.direction-pill.in {
  background: rgba(76, 175, 80, 0.15);
  color: #4caf50;
}

.direction-pill.out {
  background: rgba(244, 67, 54, 0.15);
  color: #f44336;
}

/* Vehicle information card */
.vehicle-info-body {
  padding-top: 12px;
  padding-bottom: 14px;
}

.info-plate {
  margin-left: 12px;
  font-size: 16px;
  font-weight: 700;
  color: #ffffff;
}

.info-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

.info-box {
  background: #101218;
  border-radius: 8px;
  padding: 8px 10px;
  border: 1px solid rgba(255, 255, 255, 0.04);
}

.info-box--accent {
  border-color: rgba(244, 67, 54, 0.5);
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
}

.billing-line {
  display: flex;
  justify-content: flex-start;
  align-items: baseline;
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
