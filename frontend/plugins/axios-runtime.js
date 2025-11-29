export default ({ $axios }) => {
  if (process.client && window.__APP_CONFIG__?.apiUrl) {
    $axios.setBaseURL(window.__APP_CONFIG__.apiUrl);
  }
};
