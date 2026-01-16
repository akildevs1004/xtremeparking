<template>
  <v-container fluid class="pa-0 nvr-root">
    <!-- TOP CONTROL BAR -->
    <div class="nvr-toolbar d-flex align-center px-3">
      <span class="nvr-toolbar-title mr-4">Live Camera(s)</span>


      <v-spacer></v-spacer>
      <!-- 2 split: rows=1, cams/page=2 -->
      <v-btn v-if="cameraCount >= 2" small class="mr-1" :color="isSplit(2)" @click="setSplit(2)">
        2 <v-icon>mdi-numeric-2-box</v-icon>
      </v-btn>

      <!-- 4 split: rows=2, cams/page=4 -->
      <v-btn v-if="cameraCount >= 4" small class="mr-1" :color="isSplit(4)" @click="setSplit(4)">
        4 <v-icon>mdi-view-grid</v-icon>
      </v-btn>



      <!-- Pagination -->
      <div v-if="mode === 'grid'" class="d-flex align-center">
        <v-btn icon small class="mr-1" @click="prevPage" :disabled="page === 1">
          <v-icon>mdi-chevron-left</v-icon>
        </v-btn>

        <div class="nvr-page-text">
          Page {{ page }} / {{ totalPages }}
        </div>

        <v-btn icon small class="ml-1" @click="nextPage" :disabled="page === totalPages">
          <v-icon>mdi-chevron-right</v-icon>
        </v-btn>

      </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="nvr-content">
      <!-- FOCUS (single camera) -->
      <div v-if="mode === 'focus'" class="nvr-focus">
        <RtspLiveCameraPlayer v-if="selectedCamera" :key="'focus-' + selectedWsPort" class="nvr-focus-player"
          :title="selectedCamera.name" :wsPort="selectedWsPort" :wsHost="NODE_SERVER_IP" />
        <div v-else class="nvr-empty d-flex align-center justify-center">
          No camera selected
        </div>
      </div>

      <!-- PREVIEW MODE -->
      <div v-else-if="mode === 'preview'" class="nvr-preview">
        <img :src="previewImage" class="nvr-preview-image" />
      </div>

      <div v-else-if="cameras.length === 0" class="nvr-empty d-flex align-center justify-center">
        No Camera Available
      </div>

      <!-- GRID MODE (PAGED CAMERAS) -->
      <div v-else class="nvr-grid">
        <v-row dense no-gutters class="nvr-grid-row">
          <v-col v-for="(cam, index) in pagedCameras" :key="cam.id" cols="12" :md="6" :lg="6" class="nvr-grid-col">
            <div class="nvr-grid-item" :style="{ height: cardHeightPx }" @dblclick.stop="focusCameraByCam(cam)">
              <RtspLiveCameraPlayer class="nvr-grid-player" :title="cam.name" :wsPort="BASE_WS_PORT + cam.id"
                :wsHost="NODE_SERVER_IP" />
            </div>
          </v-col>
        </v-row>
      </div>
    </div>
  </v-container>
</template>

<script>
import RtspLiveCameraPlayer from "@/components/Parking/RtspLiveCameraPlayer.vue";

export default {
  components: { RtspLiveCameraPlayer },

  props: {
    mqttNewMessage: { type: Object, default: () => ({}) }
  },

  data() {
    return {
      cameras: [],
      BASE_WS_PORT: 9991,
      NODE_SERVER_IP: null,

      mode: "grid", // 'grid' | 'preview' | 'focus'

      // Only 2 and 4 split supported:
      // 2 split => rows=1 => perPage=2
      // 4 split => rows=2 => perPage=4
      split: 2,

      // pagination
      page: 1,

      // focus
      selectedIndex: 0,

      // measured heights
      availableHeight: 0,

      // observers
      ro: null
    };
  },

  computed: {
    cameraCount() {
      return this.cameras.length;
    },

    selectedCamera() {
      return this.cameras[this.selectedIndex] || null;
    },

    selectedWsPort() {
      const cam = this.cameras[this.selectedIndex];
      return cam ? this.BASE_WS_PORT + cam.id : this.BASE_WS_PORT;
    },

    previewImage() {
      return this.mqttNewMessage?.response?.record?.image_vehicle || "/novehicle.png";
    },

    rowsCount() {
      // 2 split => 1 row, 4 split => 2 rows
      return this.split === 4 ? 2 : 1;
    },

    perPage() {
      // each row has 2 cams
      return this.rowsCount * 2;
    },

    totalPages() {
      const n = this.cameraCount;
      return Math.max(1, Math.ceil(n / this.perPage));
    },

    pagedCameras() {
      const start = (this.page - 1) * this.perPage;
      return this.cameras.slice(start, start + this.perPage);
    },

    cardHeightPx() {
      // divide available height by row count, remove tiny gap
      const h = this.availableHeight || 0;
      const rows = this.rowsCount || 1;

      // small compensation for padding/gutters inside the grid area
      const compensation = 4; // keep minimal; avoids overflow
      const perRow = Math.floor(h / rows) - compensation;

      return `${Math.max(120, perRow)}px`; // hard minimum so player never collapses
    }
  },

  watch: {
    split() {
      this.page = 1;
      this.syncPageBounds();
      this.measureAvailableHeight();
    },

    cameraCount() {
      this.syncPageBounds();
      this.measureAvailableHeight();
    },

    page() {
      this.syncPageBounds();
    }
  },

  async mounted() {
    await this.loadCameras();
    this.setSplit(this.cameraCount >= 4 ? 4 : 2);

    this.$nextTick(() => {
      this.initHeightObserver();
      this.measureAvailableHeight();
      window.addEventListener("resize", this.measureAvailableHeight, { passive: true });
    });
  },

  beforeDestroy() {
    window.removeEventListener("resize", this.measureAvailableHeight);
    if (this.ro) this.ro.disconnect();
  },

  methods: {
    async loadCameras() {
      const res = await this.$axios.get("/parking-cameras", {
        params: { company_id: this.$auth.user.company_id }
      });

      this.cameras = res.data || [];
      this.NODE_SERVER_IP = this.cameras?.[0]?.node_server_ip ?? "192.168.2.16";

      if (this.selectedIndex >= this.cameraCount) {
        this.selectedIndex = 0;
      }
    },

    // Only split layouts
    setSplit(n) {
      this.mode = "grid";
      this.split = n === 4 ? 4 : 2;
    },

    isSplit(n) {
      return this.mode === "grid" && this.split === n ? "primary" : undefined;
    },

    prevPage() {
      this.page = Math.max(1, this.page - 1);
    },

    nextPage() {
      this.page = Math.min(this.totalPages, this.page + 1);
    },

    syncPageBounds() {
      if (this.page > this.totalPages) this.page = this.totalPages;
      if (this.page < 1) this.page = 1;
    },

    // focus
    focusCameraByCam(cam) {
      const idx = this.cameras.findIndex((c) => c.id === cam.id);
      this.selectedIndex = idx >= 0 ? idx : 0;
      this.mode = "focus";
    },

    // height logic from #cameradashboardinfo
    initHeightObserver() {
      const el = document.getElementById("cameradashboardinfo");
      if (!el || typeof ResizeObserver === "undefined") return;

      this.ro = new ResizeObserver(() => {
        this.measureAvailableHeight();
      });

      this.ro.observe(el);
    },

    measureAvailableHeight() {
      // Use the height of #cameradashboardinfo as requested
      const host = document.getElementById("cameradashboardinfo");

      // fallback to root container if not found
      const root = this.$el;

      const base = host || root;
      if (!base) return;

      const rect = base.getBoundingClientRect();

      // subtract toolbar height (48) and internal paddings (nvr-content padding etc.)
      const toolbarH = 48;
      const contentPadding = 8; // content has padding 4px on each side
      const h = Math.floor(rect.height - toolbarH - contentPadding);

      this.availableHeight = Math.max(0, h);
    }
  }
};
</script>

<style scoped>
.nvr-root {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #050505;
  color: #eee;
  border-radius: 4px;

  /* do not create empty space; allow width scroll only if needed */
  overflow-x: auto;
  overflow-y: hidden;

  max-height: 720px;
}

/* top bar */
.nvr-toolbar {
  flex: 0 0 48px;
  height: 48px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.nvr-toolbar-title {
  font-size: 14px;
  font-weight: 600;
}

.nvr-page-text {
  font-size: 12px;
  opacity: 0.9;
  min-width: 64px;
  text-align: center;
}

/* main content */
.nvr-content {
  flex: 1 1 auto;
  display: flex;
  padding: 4px;
  overflow: hidden;
  /* critical: no scroll gaps */
}

/* each mode fills area */
.nvr-focus,
.nvr-preview,
.nvr-grid {
  flex: 1 1 auto;
  display: flex;
  overflow: hidden;
}

/* focus */
.nvr-focus-player {
  flex: 1 1 auto;
  min-height: 0;
}

/* preview */
.nvr-preview-image {
  width: 100%;
  height: 100%;
  border-radius: 4px;
  object-fit: cover;
}

/* grid */
.nvr-grid-row {
  flex: 1 1 auto;
  height: 100%;
  margin: 0 !important;
}

.nvr-grid-col {
  display: flex;
  padding: 0 !important;
}

.nvr-grid-item {
  flex: 1 1 auto;
  padding: 2px;
  display: flex;
  min-height: 0;
  /* allow children to fit */
}

.nvr-grid-player {
  flex: 1 1 auto;
  min-height: 0;
}

/* empty */
.nvr-empty {
  flex: 1 1 auto;
  color: #999;
}
</style>
