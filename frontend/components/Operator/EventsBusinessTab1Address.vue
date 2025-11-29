<template>
  <div>
    <v-row>
      <v-col class="pt-3">
        <table
          class="operatorcustomerTop1"
          style="width: 100%; line-height: 28px"
          v-if="customer"
        >
          <tr class=" ">
            <td style="width: 150px">Name</td>
            <td class="bold">{{ customer.building_name }}</td>
          </tr>
          <tr>
            <td>Property</td>
            <td class="bold">{{ customer.buildingtype.name }}</td>
          </tr>

          <tr>
            <td>Address</td>
            <td class="bold">
              {{ customer.house_number }}, {{ customer.street_number }},
              {{ customer.area }}
            </td>
          </tr>
          <tr>
            <td>Location</td>
            <td class="bold">
              {{ customer.city }}, {{ customer.state }}, {{ customer.country }}
            </td>
          </tr>
          <tr>
            <td>Landmark</td>
            <td class="bold">{{ customer.landmark }}</td>
          </tr>
          <tr>
            <td>Business Timings</td>
            <td class="bold">
              {{ customer.open_time || "---" }} TO
              {{ customer.close_time || "---" }}
            </td>
          </tr>
          <tr>
            <td>Business Number</td>
            <td class="bold">
              {{ customer.contact_number || "---" }}
            </td>
          </tr>
          <tr>
            <td>Business Email</td>
            <td class="bold">
              {{ customer.email || "---" }}
            </td>
          </tr>
        </table>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-card class="background" style="background-color: red">
          <v-card-item elevation="2">
            <v-tabs
              v-if="customer"
              height="25"
              center-active
              right
              class="customerEmergencyContactTabs1 customerEmergencyContactTabsBGcolor12222"
            >
              <v-tab
                style="font-size: 10px; min-width: 50px !important"
                v-if="
                  contact.address_type.toLowerCase() != 'primary' &&
                  contact.address_type.toLowerCase() != 'secondary'
                "
                v-for="(contact, index) in customer.contacts"
                :key="contact.id"
              >
                {{ contact.address_type }}</v-tab
              >
              <v-tab-item
                v-if="
                  contact.address_type.toLowerCase() != 'primary' &&
                  contact.address_type.toLowerCase() != 'secondary'
                "
                v-for="(contact, index) in customer.contacts"
                :key="contact.id + 50"
                name="index+50"
              >
                <v-card class="elevation-1">
                  <v-card-text>
                    <v-row>
                      <v-col
                        style="margin: auto; padding-top: 0px; max-width: 200px"
                      >
                        <div style="margin: auto; text-align: center">
                          {{ $utils.caps(contact.address_type) }}
                        </div>
                        <v-img
                          style="
                            width: 150px;
                            border: 1px solid #ddd;
                            margin: auto;
                          "
                          :src="
                            contact?.profile_picture
                              ? contact.profile_picture
                              : '/no-profile-image.jpg'
                          "
                        ></v-img>
                      </v-col>
                      <v-col>
                        <table
                          class="eventcustomertab"
                          style="width: 100%; line-height: 30px"
                        >
                          <tr style="font-weight: bold">
                            <td style="width: 50px">
                              <v-icon color="#2038c0" size="20"
                                >mdi-account-tie</v-icon
                              >
                            </td>
                            <td>
                              {{
                                contact?.first_name
                                  ? $utils.caps(contact.first_name) +
                                    " " +
                                    $utils.caps(contact.last_name)
                                  : "---"
                              }}
                            </td>
                          </tr>
                          <tr>
                            <td style="width: 50px">
                              <v-icon color="#2038c0" size="20"
                                >mdi-map-marker</v-icon
                              >
                            </td>
                            <td>
                              {{
                                contact?.address && contact?.address != "null"
                                  ? contact.address
                                  : "---"
                              }}
                            </td>
                          </tr>
                          <tr>
                            <td style="width: 50px">
                              <v-icon color="#2038c0" size="20"
                                >mdi-phone-classic</v-icon
                              >
                            </td>
                            <td>
                              <!-- Office Phone : -->
                              {{
                                contact?.office_phone
                                  ? contact.office_phone
                                  : "---"
                              }}
                            </td>
                          </tr>
                          <tr>
                            <td style="width: 50px">
                              <v-icon color="#2038c0" size="20"
                                >mdi-cellphone-basic</v-icon
                              >
                            </td>
                            <td>
                              <!-- Mobile : -->
                              {{ contact?.phone1 ? contact.phone1 : "---" }}
                            </td>
                          </tr>
                          <tr>
                            <td style="width: 50px">
                              <v-icon color="#2038c0" size="20"
                                >mdi-cellphone</v-icon
                              >
                            </td>
                            <td>
                              <!-- Office : -->
                              {{ contact?.phone2 ? contact.phone2 : "---" }}
                            </td>
                          </tr>
                          <tr>
                            <td style="width: 50px">
                              <v-icon color="#2038c0" size="20"
                                >mdi-whatsapp</v-icon
                              >
                            </td>
                            <td>
                              <!-- Whatsapp : -->
                              {{ contact?.whatsapp ? contact.whatsapp : "---" }}
                            </td>
                          </tr>
                          <tr>
                            <td style="width: 50px">
                              <v-icon color="#2038c0" size="20">mdi-at</v-icon>
                            </td>
                            <td>
                              <!-- E-Mail : -->
                              {{ contact?.email ? contact.email : "---" }}
                            </td>
                          </tr>
                          <tr>
                            <td style="width: 50px">
                              <v-icon color="#2038c0" size="20"
                                >mdi-message-bulleted</v-icon
                              >
                            </td>
                            <td>
                              Notes :
                              {{ contact?.notes ? contact.notes : "---" }}
                            </td>
                          </tr>
                        </table>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>
              </v-tab-item>
            </v-tabs>
          </v-card-item>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  components: {},
  props: ["customer"],
  data: () => ({
    tab: "",
  }),
  computed: {},
  mounted() {},
  created() {},
  watch: {},
  methods: {},
};
</script>
