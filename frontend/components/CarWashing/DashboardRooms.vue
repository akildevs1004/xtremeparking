<template>
  <div>

    <div class="auto-grid">
      <v-card v-for="(car, index) in devicesList" :key="car.id" class="car-card" :class="car.status" elevation="4">
        <!-- HEADER -->
        <div class="info-section px-4 py-3">

          <!-- TOP ROW: Room | Lane | vehicle_number | Lane | Status -->
          <div class="header-top">

            <!-- LEFT: Room -->
            <div class="header-left">
              <v-chip class="ma-1" color="red">
                {{ index + 1 }}
              </v-chip>
              <v-chip small class="ma-2" color="green" outlined label>

                {{ car.room }}
              </v-chip>
            </div>

            <!-- CENTER GROUP: | Vehicle | -->
            <div class="header-center">
              <div class="lane-separator"></div>

              <div class="car-number car-number-highlight">
                <v-icon left small class="status-icon">mdi-car</v-icon> {{ car.vehicle_number }}
              </div>

              <div class="lane-separator"></div>
            </div>

            <!-- RIGHT: Status chip -->
            <div class="header-right">
              <v-chip small :color="car.status === 'completed'
                ? 'success'
                : car.status === 'inprogress'
                  ? 'info'
                  : 'grey darken-1'" text-color="white" class="status-chip">
                <v-icon left small class="status-icon">
                  {{ getStatusIcon(car.status) }}
                </v-icon>
                {{ car.statusText }}
              </v-chip>
            </div>

          </div>

          <!-- WHITE SEPARATOR LINE -->
          <div class="header-white-line"></div>

          <!-- BOTTOM ROW -->
          <div class="header-bottom" style="font-size: 11px;;">

            <!-- LEFT: In -->
            <div class="meta-left">
              <v-icon small class="meta-icon">mdi-clock-check-outline</v-icon>
              <label>In:</label>
              <span class="in-time">{{ car.inTime }}</span>
            </div>

            <!-- CENTER: Duration -->
            <div class="meta-center" style="color:yellow">
              <v-icon small class="meta-icon">mdi-timer-sand</v-icon>
              <label> :</label>
              <div v-if="car.status === 'empty'">---</div>
              <div v-else>{{
                $dateFormat.getTimeDifferenceStartEndOnlyMinutes(car.inTime,
                  car.outTime || new
                    Date().toString()) }}</div>
            </div>

            <!-- RIGHT: Out -->
            <div class="meta-right">
              <v-icon small class="meta-icon">mdi-clock-out</v-icon>
              <label>Out:</label>
              <span class="out-time">{{ car.outTime || "--" }}</span>
            </div>

          </div>

        </div>

        <!-- IMAGE -->
        <div class="img-wrapper">
          <!-- <img :src="car.status === 'empty' ? '/empty_room.png' : '/car_blue.png'" class="cover-img"
            alt="washroom / vehicle" /> -->

          <img :src="getCarVehicleImage(car)" class="cover-img" alt="washroom / vehicle" />


        </div>

      </v-card>
    </div>
    <br /><br />
  </div>
</template>

<script>

export default {
  name: "CarWashDashboard",
  props: {
    updateKey: {
      type: Number,
      required: false,
      default: 0
    },
  },
  data() {
    return {
      devicesList: []
    };
  },
  mounted() {
    this.getDevicesList();

  },
  watch: {
    updateKey(newVal, oldVal) {
      this.getDevicesList();
    }
  },
  methods: {
    async getDevicesList() {

      const options = {
        params: {
          company_id: this.$auth.user.company_id,

        },

      };
      const { data } = await this.$axios.get(`/dashboard_carwashingrooms`, options);



      this.devicesList = [];
      if (data) {

        for (let device of data) {



          let dataInfo = {
            id: device.id,
            room: device.name,
            vehicle_number: device.vehicle?.log_vehicle_number || "-",
            status: device.vehicle?.out_time
              ? "completed"
              : device.vehicle?.in_time
                ? "inprogress"
                : "empty",
            statusText: device.vehicle?.out_time
              ? "completed"
              : device.vehicle?.in_time
                ? "inprogress"
                : "empty",
            inTime: device.vehicle?.in_time || null,
            duration: "0h 0m",
            outTime: device.vehicle?.out_time || null,
            public_image_url: device.vehicle?.public_image_url || "",
            in_background_file_name: device.vehicle?.in_background_file_name || ""
          };

          this.devicesList.push(dataInfo);
        }
      } else {

      }






    },
    getStatusIcon(status) {
      switch (status) {
        case "completed":
          return "mdi-check-circle-outline";
        case "inprogress":
          return "mdi-progress-clock";
        default:
          return "mdi-car-off";
      }
    },
    getCarVehicleImage(vehicle) {
      if (vehicle.status === 'empty') {
        return '/empty_room.png';
      } else {

        return vehicle.public_image_url + "/" + vehicle.in_background_file_name.replace("_BACKGROUND", "_VEHICLE") + "?timestamp=" + new Date().getTime();
        return '/car_blue.png';
      }
    }
  }
};
</script>

<style scoped>
/* page */
.dashboard-bg {
  background: var(--bg-page);
  height: (100vh);
  padding: 20px;

  color: var(--text-main);
}

/* grid layout */
.auto-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 22px;
}

@media (max-width: 900px) {
  .auto-grid {
    grid-template-columns: 1fr;
  }
}

@media (min-width: 900px) and (max-width: 1300px) {
  .auto-grid {
    grid-template-columns: 1fr 1fr;
  }
}

/* card */
.car-card {
  background: var(--surface-1);
  border-radius: 16px;
  overflow: hidden;
  color: var(--text-main);
}

/* card border colors */
.car-card.inprogress {
  box-shadow: 0 0 0 2px var(--status-inprogress);
}

.car-card.completed {
  box-shadow: 0 0 0 2px var(--status-completed);
}

.car-card.empty {
  box-shadow: 0 0 0 2px var(--status-empty);
}

/* header bg */
.info-section {
  background: #19191c;
}

/* top row */
.header-top {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* vertical separator */
.lane-separator {
  width: 1px;
  height: 18px;
  background: var(--divider-soft);
  opacity: 0.6;
}

/* highlight vehicle number */
.car-number-highlight {
  padding: 3px 10px;
  border-radius: 6px;
  font-weight: 600;
  background: var(--chip-badge-bg);
  color: var(--chip-badge-text);
  border: 1px solid var(--divider-soft);
}

/* white horizontal separator line */
.header-white-line {
  height: 1px;
  width: 100%;
  background: rgba(255, 255, 255, 0.45);
  margin: 8px 0;
  border-radius: 2px;
}

/* light theme override */
:root[data-theme="light"] .header-white-line {
  background: rgba(0, 0, 0, 0.20);
}

/* bottom row */
.header-bottom {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  align-items: center;
}

/* row items */
.meta-left,
.meta-center,
.meta-right {
  display: flex;
  align-items: center;
  gap: 4px;
}

.meta-left {
  justify-content: flex-start;
}

.meta-center {
  justify-content: center;
}

.meta-right {
  justify-content: flex-end;
}

.meta-icon {
  font-size: 16px !important;
}

.header-bottom label {
  font-size: 11px;
  color: var(--text-soft);
}

/* theme colors for values */
.in-time {
  color: var(--accent-in);
  /* font-weight: 600; */
}

.out-time {
  color: var(--accent-out);
  /* font-weight: 600; */
}

/* image section */
.img-wrapper {
  width: 100%;
  height: 400px;
  /*calc((100vh - 400px) / 2);*/

  overflow: hidden;
  background: var(--surface-2);
}

.cover-img {
  width: 100%;
  height: 100%;
  /* object-fit: cover; */
  object-fit: fill;

}

.header-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 32px;
  gap: 10px;
}

/* LEFT */
.header-left {
  font-size: 15px;
  font-weight: 600;
  color: var(--text-main);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
  /* pushes center + right properly */
}

/* CENTER */
.header-center {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* VEHICLE NUMBER */
.car-number-highlight {
  padding: 3px 10px;
  border-radius: 6px;
  font-weight: 600;
  background: var(--chip-badge-bg);
  color: var(--chip-badge-text);
  border: 1px solid var(--divider-soft);
  white-space: nowrap;
}

/* RIGHT */
.header-right {
  white-space: nowrap;
  display: flex;
  align-items: center;
}

/* Separators */
.lane-separator {
  width: 1px;
  height: 18px;
  background: var(--divider-soft);
  opacity: 0.7;
  border-radius: 1px;
}

/* Status chip icon spacing */
.status-icon {
  margin-right: 4px;
}
</style>
