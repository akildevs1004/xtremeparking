export default function ({ $auth }, inject) {
  const KEY = "envsettings";

  const api = {
    getAll() {
      return $auth?.$storage?.getUniversal(KEY) || {};
    },
    get(key, fallback = null) {
      const all = $auth?.$storage?.getUniversal(KEY) || {};
      return all[key] ?? fallback;
    },
  };

  inject("envsettings", api);
}
