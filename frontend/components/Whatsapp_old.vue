<template>
  <v-card elevation="0">
    <v-card-text class="mt-4 text-center" style="margin: auto">
      <div v-if="statusMessage">
        <v-badge dense :color="statusColor" offset-y="5" class="mr-5"></v-badge
        ><span> Info: {{ statusMessage }}</span>
      </div>
      <div v-if="qrCodeDisplay == true" style="text-align: center">
        <v-img
          :src="qrCodePath"
          max-width="200"
          max-height="200"
          style="margin: auto"
        ></v-img>

        <div>
          Info: Scan Above QR code from Whatsapp Mobile app Linked Devices
        </div>
      </div>
      <div v-else-if="qrCodeDisplay == false && statusMessage == null">
        Info: No Response from Server <br />

        <v-btn class="pa-2" x-small color="primary" @click="connect">
          Connect
        </v-btn>
      </div>
      <div v-else-if="statusMessage != 'Online'">
        <v-btn class="pa-2" x-small color="primary" @click="connect">
          <v-icon small class="mr-1">mdi-whatsapp</v-icon>
          <!-- {{ statusMessage !== "Online" ? "Connect" : "Check Online" }} -->
          Connect
        </v-btn>
      </div>

      <!-- <div class="mt-5">
        <v-btn
          x-small
          v-if="disconnectButton"
          color="error"
          @click="disconnect"
        >
          <v-icon small class="mr-1">mdi-whatsapp</v-icon> Disconnect
        </v-btn>
      </div> -->
    </v-card-text>
  </v-card>
</template>

<script>
import QRCode from "qrcode";

export default {
  data() {
    return {
      clientId: null, // Input field model for client ID
      ws: null, // WebSocket instance
      statusMessage: null, // Message for status updates
      qrCodePath: null, // QR code image path
      qrCodeDisplay: false,
      disconnectButton: false,
      connectButton: false,
      statusColor: null,
    };
  },
  async mounted() {
    setTimeout(() => {
      this.connect();
    }, 1000 * 2);
  },
  methods: {
    async connect() {
      await this.connectWebSocket(
        this.$auth?.user?.company?.id + "_alarm_xtremeguard" ||
          Math.random() + "_alarm_xtremeguard"
      );
    },
    async disconnect() {
      this.statusMessage = null;
      this.disconnectButton = false;
      this.connectButton = false;
      this.statusColor = "error";

      let payload = {
        clientId:
          this.$auth?.user?.company?.id + "_alarm_xtremeguard" ||
          Math.random() + "_alarm_xtremeguard",
      };

      try {
        let { data } = await this.$axios.post(
          `https://wa.mytime2cloud.com/whatsapp-destroy`,
          payload
        );
        this.statusMessage = data.data;
        this.connectButton = true;
      } catch (error) {
        this.statusMessage = error.message;
        this.disconnectButton = true;
      }
    },

    async connectWebSocket(clientId) {
      this.clientId = clientId;
      const wsUrl = `wss://wa.mytime2cloud.com/ws/?clientId=${this.clientId}`;
      this.ws = new WebSocket(wsUrl);

      this.ws.onopen = () => {
        this.statusMessage = `Connected to the WebSocket server with clientId: ${this.clientId}`;
      };
      let onlineCheckcounter = 1;
      this.ws.onmessage = async (event) => {
        //this.qrCodePath = null;
        //this.qrCodeDisplay = false;

        this.statusMessage = null;
        this.disconnectButton = false;
        this.connectButton = false;
        this.statusColor = "";

        const data = JSON.parse(event.data);
        console.log("🚀 ~ this.ws.onmessage= ~ data onmessage:", data);

        if (event.data == 1) {
          //   this.connect();
        }

        if (data.event === "status") {
          this.statusMessage = data.data;
        } else if (data == "1" || data.event == "Buffer") {
          this.statusMessage = "Online";
          this.disconnectButton = true;
          this.statusColor = "success";
          this.qrCodePath = null;
          this.qrCodeDisplay = false;
          onlineCheckcounter++;
        } else if (data.event === "ready" || data.event == "Buffer") {
          onlineCheckcounter++;

          if (onlineCheckcounter >= 3) {
            this.statusMessage = data.data;
            this.disconnectButton = true;
            this.statusColor = "success";
            this.qrCodePath = null;
            this.qrCodeDisplay = false;
          } else {
          }
        } else if (data.event === "qr") {
          onlineCheckcounter = 0;
          const qrCodeData = data.data;
          try {
            // Generate a QR code as a data URL
            const qrCodePath = await QRCode.toDataURL(qrCodeData, {
              color: {
                dark: "#000000", // Black QR code
                light: "#ffffff", // White background
              },
            });

            // Update the path to display the QR code
            this.qrCodePath = qrCodePath;
            this.qrCodeDisplay = true;
          } catch (error) {
            this.statusMessage = `Error generating QR code: ${error.message}`;
          }
        } else if (data.event === "destroy") {
          this.statusMessage = data.data;
          this.connectButton = true;
          this.statusColor = "error";
        }
      };
    },
  },
};
</script>
