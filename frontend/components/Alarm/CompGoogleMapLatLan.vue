<template>
  <div>
    <!-- <v-dialog v-model="dialog" max-width="200px">TTTTTTTTTTT</v-dialog> -->

    <div
      :id="'mapCustomer' + contact_id"
      :style="
        'border-radious:10%; height:' +
        (mapheight ? mapheight : '210px') +
        ' ; width: 100%'
      "
    ></div>

    <!-- <v-btn
      class="text-right"
      style="float: right"
      small
      fill
      dark
      color="blue"
      @click="openInGoogleMaps()"
      >Open In Google Map
    </v-btn> -->
  </div>
</template>

<script>
export default {
  props: ["latitude", "longitude", "title", "contact_id", "mapheight", "alarm"],
  data: () => ({
    AdvancedMarkerElement: null,

    map: null,
    mapKey: null,
    geocoder: null,
    infowindow: null,

    snack: false,
    snackColor: "",
    snackText: "",
    dialog: false,
    customerInfo: "",
    alarm_status: null,
  }),
  computed: {},
  async mounted() {
    await this.getMapKey();

    // setInterval(() => {
    //   if (this.alarm) this.alarm_status = this.alarm.alarm_status;
    // }, 1000 * 5);
  },
  created() {
    this.colorcodes = this.$utils.getAlarmIcons();
  },
  watch: {
    // alarm_status: function () {
    //   console.log("alarm_status", this.alarm_status);
    //   this.getMapKey();
    //   //this.alarm_status = null;
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
      if (this.mapKey && typeof window !== "undefined") {
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
      const { AdvancedMarkerElement } = await window.google.maps.importLibrary(
        "marker"
      );

      this.AdvancedMarkerElement = AdvancedMarkerElement;
      this.map = new google.maps.Map(
        document.getElementById("mapCustomer" + this.contact_id),
        {
          mapTypeControl: false,
          mapId: this.mapKey, // ✅ Required for AdvancedMarkerElement
          streetViewControl: false,
          zoom: 12,
          center: { lat: 25.276987, lng: 55.296249 },
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
        }
      );
      this.geocoder = new google.maps.Geocoder();
      this.infowindow = new google.maps.InfoWindow();
      this.plotLocations();
    },
    async plotLocations() {
      console.log("his.alarm", this.alarm);

      if (!isNaN(this.latitude) && !isNaN(this.longitude)) {
        const position = {
          lat: parseFloat(this.latitude),
          lng: parseFloat(this.longitude),
        };

        let colorcodes = this.$utils.getAlarmIcons();

        // let iconURL =
        //   process.env.BACKEND_URL2 + "/google_map_icons/google_customer.png";
        // if (this.alarm?.alarm_type) {
        //   const colorObject = colorcodes[this.alarm.alarm_type.toLowerCase()];
        //   if (colorObject) iconURL = colorObject.image;
        // }

        let iconURL =
          process.env.BACKEND_APP_URL + "/google_map_icons/google_online.png";

        let colorObject = this.getAlarmColorObject(this.alarm);
        if (colorObject) iconURL = colorObject.image;

        const icon = {
          url: iconURL + "?5=5",
          // scaledSize: new google.maps.Size(28, 34),
          scaledSize: new google.maps.Size(40, 40),
          origin: new google.maps.Point(0, 0),
          anchor: new google.maps.Point(25, 25),
        };
        try {
          const content = document.createElement("div");

          content.className = "customerMapTitle";
          content.innerHTML = `
          <div style="position: relative;
    top: 50px;background:transparent;color:black; padding:6px 10px;border-radius:6px; font-size:18px;font-weight:bold ;">
           ${this.title}
          </div>
        `;

          const markerLabel = new this.AdvancedMarkerElement({
            position,
            map: this.map,
            content: content,
          });
          // this.mapMarkersLabelsList.push(markerLabel); // ✅ Track marker
        } catch (e) {}
        const marker = new google.maps.Marker({
          position,
          map: this.map,
          title: this.title,
          icon: icon,
        });

        // marker.addListener("click", () => {
        //   this.dialog = true;
        //   this.customerInfo = this.customer.building_name;
        // });
        this.map.panTo(position);
        if (this.alarm) {
          let googleDirectionIcon =
            process.env.APP_URL + "/icons/google_map.jpg";
          let html = `
            <table style="width:250px; min-height:100px" id="infowindow-content-${this.alarm.device.customer.id}">

               <tr>
                <td colspan="2" style="width:100%;;text-align:center; vertical-align: top;">
                 <div style="width:100%;margin:auto">
                   <img style="margin:auto;width:auto; max-height:120px; padding-right:5px;" src="${this.alarm.device.customer.profile_picture}" />
                  </div>

                </td>
                </tr>
              <tr>

                <td style=" vertical-align: top;font-size:10px">
                  <div>${this.alarm.device.customer.buildingtype.name}</div>
                 <div style="font-weight:bold;font-size:12px"> ${this.alarm.device.customer.building_name}</div>

                 <div>${this.alarm.device.customer.house_number},${this.alarm.device.customer.street_number}</div>
                 <div>${this.alarm.device.customer.city}</div>

                  <div>Landmark: ${this.alarm.device.customer.landmark}</div>

                  <td style=" vertical-align: middle;text-align:right">
 <a  title="Directions"  target="_blank" href="https://www.google.com/maps?q=${this.alarm.device.customer.latitude},${this.alarm.device.customer.longitude}"><img title="Directions" style="width:20px" src="${googleDirectionIcon}"/></a>


  </td>
                </td>


              </tr>

            </table>`;

          var infowindow = new google.maps.InfoWindow({
            content: html,
            map: this.map,
            position: position,
          });

          infowindow.close();

          console.log("this.alarm?.alarm_status", this.alarm?.alarm_status);

          if (this.alarm?.alarm_status == 1)
            marker.setAnimation(google.maps.Animation.BOUNCE);

          marker.addListener("mouseover", function () {
            infowindow.open(this.map, this);
          });
        }
      }
    },
    getAlarmColorObject(alarm, customer = null) {
      try {
        if (alarm) {
          if (this.colorcodes[alarm.alarm_type.toLowerCase()])
            return this.colorcodes[alarm.alarm_type.toLowerCase()];
          if (alarm.alarm_status == 1) {
            return this.colorcodes.alarm;
          }
        } else if (customer) {
          if (this.findAnyDeviceisOffline(customer.devices) > 0) {
            return this.colorcodes.offline;
          } else if (this.findanyArmedDevice(customer.devices)) {
            return this.colorcodes.armed;
          } else if (this.findanyDisamrDevice(customer.devices) > 0) {
            return this.colorcodes.disarm;
          }
        }

        return this.colorcodes.offline;
      } catch (e) {
        console.log(this.colorcodes);

        if (alarm.alarm_status == 1) {
          return this.colorcodes.alarm;
        } else return this.colorcodes.offline;
      }
    },
    openInGoogleMaps() {
      if (this.latitude && this.longitude) {
        const lat = parseFloat(this.latitude);
        const lng = parseFloat(this.longitude);
        const url = `https://www.google.com/maps?q=${lat},${lng}`;
        window.open(url, "_blank");
      }
    },
  },
};
</script>
