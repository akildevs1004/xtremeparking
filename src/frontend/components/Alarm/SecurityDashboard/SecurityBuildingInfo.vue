<template>
  <div max-width="100%">
    <v-card flat>
      <v-card-text style="padding: 0px">
        <v-row v-if="customer">
          <v-col cols="5">
            <h3>Building Info</h3>
            <br />
            <v-row>
              <v-col>
                <v-text-field
                  readonly
                  class=""
                  label="Type"
                  dense
                  outlined
                  flat
                  :value="
                    customer.buildingtype?.name
                      ? customer.buildingtype.name
                      : '---'
                  "
                  hide-details
                >
                </v-text-field>
              </v-col>

              <v-col>
                <v-text-field
                  readonly
                  class=""
                  label="Licence"
                  dense
                  outlined
                  flat
                  :value="customer.account_status == 1 ? 'Active' : 'Disabled'"
                  hide-details
                >
                </v-text-field> </v-col
              ><v-col cols="12">
                <v-text-field
                  readonly
                  class=""
                  label="Name"
                  dense
                  outlined
                  flat
                  v-model="customer.building_name"
                  hide-details
                >
                </v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  readonly
                  class=""
                  label="Address 1"
                  dense
                  outlined
                  flat
                  v-model="customer.house_number"
                  hide-details
                >
                </v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  readonly
                  class=""
                  label="Address 2"
                  dense
                  outlined
                  flat
                  v-model="customer.street_number"
                  hide-details
                >
                </v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  readonly
                  class=""
                  label="Landmark"
                  dense
                  outlined
                  flat
                  v-model="customer.landmark"
                  hide-details
                >
                </v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  readonly
                  class=""
                  label="City"
                  dense
                  outlined
                  flat
                  v-model="customer.city"
                  hide-details
                >
                </v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  readonly
                  class=""
                  label="State"
                  dense
                  outlined
                  flat
                  v-model="customer.state"
                  hide-details
                >
                </v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  readonly
                  class=""
                  label="Country"
                  dense
                  outlined
                  flat
                  v-model="customer.country"
                  hide-details
                >
                </v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  readonly
                  class=""
                  label="Email"
                  dense
                  outlined
                  flat
                  :value="customer.user?.email ? customer.user.email : '---'"
                  hide-details
                >
                </v-text-field>
              </v-col>
            </v-row>
          </v-col>
          <v-col cols="7">
            <v-tabs
              color="#dc7640"
              right
              class="customer-building-tabs-left-line"
            >
              <v-tabs-slider color="blue"></v-tabs-slider>
              <!-- <v-tab>Map</v-tab> -->
              <v-tab>Google Map</v-tab>
              <v-tab>Business Photo</v-tab>
              <v-tab>Sensor Alarm Map</v-tab>
              <!-- <v-tab-item>
                <v-card elevation="13" outlined>
                  <SecurityLeafletMap
                    class="rounded-lg"
                    :customer="customer"
                    :customer_id="customer.id"
                    style="padding: 20px"
                    name="gogole_map"
                  /> </v-card
              ></v-tab-item> -->
              <v-tab-item>
                <v-card elevation="2" outlined>
                  <SecurityGoogleMap
                    class="rounded-lg"
                    :customer="customer"
                    :customer_id="customer.id"
                    name="gogole_map"
                  /> </v-card
              ></v-tab-item>
              <v-tab-item>
                <v-card elevation="13" outlined>
                  <img
                    :src="customer.profile_picture"
                    style="width: 100%; height: 500px; height: auto"
                  />
                </v-card>
              </v-tab-item>
              <v-tab-item>
                <v-card elevation="13" outlined>
                  <EventsBusinessTabFloorPlan
                    v-if="customer"
                    :alarm="alarm"
                    :customer="customer"
                  />
                  <!-- <SecurityBuildingPhotos
                    class="rounded-lg"
                    :customer_id="customer.id"
                    :photos="customer.photos"
                    :alarm_id="alarm_id"
                    v-if="customer" />
                     -->
                </v-card></v-tab-item
              >
            </v-tabs>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import SecurityGoogleMap from "../../Alarm/SecurityDashboard/SecurityGoogleMap.vue";
import SecurityLeafletMap from "../../Alarm/SecurityDashboard/SecurityLeafletMap.vue";

import SecurityBuildingPhotos from "../../Alarm/SecurityDashboard/SecurityBuildingPhotos.vue";
export default {
  components: { SecurityGoogleMap, SecurityBuildingPhotos, SecurityLeafletMap },
  props: ["_id", "isPopup", "customer", "alarm_id", "alarm"],
  data: () => ({
    tab: "",
  }),
  computed: {},
  mounted() {},
  created() {
    //this.getDataFromApi();
  },
  watch: {},
  methods: {},
};
</script>
