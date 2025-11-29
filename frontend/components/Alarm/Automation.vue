<template>
  <div>
    <div class="text-center">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog v-model="dialogEditAutomation" width="600px">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span>Automation/Notification Manager Info </span>
          <v-spacer></v-spacer>
          <v-icon
            @click="
              closeDialog();
              dialogEditAutomation = false;
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text>
          <v-container style="min-height: 100px">
            <AlarmEditAutomation
              :key="key"
              :customer_id="customer_id"
              @closeDialog="closeDialog"
              :editId="editId"
              :editItem="editItemobject"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-row>
      <v-col cols="12" class="text-right">
        <v-row>
          <v-col cols="8"></v-col>
          <v-col cols="4" class="text-right">
            <v-row>
              <v-col cols="2">
                <!-- <CustomFilter
                  style="float: right; padding-top: 5px; z-index: 9999"
                  @filter-attr="filterAttr"
                  :default_date_from="date_from"
                  :default_date_to="date_to"
                  :defaultFilterType="1"
                  :height="'40px'"
                /> -->
              </v-col>
              <v-col cols="6">
                <v-text-field
                  width="200px"
                  class="employee-schedule-search-box mt-2"
                  @input="getDataFromApi()"
                  v-model="commonSearch"
                  label="Search"
                  dense
                  outlined
                  type="text"
                  append-icon="mdi-magnify"
                  clearable
                  hide-details
                ></v-text-field
              ></v-col>

              <v-col
                cols="1"
                style="padding-left: 0px; max-width: 50px"
                class="text-left"
              >
                <v-btn
                  v-if="isEditable"
                  small
                  dense
                  color="primary"
                  @click="
                    key = key + 1;

                    dialogEditAutomation = true;
                  "
                  class="ma-2"
                  style="margin-top: -5px"
                >
                  New
                </v-btn>
              </v-col>
              <v-col cols="1" style="max-width: 50px">
                <!-- <v-btn icon small dense color="primary" class="ma-2">
                  <v-icon>mdi mdi-download</v-icon>
                </v-btn> -->
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-data-table
          :headers="headers"
          :items="items"
          :server-items-length="totalRowsCount"
          :loading="loading"
          :options.sync="options"
          :footer-props="{
            itemsPerPageOptions: [10, 50, 100, 500, 1000],
          }"
          class="elevation-0"
        >
          <template v-slot:item.sno="{ item, index }">
            {{
              currentPage
                ? (currentPage - 1) * perPage +
                  (cumulativeIndex + items.indexOf(item))
                : "-"
            }}
          </template>
          <template v-slot:item.device="{ item }">
            <div>{{ item.device?.name }}</div>
            <div class="secondary-value">
              {{ item.device?.serial_number ?? "---" }}
            </div>
          </template>
          <template v-slot:item.sensor="{ item }">
            <div>
              {{ item.device?.device_type }}
            </div>
            <div v-if="item.zone_name" class="secondary-value">
              Sensor/Zone:{{ item.zone_name }}
            </div>
          </template>
          <template v-slot:item.location="{ item }">
            {{ item.device?.location }}
          </template>
          <template v-slot:item.zone="{ item }">
            <div>{{ item.zone }}</div>
            <div class="secondary-value">{{ item.area }}</div>
          </template>

          <template v-slot:item.status="{ item }">
            <div style="color: red" v-if="item.alarm_end_datetime == ''">
              Open
            </div>
            <div v-else>Closed</div>
          </template>
          <template v-slot:item.options="{ item }">
            <v-menu bottom left>
              <template v-slot:activator="{ on, attrs }">
                <v-btn dark-2 icon v-bind="attrs" v-on="on">
                  <v-icon>mdi-dots-vertical</v-icon>
                </v-btn>
              </template>
              <v-list width="120" dense>
                <v-list-item v-if="isEditable" @click="editItem(item)">
                  <v-list-item-title style="cursor: pointer">
                    <v-icon color="secondary" small> mdi-pencil </v-icon>
                    Edit
                  </v-list-item-title>
                </v-list-item>
                <v-list-item v-if="isEditable" @click="deleteItem(item.id)">
                  <v-list-item-title style="cursor: pointer">
                    <v-icon color="red" small> mdi-delete </v-icon>
                    Delete
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </template>
        </v-data-table>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import AlarmEditAutomation from "../../components/Alarm/EditAutomation.vue";

export default {
  components: { AlarmEditAutomation },
  props: ["customer_id", "isEditable"],
  data() {
    return {
      snackbar: false,
      response: "",
      key: 1,
      editId: "",
      editItemobject: null,
      dialogEditAutomation: false,
      date_from: "",
      date_to: "",

      loading: false,
      commonSearch: "",
      options: { perPage: 10 },
      cumulativeIndex: 1,
      perPage: 10,
      currentPage: 1,
      totalRowsCount: 0,
      headers: [
        { text: "#", value: "sno", sortable: false },
        { text: "Device", value: "device", sortable: false },
        { text: "Sensor", value: "sensor", sortable: false },
        { text: "Location", value: "location", sortable: false },

        // { text: "Alarm Type", value: "alarm_type" , sortable: false },
        { text: "Name", value: "name", sortable: false },
        // { text: "End Date", value: "end_date" , sortable: false },
        { text: "Mobile Number", value: "mobile_number", sortable: false },
        { text: "Email", value: "email", sortable: false },
        { text: "Whatsapp Number", value: "whatsapp_number", sortable: false },

        { text: "Options", value: "options", sortable: false },
      ],
      items: [],
    };
  },
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  created() {
    if (this.customer_id) {
      // let today = new Date();
      // let monthObj = this.$dateFormat.monthStartEnd(today);
      // this.date_from = monthObj.first;
      // this.date_to = monthObj.last;
      //this.getDataFromApi();

      this.getDevicesList();
    }
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    closeDialog() {
      this.getDataFromApi();
      this.dialogEditAutomation = false;
    },
    filterAttr(data) {
      this.date_from = data.from;
      this.date_to = data.to;

      this.getDataFromApi();
    },
    editItem(item) {
      this.key = this.key + 1;
      this.editItemobject = item;
      this.dialogEditAutomation = true;
    },
    getDevicesList() {
      this.$axios
        .get(`device-list`, {
          params: {
            company_id: this.$auth.user.company_id,
            customer_id: this.customer_id,
          },
        })
        .then(({ data }) => {
          this.devicesList = data;
        });
    },

    deleteItem(id) {
      if (confirm("Are you sure want to delete Alarm Notificaton  ?")) {
        this.loading = true;
        let options = {
          params: {
            company_id: this.$auth.user.company_id,
            id: id,
          },
        };

        try {
          this.$axios.delete(`delete-automation`, options).then(({ data }) => {
            this.snackbar = true;
            this.response = "Notification Deleted Successfully";
            this.getDataFromApi();
            this.loading = false;
          });
        } catch (e) {}
      }
    },
    getDataFromApi() {
      if (this.customer_id) {
        this.loading = true;

        let { sortBy, sortDesc, page, itemsPerPage } = this.options;

        let sortedBy = sortBy ? sortBy[0] : "";
        let sortedDesc = sortDesc ? sortDesc[0] : "";
        this.perPage = itemsPerPage;
        this.currentPage = page;
        if (!page > 0) return false;
        let options = {
          params: {
            page: page,
            sortBy: sortedBy,
            sortDesc: sortedDesc,
            perPage: itemsPerPage,
            pagination: true,
            company_id: this.$auth.user.company_id,
            customer_id: this.customer_id,
            // date_from: this.date_from,
            // date_to: this.date_to,
            common_search: this.commonSearch,
          },
        };

        try {
          this.$axios.get(`automation`, options).then(({ data }) => {
            this.items = data.data;

            this.totalRowsCount = data.total;
            this.loading = false;
          });
        } catch (e) {}
      }
    },
  },
};
</script>
