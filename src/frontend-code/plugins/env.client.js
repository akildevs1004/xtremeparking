function readEnvObject() {
  try {
    return JSON.parse(localStorage.getItem("envsettings") || "{}") || {};
  } catch {
    return {};
  }
}

export default function (_, inject) {
  inject("env", {
    // ✅ main object you want
    settings: readEnvObject(),

    // Optional helpers (still useful)
    get(key, fallback = null) {
      return this.settings[key] ?? fallback;
    },

    refreshFromStorage() {
      this.settings = readEnvObject();
      return this.settings;
    },

    clear() {
      localStorage.removeItem("envsettings");
      localStorage.removeItem("envsettings_ts");
      this.settings = {};
    },
  });
}
