<template>
  <div class="nvr-camera-tile">
    <!-- Header: status + title + controls -->
    <div class="nvr-camera-header d-flex align-center">
      <div class="d-flex align-center">
        <!-- <span class="status-dot" :class="connected ? 'status-dot--online' : 'status-dot--offline'"></span> -->

        <span><v-icon :color="connected ? 'success' : 'error'" style="transform: scaleX(-1);">mdi
            mdi-cctv</v-icon></span>
        <span class="ml-2 nvr-title">
          {{ title }}
        </span>
      </div>

      <div class="ml-auto d-flex align-center">
        <v-btn icon small :disabled="!player || isPlaying || connecting" @click="play">
          <v-icon small>mdi-play</v-icon>
        </v-btn>

        <v-btn icon small class="ml-1" :disabled="!player || !isPlaying" @click="pause">
          <v-icon small>mdi-pause</v-icon>
        </v-btn>

        <v-btn icon small class="ml-1" :disabled="connecting" @click="reconnect">
          <v-icon small>mdi-reload</v-icon>
        </v-btn>

        <v-btn icon small class="ml-1" ::disabled="!player || !isPlaying" @click="fullscreen">
          <v-icon small>mdi-fullscreen</v-icon>
        </v-btn>
      </div>
    </div>

    <!-- Canvas area -->
    <div class="nvr-camera-body d-flex align-center justify-center">
      <canvas ref="canvas" :width="width" :height="height" style="" class="nvr-canvas"></canvas>
    </div>
  </div>
</template>

<script>
export default {
  name: "RtspLiveCameraPlayer",

  props: {
    title: { type: String, default: "Camera" },
    wsPort: { type: Number, required: true },
    wsHost: { type: String, required: true },
    width: { type: Number, default: 1280 },
    height: { type: Number, default: 720 }
  },

  data() {
    return {
      player: null,
      connecting: false,
      isPlaying: false,
      connected: false,
      statusText: "Idle"
    };
  },

  computed: {
    wsUrl() {
      if (!process.client) return "";
      const protocol = window.location.protocol === "https:" ? "wss" : "ws";
      return `${protocol}://${this.wsHost}:${this.wsPort}`;
    }
  },

  mounted() {
    if (!process.client) return;

    if (window.JSMpeg) {
      this.connect();
    } else {
      this.setStatus("Loading JSMpeg...");
      const script = document.createElement("script");
      script.src = "/jsmpeg.min.js"; // from /static/jsmpeg.min.js
      script.async = true;
      script.onload = () => {
        this.setStatus("JSMpeg loaded. Connecting...");
        this.connect();
      };
      script.onerror = () => {
        this.setStatus("Failed to load JSMpeg");
        console.error("Unable to load /jsmpeg.min.js");
      };
      document.head.appendChild(script);
    }

    window.addEventListener("beforeunload", this.destroyPlayer);
  },

  beforeDestroy() {
    if (process.client) {
      window.removeEventListener("beforeunload", this.destroyPlayer);
      this.destroyPlayer();
    }
  },

  methods: {
    setStatus(text) {
      this.statusText = text;
    },

    destroyPlayer() {
      if (this.player) {
        try {
          this.player.destroy();
        } catch (e) {
          console.warn("Error destroying player:", e);
        }
      }
      this.player = null;
      this.isPlaying = false;
      this.connected = false;
    },

    connect() {
      if (!process.client) return;
      if (this.connecting) return;

      if (!window.JSMpeg) {
        this.setStatus("JSMpeg not available");
        return;
      }

      if (!this.wsUrl) {
        this.setStatus("Invalid WebSocket URL");
        return;
      }

      this.connecting = true;
      this.connected = false;
      this.setStatus("Connecting...");
      this.destroyPlayer();

      const canvas = this.$refs.canvas;

      try {
        this.player = new window.JSMpeg.Player(this.wsUrl, {
          canvas,
          audio: false,
          pauseWhenHidden: false,
          onSourceEstablished: () => {
            this.connecting = false;
            this.connected = true;
            this.isPlaying = true;
            this.setStatus("Connected");
            this.$emit("connected");
          },
          onSourceClosed: () => {
            this.connecting = false;
            this.connected = false;
            this.isPlaying = false;
            this.setStatus("Disconnected");
            this.$emit("disconnected");
          }
        });
      } catch (e) {
        console.error("JSMpeg init error:", e);
        this.setStatus("Player Error");
        this.connecting = false;
        this.connected = false;
      }
    },

    play() {
      if (!this.player) return;
      this.player.play();
      this.isPlaying = true;
      this.setStatus("Playing");
    },

    pause() {
      if (!this.player) return;
      this.player.pause();
      this.isPlaying = false;
      this.setStatus("Paused");
    },

    reconnect() {
      this.setStatus("Reconnecting...");
      this.connect();
    },

    fullscreen() {
      const c = this.$refs.canvas;
      if (!c) return;
      if (c.requestFullscreen) c.requestFullscreen();
      else if (c.webkitRequestFullscreen) c.webkitRequestFullscreen();
      else if (c.msRequestFullscreen) c.msRequestFullscreen();
    }
  }
};
</script>

<style scoped>
.nvr-camera-tile {
  display: flex;
  flex-direction: column;
  border-radius: 4px;
  border: 1px solid rgba(255, 255, 255, 0.06);
  background: rgba(0, 0, 0, 0.8);
  overflow: hidden;
}

.nvr-camera-header {
  padding: 4px 8px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  font-size: 12px;
}

.nvr-title {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.nvr-camera-body {
  flex: 1;
  background: #000;
}

.nvr-canvas {
  width: 100%;
  height: 100%;
  display: block;
}

/* status LED */
.status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.status-dot--online {
  background-color: #4caf50;
  /* green */
}

.status-dot--offline {
  background-color: #f44336;
  /* red */
}
</style>
