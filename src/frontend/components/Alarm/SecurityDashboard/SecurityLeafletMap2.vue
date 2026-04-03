<template>
  <div style="height: 500px; width: 100%">
    <div style="height: 200px; overflow: auto" v-if="withPopup">
      <p>First marker is placed at {{ withPopup.lat }}, {{ withPopup.lng }}</p>
      <p>Center is at {{ currentCenter }} and the zoom is: {{ currentZoom }}</p>
      <button @click="showLongText">Toggle long popup</button>
      <button @click="showMap = !showMap">Toggle map</button>
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
      <!-- Tile Layer for OpenStreetMap -->
      <l-tile-layer :url="url" :attribution="attribution" />

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

      <!-- <l-marker :lat-lng="withTooltip" :icon="customIcon">
        <l-tooltip :options="{ permanent: true, interactive: true }">
          <div @click="innerClick">
            I am a tooltip
            <p v-show="showParagraph">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque
              sed pretium nisl, ut sagittis sapien. Sed vel sollicitudin nisi.
              Donec finibus semper metus id malesuada.
            </p>
          </div>
        </l-tooltip>
      </l-marker> -->
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
    "l-tooltip": Vue2Leaflet.LTooltip,
  },
  data() {
    return {
      zoom: 4,
      center: process.client ? latLng(47.41322, -1.219482) : null,
      url: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
      attribution:
        '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
      withPopup: process.client ? latLng(47.41322, -1.219482) : null,
      withTooltip: process.client ? latLng(47.41422, -1.250482) : null,
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
            iconSize: [25, 41], // size of the icon
            iconAnchor: [12, 41], // point of the icon which will correspond to marker's location
            popupAnchor: [1, -34], // point from which the popup should open relative to the iconAnchor
            tooltipAnchor: [16, -28], // point from which the tooltip should appear
            shadowUrl:
              "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png",
            shadowSize: [41, 41], // size of the shadow
          })
        : null,
    };
  },
  created() {
    setTimeout(() => {
      if (process.client) {
        this.showMap = true;
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
  },
};
</script>
