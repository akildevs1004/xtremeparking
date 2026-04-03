<template>
  <div>
    <v-col cols="12" class="text-right" style="padding-top: 0px">
      <v-btn color="primary" dense small> Add Device </v-btn>
    </v-col>
    <v-data-table :headers="headers" :items="items" class="elevation-0">
      <template v-slot:item.device="{ item }">
        <div>{{ item.device }}</div>
        <div class="secondary-value">{{ item.serial_number }}</div>
      </template>
      <template v-slot:item.sensor="{ item }">
        <div>{{ item.sensor }}</div>
        <div class="secondary-value">{{ item.type }}</div>
      </template>

      <template v-slot:item.alarm_type="{ item }">
        <div>{{ item.alarm_type }}</div>
      </template>

      <template v-slot:item.zone="{ item }">
        <div>{{ item.zone }}</div>
        <div class="secondary-value">{{ item.area }}</div>
      </template>
      <template v-slot:item.delay="{ item }">
        <div>{{ item.delay == 0 ? "No" : item.delay }}</div>
      </template>
      <template v-slot:item.status="{ item }">
        <div style="color: red" v-if="item.status == 1">
          <v-img style="width: 30px" src="/icons/device_status_open.png">
          </v-img>
        </div>
        <div v-else>
          <v-img width="30px" src="/icons/device_status_close.png"> </v-img>
        </div>
      </template>
      <template v-slot:item.alarm="{ item }">
        <div style="color: red" v-if="item.alarm == 'ON'">
          <v-icon color="red">mdi mdi-alarm-light-outline</v-icon>
        </div>
        <div v-else>
          <v-icon>mdi mdi-alarm-light-outline</v-icon>
        </div>
      </template>
      <template v-slot:item.options="{ item }">
        <v-menu bottom left>
          <template v-slot:activator="{ on, attrs }">
            <v-btn dark-2 icon v-bind="attrs" v-on="on">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list width="120" dense>
            <v-list-item v-if="can('branch_view')" @click="viewItem(item)">
              <v-list-item-title style="cursor: pointer">
                <v-icon color="secondary" small> mdi-eye </v-icon>
                View
              </v-list-item-title>
            </v-list-item>
            <v-list-item v-if="can('branch_edit')" @click="editItem(item)">
              <v-list-item-title style="cursor: pointer">
                <v-icon color="secondary" small> mdi-pencil </v-icon>
                Edit
              </v-list-item-title>
            </v-list-item>
            <v-list-item v-if="can('branch_delete')" @click="deleteItem(item)">
              <v-list-item-title style="cursor: pointer">
                <v-icon color="error" small> mdi-delete </v-icon>
                Delete
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>
  </div>
</template>

<script>
export default {
  data() {
    return {
      headers: [
        { text: "#", value: "sno" },
        { text: "Device", value: "device" },
        { text: "Sensor", value: "sensor" },
        { text: "Location", value: "location" },

        { text: "Zone", value: "zone" },
        { text: "Alarm Type", value: "alarm_type" },

        { text: "Delay(Min)", value: "delay" },
        //{ text: "24 Hrs", value: "hrs_24" },
        { text: "Status", value: "status" },
        { text: "Alarm", value: "alarm" },
        { text: "Options", value: "options" },
      ],
      items: [
        {
          sno: "01",
          device: "Control Panel",
          sensor: "PIR", //zone
          alarm_type: "Burglary", //zone and device
          location: "Hall",
          type: "Wired", //
          zone: "Z1", //zone
          area: "A1", //zone
          delay: "0", //
          hrs_24: "-", //zone
          status: "1",
          alarm: "OFF",
          serial_number: "111111111",
        },
        {
          sno: "02",
          device: "Control Panel",
          sensor: "Door Contact ",
          alarm_type: "Burglary",
          location: "Main Door ",
          type: "Wired",
          zone: "Z2",
          area: "A1",
          delay: "1",
          hrs_24: "-",
          status: "1",
          alarm: "OFF",
          serial_number: "111111111",
        },
        {
          sno: "03",
          device: "Control Panel",
          sensor: "Panic button",
          alarm_type: "Medical",
          location: "Bed room ",
          type: "Wired",
          zone: "Z3",
          area: "A1",
          delay: "1",
          hrs_24: "-",
          status: "0",
          alarm: "OFF",
          serial_number: "111111111",
        },
        {
          sno: "04",
          device: "Arduino1",
          sensor: "Smoke sensor",
          alarm_type: "Temperature",
          location: "Kitchen",
          type: "Wireless",
          zone: "Z4",
          area: "A1",
          delay: "1",
          hrs_24: "-",
          status: "0",
          alarm: "ON",
          serial_number: "111111111",
        },
        {
          sno: "05",
          device: "Control Panel",
          sensor: "Beam Sensor",
          alarm_type: "Burglary",
          location: "Garden Lift",
          type: "Wireless",
          zone: "Z5",
          area: "A1",
          delay: "1",
          hrs_24: "Yes",
          status: "0",
          alarm: "OFF",
          serial_number: "111111111",
        },
        {
          sno: "06",
          device: "ESP32",
          sensor: "PIR",
          alarm_type: "Burglary",
          location: "Hall",
          type: "Wired",
          zone: "Z6",
          area: "A1",
          delay: "1",
          hrs_24: "-",
          status: "0",
          alarm: "OFF",
          serial_number: "111111111",
        },
      ],
    };
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
  },
};
</script>
