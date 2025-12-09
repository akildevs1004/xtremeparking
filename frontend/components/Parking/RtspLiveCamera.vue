<template>
  <v-card class="fill-height d-flex flex-column pa-0" outlined>
    <!-- Header -->
    <v-toolbar dense flat class="elevation-2">
      <v-toolbar-title class="text-subtitle-1 font-weight-medium">
        {{ title }}
      </v-toolbar-title>

      <v-spacer></v-spacer>

      <v-btn small color="primary" :disabled="!player || isPlaying || connecting" @click="play">
        Play
      </v-btn>

      <v-btn small class="ml-1" color="grey darken-2" :disabled="!player || !isPlaying" @click="pause">
        Pause
      </v-btn>

      <v-btn small class="ml-1" color="grey darken-2" :disabled="connecting" @click="reconnect">
        Reconnect
      </v-btn>

      <v-btn small class="ml-1" color="grey darken-2" @click="fullscreen">
        Fullscreen
      </v-btn>

      <span class="ml-3 caption grey--text text--lighten-1">
        {{ statusText }}
      </span>
    </v-toolbar>

    <!-- Canvas -->
    <div class="flex-grow-1 d-flex align-center justify-center rtsp-container">
      <canvas ref="canvas" :width="width" :height="height" class="rtsp-canvas"></canvas>
    </div>
  </v-card>
</template>

<script>
export default {
  name: "RtspLivePlayer",

  props: {
    title: { type: String, default: "RTSP Live View" },
    wsPort: { type: Number, default: 8082 },
    width: { type: Number, default: 1280 },
    height: { type: Number, default: 720 }
  },

  data() {
    return {
      player: null,
      connecting: false,
      isPlaying: false,
      statusText: "Idle"
    };
  },

  computed: {
    wsUrl() {
      if (!process.client) return "";
      const protocol = window.location.protocol === "https:" ? "wss" : "ws";
      const host = window.location.hostname;
      return `${protocol}://${host}:${this.wsPort}`;
    }
  },

  mounted() {
    if (!process.client) return;

    // Load JSMpeg from /static/jsmpeg.min.js if not already loaded
    if (window.JSMpeg) {
      this.connect();
    } else {
      this.setStatus("Loading JSMpeg...");
      const script = document.createElement("script");
      script.src = "/jsmpeg.min.js"; // file path: /static/jsmpeg.min.js
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
      // console.log("[RtspLivePlayer]", text);
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
    },

    connect() {
      if (!process.client) return;
      if (this.connecting) return;

      if (!window.JSMpeg) {
        this.setStatus("JSMpeg not available");
        return;
      }

      this.connecting = true;
      this.setStatus("Connecting to stream...");
      this.destroyPlayer();

      const canvas = this.$refs.canvas;

      try {
        this.player = new window.JSMpeg.Player(this.wsUrl, {
          canvas,
          audio: false,
          pauseWhenHidden: false,
          onSourceEstablished: () => {
            this.connecting = false;
            this.isPlaying = true;
            this.setStatus("Stream Connected ✅");
          },
          onSourceClosed: () => {
            this.connecting = false;
            this.isPlaying = false;
            this.setStatus("Disconnected. Reconnecting...");
            setTimeout(this.connect, 3000);
          }
        });
      } catch (e) {
        console.error("JSMpeg init error:", e);
        this.setStatus("Player Error");
        this.connecting = false;
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
      this.setStatus("Manual reconnect...");
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
.rtsp-container {
  background: #111;
}

.rtsp-canvas {
  border: 1px solid #444;
  max-width: 100%;
  max-height: 100%;
  background: #000;
}
</style>
