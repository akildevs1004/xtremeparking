export default async function ({ $axios }) {
  const KEY = "envsettings";
  if (localStorage.getItem(KEY)) return;

  const res = await $axios.$get("/envsettings");
  localStorage.setItem(KEY, JSON.stringify(res));
}
