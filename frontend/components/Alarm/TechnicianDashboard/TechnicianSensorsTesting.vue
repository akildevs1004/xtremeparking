<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row>
      <v-col class="mt-0 pt-0">
        <v-card elevation="0" class="mt-0">
          <v-data-table
            dense
            :headers="headers"
            :items="data"
            :loading="loading"
            :options.sync="options"
            :items-per-page="-1"
            :footer-props="{
              itemsPerPageOptions: [10, 50, 100, 500, 1000],
            }"
            class="elevation-1"
            fixed-header
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

            <template v-slot:item.sensor_type="{ item }">
              {{ item.sensor_type || "---" }}
            </template>

            <template v-slot:item.area_code="{ item }">
              {{
                item.area_code == ""
                  ? "Default"
                  : getAreaName(item.area_code) ?? "Default"
              }}
            </template>

            <template v-slot:item.test_status="{ item }">
              <div>
                <v-btn
                  v-if="testingStatus == false"
                  small
                  color="primary"
                  fill
                  @click="sensor_test(item)"
                  >Test</v-btn
                >
                <v-btn
                  v-if="testingStatus == true && item.test_status == 1"
                  :loading="true"
                  small
                  color="red"
                  fill
                  @click="stop_test(item)"
                >
                </v-btn>
              </div>
            </template>
            <template v-slot:item.test_result="{ item }">
              <!-- {{ item.test_result }} -->
              <v-icon
                v-if="item.test_result == null || item.test_result == 0"
                small
                color="red"
                fill
                >mdi-close-circle-outline</v-icon
              >
              <v-icon v-else-if="item.test_result == 1" small color="green" fill
                >mdi-check-circle</v-icon
              >

              <v-icon v-else small color="brown" fill>checking...</v-icon>
              <div class="secondary-value1" v-if="item.test_result != 2">
                {{ item.test_datetime }}
              </div>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
    <v-row>
      <v-col></v-col>
      <v-col style="max-width: 100px">
        <span style="padding: 5px" @click="downloadOptions('print')"
          ><img src="/icons/icon_print.png" class="iconsize"
        /></span>
        <span style="padding: 5px" @click="downloadOptions('download')"
          ><img src="/icons/icon_pdf.png" class="iconsize"
        /></span>
      </v-col>
      <v-col style="max-width: 150px">
        <v-btn small fill color="primary" @click="saveResults()"
          >Save Results</v-btn
        >
      </v-col>

      <!-- <v-col v-if="savedResults" style="max-width: 150px">
        <v-btn small fill color="primary" @click="saveResults()">Step2</v-btn>
      </v-col> -->
    </v-row>
  </div>
</template>

<script>
export default {
  props: ["customer_id", "ticket_id"],
  components: {},
  data: () => ({
    dialogSecurityCustomers: false,
    editId: null,
    item: null,
    editable: false,
    key: 1,
    viewCustomerId: null,
    commonSearch: "",
    perPage: 10,
    cumulativeIndex: 1,
    currentPage: 1,
    branchesList: [],
    isCompany: true,
    tableHeight: 750,
    id: "",
    device_id: null,
    dialogNewZone: false,
    dialogViewCustomer: false,
    totalRowsCount: 0,

    snack: false,
    snackColor: "",
    snackText: "",
    departments: [],
    Model: "Log",
    security_id: null,
    endpoint: "security",
    payload: {},
    loading: false,
    data: [],
    headers: [
      {
        text: "#",
        value: "sno",
      },
      {
        text: "Zone Number",
        value: "zone_code",
      },
      {
        text: "Zone Name",
        value: "location",
      },
      {
        text: "Zone Type",
        value: "sensor_name",
      },
      {
        text: "Sensor Type",
        value: "sensor_type",
      },
      {
        text: "Area",
        value: "area_code",
      },
      {
        text: "Wired/Wireless",
        value: "wired",
      },
      // {
      //   text: "24 Hours",
      //   value: "hours24",
      // },
      {
        text: "Start Test",
        value: "test_status",
      },

      {
        text: "Result",
        value: "test_result",
      },
    ],
    areaList: [
      { id: "01", name: "Area 1" },
      { id: "02", name: "Area 2" },
      { id: "03", name: "Area 3" },
      { id: "04", name: "Area 4" },
      { id: "05", name: "Area 5" },
      { id: "06", name: "Area 6" },
      { id: "07", name: "Area 7" },
      { id: "08", name: "Area 8" },

      { id: "09", name: "Area 9" },
      { id: "10", name: "Area 10" },
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
    interval: null, // Store interval ID
    timeout: null, // Timeout for stopping after 1 minute

    testingStatus: false,
    savedResults: false,
  }),
  computed: {},
  mounted() {
    // this.tableHeight = window.innerHeight - 270;
    // window.addEventListener("resize", () => {
    //   this.tableHeight = window.innerHeight - 270;
    // });
    // this.getBuildingTypes();
  },
  created() {
    // this._id = 4; //this.$route.params.id;
    // if (this.$auth.user.branch_id) {
    //   this.branch_id = this.$auth.user.branch_id;
    //   this.isCompany = false;
    //   return;
    // }
    this.getDataFromApi();
  },
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  beforeUnmount() {
    if (this.interval) clearInterval(this.interval); // Clean up when component unmounts
  },
  methods: {
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    downloadOptions(option) {
      let url = process.env.BACKEND_URL;
      if (option == "print") url += "/technician_test_results_print_pdf";
      if (option == "excel") url += "/technician_test_results_export_excel";
      if (option == "download") url += "/technician_test_results_download_pdf";
      url += "?company_id=" + this.$auth.user.company_id;
      url += "&customer_id=" + this.customer_id;

      url += "&ticket_id=" + this.ticket_id;

      window.open(url, "_blank");
    },
    getDataFromApi(url = "", filter_column = "", filter_value = "") {
      if (this.loading) return false;

      this.newCustomerDialog = false;

      // console.log("this.options", this.options);

      const { sortBy, sortDesc, page, perPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";

      this.payloadOptions = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: perPage,
          company_id: this.$auth.user.company_id,
          customer_id: this.customer_id,
        },
      };

      this.loading = true;

      this.currentPage = page ?? 1;
      this.perPage = perPage ?? 10;

      try {
        this.$axios
          .get("customer_devices_sensors_zones", this.payloadOptions)
          .then(({ data }) => {
            this.isBackendRequestOpen = false;
            this.data = data;
            this.total = data.length;
            this.loading = false;
            this.totalRowsCount = data.length;
          });
      } catch (e) {
        console.log(e);
        this.loading = false;
      }
    },
    getAreaName(area_code) {
      return (
        this.areaList.find((areaName) => areaName.id == area_code)?.name ||
        "---"
      );
    },
    saveResults() {
      if (confirm("Are you sure want to save the Results?")) {
        let dateTime = new Date()
          .toLocaleString("en-CA", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
            hour12: false,
          })
          .replace(",", "");
        var results = [];
        this.data.forEach((element) => {
          console.log("test_datetime_update", element.test_datetime);

          let sensor1 = {
            ticket_id: this.ticket_id,
            company_id: this.$auth.user.company_id,
            serial_number: element.device.serial_number,
            device_id: element.device.id,
            zone_code: element.zone_code,
            area_code: element.area_code,
            customer_id: this.customer_id,
            test_result:
              element.test_result == 1 ? element.test_result == 1 : 0,
            test_date_time: element.test_datetime,
          };
          results.push(sensor1);
        });

        let options = {
          params: { results: results, ticket_id: this.ticket_id },
        };

        try {
          this.$axios
            .post("technician_test_results_update", options.params)
            .then(({ data }) => {
              this.savedResults = true;

              //  this.$emit("savedResults", true);
              if (data.status) {
              }
            });
        } catch (e) {
          console.log(e);
          this.loading = false;
        }
      }
    },
    async fetchData(sensorResult, dateTime) {
      if (this.$route.name == "technician-dashboard") {
        // console.log("sensorResult", sensorResult);

        let options = {
          params: {
            company_id: this.$auth.user.company_id,
            serial_number: sensorResult.device.serial_number,
            device_id: sensorResult.device.id,
            zone: sensorResult.zone_code,
            area: sensorResult.area_code,
            customer_id: this.customer_id,
            test_date_time: dateTime,
          },
        };
        //console.log("options", options);

        try {
          this.$axios
            .get("technician_test_sensor", options)
            .then(({ data }) => {
              if (data.status) {
                this.cleartestingmethod(sensorResult);
                //console.log("Stopped polling as API result is 1");

                this.$set(sensorResult, "test_status", 0); //stop
                this.$set(sensorResult, "test_result", 1); //loading
              }
            });
        } catch (e) {
          console.log(e);
          this.loading = false;
        }
      }
    },
    sensor_test(sensor) {
      this.testingStatus = true;
      var sensorResult = this.data.find((e) => e.id == sensor.id);
      if (sensorResult) {
        this.$set(sensorResult, "test_status", 1);
        this.$set(sensorResult, "test_result", 2); //loading

        let dateTime = new Date()
          .toLocaleString("en-CA", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
            hour12: false,
          })
          .replace(",", "");
        this.interval = setInterval(
          () => this.fetchData(sensorResult, dateTime),
          1000 * 5
        );

        // Stop polling automatically after 1 minute
        this.timeout = setTimeout(() => {
          this.cleartestingmethod(sensorResult);
          //  console.log("Stopped polling after 1 minute");
        }, 1000 * 30);

        // let dateTime = new Date()
        //   .toLocaleString("en-CA", {
        //     year: "numeric",
        //     month: "2-digit",
        //     day: "2-digit",
        //     hour: "2-digit",
        //     minute: "2-digit",
        //     second: "2-digit",
        //     hour12: false,
        //   })
        //   .replace(",", "");
        // this.$set(sensorResult, "test_result", 2); //loading

        // let options = {
        //   param: {
        //     company_id: this.$auth.user.company_id,

        //     serial_number: sensor.device.serial_number,
        //     zone: sensor.zone_code,
        //     area: sensor.area_code,
        //     test_date_time: dateTime,
        //   },
        // };
        // try {
        //   this.$axios
        //     .get("technician_test_sensor", options)
        //     .then(({ data }) => {
        //       // if (data.trim() == 0) {
        //       //   this.$set(sensorResult, "test_result", 0);
        //       // } else this.$set(sensorResult, "test_result", 1);

        //       this.$set(sensorResult, "test_status", 0);
        //     });
        // } catch (e) {
        //   console.log(e);
        //   this.loading = false;
        // }
      }
    },

    cleartestingmethod(sensorResult) {
      //console.log("cleartestingmethod");

      this.testingStatus = false;

      if (this.interval) {
        clearInterval(this.interval);
        this.interval = null;
      }
      if (this.timeout) {
        clearTimeout(this.timeout);
        this.timeout = null;
      }
      if (sensorResult) {
        let dateTime = new Date()
          .toLocaleString("en-CA", {
            // year: "numeric",
            // month: "2-digit",
            // day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
            hour12: false,
          })
          .replace(",", "");

        this.$set(sensorResult, "test_status", 0); //stop
        this.$set(sensorResult, "test_result", 0); //loading
        this.$set(sensorResult, "test_datetime", dateTime); //loading

        console.log("test_datetime", dateTime);
      }
    },
    stop_test(sensor) {
      cleartestingmethod();
      sensor.test_status = 0;
    },
  },
};
</script>
