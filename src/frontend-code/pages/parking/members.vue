<template>
  <NoAccess v-if="!can('operators_view')" />
  <div v-else>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="DialogImport" max-width="80%">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense> CSV Import</span>
          <v-spacer></v-spacer>
          <v-icon @click="DialogImport = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <ImportMembersCsvPopup @refreshdata="getDataFromApi()" :key="key" />
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="DialogCredits" max-width="400px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense>
            Member - Credits - Avaialble :
            {{ item ? item.guest_parking_hours_count : 0 }}</span
          >
          <v-spacer></v-spacer>
          <v-icon @click="DialogCredits = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <ParkingMemberCredits
            :editable="true"
            :key="key"
            :memberId="memberId"
          />
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="DialogMemberParkingList" max-width="800px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense> Member - Vehicles/Guest List </span>
          <v-spacer></v-spacer>
          <v-icon @click="DialogMemberParkingList = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <MemberVehiclesList :key="key" :memberId="memberId" />
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-dialog v-model="newMemberDialog" max-width="1200px">
      <v-card>
        <v-card-title dark class="popup_background_noviolet">
          <span dense> {{ editId ? "Update" : "New" }} Membership Account</span>
          <v-spacer></v-spacer>
          <v-icon @click="newMemberDialog = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <EditMembers
            :key="key"
            :editId="editId"
            :item="item"
            :editable="editable"
            @closeDialog="closeMemberDialog"
          />
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-card
      elevation="0"
      class="mt-0"
      :style="'height:' + (browserHeight - 20) + 'px'"
    >
      <v-toolbar dense flat>
        <v-toolbar-title> <span> Members/Vehicles List </span></v-toolbar-title>

        <v-btn
          title="Reload"
          dense
          class="ma-0 px-0"
          x-small
          :ripple="false"
          @click="getDataFromApi"
          text
        >
          <v-icon class="ml-2" dark>mdi mdi-reload</v-icon>
        </v-btn>

        <v-spacer></v-spacer>
        <div>
          <v-row>
            <v-col style="max-width: 200px"
              ><v-autocomplete
                @change="getDataFromApi()"
                label="Members"
                class="employee-schedule-search-box"
                style="z-index: 999"
                height="20px"
                outlined
                v-model="filterMemberType"
                dense
                :items="[
                  { value: null, text: 'All Members' },
                  { value: 'Tenant', text: 'Tenants' },
                  { value: 'Membership', text: 'Paid Members' },
                ]"
                item-text="text"
                item-value="value"
                hide-details
              ></v-autocomplete
            ></v-col>
            <v-col style="max-width: 200px"
              ><v-autocomplete
                @change="getDataFromApi()"
                label="Status"
                class="employee-schedule-search-box"
                style="z-index: 999"
                height="20px"
                outlined
                v-model="filterMemberStatus"
                dense
                :items="[
                  { value: null, text: 'All Members' },
                  { value: false, text: 'Blocked' },
                  { value: true, text: 'Active' },
                ]"
                item-text="text"
                item-value="value"
                hide-details
              ></v-autocomplete
            ></v-col>

            <v-col style="max-width: 200px"
              ><v-autocomplete
                @change="
                  () => {
                    loadSlotList(filterFloor);
                  }
                "
                label="Floor"
                class="employee-schedule-search-box"
                style="z-index: 999"
                height="20px"
                outlined
                v-model="filterFloor"
                dense
                :items="floorList"
                item-text="name"
                item-value="id"
                hide-details
              ></v-autocomplete
            ></v-col>

            <v-col style="max-width: 200px"
              ><v-autocomplete
                @change="getDataFromApi()"
                label="Parking Number"
                class="employee-schedule-search-box"
                style="z-index: 999"
                height="20px"
                outlined
                v-model="filterSlotNumber"
                dense
                :items="[{ id: null, name: `All Slots` }, ...slotNumbers]"
                item-text="name"
                item-value="id"
                hide-details
              ></v-autocomplete
            ></v-col>

            <v-col style="max-width: 200px"
              ><v-autocomplete
                @change="getDataFromApi()"
                label="Room Number"
                class="employee-schedule-search-box"
                style="z-index: 999"
                height="20px"
                outlined
                v-model="filterRoomNumber"
                dense
                :items="[{ id: null, name: `All Rooms` }, ...slotNumbers]"
                item-text="name"
                item-value="id"
                hide-details
              ></v-autocomplete
            ></v-col>

            <v-col style="max-width: 250px">
              <span style=""
                ><v-text-field
                  style=""
                  height="20"
                  @click:clear="
                    commonSearch = '';
                    getDataFromApi();
                  "
                  class="employee-schedule-search-box"
                  @input="getDataFromApi()"
                  v-model="commonSearch"
                  label="Search (min 3)"
                  dense
                  outlined
                  type="text"
                  append-icon="mdi-magnify"
                  clearable
                  hide-details
                ></v-text-field
              ></span>
            </v-col>

            <v-col style="max-width: 40px">
              <v-btn
                v-if="can('operators_create')"
                title="Change Request"
                x-small
                :ripple="false"
                text
                @click="addItem()"
              >
                <v-icon class="">mdi mdi-plus-circle</v-icon>
              </v-btn> </v-col
            ><v-col style="max-width: 40px">
              <v-btn
                v-if="can('operators_create')"
                title="Change Request"
                x-small
                :ripple="false"
                text
                @click="
                  key++;
                  DialogImport = true;
                "
              >
                <v-icon class="">mdi-calendar-export</v-icon>
              </v-btn>
            </v-col>
          </v-row>
        </div>
      </v-toolbar>

      <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
        {{ snackText }}

        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
        </template>
      </v-snackbar>
      <v-data-table
        dense
        :headers="headers"
        :items="data"
        :loading="loading"
        :options.sync="options"
        :footer-props="{
          itemsPerPageOptions: [10, 50, 100, 500, 1000],
        }"
        class="elevation-1"
        :server-items-length="totalRowsCount"
        fixed-header
        :height="tableHeight"
        :disable-sort="true"
      >
        <template v-slot:item.sno="{ item, index }">
          {{
            currentPage
              ? (currentPage - 1) * perPage +
                (cumulativeIndex + data.indexOf(item))
              : ""
          }}
        </template>
        <template v-slot:item.first_name="{ item, index }" style="width: 300px">
          <v-row no-gutters>
            <v-col
              style="
                padding: 5px;
                padding-left: 0px;
                width: 50px;
                max-width: 50px;
              "
            >
              <v-img
                style="
                  border-radius: 50%;
                  height: 50px;
                  width: 50px;
                  max-width: 50px;
                "
                :src="item.picture ? item.picture : '/no-business_profile.png'"
              >
              </v-img>
            </v-col>
            <v-col style="padding: 10px">
              <div style="font-size: 13px">
                {{
                  item.first_name ? item.first_name + " " + item.last_name : ""
                }}
              </div>
            </v-col>
          </v-row>
        </template>

        <template v-slot:item.contact_number="{ item }">
          {{ item.phone || "---" }}
        </template>
        <template v-slot:item.email="{ item }">
          {{ item.email || "---" }}
        </template>

        <template v-slot:item.guest_parking_hours_count="{ item }">
          {{ item.guest_parking_hours_count || "---" }}
        </template>

        <template v-slot:item.is_active="{ item }">
          <v-chip
            v-if="item.is_active"
            color="green"
            small
            text-color="white"
            class="ma-1"
            style="
              font-size: 12px;
              text-align: center;
              height: 20px;
              padding-top: 0px;
              padding-bottom: 0px;
              width: 80px;
            "
          >
            Active
          </v-chip>
          <v-chip
            v-else
            color="#ef4444"
            small
            text-color="white"
            class="ma-1"
            style="
              font-size: 12px;
              text-align: center;
              height: 20px;
              padding-top: 0px;
              padding-bottom: 0px;
              width: 80px;
            "
          >
            Blocked
          </v-chip>
        </template>
        <template v-slot:item.membership_start="{ item }">
          <div
            v-if="item.member_type == 'Membership' && !item.membership_start"
            style="color: #ef4444"
          >
            Invoice Pending
          </div>
          <div v-else>{{ item.membership_start || "---" }}</div>
        </template>
        <template v-slot:item.membership_end="{ item }">
          {{ item.membership_end || "---" }}
        </template>
        <template v-slot:item.options="{ item }">
          <v-menu bottom left>
            <template v-slot:activator="{ on, attrs }">
              <v-btn dark-2 icon v-bind="attrs" v-on="on">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </template>
            <v-list width="120" dense>
              <v-list-item @click="viewItem(item)">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-eye </v-icon>
                  View
                </v-list-item-title>
              </v-list-item>
              <v-list-item @click="viewGuestsList(item)">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-car </v-icon>
                  View Guests
                </v-list-item-title>
              </v-list-item>
              <v-list-item @click="addCredits(item)">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-cash-100 </v-icon>
                  Add Credits
                </v-list-item-title>
              </v-list-item>

              <v-list-item @click="editItem(item)">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-pencil </v-icon>
                  Edit
                </v-list-item-title>
              </v-list-item>

              <v-list-item @click="deleteItem(item)">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="error" small> mdi-delete </v-icon>
                  Delete
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>

<script>
import EditMembers from "../../components/Parking/EditMembers.vue";
import ImportMembersCsvPopup from "../../components/Parking/ImportMembersCsvPopup.vue";
import MemberVehiclesList from "../../components/Parking/MemberVehiclesList.vue";
import ParkingMemberCredits from "../../components/Parking/ParkingMemberCredits.vue";

export default {
  components: {
    EditMembers,
    MemberVehiclesList,
    ParkingMemberCredits,
    ImportMembersCsvPopup,
  },
  data: () => ({
    DialogImport: false,
    DialogCredits: false,
    memberId: null,
    DialogMemberParkingList: false,
    dialogSecurityCustomers: false,
    editId: null,
    item: null,
    editable: false,
    key: 1,
    viewCustomerId: null,
    commonSearch: "",
    filterMemberStatus: null,
    filterMemberType: null,
    filterFloor: null,
    filterSlotNumber: null,
    filterRoomNumber: null,
    perPage: 10,
    cumulativeIndex: 1,
    currentPage: 1,
    branchesList: [],
    floorList: [],
    slotNumbers: [],
    isCompany: true,
    tableHeight: 750,
    id: "",

    newMemberDialog: false,
    dialogViewCustomer: false,
    totalRowsCount: 0,

    snack: false,
    snackColor: "",
    snackText: "",
    departments: [],
    Model: "Log",
    security_id: null,
    endpoint: "parking_members",
    payload: {},
    loading: true,
    browserHeight: 700,

    data: [],
    headers: [
      {
        text: "#",
        value: "sno",
      },
      {
        text: "Floor",
        value: "floor_no",
      },
      {
        text: "Parking Number",
        value: "slot_number",
      },

      {
        text: "Room Number",
        value: "unit_number",
      },

      {
        text: "Owner Name",
        value: "first_name",
      },
      {
        text: "Contact Number",
        value: "phone",
      },
      {
        text: "Email",
        value: "email",
      },
      {
        text: "Type",
        value: "member_type",
      },
      {
        text: "Active/Blocked",
        value: "is_active",
      },
      {
        text: "Membership Start",
        value: "membership_start",
      },
      {
        text: "Membership End",
        value: "membership_end",
      },
      {
        text: "Guest Balance",
        value: "guest_parking_hours_count",
      },

      {
        text: "Options",
        value: "options",
      },
    ],
    ids: [],

    data: [],
    devices: [],
    total: 0,
    pagination: {
      current: 1,
      total: 0,
      itemsPerPage: 1000,
    },
    payloadOptions: {},
    options: { perPage: 10 },
    errors: [],
    snackbar: false,
    branchesList: [],
    branch_id: "",

    responseStatusColor: "",
    response: "",
    buildingTypes: [],
    _id: null,
    isBackendRequestOpen: false,
  }),
  computed: {},
  mounted() {
    this.tableHeight = window.innerHeight - 270;
    window.addEventListener("resize", () => {
      this.tableHeight = window.innerHeight - 270;
    });

    this.getBuildingTypes();
    this.getDataFromApi();
    this.loadFloorList();
  },
  created() {
    this._id = 4; //this.$route.params.id;
    this.loading = true;

    if (this.$auth.user.branch_id) {
      this.branch_id = this.$auth.user.branch_id;
      this.isCompany = false;
      return;
    }
    try {
      if (window) this.browserHeight = window.innerHeight - 70;
    } catch (e) {}
  },
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  methods: {
    loadFloorList() {
      this.$axios
        .get("floor-list", {
          params: {
            company_id: this.$auth.user.company_id,
          },
        })
        .then(({ data }) => {
          this.floorList = [{ id: null, name: "All Floors" }, ...data];
        })
        .catch((e) => {
          console.log("Floor load error", e);
        });
    },
    loadSlotList(floor_no) {
      this.$axios
        .get("parking-slots-by-floors", {
          params: {
            company_id: this.$auth.user.company_id,
            floor_no: floor_no,
          },
        })
        .then(({ data }) => {
          this.slotNumbers = data;
        })
        .catch((e) => {
          console.log("Floor load error", e);
        });
    },
    loadRoomList(floor_no) {
      this.$axios
        .get("rooms-by-floors", {
          params: {
            company_id: this.$auth.user.company_id,
            floor_no: floor_no,
          },
        })
        .then(({ data }) => {
          this.roomNumbers = [{ id: null, name: "All Rooms" }, ...data];
        })
        .catch((e) => {
          console.log("Rooms load error", e);
        });
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    viewCustomers(item) {
      this.security_id = item.id;
      this.item = item;
      this.key += 1;
      this.dialogSecurityCustomers = true;
    },
    getExpiryDatesCountColor(date) {
      const today = new Date();

      const targetDate = new Date(date);

      const diffTime = targetDate - today;

      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      if (diffDays < 0) {
        return "red";
      } else if (diffDays <= 30) {
        return "orange";
      }
    },
    closeMemberDialog() {
      this.newMemberDialog = false;
      this.DialogCredits = false;

      this.getDataFromApi();
    },
    getBuildingTypes() {
      if (this.$store.state.storeAlarmControlPanel?.BuildingTypes) {
        this.buildingTypes =
          this.$store.state.storeAlarmControlPanel.BuildingTypes;
      }
    },
    addItem() {
      this.editId = null;
      this.editable = true;
      this.key += 1;
      this.item = null;
      this.viewCustomerId = null;
      this.newMemberDialog = true;
    },

    viewItem(item) {
      this.editId = item.id;
      this.editable = false;
      this.viewCustomerId = item.id;
      this.key += 1;
      this.item = item;
      this.newMemberDialog = true;
    },
    addCredits(item) {
      this.memberId = item.id;
      this.key += 1;
      this.item = item;
      this.DialogCredits = true;
    },
    viewGuestsList(item) {
      this.memberId = item.id;
      this.key += 1;
      this.item = item;
      this.DialogMemberParkingList = true;
    },
    // viewItem2(item) {
    //   this.$router.push("/alarm/view-customer/" + item.id);
    // },
    editItem(item) {
      this.editable = true;
      this.editId = item.id;
      this.key += 1;
      this.item = item;
      this.newMemberDialog = true;
    },

    deleteItem(item) {
      if (confirm("Are you sure want to delete  ?")) {
        this.loading = true;
        let options = {
          params: {
            company_id: this.$auth.user.company_id,
            id: item.id,
          },
        };

        this.$axios.delete(`parking_members/${item.id}`).then(({ data }) => {
          this.snackbar = true;
          this.response = "Security Deleted Successfully";
          this.getDataFromApi();
          this.loading = false;
        });
      }
    },

    getDataFromApi(url = "", filter_column = "", filter_value = "") {
      if (this.isBackendRequestOpen) return false;
      this.isBackendRequestOpen = true;

      url = this.endpoint;

      this.newCustomerDialog = false;

      const { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";

      this.payloadOptions = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          company_id: this.$auth.user.company_id,
          common_search: this.commonSearch,
          filterMemberStatus: this.filterMemberStatus,
          filterMemberType: this.filterMemberType,
          floor_no: this.filterfloor,
          slot_number: this.filterSlotNumber,
          unit_number: this.filterRoomNumber,
          // branch_id: this.branch_id,
          ...this.payload,
        },
      };
      if (filter_column != "")
        this.payloadOptions.params[filter_column] = filter_value;
      this.loading = true;

      this.currentPage = page;
      this.perPage = itemsPerPage;
      try {
        this.$axios.get(url, this.payloadOptions).then(({ data }) => {
          this.isBackendRequestOpen = false;
          this.data = data.data;
          this.total = data.total;
          this.loading = false;
          this.totalRowsCount = data.total;
        });
      } catch (e) {
        console.log(e);
        this.loading = false;
      }
    },
  },
};
</script>
