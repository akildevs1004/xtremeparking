export default async function ({ $axios }) {
  const KEY = "envsettings";

  // Do not refetch if already cached
  if (localStorage.getItem(KEY)) return;

  // Build API URL → same host, port 8000
  const apiURL = `${window.location.protocol}//${window.location.hostname}:8000`;

  try {
    const res = await $axios.get(`${apiURL}/api/envsettings`);

    // Store only the payload
    localStorage.setItem(KEY, JSON.stringify(res.data));
  } catch (error) {
    console.error("envsettings fetch failed:", error);
  }
}
