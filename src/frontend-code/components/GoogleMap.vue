<template>
  <v-card>
    <v-container fluid>
      <v-row>
        <v-col>
          <v-text-field
            outlined
            dense
            hide-details
            v-model="searchQuery"
            label="Search location"
          ></v-text-field>
        </v-col>
        <v-col>
          <v-btn @click="searchLocation" class="primary"
            >Find Location <v-icon>mdi-map-marker-radius-outline</v-icon></v-btn
          >
        </v-col>

        <v-col class="text-right">
          <v-btn small text @click="$emit(`close`)" class="grey white--text"
            >Close
          </v-btn>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <div id="map" style="height: 100vh"></div>
        </v-col>
      </v-row>
    </v-container>
  </v-card>
</template>

<script>
export default {
  data() {
    return {
      searchQuery: "",
      map: null,
      marker: null,
      location: null,
      mapKey: null,
    };
  },
  // async mounted() {
  //   await this.getMapKey();

  //   this.loadGoogleMapsScript().then(() => {
  //     this.initMap();
  //   });
  // },

  async mounted() {
    try {
      await this.getMapKey(); // Ensure this completes before proceeding
      await this.loadGoogleMapsScript();
      this.initMap();
    } catch (error) {
      console.error("Error loading map key or script:", error);
    }
  },

  methods: {
    async getMapKey() {
      let { data } = await this.$axios.get(`get-map-key`);
      this.mapKey = data;
      if (this.mapKey) {
        this.loadGoogleMapsScript(this.initMap);
      }
    },
    // async getMapKey() {
    //   try {
    //     let { data } = await this.$axios.get("get-map-key");

    //     if (data && !this.mapKey) {
    //       // Avoid reassigning mapKey if it's already set
    //       this.mapKey = data;
    //       this.loadGoogleMapsScript().then(() => this.initMap());
    //     }
    //   } catch (error) {
    //     console.error("Error fetching map key:", error);
    //   }
    // },

    getCurrentLocation() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
          const pos = {
            lat: position.coords.latitude,
            lng: position.coords.longitude,
          };

          this.map.setCenter(pos);

          // Initialize marker at current location
          this.currentMarker = new google.maps.Marker({
            map: this.map,
            position: pos,
            title: "You are here",
          });
        });
      } else {
        console.log("Geolocation is not supported by this browser.");
      }
    },
    loadGoogleMapsScript() {
      return new Promise((resolve, reject) => {
        if (typeof google !== "undefined" && google.maps) {
          resolve();
          return;
        }

        const script = document.createElement("script");
        script.src = `https://maps.googleapis.com/maps/api/js?loading=async&key=${this.mapKey}&libraries=marker&libraries=places`;
        script.async = true;
        script.defer = true;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
      });
    },
    initMap() {
      if (document.getElementById("map"))
        this.map = new google.maps.Map(document.getElementById("map"), {
          center: { lat: 25.276987, lng: 55.296249 },
          zoom: 12,
          styles: [
            {
              featureType: "administrative",
              stylers: [{ visibility: "off" }],
            },
            {
              featureType: "administrative",
              stylers: [{ visibility: "off" }],
            },
            {
              featureType: "landscape",
              stylers: [{ visibility: "off" }],
            },
            {
              featureType: "poi",
              stylers: [{ visibility: "off" }],
            },
          ],
        });
    },
    searchLocation() {
      if (!this.searchQuery) return;

      const service = new google.maps.places.PlacesService(this.map);
      const request = {
        query: this.searchQuery,
        fields: ["name", "geometry"],
      };

      service.findPlaceFromQuery(request, (results, status) => {
        if (status === google.maps.places.PlacesServiceStatus.OK) {
          const place = results[0];
          this.map.setCenter(place.geometry.location);

          if (this.marker) {
            this.marker.setMap(null);
          }

          this.marker = new google.maps.Marker({
            map: this.map,
            position: place.geometry.location,
            draggable: true,
          });

          this.updateLocation(place.geometry.location);

          // Add event listener for marker drag end
          this.marker.addListener("dragend", (event) => {
            this.updateLocation(event.latLng);
          });
        }
      });
    },
    updateLocation(latLng) {
      this.location = { lat: latLng.lat(), lng: latLng.lng() };
      this.$store.dispatch("updateData", this.location);
    },
  },
};
</script>
