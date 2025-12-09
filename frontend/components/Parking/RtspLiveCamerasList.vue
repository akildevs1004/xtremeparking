<template>
  <v-container fluid class="pa-0 nvr-root">
    <!-- TOP CONTROL BAR -->
    <div class="nvr-toolbar d-flex align-center px-3">
      <span class="nvr-toolbar-title mr-4">Live View</span>

      <v-btn small class="mr-1" :color="mode === 'preview' ? 'primary' : undefined" @click="setPreviewMode">
        Preview
      </v-btn>

      <v-btn v-if="cameraCount > 0" small class="mr-1"
        :color="mode === 'grid' && gridColsMode === 'auto' ? 'primary' : undefined" @click="setAllCameras">
        All (Auto 2 Rows)
      </v-btn>

      <v-btn v-if="cameraCount >= 2" small class="mr-1" :color="isGrid(2)" @click="setGridMode(2)">
        2/row
      </v-btn>

      <v-btn v-if="cameraCount >= 4" small class="mr-1" :color="isGrid(4)" @click="setGridMode(4)">
        4/row
      </v-btn>

      <v-btn v-if="cameraCount >= 6" small class="mr-1" :color="isGrid(6)" @click="setGridMode(6)">
        6/row
      </v-btn>

      <v-btn v-if="cameraCount >= 8" small class="mr-1" :color="isGrid(8)" @click="setGridMode(8)">
        8/row
      </v-btn>

      <v-spacer></v-spacer>

      <v-btn small text @click="setAllCameras">
        Restore
      </v-btn>
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

      <!-- GRID MODE -->
      <div v-else class="nvr-grid">
        <v-row dense no-gutters class="nvr-grid-row">
          <v-col v-for="(cam, index) in cameras" :key="cam.id" cols="12" :md="12 / gridColumns" :lg="12 / gridColumns"
            class="nvr-grid-col">
            <div class="nvr-grid-item" @dblclick.stop="focusCamera(index)">
              <RtspLiveCameraPlayer class="nvr-grid-player" :title="cam.name" :wsPort="BASE_WS_PORT + index"
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

      mode: "grid",          // 'grid' | 'preview' | 'focus'
      gridColsMode: "auto",  // 'auto' | 2 | 4 | 6 | 8
      selectedIndex: 0
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
      return this.BASE_WS_PORT + this.selectedIndex;
    },

    previewImage() {
      return (
        this.mqttNewMessage?.response?.record?.image_vehicle ||
        "/novehicle.png"
      );
    },

    // AUTO: arrange all cameras in max 2 rows
    gridColumns() {
      const n = this.cameraCount || 1;
      if (this.mode !== "grid") return 1;

      if (this.gridColsMode === "auto") {
        return Math.max(1, Math.ceil(n / 2)); // ≤ 2 rows
      }

      return Math.min(Number(this.gridColsMode), n);
    }
  },

  async mounted() {
    await this.loadCameras();
    this.setAllCameras();
  },

  methods: {
    async loadCameras() {
      const res = await this.$axios.get("/parking-cameras");
      this.cameras = res.data.data || [];
      this.NODE_SERVER_IP =
        this.cameras?.[0]?.node_server_ip ?? "192.168.2.16";

      if (this.selectedIndex >= this.cameraCount) {
        this.selectedIndex = 0;
      }
    },

    // modes
    setPreviewMode() {
      this.mode = "preview";
    },

    setAllCameras() {
      this.mode = "grid";
      this.gridColsMode = "auto";
    },

    setGridMode(cols) {
      this.mode = "grid";
      this.gridColsMode = cols;
    },

    isGrid(cols) {
      return this.mode === "grid" && this.gridColsMode === cols
        ? "primary"
        : undefined;
    },

    focusCamera(index) {
      this.selectedIndex = index;
      this.mode = "focus";
    }
  }
};
</script>

<style scoped>
.nvr-root {
  display: flex;
  flex-direction: column;
  height: 700px;
  /* full browser height */
  background: #050505;
  color: #eee;
  border-radius: 4px;
  overflow: scroll;
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

/* main area */
.nvr-content {
  flex: 1 1 auto;
  display: flex;
  padding: 4px;
}

/* all main modes fill height */
.nvr-focus,
.nvr-preview,
.nvr-grid {
  flex: 1 1 auto;
  display: flex;
}

/* focus */
.nvr-focus-player {
  flex: 1 1 auto;
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
}

.nvr-grid-col {
  display: flex;
}

.nvr-grid-item {
  flex: 1 1 auto;
  padding: 2px;
  display: flex;
}

.nvr-grid-player {
  flex: 1 1 auto;
}

/* empty state */
.nvr-empty {
  flex: 1 1 auto;
  color: #999;
}
</style>
