<template>
  <v-app :style="scaleStyle">
    <div class="text-center ma-2">
      <v-snackbar :timeout="1000" v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <!-- <AudioSoundPlay v-if="activeAudio" :key="notificationsMenuItems.length"
      :notificationsMenuItemsCount="notificationsMenuItems.length" /> -->
    <v-navigation-drawer v-if="items.length > 0" expand-on-hover rail v-model="drawer" :clipped="clipped" fixed app
      :color="sideBarcolor" :width="!miniVariant ? '80px!important' : '150px'" style="z-index: 100">
      <v-list v-for="(i, idx) in items" :key="idx" style="padding: 5px 0 0 0px" :title="i.title">
        <v-list-item :to="i.module != 'dashboard' ? i.to : ''" @click="getTopMenuItems(i)" router v-if="!i.hasChildren"
          :class="!miniVariant || 'pl-2'" vertical style="display: inline-block" :title="i.title">
          <v-list-item-icon class="ma-2" :title="i.title">
            <v-icon>{{ i.icon }} </v-icon>
          </v-list-item-icon>

          <v-list-item-title class="text-center p-2 text--white">
            {{ i.title }}&nbsp;
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-else :class="!miniVariant || 'pl-2'" @click="i.open_menu = !i.open_menu">
          <v-list-item-icon class="mx-2">
            <v-icon>{{ i.icon }}</v-icon>
            <v-icon v-if="miniVariant" small>{{ !i.open_menu ? "mdi-chevron-down" : "mdi-chevron-up" }}
            </v-icon>
          </v-list-item-icon>

          <v-list-item-title>{{ i.title }} </v-list-item-title>
          <v-icon small>{{ !i.open_menu ? "mdi-chevron-down" : "mdi-chevron-up" }}
          </v-icon>
        </v-list-item>
        <div v-if="i.open_menu">
          <div v-for="(j, jdx) in i.hasChildren" :key="jdx">
            <v-tooltip style="margin-left: 25px" v-if="miniVariant" right color="primary">
              <template v-slot:activator="{ on, attrs }">
                <v-list-item v-bind="attrs" v-on="on" style="min-height: 0" :to="j.to" class="submenutitle">
                  <v-list-item-title class="my-2">
                    {{ j.title }}
                  </v-list-item-title>

                  <v-list-item-icon :style="miniVariant ? 'margin-left: -54px;' : ''">
                    <v-icon :to="j.to" :style="miniVariant ? 'margin-left: 12px;' : ''">
                      {{ j.icon }}
                    </v-icon>
                  </v-list-item-icon>
                </v-list-item>
              </template>
              <span>{{ j.title }}</span>
            </v-tooltip>

            <v-list-item v-else style="min-height: 0; margin-left: 50px" :to="j.to" class="submenutitle">
              <v-list-item-title v-if="can(j.menu)" class="my-2" style="text-align: left">
                {{ j.title }}
              </v-list-item-title>
            </v-list-item>
          </div>
        </div>
      </v-list>
    </v-navigation-drawer>
    <v-app-bar :clipped-left="clipped" fixed app :style="$nuxt.$route.name == 'index' ? 'z-index: 100000' : ''">
      <v-app-bar-nav-icon v-if="displayTopMenu" @click.stop="drawer = !drawer" />
      <span class="text-overflow" style="cursor: pointer; margin: auto; display: flex" @click="gotoHomePage()">
        <!-- <img
          v-if="$vuetify.theme.dark"
          class="logo-image"
          title="Parking Control Panel - Xtremeguard"
          src="../static/logo33.jpeg"
          style="width: 60px !important"
        /> -->

        <img v-if="$vuetify.theme.dark" class="logo-image" title="Parking Control Panel - Xtremeguard"
          src="../static/logo.png" style="width: 150px !important" />
        <img v-else class="logo-image" title="Parking Control Panel - Xtremeguard" src="../static/logo_header1.png"
          style="width: 150px !important" />
      </span>
      <v-spacer></v-spacer>
      <!-- header-menu top menu -->
      <span class="header-menu">
        <template v-if="
          displayTopMenu &&
          (getLoginType == 'company' ||
            getLoginType == 'branch' ||
            getLoginType == 'department' ||
            ($auth.user?.role?.role_type?.toLowerCase() != 'guard' &&
              $auth.user?.role?.role_type?.toLowerCase() != 'host'))
        ">
          <v-row align="center" justify="space-around" class="header-menu-row">
            <v-col v-for="(items, index) in controlpanel_top_menu" :key="index" class="header-menu-box">
              <!-- <v-btn
                small
                text
                :elevation="0"
                :style="
                  menuProperties[items.menu]?.selected
                    ? 'background-color: #007d61; color: white;'
                    : ''
                "
                @click="setSubLeftMenuItems(items.menu, items.to)"
              >
                <b class="header-menu-item">{{ items.title }} </b>
              </v-btn> -->

              <v-btn class="header-menu-button" small text :elevation="0" :color="menuProperties[items.menu] &&
                menuProperties[items.menu].selected
                " :style="$vuetify.theme.dark && menuProperties[items.menu]?.selected
                  ? 'background-color: #007d61; color: white;'
                  : ''
                  " fill @click="setSubLeftMenuItems(items.menu, items.to)">
                <b class="header-menu-item"> {{ items.title }} </b>
              </v-btn>
            </v-col>
            <!-- <v-col class="header-menu-box">
              <v-menu offset-y>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn color="primary" dark v-bind="attrs" v-on="on">
                    Employees
                  </v-btn>
                </template>
    <v-list>
      <v-list-item v-for="(items, index) in company_top_menu" :key="index">
        <v-list-item-title class="text-left" @click="setSubLeftMenuItems(items.menu, items.to)" :color="
                        menuProperties[items.menu] &&
                        menuProperties[items.menu].selected
                      ">
          {{ items.title }}</v-list-item-title>
      </v-list-item>
    </v-list>
    </v-menu>
    </v-col> -->
          </v-row>
        </template>
      </span>

      <span>
        <!-- <v-icon
          class="violet--text"
          @click="showGlobalsearchPopup"
          style="text-align: center"
          >mdi-magnify</v-icon
        > -->
        <!-- <v-text-field
          style="
            width: 200px;
            margin-top: 100px !important;
            max-height: 30px !important;
          "
          max-height="10px"
          class="custom-text-field-height mt-10 global-search-textbox"
          color="black"
          outlined
          dense
          height:="20px"
          border-color="black"
          append-icon="mdi-magnify"
          v-model="globalsearch"
        ></v-text-field> -->
        <v-form autocomplete="off">
          <!-- <v-text-field
            autocomplete="off"
            @keyup.enter="showGlobalsearchPopup"
            @keydown="showGlobalsearchPopup"
            @keyup="showGlobalsearchPopup"
            style="width: 170px; padding-left: 50px"
            height="26px"
            class="custom-text-field-height pt-7 global-search-textbox"
            @click="showGlobalsearchPopup"
            color="black"
            outlined
            dense
            border-color="black"
            prepend-inner-icon="mdi-magnify"
            placeholder="Search"
            v-model="globalsearch"
          ></v-text-field> -->
        </v-form>
      </span>

      <v-spacer></v-spacer>
      <span class="pl-5">
        <TopMenuClock v-if="displayTopMenu"></TopMenuClock>
      </span>
      <span class="pl-2 pr-2" @click="toggleTheme()" style="cursor: pointer"><v-icon>mdi-palette</v-icon></span>
      <div v-if="$auth && $auth.user?.role_id > 1" style="font-size: 10px">
        {{ $auth.user.name }}
      </div>
      <v-menu style="z-index: 9999 !important" bottom origin="center center" offset-y transition="scale-transition">
        <template v-slot:activator="{ on, attrs }" style="z-index: 9999 !important">
          <v-btn icon v-bind="attrs" v-on="on" style="display: none;">
            <v-badge :color="'  ' + pendingNotificationsCount > 0 ? 'red' : 'green'" :content="pendingNotificationsCount == ''
              ? '0'
              : pendingNotificationsCount
              " style="top: 10px; left: -19px; z-index: 9999 !important">
              <v-icon style="top: -10px; left: 10px" class="violet--text">mdi mdi-bell-ring</v-icon>
            </v-badge>
          </v-btn>
        </template>
        <v-list style="z-index: 9999">
          <v-list-item style="height: 30px; padding-left: 5px" :class="notificationsMenuItems.length > 0 &&
            index != notificationsMenuItems.length - 1
            ? 'border-bottom'
            : ''
            " @click="showPopupAlarmStatus()" v-for="(item, index) in notificationsMenuItems" :key="index">
            <v-list-item-content @click="showPopupAlarmStatus()">
              <v-list-item-title class="align-left text-left">
                <v-row style="">
                  <v-col cols="2" class="align-right text-right pr-1">
                    <img :src="'/device-icons/' + item.icon" style="width: 20px; vertical-align: middle" /></v-col>
                  <v-col cols="10">
                    <span style="font-size: 14px">
                      <span>
                        {{ item.title }}
                        <div class="secondary-value" v-if="item.date_from">
                          {{ $dateFormat.formatDateMonthYear(item.date_from) }}
                        </div>
                      </span>
                    </span>
                  </v-col>
                </v-row>
              </v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </v-menu>
      <v-menu nudge-bottom="50" transition="scale-transition" origin="center center" bottom left min-width="200"
        nudge-left="20" style="z-index: 9999">
        <template v-slot:activator="{ on, attrs }">
          <v-btn icon color="red" v-bind="attrs" v-on="on">
            <v-avatar size="35" style="border: 0px solid #6946dd">
              <v-img class="company_logo" :src="getLogo"></v-img>
            </v-avatar>
          </v-btn>
        </template>

        <v-list light nav dense style="z-index: 9999">
          <v-list-item-group color="primary">
            <v-list-item v-if="
              displayTopMenu && $auth && $auth.user?.user_type == 'company'
            " @click="goToCompany()">
              <v-list-item-icon>
                <v-icon>mdi-account-multiple-outline</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title class="black--text">Profile</v-list-item-title>
              </v-list-item-content>
            </v-list-item>

            <v-list-item @click="logout">
              <v-list-item-icon>
                <v-icon>mdi-logout</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title class="black--text">Logout</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list-item-group>
        </v-list>
      </v-menu>

      <!-- <v-btn icon plan @click="toggleTheme">
        <v-icon
          >mdi-{{
            $vuetify.theme.dark ? "white-balance-sunny" : "moon-waning-crescent"
          }}</v-icon
        >
      </v-btn>  -->

      <!-- <v-btn
        v-if="getLoginType == 'company' || getLoginType == 'branch'"
        icon
        plan
        @click="goToSettings()"
        class="mr-3"
        ><v-icon class="violet--text" style="text-align: center"
          >mdi-cog</v-icon
        ></v-btn
      > -->

      <!-- <v-menu
        bottom
        origin="center center"
        offset-y
        transition="scale-transition"
      >
        <template v-slot:activator="{ on, attrs }">
          <v-badge
            v-bind="attrs"
            v-on="on"
            :color="pendingLeavesCount > 0 ? 'red' : 'red'"
            content="1"
            style="top: 10px; left: -19px"
          >
            <v-icon style="top: -10px; left: 10px" class="violet--text"
              >mdi mdi-bell-ring</v-icon
            >
          </v-badge>
        </template>

        <v-list>
          <v-list-item v-for="(item, i) in notificationItems" :key="i">
            <v-list-item-title>{{ item.title }}</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu> -->
      <!-- <label class=" ">
        <v-badge
          v-if="pendingLeavesCount > 0"
          @click="navigateToLeavePage()"
          :color="pendingLeavesCount > 0 ? 'red' : 'white'"
          :content="pendingLeavesCount"
        >
          <v-icon class="violet--text" @click="navigateToLeavePage()"
            >mdi mdi-bell-ring</v-icon
          >
        </v-badge>
        <v-badge v-else @click="navigateToLeavePage()" content="0">
          <v-icon
            style="color: #e91919 !important"
            @click="navigateToLeavePage()"
            >mdi mdi-bell-ring</v-icon
          >
        </v-badge>
      </label> -->
      <v-snackbar top="top" v-model="snackNotification" location="right" :timeout="5000"
        :color="snackNotificationColor">
        {{ snackNotificationText }}

        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snackNotification = false">
            Close
          </v-btn>
        </template>
      </v-snackbar>
      <v-dialog v-model="globalSearchPopup" :width="globalSearchPopupWidth">
        <v-card>
          <v-card-title dense class="popup_background">
            Global Search
            <v-spacer></v-spacer>
            <v-icon @click="globalSearchPopup = false" outlined>
              mdi mdi-close-circle
            </v-icon>
          </v-card-title>
          <v-card-text> </v-card-text>
        </v-card>
      </v-dialog>
      <v-dialog v-model="dialogAlarmPopupNotificationStatus" transition="dialog-top-transition" max-width="1000"
        style="z-index: 9999" key="key">
        <!-- <template v-slot:activator="{ on, attrs }">
          <v-btn color="primary" v-bind="attrs" v-on="on">From the top</v-btn>
        </template> -->
        <template v-slot:default="dialog">
          <v-card style="z-index: 9999">
            <v-card-title dense class="error popup_background_red" style="
                text-align: center !important;
                padding-left: 30%;
                color: #fff !important;
                background-color: red;
              ">
              <div style="text-align: right; width: 60%">
                Attention : Alarm Notification(s)
              </div>
              <v-spacer></v-spacer>
              <v-icon style="color: #fff" @click="wait5MinutesNextNotification()" outlined>
                mdi mdi-close-circle
              </v-icon>
            </v-card-title>
            <!-- <v-card-title
              class="error alarm-popup-header"
              background-color="error"
              style="
                text-align: center;
                padding-left: 35%;
                color: #fff !important;
              "
              >Attention : Alarm Notification(s)
              <v-spacer></v-spacer>
              <v-icon color="white" @click="wait5MinutesNextNotification()"
                >mdi-close-circle-outline</v-icon
              >
            </v-card-title> -->
            <v-card-text style="padding-left: 0px">
              <AlarmPopupAllAlarmEvents :items="notificationAlarmDevicesContent"
                @callwait5MinutesNextNotification="wait5MinutesNotification" @callReset5Minutes="Reset5Minutes"
                :key="popupKey" :alarm_icons="alarm_icons" />
              <!-- <v-row
                v-for="(device, index) in notificationAlarmDevicesContent"
                :key="index"
              >
                <v-col cols="2"
                  ><img src="../static/fire2.png" width="50px"
                /></v-col>
                <v-col cols="10" class="pl-4">
                  <div class="pa-3" style="font-size: 20px; font-weight: bold">
                    Fire Alarm Triggered at :
                    {{ $dateFormat.format5(device.alarm_start_datetime) }}
                  </div>
                  <div class="bold pa-1">Device Name :{{ device.name }}</div>
                  <div class="bold pa-1">
                    Branch Name :{{ device?.branch?.branch_name }}
                  </div>
                  <div class="bold pa-1">
                    Device Location :{{ device?.branch?.location }}
                  </div>
                </v-col>
              </v-row>

              <div></div>-->
              <!-- <div>
                 <div style="color: green">
                  <strong>Note: </strong>All Branch Level Doors are Opened
                </div>
                <br />
                Check Devices list and Turn off Alarm to Close this popup.

                <v-btn
                  color="error"
                  @click="
                    goToPage('/device');
                    dialogAlarmPopupNotificationStatus = false;
                  "
                  >View Devices</v-btn
                >
              </div> -->
            </v-card-text>
            <!-- <v-card-actions class="justify-end">
              <v-btn text @click="dialogAlarmPopupNotificationStatus = false">Close</v-btn>
            </v-card-actions> -->
          </v-card>
        </template>
      </v-dialog>
    </v-app-bar>

    <v-main class="main_bg" :style="items.length == 0
      ? 'padding-left:  0px;'
      : miniVariant && items.length > 0
        ? 'padding-left: 60px;'
        : 'padding-left: 140px;'
      ">
      <v-container style="max-width: 100%; padding-left: 5px; padding: 8px">
        <nuxt />
      </v-container>
    </v-main>

    <!-- <v-navigation-drawer
      v-model="rightDrawer"
      :clipped="true"
      :right="right"
      fixed
      style="z-index: 1000"
    >
      <v-row style="margin-top: 50px">
        <v-col>
          <v-card class="pa-2" elevation="0">
            <v-col cols="12">
              <div class="mb-3">
                <Strong>Theme</Strong>
              </div>
              <div
                class="btn-group"
                role="group"
                aria-label="Basic radio toggle button group"
              >
                <input
                  type="radio"
                  class="btn-check"
                  name="theme"
                  id="light"
                  autocomplete="off"
                  @click="changeTheme('light')"
                />
                <label class="btn" :class="'btn-outline-dark'" for="light"
                  >Light</label
                >
                <input
                  type="radio"
                  class="btn-check"
                  name="theme"
                  id="dark"
                  autocomplete="off"
                  @click="changeTheme('dark')"
                />
                <label class="btn btn-outline-dark" for="dark">Dark</label>
              </div>
            </v-col>
            <v-divider></v-divider>
            <v-col cols="12">
              <div class="mb-3">
                <Strong>Top Bar</Strong>
              </div>
              <div class="d-flex">
                <v-btn
                  class="mx-2 stg-color-icon"
                  fab
                  dark
                  x-small
                  color="primary"
                  @click="changeTopBarColor('primary')"
                ></v-btn>
                <v-btn
                  class="mx-2 stg-color-icon"
                  fab
                  dark
                  x-small
                  color="error"
                  @click="changeTopBarColor('error')"
                ></v-btn>
                <v-btn
                  class="mx-2 stg-color-icon"
                  fab
                  dark
                  x-small
                  color="indigo"
                  @click="changeTopBarColor('indigo')"
                ></v-btn>
                <v-btn
                  class="mx-2 stg-color-icon"
                  fab
                  dark
                  x-small
                  color="background"
                  @click="changeTopBarColor('background')"
                ></v-btn>
              </div>
            </v-col>
            <v-divider></v-divider>
            <v-col cols="12">
              <div class="mb-3">
                <Strong>Side Bar</Strong>
              </div>
              <div class="d-flex">
                <v-btn
                  class="mx-2 stg-color-icon"
                  fab
                  dark
                  x-small
                  color="primary"
                  @click="changeSideBarColor('primary')"
                ></v-btn>
                <v-btn
                  class="mx-2 stg-color-icon"
                  fab
                  dark
                  x-small
                  color="error"
                  @click="changeSideBarColor('error')"
                ></v-btn>
                <v-btn
                  class="mx-2 stg-color-icon"
                  fab
                  dark
                  x-small
                  color="indigo"
                  @click="changeSideBarColor('indigo')"
                ></v-btn>
                <v-btn
                  class="mx-2 stg-color-icon"
                  fab
                  dark
                  x-small
                  color="background"
                  @click="changeSideBarColor('background')"
                >
                </v-btn>
              </div>
            </v-col>
          </v-card>
        </v-col>
      </v-row>
    </v-navigation-drawer> -->
  </v-app>
</template>
<style lang="scss">
@import "@/assets/variables.scss";
@import "@/assets/darktheme.scss";
@import "@/assets/whitetheme.scss";
@import "@/assets/common.scss";
</style>

<script>
//parking
import company_menus from "../menus/parking_menu_left.json";
import controlpanel_top_menu from "../menus/parking_menu_top.json";


//car washing
// import company_menus from "../menus/car_washing_menu_left.json";
// import controlpanel_top_menu from "../menus/car_washing_menu_top.json";



import employee_menus from "../menus/employee.json";
import branch_menus from "../menus/branch.json";
import guard_menus from "../menus/guard.json";
import host_menus from "../menus/host.json";
import company_top_menu from "../menus/company_modules_top.json";
import employee_top_menu from "../menus/employee_modules_top.json";
import AlarmPopupAllAlarmEvents from "../components/Alarm/PopupAllAlarmEvents.vue";
import TopMenuClock from "../components/Operator/TopMenuClock.vue";
import AudioSoundPlay from "../components/Alarm/AudioSoundPlay.vue";

export default {
  theme: {
    dark: true, // 👈 Default dark mode
  },
  head() {
    return {
      link: [
        {
          rel: "stylesheet",
          href: "https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap",
        },
      ],
    };
  },
  components: {
    AlarmPopupAllAlarmEvents,
    TopMenuClock,
    AudioSoundPlay,
  },
  data() {
    return {

      scale: 1,
      displayTopMenu: false,
      activeAudio: true,
      popupKey: 1,
      key: 1,
      snackbar: false,
      response: "",
      alarm_icons: {},

      wait5Minutes: false,
      globalSearchPopupWidth: "500px",
      globalsearch: "",
      globalSearchPopup: false,
      notificationsMenuItems: [],
      notificationAlarmDevicesContent: {},
      selectedBranchName: "All Branches",
      seelctedBranchId: "",
      branch_id: "",
      menuProperties: {


        parking_dashboard: {
          elevation: 0,
          selected: "",
        },
        parking_reports: {
          elevation: 0,
          selected: "",
        },
        parking_members: {
          elevation: 0,
          selected: "",
        },
        parking_blocked: {
          elevation: 0,
          selected: "",
        },
        parking_invoices: {
          elevation: 0,
          selected: "",
        },
        parking_cameras: {
          elevation: 0,
          selected: "",
        },

        alarm_dashboard: {
          elevation: 0,
          selected: "",
        },
        alarm_intruderdashboard: {
          elevation: 0,
          selected: "",
        },

        alarm_map: {
          elevation: 0,
          selected: "",
        },
        alarm_customersmap: {
          elevation: 0,
          selected: "",
        },
        alarm_allevents: {
          elevation: 0,
          selected: "",
        },
        alarm_supervisorevents: {
          elevation: 0,
          selected: "",
        },
        alarm_alarms: {
          elevation: 0,
          selected: "",
        },
        alarm_customers: {
          elevation: 0,
          selected: "",
        },
        alarm_reports: {
          elevation: 0,
          selected: "",
        },
        alarm_security: {
          elevation: 0,
          selected: "",
        },
        alarm_technicians: {
          elevation: 0,
          selected: "",
        },
        alarm_armed_reports: {
          elevation: 0,
          selected: "",
        },
        alarm_tickets: {
          elevation: 0,
          selected: "",
        },
        alarm_devices: {
          elevation: 0,
          selected: "",
        }, devices_devices: {
          elevation: 0,
          selected: "",
        },
        settings: {
          elevation: 0,
          selected: "",
        },
        sales: {
          elevation: 0,
          selected: "",
        },
      },

      topMenu_Selected: "dashboard",
      company_menus,
      employee_menus,
      branch_menus,
      guard_menus,
      host_menus,
      controlpanel_top_menu,
      company_top_menu,
      employee_top_menu,
      pendingLeavesCount: 0,
      pendingNotificationsCount: 0,
      snackNotificationText: "",
      snackNotification: false,
      snackNotificationColor: "black",
      socketConnectionStatus: 0,

      right: true,
      rightDrawer: false,
      color: "",
      sideBarcolor: "background",
      year: new Date().getFullYear(),
      dropdown_menus: [{ title: "setting" }, { title: "logout" }],

      clipped: false,
      open_menu: [],
      drawer: true,
      fixed: false,
      order_count: "",
      logo_src: "",
      logo_src2: "",
      items: [],
      modules: {
        module_ids: [],
        module_names: [],
      },
      clipped: true,

      miniVariant: true,
      title: "Parking Control Panel",
      socket: null,
      logout_btn: {
        icon: "mdi-logout",
        label: "Logout",
      },
      viewing_page_name: "",

      inactivityTimeout: null,
      dialogAlarmPopupNotificationStatus: false,
      key: 1,
      isBackendRequestOpen: false,
      previousnotificationsMenuItems: [],
    };
  },
  async created() {

  },
  beforeUnmount() {
    console.log("Component is unmounted!");
  },
  onBeforeUnmount() {
    console.log("Component is unmounted!");
  },
  beforeDestroy() {
    window.removeEventListener("resize", this.handleResize);
  },

  async mounted() {

    // let company_menus = null;
    // let controlpanel_top_menu = null;

    try {
      const isParking =
        window &&
        (window.__APP_CONFIG__.PARKING_MODE === true ||
          window.__APP_CONFIG__.PARKING_MODE === "true");

      if (!isParking) {
        // car washing menus
        const left = await import("../menus/car_washing_menu_left.json");
        const top = await import("../menus/car_washing_menu_top.json");

        this.company_menus = left.default;
        this.controlpanel_top_menu = top.default;

        console.log("car washing mode enabled");
      } else {
        // parking menus
        const left = await import("../menus/parking_menu_left.json");
        const top = await import("../menus/parking_menu_top.json");
        console.log("parking mode enabled");

        this.company_menus = left.default;
        this.controlpanel_top_menu = top.default;
      }
    } catch (ex) {
      console.error("Error loading menus:", ex);

      // parking menus
      const left = await import("../menus/parking_menu_left.json");
      const top = await import("../menus/parking_menu_top.json");
      console.log("parking mode enabled");

      this.company_menus = left.default;
      this.controlpanel_top_menu = top.default;
    }

    this.handleResize();
    window.addEventListener("resize", this.handleResize);
    if (typeof window !== "undefined" && window.innerWidth < 700) {
      // console.log("window.innerWidth", window.innerWidth);
      this.$router.push("/alarm/mobiledashboard");
    }

    //console.log("company auth", this.$auth);
    // console.log("company", this.$auth.user);
    if (!this.$auth.user) {
      this.$router.push("/logout");
      return false;
    }
    if (this.$auth.user?.user_type != "company") {
      try {
        if (window) {
          this.logout();
          //if (this.$route.name != "logout") window.location.href = "../login";
          //window.location.reload();
        }
      } catch (e) { }
      this.$router.push("/login");

      return false;
    }


    await this.updateTopmenu();

    this.$store.commit("loginType", this.$auth.user?.user_type);


    //console.log("top", this.$route.name);
    //this.topMenu_Selected = menu_name;
    // if(this.$route.name=='')

    await this.setMenus();
    // await this.loadAlarmNotificationIcons();
    await this.getCompanyDetails();
    //this.logo_src = require("@/static/logo_header.png");
    this.pendingNotificationsCount = 0;

    this.setTopmenuHilighterOnPageReload();

    // setInterval(() => {
    //   try {
    //     if (window) window.location.reload();
    //   } catch (e) { }
    // }, 1000 * 60 * 60);
    // if (!this.$auth.user) {
    //   this.$router.push("/logout");
    //   return;
    // }

    setTimeout(() => {
      this.loadHeaderNotificationMenu();
      //this.verifyPopupAlarmStatus();
    }, 1000 * 1);
    // await this.getBuildingTypes();
    // await this.getAddressTypes();
    // await this.getDeviceTypes();
    // await this.getSensorTypes();

    // await this.getZoneTypes();

    await this.getDeviceModels();

    // setInterval(() => {
    //   if (!this.$route.name.includes("alarm")) return false;
    //   //this.loadHeaderNotificationMenu();

    //   //console.log("wait5Minutes", this.wait5Minutes);
    //   //if (this.wait5Minutes == false)
    //   if (
    //     this.$route.name !== "login" &&
    //     this.$route.name !== "operator-eventslist" &&
    //     this.$route.name !== "operator-dashboard"
    //   ) {
    //     this.resetTimer();
    //     this.loadHeaderNotificationMenu();
    //   }
    // }, 1000 * 10 * 1);

    let menu_name = this.$route.name;
    let bgColor = "violet";
    let loadSelectedMenu = "";
    if (menu_name) menu_name = menu_name.replaceAll("-", "/");

    this.setupInactivityDetection();

    setInterval(() => {
      this.verifyCompanyEndDateValidaty();
      try {
        if (window) window.location.reload();
      } catch (e) { }
    }, 1000 * 60 * 60);
    try {
      if (window) {
        if (window) {
          console.log("window.innerWidth ", window.innerWidth);

          if (window.innerWidth < 700) {
            this.displayTopMenu = false;
          } else this.displayTopMenu = true;
        }
      }
    } catch (e) { }

    // this.loadAlarmNotificationIcons();
  },

  watch: {
    // notificationsMenuItems: {
    //   deep: true,
    //   handler(newVal, oldVal) {
    //     if (newVal.length > 0) {
    //       let newIds = newVal.map((e) => e.id);
    //       let oldIds = oldVal.map((e) => e.id);
    //       let added = newIds.filter((id) => !oldIds.includes(id));
    //       if (added.length) {
    //         this.wait5Minutes = false;
    //         console.log("wait5Minutes", this.wait5Minutes);
    //       }
    //     }
    //   },
    // },
  },
  computed: {
    changeColor() {
      return "#ecf0f4"; //this.$store.state.color;
    },
    scaleStyle() {
      return {
        transform: `scale(${this.scale})`,
        transformOrigin: "top left",
        width: `${100 / this.scale}%`, // prevents scrollbars
        height: `${100 / this.scale}%`,
      };
    },

    getUser() {
      const user = this.$auth.user;
      const userType = user.user_type;

      if (userType === "master") {
        return user.name;
      } else if (userType === "company") {
        return user.company.name;
      }

      const employee = user.employee;
      if (employee) {
        return employee.display_name || employee.first_name;
      }

      return null; // Or some default value indicating no user found
    },

    getLogo() {
      const { user } = this.$auth;

      if (!user) {
        return "/no-image.PNG";
      }

      const defaultLogo = "/no-image.PNG";
      const profilePicture = "/no-profile-image.jpg";

      switch (user.user_type) {
        case "company": {
          if (user.role_id > 1) return user.profile_picture;
          return user.company?.logo || defaultLogo;
        }

        case "master":
          return defaultLogo;
        case "employee":
          return user.employee?.profile_picture || profilePicture;
        case "branch":
          return user.branch_logo || profilePicture;
        default:
          return defaultLogo;
      }
    },
    getLoginType() {
      // if (!this.$auth.user) {
      //   this.$router.push("/logout");
      //   return;
      // }
      return this.$auth.user.user_type || "company";
    },
  },
  methods: {
    handleResize() {
      const width = window.innerWidth;
      this.scale = 1; // width < 1900 ? width / 1900 + 0.2 : 1;
    },
    toggleTheme() {
      this.$vuetify.theme.dark = !this.$vuetify.theme.dark;
    },
    wait5MinutesNextNotification() {
      //console.log(this.$auth.user.company.notification_popup_pause_minutes);

      let minutes =
        this.$auth.user.company.notification_popup_pause_minutes ?? 1;

      this.snackbar = true;
      this.response = `New Alarm will be Display after  ${minutes} minutes`;
      //alert(`New Alarm will be Display after  ${minutes} minutes`);
      this.wait5Minutes = true;

      setTimeout(() => {
        this.wait5Minutes = false;
      }, 1000 * 60 * minutes);

      this.dialogAlarmPopupNotificationStatus = false;
    },
    Reset5Minutes() {
      this.wait5Minutes = false;
    },
    wait5MinutesNotification() {
      this.wait5Minutes = true;
      setTimeout(() => {
        this.wait5Minutes = false;
      }, 1000 * 60 * 1);
    },
    verifyCompanyEndDateValidaty() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios
        .get(`get_company_validate_details`, options)
        .then(({ data }) => {
          let companyEndDate = new Date(data.expiry);

          let now = new Date();

          if (companyEndDate < now) {
            this.$router.push("/logoutget_company_validate_details");

            return false;
          }
        });
    },
    setTopmenuHilighterOnPageReload() {
      const routeMap = {
        "alarm-map": { name: "alarm_map", path: "/alarm/map" },
        "alarm-dashboard": {
          name: "alarm_dashboard",
          path: "/alarm/dashboard",
        },
        "alarm-customersmap": {
          name: "alarm_customersmap",
          path: "/alarm/customersmap",
        },

        "alarm-allevents": {
          name: "alarm_allevents",
          path: "/alarm/allevents",
        },
        "alarm-supervisorevents": {
          name: "alarm_supervisorevents",
          path: "/alarm/supervisorevents",
        },
        "alarm-reports": { name: "alarm_reports", path: "/alarm/reports" },
        "alarm-tickets": { name: "alarm_tickets", path: "/alarm/tickets" },

        "alarm-customers": {
          name: "alarm_customers",
          path: "/alarm/customers",
        },
        "alarm-view-customer-id": {
          name: "alarm_customers",
          path: "/alarm/customers",
        },
        "alarm-security": {
          name: "alarm_security",
          path: "/alarm/security",
        },
        "alarm-technicians": {
          name: "alarm_technicians",
          path: "/alarm/technicians",
        },

        "alarm-armedreports": {
          name: "alarm_armed_reports",
          path: "/alarm/armedreports",
        },
        "devices_devices": {
          name: "devices_devices",
          path: "/devices/devices",
        },

        settings: {
          name: "settings",
          path: "/settings",
        },
        sales: {
          name: "alarm_inquiry",
          path: "/alarm/inquiry",
        },
      };

      const defaultRoute = {
        name: "alarm_dashboard",
        path: "/parking/dashboard",
      };

      const routeConfig = routeMap[this.$route.name] || defaultRoute;

      this.setSubLeftMenuItems(routeConfig.name, routeConfig.path, false);
    },
    async loadAlarmNotificationIcons() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`alarm_notification_icons`, options).then(({ data }) => {
        this.alarm_icons = data;

        this.$store.commit(
          "storeAlarmControlPanel/AlarmTypeNotificationIcons",
          data
        );
      });
    },
    async getBuildingTypes() {
      if (!this.$store.state.storeAlarmControlPanel?.BuildingTypes) {
        const { data } = await this.$axios.get("building_types", {
          params: {
            company_id: this.$auth.user.company_id,
          },
        });

        this.$store.commit("storeAlarmControlPanel/BuildingTypes", data);
      }
    },
    async getAddressTypes() {
      if (!this.$store.state.storeAlarmControlPanel?.AddressTypes) {
        const { data } = await this.$axios.get("address_types", {
          params: {
            company_id: this.$auth.user.company_id,
          },
        });

        this.$store.commit("storeAlarmControlPanel/AddressTypes", data);
      }
    },
    async getDeviceModels() {
      if (!this.$store.state.storeAlarmControlPanel?.DeviceModels) {
        const { data } = await this.$axios.get("device_models", {
          params: {
            company_id: this.$auth.user.company_id,
          },
        });

        this.$store.commit("storeAlarmControlPanel/DeviceModels", data);
      }
    },
    async getSensorTypes() {
      if (!this.$store.state.storeAlarmControlPanel?.SensorTypes) {
        const { data } = await this.$axios.get("sensor_types", {
          params: {
            company_id: this.$auth.user.company_id,
          },
        });

        this.$store.commit("storeAlarmControlPanel/SensorTypes", data);
      }
    },
    async getZoneTypes() {
      if (!this.$store.state.storeAlarmControlPanel?.ZoneTypes) {
        const { data } = await this.$axios.get("device_zone_types_dropdown", {
          params: {
            company_id: this.$auth.user.company_id,
          },
        });

        this.$store.commit("storeAlarmControlPanel/ZoneTypes", data);
      }
    },
    async getDeviceTypes() {
      if (!this.$store.state.storeAlarmControlPanel?.DeviceTypes) {
        const { data } = await this.$axios.get("device_types", {
          params: {
            company_id: this.$auth.user.company_id,
          },
        });

        this.$store.commit("storeAlarmControlPanel/DeviceTypes", data);
      }
    },
    showGlobalsearchPopup() {
      if (this.$route.name != "alarm-view-customer") {
        this.key = this.key + 1;
        this.globalSearchPopupWidth = "500px";
        this.globalSearchPopup = true;
        //this.$refs.globalSearchTextbox.focus();
      }
    },
    toggleTheme() {
      this.$vuetify.theme.dark = !this.$vuetify.theme.dark;

      // if (!this.$vuetify.theme.dark) {
      //   this.$vuetify.theme.themes.light = {
      //     primary: "#6946dd", //violoet
      //     accent: "#d8363a",
      //     secondary: "#242424",
      //     background: "#34444c",
      //     main_bg: "#ECF0F4",
      //     violet: "#6946dd",
      //     popup_background: "#ecf0f4",
      //   };
      // }
    },
    async updateTopmenu() {
      if (!this.$auth.user) {
        this.$router.push("/login222222222222222222");
        return;
      }
      if (this.$auth.user.user_type == "department") {
        this.company_top_menu = require("../menus/department_modules_top.json");
        return;
      }

      try {
        if (this.$auth.user.company.display_modules) {
          let display_modules = JSON.parse(
            this.$auth.user.company.display_modules
          );
          if (display_modules) {
            if (display_modules.access_control == false) {
              this.company_top_menu = this.company_top_menu.filter(
                (item) => item.menu != "access_control"
              );
            }
            if (display_modules.visitors == false) {
              this.company_top_menu = this.company_top_menu.filter(
                (item) => item.menu != "visitors"
              );
            }
            if (display_modules.community == false) {
              this.company_top_menu = this.company_top_menu.filter(
                (item) => item.menu != "community"
              );
            }
          }
        }
      } catch (e) { }
    },
    handleActivity() {
      this.resetTimer();
    },
    resetTimer() {
      // Time in milliseconds after which the user is considered inactive
      const INACTIVITY_TIME = 1000 * 60 * 5; //30 minutes
      clearTimeout(this.inactivityTimeout);
      this.inactivityTimeout = setTimeout(
        this.handleInactivity,
        INACTIVITY_TIME
      );
    },
    setupInactivityDetection() {
      // Handle inactivity
      this.handleInactivity = () => {
        // if (window) window.location.reload();

        // Perform actions when the user is inactive
        // this.$router.push(`/alarm/dashboard`);
        // For example, you could redirect the user, show a message, etc.
      };

      // Attach event listeners
      window.addEventListener("mousemove", this.handleActivity);
      window.addEventListener("mousedown", this.handleActivity);
      window.addEventListener("keypress", this.handleActivity);
      window.addEventListener("touchmove", this.handleActivity);

      // Initialize the timer
      this.resetTimer();
    },
    gotoHomePage() {
      //location.href = this.$env.settings.APP_URL + "/dashboard2";
      location.href = location.href; // this.$env.settings.APP_URL + "/dashboard2";
    },
    isNewAlarmAdded() {
      let added = this.notificationsMenuItems.filter(
        (item) =>
          !this.previousnotificationsMenuItems.some(
            (prev) => prev.id === item.id
          )
      );

      // console.log(added);

      return added.length > 0;
    },
    loadHeaderNotificationMenu() {
      if (this.isBackendRequestOpen) {
        // Cancel the previous request if it's still pending
        if (this.cancelRequest) {
          this.cancelRequest(); // This triggers the cancellation
        }
      }

      this.isBackendRequestOpen = true;
      this.key = this.key + 1;

      let company_id = this.$auth.user?.company?.id || 0;
      if (company_id == 0) {
        this.isBackendRequestOpen = false;
        return false;
      }

      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          alarm_status: this.filterAlarmStatus,
          page_source: "default",
        },
        cancelToken: new this.$axios.CancelToken((cancel) => {
          this.cancelRequest = cancel; // Store the cancel function
        }),
      };

      // this.$axios
      //   .get(`get_alarm_notification_display`, options)
      //   .then(({ data }) => {
      //     this.isBackendRequestOpen = false;
      //     this.notificationsMenuItems = [];
      //     this.pendingNotificationsCount = 0;
      //     this.notificationAlarmDevicesContent = data;
      //     this.key += 1;

      //     data.forEach((element) => {
      //       let notification = {
      //         id: element.id,
      //         title: element.device?.customer?.building_name
      //           ? element.device.customer.building_name +
      //           " - " +
      //           element.alarm_type
      //           : "---",
      //         date_from: element.alarm_start_datetime,
      //         click: "/alarm/allevents",
      //         icon:
      //           this.alarm_icons[element.alarm_type] ??
      //           element.alarm_type + ".png",
      //         key: "leaves",
      //       };

      //       this.notificationsMenuItems.push(notification);
      //     });

      //     this.pendingNotificationsCount = data.length;
      //     if (this.$route.name == "alarm-dashboard") {
      //       if (
      //         this.notificationsMenuItems.length > 0 &&
      //         (!this.wait5Minutes || this.isNewAlarmAdded())
      //       )
      //         this.dialogAlarmPopupNotificationStatus = true;
      //       else this.dialogAlarmPopupNotificationStatus = false;
      //     }

      //     // console.log(
      //     //   this.notificationsMenuItems.length,
      //     //   this.wait5Minutes,
      //     //   this.dialogAlarmPopupNotificationStatus
      //     // );

      //     this.previousnotificationsMenuItems = this.notificationsMenuItems;
      //   })
      //   .catch((error) => {
      //     if (this.$axios.isCancel(error)) {
      //       console.log("Previous request canceled");
      //     } else {
      //       console.error("Error loading notifications:", error);
      //     }
      //     this.isBackendRequestOpen = false;
      //   });
    },

    showPopupAlarmStatus() {
      this.wait5Minutes = false;
      if (!this.dialogAlarmPopupNotificationStatus) {
        this.popupKey += 1;
        this.dialogAlarmPopupNotificationStatus = true;
      }

      // this.verifyPopupAlarmStatus();
    },
    // verifyPopupAlarmStatus() {
    //   if (this.isBackendRequestOpen) return false;
    //   this.isBackendRequestOpen = true;
    //   let company_id = this.$auth.user?.company?.id || 0;
    //   if (company_id == 0) {
    //     return false;
    //   }
    //   let options = {
    //     params: {
    //       company_id: company_id,
    //     },
    //   };
    //   this.$axios
    //     .get(`get_alarm_notification_display`, options)
    //     .then(({ data }) => {
    //       this.isBackendRequestOpen = false;
    //       if (data.length > 0) {
    //         this.notificationAlarmDevicesContent = data;

    //         this.dialogAlarmPopupNotificationStatus = true;
    //       } else {
    //         this.dialogAlarmPopupNotificationStatus = false;
    //       }
    //     });
    // },

    getBranchName() {
      return this.$auth.user.branch_name;
    },
    getTopMenuItems(i) {
      if (i.module == "dashboard") {
        this.setSubLeftMenuItems(i.submenu, i.to);
      }
    },
    goToPage(page) {
      this.$router.push(page);
    },
    goToSettings() {
      this.setSubLeftMenuItems("settings", "/branches");
    },

    //change selected menu color
    setSubLeftMenuItems(menu_name, page, redirect = true) {
      this.topMenu_Selected = menu_name;

      let bgColor = "violet";
      this.setMenus();

      console.log("menu_name", menu_name, page);

      // Check if menu_name exists in menuProperties// data sesction
      if (this.menuProperties.hasOwnProperty(menu_name)) {
        for (const key in this.menuProperties) {
          this.menuProperties[key].elevation = 0;
          this.menuProperties[key].selected = "";
        }

        this.menuProperties[menu_name].elevation = 0;
        this.menuProperties[menu_name].selected = bgColor;
      }

      // console.log("menu_name", menu_name);

      // console.log(
      //   this.menuProperties,
      //   menu_name,
      //   this.menuProperties.hasOwnProperty(menu_name)
      // );

      //alarm menu select color
      if (this.menuProperties.hasOwnProperty(menu_name)) {
        for (const key in this.menuProperties) {
          this.menuProperties[key].elevation = 0;
          this.menuProperties[key].selected = "";
        }

        this.menuProperties[menu_name].elevation = 0;
        this.menuProperties[menu_name].selected = bgColor;
      }
      if (redirect) return this.$router.push(page);
    },

    async setMenus() {
      //left menu sub menu
      // this.items = this.company_menus.filter(
      //   (item) => item.module === this.topMenu_Selected
      // );
      this.items = this.company_menus.filter(
        (item) => item.top_menu_name === this.topMenu_Selected
      );
    },

    changeLoginType() {
      try {
        // if (this.getLoginType == "branch")
        {
          // this.$store.commit("loginType", "employee");
          // this.setMenus();
          let email = this.$store.state.email;
          let password = this.$store.state.password;

          email = this.$crypto.encrypt(email);
          password = this.$crypto.encrypt(password);

          email = encodeURIComponent(email);
          password = encodeURIComponent(password);

          if (email && password) {
            window.location.href =
              this.$env.settings.EMPLOYEE_APP_URL +
              "/loginwithtoken?email=" +
              email +
              "&password=" +
              password;

            return "";
          } else {
            console.log("Empty Username and Password");
          }
          // this.$router.push("/employees/profile");
        }
        // else {
        //   this.$store.commit("loginType", "branch");
        //   this.setMenus();
        //   this.$router.push("/dashboard2");
        // }
      } catch (e) {
        console.log(e);
      }
    },
    navigateToLeavePage() {
      this.$router.push("/leaves");
    },

    filterBranch(branch) {
      this.$emit("openalert", "");

      if (branch) {
        this.selectedBranchName = branch.branch_name;
        this.seelctedBranchId = branch.id;
      } else {
        this.selectedBranchName = "All Branches";
        this.seelctedBranchId = "";
      }
    },
    collapseSubItems() {
      this.company_menus.map((item) => (item.active = false));
    },
    changeTopBarColor(color) {
      this.color = color;
      this.$store.commit("change_color", color);
    },

    changeTheme(color) {
      // alert(color);
    },

    changeSideBarColor(color) {
      this.sideBarcolor = color;
    },

    caps(str) {
      return str.replace(/\b\w/g, (c) => c.toUpperCase());
    },
    goToSetting() {
      this.$router.push("/setting");
    },
    goToLeaves() {
      this.$router.push("/leaves");
    },
    goToCompany() {
      this.$router.push(`/settings`);
    },
    async getCompanyDetails() {
      this.$axios
        .get(`company/${this.$auth.user?.company_id}`)
        .then(({ data }) => {
          let { modules } = data.record;

          if (modules !== null) {
            this.modules = {
              module_ids: modules.module_ids || [],
              module_names: modules.module_names.map((e) => ({
                icon: "mdi-chart-bubble",
                title: this.caps(e),
                to: "/" + e + "_modules",
                permission: true,
              })),
            };
          }
        });
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    async logout() {
      this.activeAudio = false;
      try {
        await this.$axios.get(`/logout`);
        await this.$auth.logout();
        window.location.href = "../login";
      } catch (error) {
        console.error("Logout failed:", error);
      }
    },

    GlobalSearchResultsUpdated(value) {
      if (value > 0) this.globalSearchPopupWidth = "1400px";
      else this.globalSearchPopupWidth = "500px";
    },
  },
  beforeDestroy() {
    // Cleanup: Remove event listeners
    window.removeEventListener("mousemove", this.handleActivity);
    window.removeEventListener("mousedown", this.handleActivity);
    window.removeEventListener("keypress", this.handleActivity);
    window.removeEventListener("touchmove", this.handleActivity);
    clearTimeout(this.inactivityTimeout);
  },
};
</script>
<style>
/* body {
  min-width: 1400px !important;
  overflow: scroll !important;
} */
/* .v-card {
  min-width: 1400px !important;
  overflow-x: scroll !important;
} */
</style>
<style scoped>
.btn-text-size {
  font-size: 15px !important;
}

.leftMenuWidth {
  width: 140px !important;
}

.main_bg {
  padding-left: 140px;
  padding-top: 40px !important;
}

.v-list-item {
  text-align: center;
  width: 100%;
}
</style>
<!-- Extra overriting classes-->
<style scoped>
.bold {
  color: bold;
}

.text--violet {
  color: #6946dd;
}

.v-list-item--active i {
  color: #6946dd;
}

.theme--dark.v-list-item:not(.v-list-item--active):not(.v-list-item--disabled) {
  color: #9aa9b9;
}

.theme--dark.v-list-item:not(.v-list-item--active):not(.v-list-item--disabled) i {
  color: #9aa9b9;
}

/* header,
header button,
header a,
header i {
  color: black !important;
} */
header,
.v-sheet {
  z-index: 99 !important;
}

.theme--dark.v-bottom-navigation .v-btn:not(.v-btn--active) {
  color: black !important;
}

.theme--dark.v-bottom-navigation .v-btn--active {
  background: rgb(105, 70, 221);
  color: #fff !important;
}
</style>

<style>
.table-td-padding-10 td,
.table-td-padding-10 th {
  padding: 5px !important;
}

.violet {
  background-color: #6946dd;
}

.bold {
  font-weight: bold;
}

.text--violet {
  color: #6946dd;
}

.view-profile-table-lineheight {
  line-height: 40px;
  width: 100%;
}

.border-bottom {
  border-bottom: 1px solid #ddd;
}

.view-profile-table-lineheight tr {
  border-bottom: 1px solid #ddd;
}

.view-profile-table-lineheight td {
  padding-right: 5px;
  padding-left: 5px;
}

.whitebackground--text {
  background-color: #ecf0f4;
}

/* New Theme  popup_background*/
.v-application .popup_background {
  background-color: #ecf0f4 !important;
}

.popup_background {
  background-color: #ecf0f4 !important;
  color: black !important;
  font-weight: 400 !important;

  font-size: 1.25rem !important;
}

.popup_title,
.v-dialog>.v-card>.v-card__title {
  color: black !important;
  font-weight: 400 !important;

  font-size: 1.25rem !important;
}

.popup_background i {
  color: black !important;
}

.popup_background button {
  color: black !important;
}

.popup_background .v-tabs-bar {
  background-color: #ecf0f4 !important;
  color: black;
  font-weight: 400;

  font-size: 1.25rem;
}

.popup_background .v-tabs-bar i {
  color: black !important;
}

.popup_background .v-toolbar__title {
  color: black !important;
}

.popup_background .v-icon {
  color: black !important;
}

.v-dialog>.v-card>.popup_background {
  background-color: #6946dd !important;
  padding: 5px 6px 5px !important;
  color: #fff !important;
}

.popup_background_white {}

.v-dialog>.v-card>.popup_background_noviolet {
  background-color: #ecf0f4 !important;
  padding: 5px 6px 5px !important;
  color: black !important;
}

.v-dialog>.v-card>.popup_background_white {
  background-color: #fff !important;
  padding: 5px 6px 5px !important;
  color: black !important;
}

.popup_background_white .v-icon {
  color: black !important;
}

.popup_background_noviolet .v-tabs-bar {
  background-color: #ecf0f4 !important;
}

.popup_background_noviolet .v-card__title,
.popup_background_noviolet button {
  color: black;
}

.v-dialog>.v-card>.popup_background_red {
  background-color: red !important;
  padding: 5px 6px 5px !important;
  color: #fff !important;
}

.popup_background_red .v-tabs-bar {
  background-color: red !important;
}

.noviolet {
  background-color: #ecf0f4 !important;
}

.v-dialog>.v-card>.popup_background>.mdi-close-circle {
  color: #fff !important;
}

/* .theme--dark.v-toolbar.v-sheet {
  background-color: #cfd8dc !important;
} */
/* .v-card {
  background-color: #cfd8dc;
}
.v-card header {
  background-color: #cfd8dc;
}
.v-card .v-card__title {
  color: black;
}
.v-card i {
  color: black;
}

.v-card .v-toolbar__title {
  color: black;
} */
/* For all INPUT boxes */
/* fieldset {
  height: 30px;
}
input {
  margin-top: -15px;
}
.v-input__append-inner {
  margin-top: -2px;
} */

/* For all INPUT boxes */
.input-small-fieldset fieldset {
  height: 30px;
}

.input-small-fieldset input {
  margin-top: -15px;
}

.input-small-fieldset .v-input__append-inner {
  margin-top: -2px;
}

.black--text {
  color: black;
}

.black {
  color: black;
}

.iconsize {
  width: 20px;
}

.secondary-value {
  font-size: 10px;
}

.form-control:focus {
  box-shadow: none !important;

  /* height: 32px !important; */
}

.iconsize30 {
  width: 30px;
  height: auto;
}

.basic-table-design {
  tr {
    width: 100%;
    border-bottom: 1px solid #ddd;
  }
}

/* .v-application .primary--text {
  color: #6946dd !important;
  caret-color: #6946dd !important;
} */

.slidegroup1 .v-slide-group {
  height: 34px !important;
}
</style>

<style>
/*! CSS Used from: https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.10.2/mdb.min.css */
*,
:after,
:before {
  box-sizing: border-box;
}

button {
  border-radius: 0;
}

button:focus:not(:focus-visible) {
  outline: 0;
}

button {
  margin: 0;
  font-family: inherit;
  font-size: inherit;
  line-height: inherit;
}

button {
  text-transform: none;
}

[type="button"],
button {
  -webkit-appearance: button;
}

[type="button"]:not(:disabled),
button:not(:disabled) {
  cursor: pointer;
}

.btn {
  display: inline-block;
  color: #4f4f4f;
  text-align: center;
  text-decoration: none;
  vertical-align: middle;
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  user-select: none;
  background-color: transparent;
  border: 0.125rem solid transparent;
  padding: 0.375rem 0.75rem;
  border-radius: 0.25rem;
  transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out,
    border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

@media (prefers-reduced-motion: reduce) {
  .btn {
    transition: none;
  }
}

.btn:hover {
  color: #4f4f4f;
}

.btn:disabled {
  pointer-events: none;
  opacity: 0.65;
}

button:focus {
  outline: 0;
}

.btn {
  text-transform: uppercase;
  vertical-align: bottom;
  border: 0;
  box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.2), 0 2px 10px 0 rgba(0, 0, 0, 0.1);
  font-weight: 500;
  padding: 0.625rem 1.5rem 0.5rem;
  font-size: 0.75rem;
  line-height: 1.5;
}

.btn:active,
.btn:active:focus,
.btn:focus,
.btn:hover {
  box-shadow: 0 4px 10px 0 rgba(0, 0, 0, 0.2), 0 4px 20px 0 rgba(0, 0, 0, 0.1);
}

.btn:disabled {
  box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.2), 0 2px 10px 0 rgba(0, 0, 0, 0.1);
  border: 0;
}

.btn:focus {
  outline: 0;
  box-shadow: 0 4px 10px 0 rgba(0, 0, 0, 0.2), 0 4px 20px 0 rgba(0, 0, 0, 0.1);
}

.btn-block {
  display: block;
  width: 100%;
}

.btn-block+.btn-block {
  margin-top: 0.5rem;
}

/*! CSS Used from: https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/4.4.95/css/materialdesignicons.min.css */
.mdi:before {
  display: inline-block;
  font: normal normal normal 24px/1 "Material Design Icons";
  font-size: inherit;
  text-rendering: auto;
  line-height: inherit;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.mdi-chevron-right::before {
  content: "\F142";
}

/*! CSS Used from: https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css */
.mdi:before {
  display: inline-block;
  font: normal normal normal 24px/1 "Material Design Icons";
  font-size: inherit;
  text-rendering: auto;
  line-height: inherit;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.mdi-chevron-right::before {
  content: "\F0142";
}

/*! CSS Used from: Embedded */
*,
:after,
:before {
  background-repeat: no-repeat;
  box-sizing: inherit;
}

:after,
:before {
  text-decoration: inherit;
  vertical-align: inherit;
}

* {
  margin: 0;
  padding: 0;
}

button {
  font: inherit;
}

button {
  overflow: visible;
}

button {
  text-transform: none;
}

[type="button"],
button {
  color: inherit;
  cursor: pointer;
}

button,
html [type="button"] {
  -webkit-appearance: button;
}

button {
  background-color: transparent;
  border-style: none;
}

.v-icon.v-icon {
  font-feature-settings: "liga";
  align-items: center;
  display: inline-flex;
  font-size: 24px;
  justify-content: center;
  letter-spacing: normal;
  line-height: 1;
  position: relative;
  text-indent: 0;
  transition: 0.3s cubic-bezier(0.25, 0.8, 0.5, 1), visibility 0s;
  -webkit-user-select: none;
  -moz-user-select: none;
  user-select: none;
  vertical-align: middle;
}

.v-icon.v-icon:after {
  background-color: currentColor;
  border-radius: 50%;
  content: "";
  display: inline-block;
  height: 100%;
  left: 0;
  opacity: 0;
  pointer-events: none;
  position: absolute;
  top: 0;
  transform: scale(1.3);
  transition: opacity 0.2s cubic-bezier(0.4, 0, 0.6, 1);
  width: 100%;
}

.apexcharts-menu-icon {
  padding-top: 10px;
}
</style>
<style>
.v-application .error--text {
  color: red;
}

.error--text,
.text-danger {
  font-size: 13px;
  color: red !important;
}

.v-messages {
  min-height: 0px !important;
}

.text-break-dot {
  text-overflow: ellipsis;
  width: 200px;
  overflow: hidden;
  white-space: nowrap;
  height: 25px;
}

.breakthewords {
  display: -webkit-box;
  font-size: 12px !important;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

.branchlogo {
  width: 50px;
}

.table_active {
  background: #e1e1e1;
  border-left: 1px solid green;
  font-weight: bold;
}

.annnouncment_table .text-left {
  padding: 0px !important;
}

.announ_priority {
  color: rgb(63, 81, 181);
  width: 100%;
  text-align: center;
  padding-bottom: 10px;
  font-weight: bold;
  font-size: 19px;
}

.slidegroup1 .v-slide-group {
  height: 34px !important;
}

.icon-blue {
  color: #755fc9 !important;
}

.company_logo .v-image__image--cover {
  background-size: contain;
}

.no-border>.v-input__control>.v-input__slot:before {
  border-color: #fff !important;
}

/* .no-border:before {
  border-color: #fff !important;
} */

.logtable .v-data-table__wrapper {
  height: 670px;
  overflow-y: scroll;
}

.logtable-comm .v-data-table__wrapper {
  height: 830px;
  overflow-y: scroll;
}

.col-border-label {
  position: absolute;
  top: -9px;
  background: #fff;
  margin-left: -2px;
  width: auto;
  padding: 0px 7px;
  color: black;
}

.div-border-label {
  position: absolute;
  top: 3px;
  background: #fff;
  margin-left: 5px;
  width: auto;
  padding: 0px 7px;
  color: black;
}

.alarm-red-to-green {
  animation: changeBackgroundColorRed 1s infinite;
}

@keyframes changeBackgroundColorRed {
  0% {
    color: green;
  }

  20% {
    color: #f73030;
  }

  40% {
    color: green;
  }

  60% {
    color: #f73030;
  }

  80% {
    color: green;
  }

  100% {
    color: #f73030;
  }
}

.alarm {
  animation: changeBackgroundColor 1s infinite;
}

@keyframes changeBackgroundColor {
  0% {
    color: #000000;
  }

  10% {
    color: #f73030;
  }

  20% {
    color: #000000;
  }

  40% {
    color: #f73030;
  }

  60% {
    color: #000000;
  }

  80% {
    color: #fc0000;
  }

  90% {
    color: #000000;
  }

  100% {
    color: #fc0000;
  }
}
</style>

<style>
* {
  font-family: "Source Sans Pro", sans-serif !important;
}

.apexcharts-canvas {
  width: 100%;
}
</style>

<style>
.theme--light.v-text-field>.v-input__control>.v-input__slot:before {
  border-color: #fff !important;
}

.no-border:before {
  border-color: #fff !important;
}

.small-custom-textbox .v-input__slot {
  min-height: 30px !important;
}

.small-custom-textbox .v-label {
  line-height: 11px !important;
}

.small-custom-textbox .v-input__icon {
  height: 17px !important;
}

.global-search-textbox fieldset,
.global-search-textbox .v-label,
.global-search-textbox .v-icon,
.global-search-textbox .theme--dark.v-input textarea {
  color: black !important;
}

.global-search-textbox.v-text-field--enclosed:not(.v-text-field--rounded)>.v-input__control>.v-input__slot {
  padding: 0 4px;
}

.global-search-textbox.v-input input::placeholder {
  color: black !important;
}

.global-search-textbox .v-input__slot {
  padding: 0 3px !important;
}

.company-profile-picture .v-image__image--cover {
  background-size: contain;
}

.custom-text-field-height .v-input__slot {
  min-height: auto !important;
  display: flex !important;
  align-items: center !important;
}

.custom-text-field-height .v-label {
  top: 6px !important;
}

.custom-text-field-height .v-input__append-inner {
  margin-top: 3px !important;
}

.custom-text-field-height .v-input__prepend-inner {
  margin-top: 3px !important;
}

.employee-schedule-cropdown .mdi-menu-down {
  padding-top: 7px;
}

.small-dropdown .v-input__slot {
  min-height: 30px !important;
}

.small-dropdown .v-label {
  line-height: 11px !important;
}

.small-dropdown .v-input__icon {
  height: 17px !important;
}

.small-dropdown {
  height: 20px;
}

.employee-schedule-search-box .v-input__slot {
  min-height: 30px !important;
}

.employee-schedule-search-box .v-label {
  line-height: 11px !important;
}

.employee-schedule-search-box .v-input__icon {
  height: 17px !important;
}

.reports-events-autocomplete .v-input__slot {
  min-height: 33px !important;
}

.reports-events-autocomplete .v-label {
  line-height: 15px !important;
}

.reports-events-autocomplete .v-input__icon {
  height: 20px !important;
}

.table-payslip td {
  text-align: left;
  padding: 0px;

  /* line-height: 30px; */
}

.table-payslip table,
.table-payslip div {
  border-collapse: collapse;
  font-size: 13px;
  color: black;
}

.outer-wrapper {
  display: inline-block;
  margin: 0px;
}

.frame {
  width: 50px;
  height: auto;
  border: 1px solid black;
  vertical-align: middle;
  text-align: center;
  display: table-cell;
}

.img1 {
  max-width: 100%;
  max-height: 100%;
  display: block;
  margin: 0 auto;
}

.header-menu-item {
  font-size: 16px;
  font-weight: 600;
}

.customer-tabs a {
  font-size: 12px !important;
}

/* .customer-tabs i {
  font-size: 12px !important;
} */

#AlamCustomerSensorPieChart .apexcharts-legend {
  top: 0px !important;
}

.notificationbadge {
  z-index: 9999;
}
</style>

<style>
/*  //ipad 1300 to 1600  */
/* @media (max-width: 1600px) {
  .header-menu,
  .header-menu-item {
    font-size: 10px !important;
    font-weight: bold !important;
    padding: 0px;
  }
  .header-menu-box {
    padding: 0px;
  }
  .header-menu-button {
    padding: 0px 3px !important;
  }
} */

@media (min-width: 1000px) and (max-width: 1300px) {

  .header-menu,
  .header-menu-item {
    font-size: 14px !important;
    font-weight: bold !important;
    padding: 0px;
  }

  .header-menu-box {
    padding: 0px !important;
  }

  .header-menu-button {
    padding: 0px 0px !important;
  }

  .logo-image {
    width: 100px !important;
  }
}

@media (min-width: 1301px) and (max-width: 1400px) {

  .header-menu,
  .header-menu-item {
    font-size: 15px !important;
    font-weight: 500 !important;
    padding: 6px !important;
  }

  .header-menu-box {
    padding: 0px !important;
  }

  .header-menu-button {
    padding: 0px 6px !important;
  }

  .logo-image {
    width: 100px !important;
  }
}

@media (min-width: 1401px) and (max-width: 1600px) {

  .header-menu,
  .header-menu-item {
    font-size: 14px !important;
    font-weight: bold !important;
    padding: 6px !important;
  }

  .header-menu-box {
    padding: 0px !important;
  }

  .header-menu-button {
    padding: 0px 8px !important;
  }

  .logo-image {
    width: 100px !important;
  }
}

.gm-style-iw-ch {
  padding-top: 10px !important;
}

.gm-ui-hover-effect span {
  width: 13px !important;
  height: 11px !important;
  margin: 0px !important;
}

.gm-ui-hover-effect {
  width: 21px !important;
  height: 19px !important;
}
</style>

<style scoped>
label.v-label.v-label--active.v-label--is-disabled.theme--light {
  color: red !important;
}
</style>

<style>
.empty-doughnut3 {
  border: 18px solid rgb(150, 150, 150);
  border-radius: 100px;
  height: 105px;
  width: 105px;
  padding-top: 8px;
  padding-left: 2px;
  font-size: 18px;
  margin: auto;
  line-height: 42px;
  margin-top: 15px;
  font-size: 16px;
  color: #373d3f;
}

.empty-doughnut2 {
  border: 20px solid rgb(150, 150, 150);
  border-radius: 100px;
  height: 130px;
  width: 130px;
  padding-top: 8px;
  padding-left: 2px;
  font-size: 18px;
  margin: auto;
  line-height: 42px;
  margin-top: 15px;
  font-size: 16px;
  color: #373d3f;
}

/* .employee-schedule-search-box .v-input__append-inner {
  background: red !important;
  padding: 6px !important;
  margin-top: 0px !important;
  margin-right: -11px !important;
  color: #fff !important;
}
.employee-schedule-search-box .v-icon {
  color: #fff !important;
} */
</style>

<!-- <link rel="stylesheet" href="../static/css/textbox-label-style.css" /> -->

<style>
.customer-tabs-right-line .v-tabs-slider-wrapper {
  left: auto !important;
  right: 0 !important;
}

.customer-tabs-right-line .v-slide-group__content {
  width: 90px;
  font-size: 9px !important;
  min-width: 75px !important;
}

.customer-tab {
  font-size: 9px !important;
  min-width: 50px !important;
}
</style>

<style>
.customerEmergencyContactTabs .v-slide-group__content {
  max-width: 100% !important;
}

.radiogroup {
  color: black;
}

.radiogroup label {
  color: black !important;
}

.v-radio--is-disabled label {
  color: #ddd !important;
}

.alarm-notes-timeline .v-timeline-item__body {
  max-width: 83% !important;
}

.alarm-notes-timeline .v-timeline-item__opposite {
  max-width: 17% !important;
}

.alarm-notes-timeline .v-application--is-ltr .v-timeline:not(.v-timeline--dense):not(.v-timeline--reverse)::before {
  left: 21% !important;
}

v-application .primary {
  background-color: red !important;
}

.v-application--is-ltr .v-timeline:not(.v-timeline--dense):not(.v-timeline--reverse)::before {
  left: 21% !important;
}

.v-timeline-item__body>.v-card:not(.v-card--flat)::after {
  border-right-color: black !important;
}
</style>

<style>
table {
  border-collapse: collapse;
  width: 100%;
}

td {
  padding: 0;
  margin: 0;
}

.v-timeline:not(.v-timeline--dense):not(.v-timeline--reverse) .v-timeline-item--after,
.v-timeline:not(.v-timeline--dense):not(.v-timeline--reverse) .v-timeline-item:nth-child(odd):not(.v-timeline-item--before) {
  flex-direction: row-reverse;
}

.v-timeline-item__body {
  max-width: 80% !important;
}

.v-timeline-item__divider {
  align-items: center;
  display: flex;
  justify-content: center;
  min-width: 96px;
  position: relative;
}

.v-timeline-item__dot {
  z-index: 2;
  border-radius: 50%;
  box-shadow: 0px 2px 1px -1px rgba(0, 0, 0, 0.2),
    0px 1px 1px 0px rgba(0, 0, 0, 0.14), 0px 1px 3px 0px rgba(0, 0, 0, 0.12);
  height: 38px;
  left: calc(50% - 19px);
  width: 38px;
}

.v-application--is-ltr .v-timeline:not(.v-timeline--dense):not(.v-timeline--reverse) .v-timeline-item--after .v-timeline-item__opposite,
.v-application--is-ltr .v-timeline:not(.v-timeline--dense):not(.v-timeline--reverse) .v-timeline-item:nth-child(odd):not(.v-timeline-item--before) .v-timeline-item__opposite {
  text-align: right;
}

.v-timeline-item__opposite {
  max-width: 24% !important;
}

.v-timeline-item__opposite {
  align-self: center;
  flex: 1 1 auto;
  max-width: calc(50% - 48px);
}

.theme--light.v-timeline .v-timeline-item__dot {
  background: #fff;
}

.v-timeline-item__dot {
  border-radius: 50%;
  box-shadow: 0 2px 1px -1px rgba(0, 0, 0, 0.2), 0 1px 1px 0 rgba(0, 0, 0, 0.14),
    0 1px 3px 0 rgba(0, 0, 0, 0.12);
  height: 38px;
  left: calc(50% - 19px);
  width: 38px;
  z-index: 2;
}

.v-timeline-item--fill-dot .v-timeline-item__inner-dot {
  height: inherit;
  margin: 0;
  width: inherit;
}

.v-application .white {
  background-color: #fff !important;
  border-color: #fff !important;
}

.v-timeline-item__inner-dot {
  align-items: center;
  border-radius: 50%;
  display: flex;
  justify-content: center;
}

.v-avatar {
  align-items: center;
  border-radius: 50%;
  display: inline-flex;
  justify-content: center;
  line-height: normal;
  overflow: hidden;
  position: relative;
  text-align: center;
  vertical-align: middle;
}

.v-icon.v-icon {
  font-feature-settings: "liga";
  align-items: center;
  display: inline-flex;
  font-size: 24px;
  justify-content: center;
  letter-spacing: normal;
  line-height: 1;
  position: relative;
  text-indent: 0;
  transition: 0.3s cubic-bezier(0.25, 0.8, 0.5, 1), visibility 0s;
  -webkit-user-select: none;
  -moz-user-select: none;
  user-select: none;
  vertical-align: middle;
}

.mdi:before {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  display: inline-block;
  font: normal normal normal 24px / 1 "Material Design Icons";
  font-size: inherit;
  line-height: inherit;
  text-rendering: auto;
}

.v-icon.v-icon:after {
  background-color: currentColor;
  border-radius: 50%;
  content: "";
  display: inline-block;
  height: 100%;
  left: 0;
  opacity: 0;
  pointer-events: none;
  position: absolute;
  top: 0;
  transform: scale(1.3);
  transition: opacity 0.2s cubic-bezier(0.4, 0, 0.6, 1);
  width: 100%;
}

.v-timeline::before {
  bottom: 0;
  content: "";
  height: 100%;
  position: absolute;
  top: 0;
  width: 2px;
}
</style>
<style>
.v-expansion-panel-header {
  padding: 10px !important;
  min-height: 30px !important;
}

.footermarkerstyle {
  text-align: center;
  max-width: 50px;
  padding-right: 0px;
  padding-left: 0px;
  padding-top: 3px;

  padding-bottom: 0px;
  margin: auto;
}

.font-black div {
  color: black;
}

.table-font12 td {
  font-size: 12px !important;
}

.eventslistscroll {
  overflow: auto;
  /* or scroll */
}

.eventslistscroll::-webkit-scrollbar {
  width: 6px;
  /* Width of the vertical scrollbar */
  height: 6px;
  /* Height of the horizontal scrollbar */
}

.eventslistscroll::-webkit-scrollbar-thumb {
  background: #888;
  /* Scrollbar color */
  border-radius: 10px;
  /* Rounded corners */
}

.eventslistscroll::-webkit-scrollbar-thumb:hover {
  background: #555;
  /* Scrollbar color on hover */
}

.eventslistscroll::-webkit-scrollbar-track {
  background: #f1f1f1;
  /* Background of the scrollbar track */
  border-radius: 10px;
}

/* For Firefox */
.eventslistscroll {
  scrollbar-width: thin;
  /* Reduces width of the scrollbar */
  scrollbar-color: #888 #f1f1f1;
  /* Thumb and track colors */
}

.eventslistscroll .v-data-footer__select {
  margin-right: -8px !important;
}

.map-customers-list-table td {
  border: 0px;
}

.map-customers-list-table .text-start {
  padding: 0px !important;
}

.sensorplotting .v-expansion-panel-content__wrap {
  padding: 0px !important;
}

.v-navigation-drawer {
  top: 55px !important;
  max-height: calc(100% - 55px) !important;
}
</style>
<style>
.floorplanimage .v-responsive__content {
  margin: auto;
}

.floorplancarousel,
.floorplancarousel .v-carousel__item {
  min-height: 570px !important;
}

.greenborder {
  border-left: 1px solid black;

  border-right: 1px solid black;
}

.yellowborder {
  border-right: 1px solid black;
}

.blackborder {
  border-right: 1px solid black;
}

.pricing-container {
  display: flex;
  gap: 20px;
}

.pricing-card {
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  text-align: center;
  width: 250px;
  box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
}

.pricing-container .header {
  font-size: 20px;
  font-weight: bold;
  color: white;
  padding: 10px;
  border-radius: 10px 10px 0 0;
}

.basic .header {
  background: #4a49da;
}

.standard .header {
  background: #f4c000;
}

.premium .header {
  background: #e91e63;
}

.price {
  font-size: 40px;
  font-weight: bold;
  margin: 10px 0;
}

.features {
  list-style: none;
  padding: 0;
}

.features li {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 5px 0;

  text-align: left;
}

.check {
  font-size: 18px;
}

.basic .check {
  color: #4a49da;
}

.standard .check {
  color: #f4c000;
}

.premium .check {
  color: #e91e63;
}

.disabled {
  color: #ccc;
}

.buy-button {
  display: inline-block;
  margin-top: 10px;
  padding: 10px;
  font-size: 16px;
  font-weight: bold;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  color: white;
}

.basic .buy-button {
  background: #4a49da;
}

.standard .buy-button {
  background: #f4c000;
}

.premium .buy-button {
  background: #e91e63;
}

.services-table td {
  text-align: left;
}

.services-table .v-data-table__wrapper {
  min-height: 200px;
}

.custom-header-background {
  background-color: #1976d2 !important;
  /* Change to your preferred color */
  color: white !important;
  /* Text color */
}

#table-services {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#table-services td,
#table-services th {
  border-bottom: 1px solid #2d2d2d;
  padding: 6px;
}

#table-services tr:nth-child(even) {
  /* background-color: #f2f2f2; */
}

#table-services tr:hover {
  /* background-color: #ddd; */
}

#table-services td {
  text-align: left;
}

#table-services th {
  padding-top: 6px;
  padding-bottom: 6px;
  text-align: left;
  /* background-color: #203864; */
  color: white;
  font-weight: normal;
}

#table-services .v-radio {
  margin: auto;
}

/*
.simpletable table,
.simpletable th,
.simpletable td {
  border-top: 1px solid black;
  border-collapse: collapse;
}
.simpletable tr:last-child td {
  border-bottom: 1px solid black;
  border-collapse: collapse;
}
.simpletable td {
  height: 50px;
  inline-size: 100px;
  overflow: hidden;
  color: black;
  vertical-align: middle;
} */

.table-header-color th {
  color: #4b9eff !important;
}

.tiptap-vuetify-editor__btn-icon,
.tiptap-vuetify-editor__action-render-btn .v-icon {
  color: #fff !important;
}

.tiptap-vuetify-editor__content p {
  margin-top: 5px !important;
  margin-bottom: 5px !important;
}

.ProseMirror {
  height: 500px !important;
}

.v-data-table-header th {
  color: #4b9eff !important;
  font-weight: normal;
  font-size: 15px !important;
}

.v-data-table-header tr {
  border-top: 0px solid #ddd;
}

.alarmevents .v-chip__content {
  margin: auto;
}

.pharagrah-content p {
  margin-bottom: 5px;
}

.tiptap-vuetify-editor .tiptap-vuetify-editor__content p {
  margin-top: 0px !important;
  margin-bottom: 5px !important;
}

.v-dialog__content {
  height: 0 !important;
  /* pointer-events: none !important; */
}

.v-dialog__content.v-dialog__content--active {
  height: 100dvh !important;

  /* pointer-events: auto !important; */
}

@supports not (height: 100dvh) {
  .v-dialog__content.v-dialog__content--active {
    height: 100vh !important;
  }
}

/* Global (non-scoped) CSS */
.theme--light .menu-bordered {
  border: 1px solid #E5E7EB !important;
  border-radius: 12px;
}

.theme--dark .menu-bordered {
  border: 1px solid #3A3F4B !important;
  border-radius: 12px;
}

.theme--light .themed-outline.v-text-field--outlined>.v-input__control>.v-input__slot {
  border-color: #D1D5DB !important;
  /* light border */
}

.theme--dark .themed-outline.v-text-field--outlined>.v-input__control>.v-input__slot {
  border-color: #4B5563 !important;
  /* dark border */
}
</style>

<style>
.dashboard-bg {
  background: #020617;
  /* overall dark dashboard */
}

/* outer shell with gradient border */
.card-shell {
  position: relative;
  border-radius: 18px;
  padding: 2px;
  margin-bottom: 22px;
  background: linear-gradient(135deg, #1e293b, #020617);
}

.card-shell.inprogress {
  background: linear-gradient(135deg, #2563eb, #0ea5e9);
}

.card-shell.completed {
  background: linear-gradient(135deg, #16a34a, #4ade80);
}

.car-card {
  border-radius: 16px;
  background: #020617;
  color: #e5e7eb;
  box-shadow: 0 18px 30px rgba(0, 0, 0, 0.5);
}

/* status bar top */
.card-status {
  display: flex;
  align-items: center;
  padding: 10px 14px 4px;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #9ca3af;
}

.status-dot {
  width: 9px;
  height: 9px;
  border-radius: 999px;
  margin-right: 8px;
  background: #6b7280;
}

.status-dot.inprogress {
  background: #3b82f6;
  box-shadow: 0 0 10px rgba(59, 130, 246, 0.8);
}

.status-dot.completed {
  background: #22c55e;
  box-shadow: 0 0 10px rgba(34, 197, 94, 0.8);
}

.status-text {
  font-weight: 600;
}

.small-tag {
  font-size: 11px;
  padding: 3px 10px;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.9);
  border: 1px solid #334155;
}

/* image */
.car-img {
  border-radius: 14px;
  background: #0b1220;
}

/* titles */
.car-title {
  font-size: 16px;
  font-weight: 600;
}

.car-reg {
  font-size: 12px;
  color: #9ca3af;
  margin-top: 2px;
}

/* times row */
.meta-row {
  display: flex;
  justify-content: space-between;
  margin-top: 4px;
}

.meta-row label {
  font-size: 11px;
  color: #6b7280;
}

.meta-row p {
  margin: 3px 0 0;
  font-size: 13px;
  font-weight: 600;
  color: #e5e7eb;
}
</style>
