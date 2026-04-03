<template>
  <div>
    <v-row>
      <v-col style="padding: 0px">
        <v-row v-if="alarm && customer">
          <v-col>
            <!-- <v-img
              :src="customer.profile_picture"
              style="
                width: 100px;
                height: auto;
                margin-left: -2px;

                vertical-align: bottom;
              "
            /> -->
            <v-row>
              <v-col style="text-align: center">
                <v-card>
                  <v-card-text style="padding: 0px; height: 170px">
                    <img
                      :src="customer.profile_picture"
                      style="
                        width: 100%;
                        height: auto;
                        margin-left: 0px;
                        height: 170px;
                        vertical-align: bottom;
                      "
                    />
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
            <v-row style="margin-top: 0px">
              <v-col>
                <v-card elevation="2">
                  <v-card-text style="padding: 0px">
                    <table
                      class="eventcustomertab"
                      style="width: 100%; height: 150px"
                    >
                      <tr>
                        <td
                          class="bold1"
                          colspan="2"
                          style="font-size: 15px !important"
                        >
                          {{
                            customer?.building_name
                              ? $utils.caps(customer.building_name)
                              : "---"
                          }}
                        </td>
                      </tr>
                      <tr>
                        <td class="bol1">Event ID : #{{ alarm.id }}</td>
                        <td class="bold1">
                          {{
                            $dateFormat.formatDateMonthYear(
                              alarm.alarm_start_datetime
                            )
                          }}
                        </td>
                      </tr>
                      <tr>
                        <td class="bold1">
                          Type :{{ alarm.alarm_type ?? "---" }}
                        </td>
                        <td class="bold1">
                          Category :
                          <span v-if="alarm.alarm_category == 1">Critical</span>
                          <span v-else-if="alarm.alarm_category == 2"
                            >Medium</span
                          >
                          <span v-else-if="alarm.alarm_category == 3">Low</span>
                        </td>
                      </tr>
                      <tr>
                        <td class="text-truncate">
                          Property :{{
                            customer.buildingtype
                              ? customer.buildingtype.name
                              : "---"
                          }}
                        </td>
                        <td class="text-truncate">
                          Zone: {{ alarm.zone_data?.sensor_name ?? "---" }}
                        </td>
                      </tr>
                      <tr>
                        <td class="text-truncate">
                          Location: {{ alarm.zone_data?.location ?? "---" }}
                        </td>
                        <td class="text-truncate">
                          Sensor :
                          {{ alarm.zone_data?.sensor_type ?? "---" }}
                        </td>
                      </tr>
                    </table>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-col>
    </v-row>

    <v-row style="padding: 0px; padding-top: 10px">
      <v-col style="padding: 0px">
        <v-card elevation="2" v-if="alarm.device.customer.contacts.length == 0">
          <v-card-text>
            <div style="height: 250px">No Contacts Found</div>
          </v-card-text></v-card
        >
        <v-tabs
          v-if="alarm.device.customer"
          height="25"
          center-active
          right
          class="customerEmergencyContactTabs1 customerEmergencyContactTabsBGcolor1222"
        >
          <v-tab
            style="font-size: 10px; min-width: 50px !important"
            v-if="
              (alarm.device.customer &&
                contact.address_type.toLowerCase() == 'primary') ||
              contact.address_type.toLowerCase() == 'secondary' ||
              contact.address_type.toLowerCase() == 'security'
            "
            v-for="(contact, index) in alarm.device.customer.contacts"
            :key="contact.id"
          >
            {{ contact.address_type }}</v-tab
          >

          <v-tab-item
            v-if="
              alarm.device.customer &&
              (contact.address_type.toLowerCase() == 'primary' ||
                contact.address_type.toLowerCase() == 'secondary' ||
                contact.address_type.toLowerCase() == 'security')
            "
            v-for="(contact, index) in alarm.device.customer.contacts"
            :key="contact.id + 50"
            name="index+50"
          >
            <v-card elevation="2">
              <v-card-text :style="' padding: 2px; '">
                <EventCustomerTabContacts
                  :alarmId="alarm.id"
                  v-if="contact"
                  :alarm="alarm"
                  :customer="alarm.device.customer"
                  :contact_type="contact.address_type"
                  :key="contact.address_type"
                  @emitreloadEventNotesStep1="emitreloadEventNotes2"
                  :browserHeight="browserHeight"
                  :qrcode="qrcode"
                />
              </v-card-text>
            </v-card>
          </v-tab-item>
        </v-tabs>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import EventCustomerTabContacts from "../../components/Operator/EventCustomerTabContacts.vue";

export default {
  components: { EventCustomerTabContacts },
  props: [
    "_id",
    "isPopup",
    "customer",
    "alarm",
    "colorcodes",
    "browserHeight",
    "qrcode",
  ],
  data: () => ({
    tab: "",
  }),
  computed: {},
  mounted() {},
  created() {},
  watch: {},
  methods: {
    emitreloadEventNotes2() {
      console.log("emitreloadEventNotes2");

      this.$emit("emitreloadEventNotes3");
    },
    emitShowCustomerInfoTabs(status) {
      this.$emit("emitShowCustomerInfoTabs", status);
    },
    getAlarmColorObject(alarm, customer = null) {
      if (alarm) {
        if (this.colorcodes[alarm.alarm_type.toLowerCase()])
          return this.colorcodes[alarm.alarm_type.toLowerCase()];
        if (alarm.alarm_status == 1) {
          return this.colorcodes.alarm;
        }
      }
      // else if (alarm.alarm_status == 0) {
      //   return this.colorcodes.closed;
      // }
      //if (
      //   alarm.customer &&
      //   this.findanyArmedDevice(alarm.customer.devices)
      // ) {
      //   return this.colorcodes.armed;
      // }
      else if (customer) {
        if (this.findAnyDeviceisOffline(customer.devices) > 0) {
          return this.colorcodes.offline;
        } else if (this.findanyArmedDevice(customer.devices)) {
          return this.colorcodes.armed;
        } else if (this.findanyDisamrDevice(customer.devices) > 0) {
          return this.colorcodes.disarm;
        }
      }
      // console.log(
      //   "findAnyDeviceisOffline",
      //   this.findAnyDeviceisOffline(item.devices)
      // );
      // console.log(alarm.alarm_status);

      return this.colorcodes.offline;
    },
  },
};
</script>
