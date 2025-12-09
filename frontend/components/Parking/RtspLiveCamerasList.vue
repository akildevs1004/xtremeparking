<template>
  <v-container fluid>
    <v-row>
      <v-col v-for="(cam, index) in cameras" :key="cam.id" cols="12" md="6">
        <RtspLiveCameraPlayer :title="cam.name" :wsPort="BASE_WS_PORT + index" v-if="NODE_SERVER_IP"
          :wsHost="NODE_SERVER_IP" :width="1280" :height="720" />
      </v-col>
    </v-row>
  </v-container>
</template>

<script>

import RtspLiveCameraPlayer from "../../components/Parking/RtspLiveCameraPlayer.vue";
export default {
  components: { RtspLiveCameraPlayer },

  data() {
    return {
      cameras: [],
      BASE_WS_PORT: 9991,
      NODE_SERVER_IP: null // Windows PC running server_stream.js
    };
  },

  async mounted() {
    await this.loadCameras();
  },

  methods: {
    async loadCameras() {
      const res = await this.$axios.get("/parking-cameras");
      this.cameras = res.data.data || [];


      // Safe extraction of the Node server IP
      this.NODE_SERVER_IP =
        this.cameras?.[0]?.node_server_ip ?? "192.168.2.16";

      console.log("NODE SERVER:", this.NODE_SERVER_IP);
    }
  }
};
</script>
