<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-dialog v-model="dialogEditCustomerNotes" width="600px">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span style="color: black111">Alarm Notes Information </span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="
              closeDialog();
              dialogEditCustomerNotes = false;
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text>
          <v-container style="min-height: 100px">
            <EditAlarmCustomerEventNotes
              name="EditAlarmCustomerEventNotes"
              :key="key"
              :customer_id="customer_id"
              @closeDialog="closeDialog"
              :alarmId="alarm_id"
              :noteId="noteId"
              :editItem="editItem"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogViewPhotos" width="60%">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span style="color: black111">
            {{ editItem ? editItem.title : "---" }}</span
          >
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="dialogViewPhotos = false"
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text>
          <v-container style="min-height: 100px">
            <v-img
              :src="editItem ? editItem.picture : '/no-image.png'"
              aspect-ratio="1"
              class="grey222 lighten-2"
              width="100%"
            >
            </v-img>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-row>
      <v-col
        v-if="showOptions == 'true'"
        cols="12"
        class="text-right"
        style="padding-top: 0px"
      >
        <v-btn color="primary" @click="addNotes()" dense x-small> Add </v-btn>
      </v-col>
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
          <template v-slot:item.picture="{ item }">
            <v-row no-gutters>
              <v-col
                @click="viewPhoto(item)"
                style="
                  padding: 5px;
                  padding-left: 0px;
                  width: 50px;
                  max-width: 50px;
                "
              >
                <v-img
                  style="
                    border-radius: 10%;
                    height: auto;
                    width: 50px;
                    max-width: 50px;
                  "
                  :src="item.picture ? item.picture : '/noimage.png'"
                >
                </v-img>
              </v-col>
            </v-row>
          </template>
          <template v-slot:item.date="{ item, index }">
            {{ $dateFormat.format4(item.created_datetime) }}
          </template>
          <template v-slot:item.options="{ item }" v-if="showOptions == 'true'">
            <v-menu bottom left>
              <template v-slot:activator="{ on, attrs }">
                <v-btn dark-2 icon v-bind="attrs" v-on="on">
                  <v-icon>mdi-dots-vertical</v-icon>
                </v-btn>
              </template>
              <v-list width="120" dense>
                <v-list-item v-if="can('branch_view')" @click="addNotes(item)">
                  <v-list-item-title style="cursor: pointer">
                    <v-icon color="secondary" small> mdi-pencil </v-icon>
                    Edit
                  </v-list-item-title>
                </v-list-item>
                <v-list-item
                  v-if="can('branch_edit')"
                  @click="deleteNotes(item.id)"
                >
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
import EditAlarmCustomerEventNotes from "../../components/Alarm/EditCustomerEventNotes.vue";
export default {
  components: { EditAlarmCustomerEventNotes },
  props: ["alarm_id", "customer_id", "showOptions"],
  data() {
    return {
      snackbar: false,
      response: "",
      noteId: "",
      dialogViewPhotos: false,
      editItem: null,
      key: "",

      dialogEditCustomerNotes: false,
      dialogNotesList: false,
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
        { text: "Image", value: "picture", sortable: false },
        { text: "Title", value: "title", sortable: false },
        { text: "Notes", value: "notes", sortable: false },
        { text: "Date", value: "date", sortable: false },
        ,
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
      let today = new Date();
      let monthObj = this.$dateFormat.monthStartEnd(today);
      this.date_from = monthObj.first;
      this.date_to = monthObj.last;
      //this.getDataFromApi();

      if (this.showOptions == "true") {
        this.headers.push({
          text: "Options",
          value: "options",
          sortable: false,
        });
      }
    }
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    viewPhoto(item) {
      if (item.picture) {
        this.editItem = item;
        this.dialogViewPhotos = true;
      }
    },
    viewItem(item) {
      this.alarm_id = item.id;
      this.dialogNotesList = true;
    },
    deleteNotes(id) {
      if (confirm("Are you sure want to delete Alarm Event notes?")) {
        this.loading = true;
        let options = {
          params: {
            company_id: this.$auth.user.company_id,
            id: id,
          },
        };

        try {
          this.$axios.delete(`delete-notes`, options).then(({ data }) => {
            this.snackbar = true;
            this.response = "Event Note Deleted Successfully";
            this.getDataFromApi();
            this.loading = false;
          });
        } catch (e) {}
      }
    },
    addNotes(item = null) {
      this.key = this.key + 1;
      if (item) {
        this.editItem = item;
      }
      this.alarm_id = this.alarm_id;
      this.dialogEditCustomerNotes = true;
    },
    closeDialog() {
      this.dialogEditCustomerNotes = false;
      this.getDataFromApi();
      this.$emit("closeDialog");
    },
    filterAttr(data) {
      this.date_from = data.from;
      this.date_to = data.to;

      this.getDataFromApi();
    },
    getDataFromApi() {
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

          alarm_id: this.alarm_id,
        },
      };

      try {
        this.$axios.get(`get_alarm_events_notes`, options).then(({ data }) => {
          this.items = data.data;
          this.totalRowsCount = data.total;

          this.loading = false;
        });
      } catch (e) {}
    },
  },
};
</script>
