<template>
  <div fab>
    <v-card elevation="3">
      <div
        :id="'mapCustomer' + customer_id"
        style="height: 250px; width: 100%"
      ></div
    ></v-card>
  </div>
</template>

<script>
import google_map_style_bandw from "../../google/google_style_blackandwhite.json";
import google_map_style_regular from "../../google/google_style_regular.json";

export default {
  props: ["customer", "customer_id", "mapimage"],
  data: () => ({
    google_map_style_bandw,
    google_map_style_regular,
    map: null,
    mapKey: null,
    geocoder: null,
    infowindow: null,

    snack: false,
    snackColor: "",
    snackText: "",
    dialog: false,
    AdvancedMarkerElement: null,
    mapMarkersLabelsList: [],
    customerInfo: "",
  }),
  computed: {},
  async mounted() {
    await this.getMapKey();
  },
  created() {},
  watch: {
    // options: {
    //   handler() {
    //     this.getCustomers();
    //   },
    //   deep: true,
    // },
  },
  methods: {
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

    can(per) {
      return this.$pagePermission.can(per, this);
    },
    async getMapKey() {
      let { data } = await this.$axios.get(`get-map-key`);
      this.mapKey = data;
      if (this.mapKey) {
        this.loadGoogleMapsScript(this.initMap);
      }
    },

    loadGoogleMapsScript(callback) {
      if (window.google && window.google.maps) {
        callback();
        return;
      }
      const script = document.createElement("script");
      script.src = `https://maps.googleapis.com/maps/api/js?loading=async&key=${this.mapKey}&callback=initMap`;
      script.async = true;
      script.defer = true;
      window.initMap = callback;
      document.head.appendChild(script);
    },
    async initMap() {
      try {
        // console.log("Operator Google Map", this.customer.latitude);
        // console.log(this.customer.longitude);

        const { AdvancedMarkerElement } =
          await window.google.maps.importLibrary("marker");

        this.AdvancedMarkerElement = AdvancedMarkerElement;

        this.map = new google.maps.Map(
          document.getElementById("mapCustomer" + this.customer_id),
          {
            controlSize: 20,
            zoom: 12,
            center: {
              lat: parseFloat(this.customer.latitude),
              lng: parseFloat(this.customer.longitude),
            },
            styles: this.google_map_style_bandw,
          }
        );
        this.geocoder = new google.maps.Geocoder();
        this.infowindow = new google.maps.InfoWindow();
        this.plotLocations();
      } catch (e) {}
    },
    async plotLocations() {
      if (this.customer) {
        const position = {
          lat: parseFloat(this.customer.latitude),
          lng: parseFloat(this.customer.longitude),
        };

        const icon = {
          url: this.mapimage, // Path to the customer image
          // scaledSize: new google.maps.Size(25, 30),
          scaledSize: new google.maps.Size(40, 40),
          origin: new google.maps.Point(0, 0),
          anchor: new google.maps.Point(25, 25), // Adjust anchor point to the center
        };
        try {
          const content = document.createElement("div");

          content.className = "customerMapTitle";
          content.innerHTML = `
          <div style="position: relative;
    top: 50px;background:transparent;color:black; padding:6px 10px;border-radius:6px; font-size:18px ;">
           ${this.customer.building_name}
          </div>
        `;

          const markerLabel = new this.AdvancedMarkerElement({
            position,
            map: this.map,
            content: content,
          });
          this.mapMarkersLabelsList.push(markerLabel); // ✅ Track marker
        } catch (e) {}
        const marker = new google.maps.Marker({
          position,
          map: this.map,
          title: this.customer.building_name,
          icon: icon,
        });

        // marker.addListener("click", () => {
        //   this.dialog = true;
        //   this.customerInfo = this.customer.building_name;
        // });

        let html =
          "<div style='width:250px'><div style='width:100px;float:left'>  " +
          "<img style='width:100px;padding-right:5px;' src='" +
          this.customer.profile_picture +
          "'/>" +
          "</div>";
        html +=
          "<div style='width:150px; float:left'>" +
          this.customer.building_name +
          " <br/> " +
          this.customer.city +
          "<div>Landmark: " +
          this.customer.landmark +
          "</div>" +
          "" +
          " ";
        html +=
          "<br/> <a target='_blank' href='https://www.google.com/maps?q=" +
          this.customer.latitude +
          "," +
          this.customer.longitude +
          "'>Google Map Link</a>" +
          " ";
        html += "</div></div>";

        var infowindow = new google.maps.InfoWindow({
          content: html,
          map: this.map,
          position: position,
        });
        infowindow.close();
        marker.addListener("mouseover", function () {
          infowindow.open(this.map, this);
        });
      }
    },
    openInGoogleMaps() {
      if (
        this.customer &&
        this.customer?.latitude &&
        this.customer?.longitude
      ) {
        const lat = parseFloat(this.customer?.latitude);
        const lng = parseFloat(this.customer?.longitude);
        const url = `https://www.google.com/maps?q=${lat},${lng}`;
        window.open(url, "_blank");
      }
    },
  },
};
</script>
