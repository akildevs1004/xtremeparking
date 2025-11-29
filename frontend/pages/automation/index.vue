<template>
  <v-tabs color="deep-purple accent-4" right>
    <v-tab>Absent</v-tab>
    <v-tab>Attendance</v-tab>
    <v-tab>Device</v-tab>
    <v-tab>Document</v-tab>

    <v-tab-item> <SetupAbsent /> </v-tab-item>
    <v-tab-item> <SetupAttendance /> </v-tab-item>
    <v-tab-item> <SetupDeviceNotificationAutomation /> </v-tab-item>
    <v-tab-item> <DocumentExpiry /> </v-tab-item>
  </v-tabs>
</template>
