export default async function ({ $axios, $auth, route }) {
  // Key in storage
  const KEY = "envsettings";

  // If already cached, do nothing
  const cached = $auth?.$storage?.getUniversal(KEY);
  if (cached && cached.MQTT_SOCKET_HOST) return;

  try {
    // Call your API (adjust path)
    const res = await $axios.$get("/api/envsettings");

    // Save persistently (works SSR + client)
    $auth.$storage.setUniversal(KEY, res);

    // Optional: also keep a convenience flat keys
    // if (res.MQTT_SOCKET_HOST)
    //   $auth.$storage.setUniversal("MQTT_SOCKET_HOST", res.MQTT_SOCKET_HOST);
    const map = {
      MQTT_SOCKET_HOST: "MQTT_SOCKET_HOST",
      MQTT_DEVICE_CLIENTID: "MQTT_DEVICE_CLIENTID",
      TV_COMPANY_ID: "TV_COMPANY_ID",
      BACKEND_URL2: "BACKEND_URL2",
      MQTT_QRCODE_PAYMENT: "MQTT_QRCODE_PAYMENT",
      host: "host",
      WATCH_DIR: "WATCH_DIR",
      COMPANY_ID: "COMPANY_ID",
      API_URL: "API_URL",
      API_KEY: "API_KEY",
      MQTT_SERVER: "MQTT_SERVER",
      MQTT_FRONTEND: "MQTT_FRONTEND",
      BASE_HTTP_PORT: "BASE_HTTP_PORT",
      BASE_WS_PORT: "BASE_WS_PORT",
    };

    Object.keys(map).forEach((key) => {
      if (res[key] !== undefined && res[key] !== null) {
        $auth.$storage.setUniversal(key, res[key]);
      }
    });
  } catch (e) {
    // If API fails and you have no cached data, let app continue.
    // You can also add a safe fallback here.
    // console.error('envsettings load failed', e);
  }
}
