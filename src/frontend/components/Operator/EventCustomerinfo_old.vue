<template>
  <div>
    <v-row style="min-width: 700px">
      <v-col>
        <div v-if="alarm && customer">
          <v-row style="height: auto; width: 100%">
            <v-col style="max-width: 40%; line-height: 23px">
              <v-row>
                <v-col
                  style="
                    max-width: 100px;
                    padding: 0px;

                    text-align: center;
                  "
                >
                  <img
                    :src="customer.profile_picture"
                    style="
                      width: 100%;
                      border-radius: 10px;
                      height: 100%;
                      vertical-align: bottom;
                    "
                  /> </v-col
                ><v-col
                  style="
                    padding: 0px;
                    font-size: 12px;
                    padding-left: 10px;
                    max-width: 200px;
                  "
                >
                  <div style="color: red">
                    {{
                      customer.buildingtype ? customer.buildingtype.name : "---"
                    }}
                  </div>
                  <div
                    style="height: auto; overflow: hidden; line-height: 23px"
                  >
                    <div style="font-weight: bold">
                      {{
                        customer.primary_contact
                          ? customer.primary_contact.first_name +
                            " " +
                            customer.primary_contact.last_name
                          : "---"
                      }}
                    </div>
                    <div style="font-weight: bold">
                      {{ customer.building_name || "---" }}
                    </div>
                    {{ customer.city }}
                    <br />
                    {{ customer.primary_contact?.phone1 || "---" }}
                  </div>
                </v-col>
              </v-row>
            </v-col>
            <v-col
              style="
                max-width: 200px;
                margin: auto;
                text-align: center;
                font-size: 12px;
                line-height: 23px;
                padding-top: 0px;
                padding-bottom: 0px;
              "
            >
              <img
                :title="alarm.alarm_type"
                style="width: 30px"
                :src="getAlarmColorObject(alarm, customer).image + '?3=3'"
              />
              <div style="color: blue">
                {{ alarm.alarm_type ?? "---" }},{{
                  alarm.zone_data?.location ?? "---"
                }}
              </div>
              <div style="color: red">
                <!-- {{ alarm.zone ?? "---" }} -->
                {{ alarm.zone_data?.sensor_type ?? "---" }}

                <!-- ,{{ alarm.area ?? "---" }} -->
                ,{{ alarm.zone_data?.sensor_name ?? "---" }}
              </div>
            </v-col>

            <v-col
              style="
                padding: 0px;
                font-size: 12px;
                padding-left: 10px;
                line-height: 15px;
              "
            >
              <table
                style="
                  font-size: 12px;
                  line-height: 23px;
                  padding-left: 10px;
                  border-collapse: collapse;
                "
              >
                <tbody>
                  <tr style="border-bottom: 1px solid #ddd">
                    <td style="color: red; padding: 0 10px">Event Id</td>
                    <td style="color: red; padding: 0 10px">#{{ alarm.id }}</td>
                  </tr>
                  <tr style="border-bottom: 1px solid #ddd">
                    <td style="padding: 0 10px">Status</td>
                    <td style="padding: 0 10px">
                      <div
                        v-if="
                          alarm.forwarded == true && alarm.alarm_status == 1
                        "
                      >
                        Forwarded
                      </div>
                      <div v-else-if="alarm.alarm_status == 1">Open</div>
                      <div v-else-if="alarm.alarm_status == 0">Closed</div>
                    </td>
                  </tr>
                  <tr style="border-bottom: 1px solid #ddd">
                    <td style="padding: 0 10px">Alarm Category</td>
                    <td style="padding: 0 10px">
                      <div v-if="alarm.alarm_category == 1">Critical</div>
                      <div v-if="alarm.alarm_category == 2">Medium</div>
                      <div v-if="alarm.alarm_category == 3">Low</div>
                    </td>
                  </tr>
                  <tr style="border-bottom: 1px solid #ddd">
                    <td style="padding: 0 10px">Start Time</td>
                    <td style="padding: 0 10px">
                      {{
                        $dateFormat.formatDateMonthYear(
                          alarm.alarm_start_datetime
                        )
                      }}
                    </td>
                  </tr>
                  <tr style="border-bottom: 1px solid #ddd">
                    <td style="padding: 0 10px">End Time</td>
                    <td style="padding: 0 10px">
                      <div v-if="alarm.alarm_end_datetime">
                        {{
                          $dateFormat.formatDateMonthYear(
                            alarm.alarm_end_datetime
                          )
                        }}
                      </div>
                      <div else>---</div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </v-col>
          </v-row>
        </div>
      </v-col>
    </v-row>

    <v-row>
      <v-col>
        <v-tabs
          background-color="#DDD"
          height="25"
          center-active
          right
          class="customerEmergencyContactTabs"
        >
          <v-tab
            v-if="
              contact.address_type.toLowerCase() == 'primary' ||
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
              contact.address_type.toLowerCase() == 'primary' ||
              contact.address_type.toLowerCase() == 'secondary' ||
              contact.address_type.toLowerCase() == 'security'
            "
            v-for="(contact, index) in alarm.device.customer.contacts"
            :key="contact.id + 50"
            name="index+50"
          >
            <v-card class="elevation-1">
              <EventCustomerTabContacts
                :alarmId="alarm.id"
                v-if="contact"
                :customer="alarm.device.customer"
                :contact_type="contact.address_type"
                :key="contact.address_type"
                @emitreloadEventNotesStep1="emitreloadEventNotes2"
              />
            </v-card>
          </v-tab-item>
        </v-tabs>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import EventCustomerTabContacts from "./EventCustomerTabContacts.vue";

export default {
  components: { EventCustomerTabContacts },
  props: ["_id", "isPopup", "customer", "alarm", "colorcodes"],
  data: () => ({
    tab: "",
  }),
  computed: {},
  mounted() {},
  created() {},
  watch: {},
  methods: {
    emitreloadEventNotes2() {
      this.$emit("emitreloadEventNotes3");
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
