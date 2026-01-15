export default (_, inject) => {
  inject("env", {
    all: () => JSON.parse(localStorage.getItem("envsettings") || "{}"),
    get: (k, fb = null) =>
      JSON.parse(localStorage.getItem("envsettings") || "{}")[k] ?? fb,
  });
};
