<template>
  <v-card elevation="2" class="pa-4">
    <v-card-text>
      <v-snackbar v-model="snack" :timeout="3000" top>
        {{ snackText }}
      </v-snackbar>
      <!-- Accounts Display -->
      <div style="display: flex; gap: 12px" v-if="can('whatsapp_view')">
        <v-card
          outlined
          v-for="(account, index) in accounts"
          :key="index"
          style="display: flex"
          max-width="250"
        >
          <v-card-text class="pa-2">
            <div style="width: 100%; display: flex; align-items: center">
              <div
                style="width: 100%"
                class="text-h6 text--primary text-center mb-1"
              >
                <v-text-field
                  :placeholder="account.label || 'Enter Title'"
                  dense
                  v-model="account.label"
                  hide-details
                ></v-text-field>
              </div>

              <div>
                <v-icon
                  title="Disconnect and Delete"
                  small
                  color="red"
                  @click="deleteItem(index)"
                  >mdi-close</v-icon
                >
              </div>
            </div>

            <div v-if="!whatsappSocketClosed">
              <div v-if="account.qrCodeDisplay">
                <v-img
                  max-width="250"
                  max-height="250"
                  :src="account.qrCodePath"
                ></v-img>
              </div>
              <div v-else style="max-width: 250px; padding: 15px">
                <v-img :src="account.placeHolderImage"></v-img>
              </div>

              <div class="text-center">
                <v-btn
                  :loading="account.loading"
                  v-if="account.statusMessage"
                  block
                  small
                  color="#139c4a"
                  class="white--text"
                  style="cursor: text"
                >
                  {{ account.statusMessage }}
                </v-btn>

                <br />
                <v-btn
                  :loading="account.loading"
                  v-if="account.statusMessage == 'Online'"
                  block
                  small
                  color="red"
                  class="white--text"
                  @click="deleteItem(index)"
                >
                  Click to Disconnect
                </v-btn>
                <br />
                <v-btn
                  :loading="account.loading"
                  v-if="account.statusMessage == 'Online'"
                  block
                  small
                  color="green"
                  class="white--text"
                  @click="testMessage(index)"
                >
                  Test Message({{ $auth.user.company?.contact?.whatsapp }})
                </v-btn>

                <v-btn
                  :loading="account.loading"
                  v-else-if="account.connectButton"
                  block
                  small
                  color="primary"
                  class="white--text"
                  @click="connect(account.clientId, index)"
                >
                  Scan QR code and Reconnect
                </v-btn>
                <v-btn
                  :loading="account.loading"
                  v-else-if="
                    account.client_id != '' &&
                    account.statusMessage != 'Connecting to WhatsApp...'
                  "
                  block
                  small
                  color="green"
                  @click="connect(account.clientId, index)"
                >
                  Click to Connect Wahtsapp
                </v-btn>

                <br />
                <v-btn
                  :loading="account.loading"
                  v-if="
                    !account.statusMessage && account.statusMessage == 'Online'
                  "
                  block
                  small
                  color="red"
                  class="white--text"
                  @click="deleteItem(index, account.client_id)"
                >
                  Delete
                </v-btn>

                <div v-if="account.qrCodePath" style="color: blue">
                  <div v-if="!qrCodeScanned">
                    <v-btn
                      block
                      v-if="!qrCodeScanned"
                      @click="qrCodeScanned = true"
                      small
                      color="green"
                      class="white--text"
                    >
                      Click here after QR Scanned
                    </v-btn>

                    Step2: Scan QR Code and click Button
                  </div>
                  <div v-else>Step3: Verifieng QR Code.....<br /></div>

                  <br />
                  <v-btn
                    block
                    small
                    color="red"
                    class="white--text"
                    @click="connect(account.clientId, index)"
                  >
                    Re-Generate QR Code
                  </v-btn>
                </div>

                <div
                  v-else-if="
                    account.statusMessage == 'Connecting to WhatsApp...'
                  "
                >
                  Status: Connecting to Whatsapp Server.....
                </div>

                <!-- {{ account }} -->
              </div>
            </div>
            <div v-else>
              <!-- {{ account }} -->
              <br />
              <v-btn
                block
                small
                color="red"
                class="white--text"
                @click="connect(account.clientId, index)"
              >
                Disconnected. Connect Again
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
import QRCode from "qrcode";

export default {
  data() {
    return {
      snack: false,
      snackText: "",
      accounts: [],
      qrCodeScanned: false,
      whatsappSocketClosed: false,
    };
  },
  async mounted() {
    await this.getWhatsappAccount(this.$auth.user.company_id);
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    async getWhatsappAccount(company_id) {
      let { data } = await this.$axios.get(
        `whatsapp-client-json/${company_id}`
      );
      if (data?.accounts?.length) {
        this.accounts = data.accounts.map((acc) => ({
          ...acc,
          loading: false,
        }));

        //connect clients - for re-verifications and trigger the whatsapp server
        for (const [index, element] of data.accounts.entries()) {
          // await this.connect(element.clientId, index);
          const wsUrl = `wss://wa.mytime2cloud.com/ws/?clientId=${element.clientId}`;
          let socketResponse = new WebSocket(wsUrl);
          console.log(socketResponse);
          if (socketResponse)
            socketResponse.onmessage = async (event) => {
              const data = JSON.parse(event.data);

              if (data.event === "status") {
              } else if (data.event === "ready") {
              } else if (data.event === "qr") {
                this.whatsappSocketClosed = true;
              }
            };
        }
      } else {
        await this.addAccount();
      }
    },

    async addAccount() {
      let clientId = `temperature_xtremeguard_${Date.now()}`;
      this.accounts.push({
        clientId,
        ws: null,
        statusMessage: null,
        placeHolderImage: `/whatsapp-sleep.png`,
        qrCodePath: null,
        qrCodeDisplay: false,
        disconnectButton: false,
        connectButton: false,
        statusColor: null,
        label: "Whatsapp Linked Account",
        loading: false,
      });

      await this.insertAccountIntoDB(); // Auto-save on add
    },

    async deleteItem(index, client_id = "") {
      const confirmDelete = window.confirm(
        "Are you sure you want to delete this account?"
      );
      if (!confirmDelete) return;

      this.accounts.splice(index, 1);

      try {
        await this.insertAccountIntoDB();
        await this.getWhatsappAccount(this.$auth.user.company_id);
      } catch (error) {
        console.error("Error deleting account:", error);
      }
    },

    async testMessage() {
      try {
        let payload = {
          company_id: this.$auth.user.company_id,
          accounts: this.accounts,
        };

        if (process.env.NODE_ENV !== "production") {
          //console.log("🚀 ~ Sending payload:", payload);
        }

        let data = await this.$axios.post(`whatsapp-test-message`, payload);

        this.snack = true;

        if (data.data.success) {
          this.snackText = data.data.data;
        } else {
          this.snackText = "Try Again";
        }
      } catch (error) {
        console.error("Error inserting account:", error);
      }
    },
    async insertAccountIntoDB() {
      try {
        let payload = {
          company_id: this.$auth.user.company_id,
          accounts: this.accounts,
        };

        if (process.env.NODE_ENV !== "production") {
          //console.log("🚀 ~ Sending payload:", payload);
        }

        await this.$axios.post(`whatsapp-client-json`, payload);
      } catch (error) {
        console.error("Error inserting account:", error);
      }
    },

    async connect(clientId, index) {
      this.whatsappSocketClosed = false;
      this.accounts[index].loading = true;
      await this.connectWebSocket(clientId, index);
    },

    async connectWebSocket(clientId, index) {
      this.qrCodeScanned = false;
      const account = this.accounts[index];

      if (!account) return;

      const wsUrl = `wss://wa.mytime2cloud.com/ws/?clientId=${clientId}`;
      account.ws = new WebSocket(wsUrl);
      let reconnectAttempts = 0;

      account.ws.onopen = () => {
        account.statusMessage = `Connected to the WebSocket server with clientId: ${clientId}`;
      };

      account.ws.onmessage = async (event) => {
        const data = JSON.parse(event.data);
        account.statusMessage = null;
        account.qrCodePath = null;
        account.qrCodeDisplay = false;
        account.disconnectButton = false;
        account.connectButton = false;
        account.statusColor = "";

        if (data.event === "status") {
          account.statusMessage = data.data;
          account.loading = true;
        } else if (data.event === "ready") {
          clearTimeout(account.connectTimeout); // Clear connect timeout if success
          account.statusMessage = data.data;
          account.placeHolderImage = `/whatsapp.jpeg`;
          account.disconnectButton = true;
          account.statusColor = "success";
          account.loading = false;
          await this.insertAccountIntoDB();
        } else if (data.event === "qr") {
          try {
            const qrCodePath = await QRCode.toDataURL(data.data, {
              color: {
                dark: "#000000",
                light: "#ffffff",
              },
            });

            account.qrCodePath = qrCodePath;
            account.qrCodeDisplay = true;
            account.statusMessage = "Connecting to WhatsApp...";
            //account.loading = true;

            // ⏱️ Timeout after 60 seconds if not ready
            account.connectTimeout = setTimeout(() => {
              account.loading = false;
              account.statusMessage = "Timeout. Please retry.";
              account.connectButton = true;
            }, 1000 * 60 * 5); // 60 seconds
          } catch (error) {
            account.statusMessage = `Error generating QR code: ${error.message}`;
            account.loading = false;
          }
        } else if (data.event === "destroy") {
          clearTimeout(account.connectTimeout);
          account.placeHolderImage = `/whatsapp-sleep.png`;
          account.statusMessage = data.data;
          account.connectButton = true;
          account.statusColor = "error";
          account.loading = false;
        }
      };

      account.ws.onclose = () => {
        if (reconnectAttempts < 3) {
          setTimeout(() => {
            this.connectWebSocket(clientId, index);
          }, 2000);
          reconnectAttempts++;
        } else {
          account.statusMessage = "WebSocket disconnected. Please reconnect.";
          account.connectButton = true;
          account.loading = false;
        }
      };
    },
  },
};
</script>
