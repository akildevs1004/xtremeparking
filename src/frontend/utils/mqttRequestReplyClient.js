// mqttClient.js
import mqtt from "mqtt";
// Assumes your broker exposes a WebSocket listener (e.g. ws://165.22.222.17:9001)
const BROKER_URL = process.env.MQTT_QRCODE_PAYMENT; // "wss://mqtt.xtremeguard.org:8084"; // change if your WS port differs
const DEVICE_ROOT = "xtreemparking"; // topic root (static as per your backend)
let client;

export function getMqtt() {
  if (client && client.connected) return client;

  client = mqtt.connect(BROKER_URL, {
    clientId: "vue-qrcode-" + Math.random().toString(16).slice(2),
    clean: true,
    reconnectPeriod: 1000 * 60,
    keepalive: 30,
  });

  client.on("connect", () => console.log("MQTT connected (browser)"));
  client.on("reconnect", () => console.log("MQTT reconnecting…"));
  client.on("error", (err) =>
    console.error("MQTT error:", err?.message || err)
  );

  return client;
}

/**
 * Do an MQTT RPC:
 *   - subscribes to replyTopic
 *   - publishes payload (with correlationId)
 *   - waits for the matching reply or times out
 */
export function mqttRequestReply({
  companyId,
  action,
  payload,
  timeoutMs = 8000,
}) {
  return new Promise((resolve, reject) => {
    const cli = getMqtt();
    const correlationId = "r" + Math.random().toString(16).slice(2);
    const requestTopic = `${DEVICE_ROOT}/${companyId}/qrcodepaymentsapi/laravel`;
    const replyTopic = `${DEVICE_ROOT}/${companyId}/qrcodepaymentsapi/vue`;

    const message = JSON.stringify({
      action,
      correlation_id: correlationId, // (optional) add this to your Laravel echo if you want strict matching
      ...payload,
    });

    let timer;
    const onMessage = (topic, msg) => {
      if (topic !== replyTopic) return;

      try {
        const json = JSON.parse(msg.toString());

        // If your server echoes correlation_id, filter here:
        if (json?.correlation_id && json.correlation_id !== correlationId)
          return;

        clearTimeout(timer);
        cli.unsubscribe(replyTopic, () => {});
        cli.removeListener("message", onMessage);
        resolve(json);
      } catch (e) {
        clearTimeout(timer);
        cli.unsubscribe(replyTopic, () => {});
        cli.removeListener("message", onMessage);
        reject(e);
      }
    };

    // 1) subscribe to reply
    cli.subscribe(replyTopic, { qos: 0 }, (err) => {
      if (err) return reject(err);

      // 2) start listening
      cli.on("message", onMessage);

      // 3) publish request
      cli.publish(requestTopic, message, { qos: 0, retain: false }, (perr) => {
        if (perr) {
          cli.removeListener("message", onMessage);
          return reject(perr);
        }
        // 4) timeout
        timer = setTimeout(() => {
          cli.unsubscribe(replyTopic, () => {});
          cli.removeListener("message", onMessage);
          reject(new Error("MQTT request timed out"));
        }, timeoutMs);
      });
    });
  });
}
