<template>
  <div style="height: 500px; width: 100%">
    <div style="height: 200px; overflow: auto" v-if="withPopup">
      <p>First marker is placed at {{ withPopup.lat }}, {{ withPopup.lng }}</p>
      <p>
        Center is at {{ currentCenter.lat }}, {{ currentCenter.lng }} and the
        zoom is: {{ currentZoom }}
      </p>
      <button @click="showLongText">Toggle long popup</button>
      <button @click="showMap = !showMap">Toggle map</button>
      <select v-model="selectedMap" @change="changeMap">
        <option value="osm">OpenStreetMap</option>
        <option value="google">Google Maps</option>
        <option value="amap">Amap</option>
      </select>
    </div>
    <l-map
      v-if="showMap"
      :zoom="zoom"
      :center="center"
      :options="mapOptions"
      style="height: 300px"
      @update:center="centerUpdate"
      @update:zoom="zoomUpdate"
    >
      <!-- Tile Layer based on selected map -->
      <l-tile-layer
        v-if="selectedMap === 'osm'"
        :url="osmUrl"
        :attribution="attribution"
      />
      <l-tile-layer
        v-if="selectedMap === 'google'"
        :url="googleUrl"
        :attribution="googleAttribution"
      />
      <l-tile-layer
        v-if="selectedMap === 'amap'"
        :url="amapUrl"
        :attribution="amapAttribution"
      />

      <!-- Custom Markers -->
      <l-marker :lat-lng="withPopup" :icon="customIcon">
        <l-popup>
          <div @click="innerClick">
            I am a popup
            <p v-show="showParagraph">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque
              sed pretium nisl, ut sagittis sapien. Sed vel sollicitudin nisi.
              Donec finibus semper metus id malesuada.
            </p>
          </div>
        </l-popup>
      </l-marker>
    </l-map>
  </div>
</template>

<script>
let Vue2Leaflet = {};
let latLng = null;
let L = null;

if (process.client) {
  Vue2Leaflet = require("vue2-leaflet");
  latLng = require("leaflet").latLng;
  L = require("leaflet");
}
import "leaflet/dist/leaflet.css";

export default {
  name: "Example",
  components: {
    "l-map": Vue2Leaflet.LMap,
    "l-tile-layer": Vue2Leaflet.LTileLayer,
    "l-marker": Vue2Leaflet.LMarker,
    "l-popup": Vue2Leaflet.LPopup,
  },
  data() {
    return {
      zoom: 4,
      center: process.client ? latLng(47.41322, -1.219482) : null,
      selectedMap: "osm", // Default to OpenStreetMap
      osmUrl: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
      googleUrl:
        "https://{s}.maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY", // Replace with your API key
      amapUrl:
        "http://webrd02.is.autonavi.com/appmaptile?size=1&scale=1&style=6&x={x}&y={y}&z={z}",
      attribution:
        '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
      googleAttribution:
        '&copy; <a href="https://www.google.com/intl/en_us/help/terms_maps.html">Google Maps</a>',
      amapAttribution: '&copy; <a href="https://www.amap.com/">AMap</a>',
      withPopup: process.client ? latLng(47.41322, -1.219482) : null,
      currentZoom: 11.5,
      currentCenter: process.client ? latLng(47.41322, -1.219482) : null,
      showParagraph: false,
      mapOptions: {
        zoomSnap: 0.5,
      },
      showMap: false,
      customIcon: process.client
        ? L.icon({
            iconUrl: "/google_map_icons/google_alarm.png",
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            tooltipAnchor: [16, -28],
            shadowUrl:
              "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png",
            shadowSize: [41, 41],
          })
        : null,
    };
  },
  created() {
    setTimeout(() => {
      if (process.client) {
        this.$axios.get(`get-map-key`).then(({ data }) => {
          console.log(data);
          this.googleUrl =
            "https://{s}.maps.googleapis.com/maps/api/js?key=" + data;
          this.showMap = true;
        });
      }
    }, 1000);
  },
  methods: {
    zoomUpdate(zoom) {
      this.currentZoom = zoom;
    },
    centerUpdate(center) {
      this.currentCenter = center;
    },
    showLongText() {
      this.showParagraph = !this.showParagraph;
    },
    innerClick() {
      alert("Click!");
    },
    changeMap() {
      // Logic for changing the map can be added here
      console.log(`Map changed to: ${this.selectedMap}`);
    },
  },
};
</script>
