<template>
  <v-container fluid class="dashboard-bg">

    <!-- GRID with max 3 cards per row -->
    <div class="auto-grid">
      <v-card v-for="car in cars" :key="car.id" class="car-card" :class="car.status" elevation="4">

        <!-- HEADER (single row) -->
        <div class="info-section px-4 py-3">
          <div class="single-row">

            <!-- Room + Reg -->
            <div class="left-info">
              <div class="car-title">{{ car.room }}</div>
              <div class="car-reg">{{ car.reg }}</div>
            </div>

            <!-- Meta inline -->
            <div class="meta-inline">
              <span><label>In:</label> {{ car.inTime }}</span>
              <span><label>Dur:</label> {{ car.duration }}</span>
              <span><label>Out:</label> {{ car.outTime || "--" }}</span>
            </div>

            <!-- Status -->
            <v-chip small :color="car.status === 'completed' ? 'green'
              : car.status === 'inprogress' ? 'blue'
                : 'grey'" text-color="white">
              {{ car.statusText }}
            </v-chip>

          </div>
        </div>

        <!-- IMAGE: cover entire width & height of this area -->
        <div class="img-wrapper">
          <img :src="car.status === 'empty' ? '/empty_room.png' : '/car_blue.png'" class="cover-img"
            alt="washroom/vehicle" />
        </div>

      </v-card>
    </div>

  </v-container>
</template>

<script>
export default {
  name: "CarWashDashboard",

  data() {
    return {
      cars: [
        {
          id: 1,
          room: "Car Washroom 1",
          reg: "-",
          status: "empty",
          statusText: "Available",
          inTime: "--",
          duration: "0h 0m",
          outTime: "--"
        },
        {
          id: 2,
          room: "Car Washroom 2",
          reg: "TN-04-GH-3456",
          status: "inprogress",
          statusText: "In-progress",
          inTime: "01:15 PM",
          duration: "0h 25m",
          outTime: "--"
        },
        {
          id: 3,
          room: "Car Washroom 3",
          reg: "TN-04-GH-3456",
          status: "completed",
          statusText: "Completed",
          inTime: "12:01 PM",
          duration: "0h 55m",
          outTime: "12:56 PM"
        },
        {
          id: 4,
          room: "Car Washroom 4",
          reg: "-",
          status: "empty",
          statusText: "Available",
          inTime: "--",
          duration: "0h 0m",
          outTime: "--"
        },
        {
          id: 5,
          room: "Car Washroom 5",
          reg: "TN-04-GH-3456",
          status: "inprogress",
          statusText: "In-progress",
          inTime: "12:01 PM",
          duration: "0h 55m",
          outTime: "--"
        },
        {
          id: 6,
          room: "Car Washroom 6",
          reg: "-",
          status: "empty",
          statusText: "Available",
          inTime: "--",
          duration: "0h 0m",
          outTime: "--"
        }
      ]
    };
  }
};
</script>

<style scoped>
/* PAGE BACKGROUND */
.dashboard-bg {
  background: #1E1E1E;
  min-height: 100vh;
  padding: 20px;
}

/* GRID: max 3 cards per row */
.auto-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 22px;
  width: 100%;
}

/* Mobile: 1 per row */
@media (max-width: 900px) {
  .auto-grid {
    grid-template-columns: repeat(1, 1fr);
  }
}

/* Tablet: 2 per row */
@media (min-width: 900px) and (max-width: 1300px) {
  .auto-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Desktop: 3 per row */
@media (min-width: 1300px) {
  .auto-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* CARD */
.car-card {
  background: #262626;
  border-radius: 16px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* STATUS COLORS */
.car-card.inprogress {
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.45);
}

.car-card.completed {
  box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.45);
}

.car-card.empty {
  box-shadow: 0 0 0 2px rgba(150, 150, 150, 0.45);
}

/* HEADER SECTION */
.info-section {
  background: #2A2A2A;
  border-bottom: 1px solid #333;
}

/* HEADER ROW */
.single-row {
  display: grid;
  grid-template-columns: 1fr auto auto;
  gap: 10px;
  align-items: center;
}

/* LEFT INFO */
.left-info {
  display: flex;
  flex-direction: column;
}

.car-title {
  font-size: 15px;
  font-weight: 600;
}

.car-reg {
  font-size: 12px;
  color: #cfcfcf;
}

/* META INFO */
.meta-inline {
  display: flex;
  gap: 12px;
  white-space: nowrap;
  font-size: 13px;
}

.meta-inline label {
  font-size: 11px;
  color: #aaa;
  margin-right: 2px;
}

/* IMAGE AREA – you control height here */
.img-wrapper {
  width: 100%;
  height: 40vh;
  /* 🔥 40% of viewport height; use 50vh for half-screen */
  overflow: hidden;
  background: #000;
}

/* IMAGE COVERS ENTIRE AREA */
.cover-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  /* key: fills width & height */
  object-position: center;
  display: block;
}
</style>
