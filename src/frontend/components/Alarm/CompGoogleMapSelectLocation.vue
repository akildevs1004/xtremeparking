<template>
  <div>
    <div
      v-if="contact_id"
      :id="'mapCustomerChangemarker' + contact_id"
      style="height: 400px; width: 100%"
    ></div>

    <div class="text-center" style="width: 95%">
      <v-row style="padding: 20px; padding-bottom: 0px">
        <v-col>
          <!-- <v-btn
            dense
            color="red"
            style="color: #fff"
            small
            @click="$emit('closePopup')"
            >Close</v-btn
          > -->
        </v-col>
        <v-col style="max-width: 200px"
          ><v-btn dense color="primary" small @click="readmyBrowserLocation()"
            ><v-icon size="15">mdi-image-filter-center-focus-strong</v-icon>
            Find My Location</v-btn
          ></v-col
        >
        <v-col style="max-width: 200px; text-align: right"
          ><v-btn dense color="primary" small @click="updateAddress()"
            >Update and Close</v-btn
          ></v-col
        >
      </v-row>

      <!-- <p>Latitude: {{ latitude }}</p>
    <p>Longitude: {{ longitude }}</p>
    <p>Address: {{ address }}</p> -->
    </div>
  </div>
</template>

<script>
export default {
  props: ["title", "contact_id", "customer_latitude", "customer_longitude"],
  data() {
    return {
      latitude: 25.239416614237363,
      longitude: 55.319938270019534,

      address: "",
      map: null,
      mapKey: null,
      marker: null,
    };
  },
  async mounted() {
    await this.getMapKey();
  },
  methods: {
    updateAddress() {
      this.$emit("triggerAddress", this.latitude, this.longitude);
    },
    async getMapKey() {
      try {
        let { data } = await this.$axios.get(`get-map-key`);
        this.mapKey = data;
        if (this.mapKey) {
          this.loadGoogleMapsScript(this.initMap);
        } else {
          console.error("Google Maps API Key is missing.");
        }
      } catch (error) {
        console.error("Error fetching Google Maps API Key:", error);
      }
    },

    loadGoogleMapsScript(callback) {
      if (window.google && window.google.maps) {
        callback();
        return;
      }
      const script = document.createElement("script");
      script.src = `https://maps.googleapis.com/maps/api/js?key=${this.mapKey}&callback=initMap`;
      script.async = true;
      script.defer = true;
      window.initMap = callback;
      document.head.appendChild(script);
    },

    initMap() {
      // Retrieve and center map on user's location

      // let mapLatitude = 25.276987;
      // let mapLongtude = 55.296249;
      if (this.customer_latitude != null || this.customer_latitude != "") {
        this.latitude = parseFloat(this.customer_latitude);
        this.longitude = parseFloat(this.customer_longitude);
      }

      this.map = new google.maps.Map(
        document.getElementById("mapCustomerChangemarker" + this.contact_id),
        {
          zoom: 14,
          center: { lat: this.latitude, lng: this.longitude },
          mapTypeControl: false,
          streetViewControl: false,
        }
      );

      if (this.customer_latitude == null || this.customer_latitude == "")
        this.getUserLocation();

      if (isNaN(this.latitude) || isNaN(this.longitude)) {
        this.latitude = 25.239416614237363;
        this.longitude = 55.319938270019534;
      }

      //console.log("this.latitude", this.latitude, this.longitude);

      const userLocation = {
        lat: this.latitude,
        lng: this.longitude,
      };

      // Center map on user's location and place draggable marker
      this.map.panTo(userLocation);

      this.placeDraggableMarker(userLocation);
    },

    placeDraggableMarker(latLng) {
      if (this.marker) {
        this.marker.setPosition(latLng);
      } else {
        this.marker = new google.maps.Marker({
          position: latLng,
          map: this.map,
          draggable: true,
          title: "Move to change Location",
          label: {
            text: "Move",
            color: "#FFF",
            fontSize: "10px",
          },
        });

        // Update latitude and longitude when marker is moved
        this.marker.addListener("dragend", () => {
          const newPosition = this.marker.getPosition();
          this.latitude = newPosition.lat();
          this.longitude = newPosition.lng();

          //this.getAddress(newPosition);
        });
      }
    },

    getAddress(latLng) {
      const geocoder = new google.maps.Geocoder();
      geocoder.geocode({ location: latLng }, (results, status) => {
        if (status === "OK" && results[0]) {
          this.address = results[0].formatted_address;
        } else {
          console.error("Geocoder failed due to:", status);
        }
      });
    },
    readmyBrowserLocation() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          (position) => {
            this.latitude = position.coords.latitude;
            this.longitude = position.coords.longitude;
            const userLocation = {
              lat: this.latitude,
              lng: this.longitude,
            };

            // Center map on user's location and place draggable marker
            this.map.panTo(userLocation);

            this.placeDraggableMarker(userLocation);

            return;
          },
          (error) => {
            if (error.code === error.PERMISSION_DENIED) {
              alert(
                "Location access was denied. Please enable location services  and reload the page"
              );
            } else {
              alert("Unable to retrieve location.");
            }
          }
        );
      } else {
        alert("Geolocation is not supported by this browser.");
      }
    },
    getUserLocation() {
      //if user has no map loaded

      const userLocation = {
        lat: 25.239416614237363,
        lng: 55.319938270019534,
      };
      console.log("userLocation", userLocation);
      // Center map on user's location and place draggable marker
      this.map.panTo(userLocation);

      this.placeDraggableMarker(userLocation);
    },
  },
};
</script>
