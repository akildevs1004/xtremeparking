<template>
  <div>
    <v-dialog v-model="dialogGoogleMap" width="600px">
      <v-card>
        <v-card-title dense class="popup_background">
          <span>Google Map Location</span>
          <v-spacer></v-spacer>
          <v-icon color="primary" @click="dialogGoogleMap = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text>
          <v-container style="min-height: 100px">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d14433.170866542543!2d55.291463!3d25.2607368!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e5f43145ef11097%3A0x78e0d1eb3fed1225!2sAkil%20Security%20%26%20Alarm%20System%20Dubai%20LLC!5e0!3m2!1sen!2sae!4v1719239839580!5m2!1sen!2sae"
              width="100%"
              height="450"
              style="border: 0"
              allowfullscreen=""
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogEditContacts" width="1000px">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span style="color: black111">Edit Contacts</span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="dialogEditContacts = false"
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text>
          <v-container style="min-height: 100px">
            <AlarmEditContact
              :customer_id="customer_id"
              :customer_contacts="customer_contacts"
              :customer="customer"
              @closeDialog="closeDialog"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogEditBuilding" width="1000px">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span style="color: black111">Edit Customer/Building Info</span>
          <v-spacer></v-spacer>
          <v-icon color="black" @click="dialogEditBuilding = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text>
          <v-container style="min-height: 100px">
            <NewCustomer
              name="NewCustomerCustomerContact"
              :customer_id="customer_id"
              :customer="customer"
              @closeDialog="closeDialog"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-row>
      <!-- <v-col cols="12" class="text-right" style="padding-top: 0px">
        <v-btn x-small @click="dialogEditContacts = true" color="primary" dense>
          Edit Contact
        </v-btn>
        <v-btn @click="dialogEditBuilding = true" color="primary" dense x-small>
          Edit Building/Customer
        </v-btn>
      </v-col> -->

      <v-col cols="4" style="line-height: 35px">
        <v-row>
          <v-col cols="3" class="p1-0"
            ><v-img
              style="
                width: 100px;

                border-radius: 5%;
                border: 1px solid #ddd;
              "
              :src="
                customer && customer.primary_contact
                  ? customer.primary_contact?.profile_picture
                  : '/no-profile-image.jpg'
              "
            ></v-img>
          </v-col>
          <v-col cols="9" style="line-height: 20px; font-weight: bold">
            <div style="padding-top: 10px; font-weight: ">Primary Contact</div>
            <div style="color: red; font-size: 16px">
              {{
                customer && customer.primary_contact
                  ? customer.primary_contact?.first_name +
                    " " +
                    customer.primary_contact?.last_name
                  : "---"
              }}
            </div>
          </v-col>
        </v-row>

        <v-row>
          <v-col cols="3">
            <!-- <v-icon color="primary" style="line-height: 2px"
              >mdi mdi-cellphone-basic</v-icon
            > -->
            Phone 1
          </v-col>
          <v-col cols="9" class="bold">
            {{
              customer && customer.primary_contact
                ? customer.primary_contact?.phone1
                : "---"
            }}
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="3">
            <!-- <v-icon color="primary" style="line-height: 2px"
              >mdi mdi-cellphone-basic</v-icon
            > -->
            Phone 2</v-col
          >
          <v-col cols="9" class="bold">
            {{
              customer && customer.primary_contact
                ? customer.primary_contact?.phone2
                : "---"
            }}
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="3">
            <!-- <v-icon color="primary" size="18" style="line-height: 2px"
              >mdi mdi-phone-classic</v-icon
            > -->
            Office</v-col
          >
          <v-col cols="9" class="bold">
            {{
              customer && customer.primary_contact
                ? customer.primary_contact?.office_phone
                : "---"
            }}
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="3" class="p1-0">
            <!-- <v-icon color="primary" size="18" style="line-height: 2px"
              >mdi mdi-at</v-icon
            > -->
            Email</v-col
          >
          <v-col cols="9" class="bold">
            {{
              customer && customer.primary_contact
                ? customer.primary_contact?.email
                : "---"
            }}</v-col
          >
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="3" class="p1-0"> Whatsapp</v-col>
          <v-col cols="9" class="bold">
            {{
              customer && customer.primary_contact
                ? customer.primary_contact?.whatsapp
                : "---"
            }}
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="3" class="p1-0"> Alarm PIN </v-col>
          <v-col cols="9" class="bold red--text">
            {{
              customer && customer.primary_contact
                ? customer.primary_contact?.alarm_stop_pin
                : "---"
            }}
            <v-btn
              color="red"
              @click="resetAlarmStopPin('primary')"
              small
              outlined
              >RESET</v-btn
            >
            <!-- <v-icon title="Reset PIN" @click="resetAlarmStopPin('primary')"
              >mdi-refresh</v-icon
            > -->
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="1" style="max-width: 20px">
        <v-divider vertical></v-divider
      ></v-col>
      <v-col cols="4" style="line-height: 35px">
        <v-row>
          <v-col cols="3" class="p1-0"
            ><v-img
              style="
                width: 100px;

                border-radius: 5%;
                border: 1px solid #ddd;
              "
              :src="
                customer && customer.secondary_contact
                  ? customer.secondary_contact?.profile_picture
                  : '/no-profile-image.jpg'
              "
            ></v-img>
          </v-col>
          <v-col cols="9" style="line-height: 20px; font-weight: bold">
            <div style="padding-top: 10px; font-weight: ">
              Secondary Contact
            </div>
            <div style="color: red; font-size: 16px">
              {{
                customer && customer.secondary_contact
                  ? customer.secondary_contact?.first_name +
                    " " +
                    customer.secondary_contact?.last_name
                  : "---"
              }}
            </div>
          </v-col>
        </v-row>

        <v-row>
          <v-col cols="3">
            <!-- <v-icon color="primary" style="line-height: 2px"
              >mdi mdi-cellphone-basic</v-icon
            > -->
            Phone 1
          </v-col>
          <v-col cols="9" class="bold">
            {{
              customer && customer.secondary_contact
                ? customer.secondary_contact?.phone1
                : "---"
            }}
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="3">
            <!-- <v-icon color="primary" style="line-height: 2px"
              >mdi mdi-cellphone-basic</v-icon
            > -->
            Phone 2</v-col
          >
          <v-col cols="9" class="bold">
            {{
              customer && customer.secondary_contact
                ? customer.secondary_contact?.phone2
                : "---"
            }}
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="3">
            <!-- <v-icon color="primary" size="18" style="line-height: 2px"
              >mdi mdi-phone-classic</v-icon
            > -->
            Office</v-col
          >
          <v-col cols="9" class="bold">
            {{
              customer && customer.secondary_contact
                ? customer.secondary_contact?.office_phone
                : "---"
            }}
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="3" class="p1-0">
            <!-- <v-icon color="primary" size="18" style="line-height: 2px"
              >mdi mdi-at</v-icon
            > -->
            Email</v-col
          >
          <v-col cols="9" class="bold">
            {{
              customer && customer.secondary_contact
                ? customer.secondary_contact?.email
                : "---"
            }}</v-col
          >
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="3" class="p1-0">
            <!-- <v-icon color="primary" size="18" style="line-height: 2px"
              >mdi mdi-whatsapp</v-icon
            > -->
            Whatsapp</v-col
          >
          <v-col cols="9" class="bold">
            {{
              customer && customer.secondary_contact
                ? customer.secondary_contact?.whatsapp
                : "---"
            }}
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="3" class="p1-0"> Alarm PIN </v-col>
          <v-col cols="9" class="bold red--text">
            {{
              customer && customer.secondary_contact
                ? customer.secondary_contact?.alarm_stop_pin
                : "---"
            }}

            <v-btn
              color="red"
              @click="resetAlarmStopPin('secondary')"
              small
              outlined
              >RESET</v-btn
            >
            <!-- <v-icon title="Reset PIN" @click="resetAlarmStopPin('secondary')"
              >mdi-refresh</v-icon
            > -->
          </v-col>
        </v-row>
        <!-- <v-row>
          <v-col cols="4" class="p1-0"> </v-col>
          <v-col cols="8">
            <v-img
              style="
                width: 100px;

                border-radius: 5%;
                margin: 0 auto;
              "
              src="/1696868606.jpg"
            ></v-img
          ></v-col>
        </v-row> -->
      </v-col>
      <v-col cols="1" style="max-width: 20px">
        <v-divider vertical></v-divider
      ></v-col>
      <v-col cols="3" style="line-height: 35px">
        <!-- <h3 style="">Building Address</h3> -->

        <v-row style="padding-top: 15px">
          <v-col cols="4"> Type</v-col>
          <v-col cols="8" class="bold"> {{ building_type_name }}</v-col>
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="4">Name</v-col>
          <v-col cols="8" class="bold"
            >{{ customer ? customer.building_name : "---" }}
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="4">House </v-col>
          <v-col cols="8" class="bold">
            {{ customer ? customer.house_number : "---" }}
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="4">Street </v-col>
          <v-col cols="8" class="bold">
            {{ customer ? customer.street_number : "---" }}</v-col
          >
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="4">Area</v-col>
          <v-col cols="8" class="bold">
            {{ customer ? customer.area : "---" }}</v-col
          >
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="4" class="p1-0">City/State</v-col>
          <v-col cols="8" class="bold">
            {{ customer ? customer.city : "---" }},
            {{ customer ? customer.state : "---" }}</v-col
          >
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="4" class="p1-0">Country</v-col>
          <v-col cols="8" class="bold">
            {{ customer ? customer.country : "---" }}
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col cols="4" class="p1-0">Map Positions</v-col>
          <v-col cols="8" class="bold pr-0"
            >{{ customer?.latitude }} <br />
            {{ customer?.longitude }}
            <br />
            <a
              style="text-decoration: none"
              :href="
                'https://maps.google.com/?q=' +
                customer?.latitude +
                ',' +
                customer?.longitude
              "
              target="_blank"
            >
              <img
                src="/google_map.jpg"
                style="width: 30px; padding-top: 5px"
              />
            </a>
          </v-col>
        </v-row>
        <!-- <v-row>
          <v-col cols="4" class="p1-0"> Map Link</v-col>
          <v-col cols="3" class="bold pr-0">
            <v-btn
              @click="dialogGoogleMap = true"
              color="primary"
              text
              outlined
              dense
              small
            >
              Popup</v-btn
            >
          </v-col>
          <v-col cols="5" class="bold text-left pl-0">
            <a
              style="text-decoration: none"
              :href="
                'https://maps.google.com/?q=' + customer
                  ? customer?.latitude + ',' + customer?.longitude
                  : '-'
              "
              target="_blank"
            >
              <img
                src="/google_map.jpg"
                style="width: 30px; padding-top: 5px"
              />
            </a>
          </v-col>
        </v-row> -->
      </v-col>
    </v-row>
  </div>
</template>

<script>
import AlarmEditContact from "../../components/Alarm/EditContacts.vue";
import NewCustomer from "../../components/Alarm/NewCustomer.vue";

export default {
  props: ["customer_id", "customer_contacts", "customer", "building_type_name"],
  components: {
    AlarmEditContact,
    NewCustomer,
  },
  data: () => ({
    dialogEditContacts: false,
    dialogEditBuilding: false,

    dialogGoogleMap: false,
    branchesList: [],
    startDateMenuOpen: "",
    endDateMenuOpen: "",
    preloader: false,
    loading: false,
    upload: {
      name: "",
    },
    start_date: "",
    end_date: "",

    customer_payload: {
      branch_id: "",
      building_type_id: "",
      first_name: "",
      last_name: "",
      building_name: "",
      house_number: "",
      street_number: "",
      city: "",
      state: "",
      country: "",
      landmark: "",
      latitude: "",
      longitude: "",
      contact_number: "",
      start_date: "",
      end_date: "",
    },
    contact_payload: {
      name: "",
      number: "",
      position: "",
      whatsapp: "",
    },
    // location: "",
    geographic_payload: {
      location: "",
      lat: "",
      lon: "",
    },
    e1: 1,
    errors: [],
    previewImage: null,
  }),
  created() {
    this.preloader = false;
    //this.getBranchesList();
    // this.getDataFromApi();
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    closeDialog() {
      this.$emit("closeDialog");
      // location.reload();
      this.dialogEditBuilding = false;
      this.dialogEditContacts = false;
    },
    getBranchesList() {
      this.$axios
        .get(`branches_list`, {
          params: {
            company_id: this.$auth.user.company_id,
          },
        })
        .then(({ data }) => {
          this.branchesList = data;
          this.customer_payload.branch_id = this.branchesList[0].id;
          //this.branch_id = this.$auth.user.branch_id || "";
        });
    },
    onpick_attachment() {
      this.$refs.attachment_input.click();
    },
    attachment(e) {
      this.upload.name = e.target.files[0] || "";

      let input = this.$refs.attachment_input;
      let file = input.files;

      if (file[0].size > 1024 * 1024) {
        e.preventDefault();
        this.errors["logo"] = ["File too big (> 1MB). Upload less than 1MB"];
        return;
      }

      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
          //croppedimage step6
          this.previewImage = e.target.result;

          // this.selectedFile = event.target.result;

          // this.$refs.cropper.replace(this.selectedFile);
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);

        // this.dialogCropping = true;
      }
    },
    submit() {
      let customer = new FormData();

      for (const key in this.customer_payload) {
        customer.append(key, fields[key]);
      }
      if (this.upload.name) {
        customer.append("logo", this.upload.name);
      }

      this.$axios
        .post("/customer", customer)
        .then(({ data }) => {
          //this.loading = false;

          if (!data.status) {
            this.errors = [];
            if (data.errors) this.errors = data.errors;
            this.color = "red";

            this.snackbar = true;
            this.response = data.message;
          } else {
            this.color = "background";
            this.errors = [];
            this.snackbar = true;
            this.response = "Customer Details Created successfully";

            this.$emit("closeDialog");
          }
        })
        .catch((e) => console.log(e));
    },
    resetAlarmStopPin(contactType) {
      if (confirm("Are you sure you want to reset?"))
        this.$axios
          .post(`reset_customer_alarm_pin`, {
            company_id: this.$auth.user.company_id,
            customer_id: this.customer_id,
            contact_type: contactType,
          })
          .then(({ data }) => {
            this.$emit("closeDialog");
            this.snackbar = true;
            this.response =
              "Customer Pin reset successfully and Email Notification sent";
          });
    },
    update() {
      let branch = new FormData();
      branch.append("company_id", this.$auth.user.company_id);
      branch.append("branch_name", this.branch.branch_name);
      branch.append("user_id", this.branch.user_id);

      branch.append("licence_number", this.branch.licence_number);
      branch.append(
        "licence_issue_by_department",
        this.branch.licence_issue_by_department
      );
      branch.append("licence_expiry", this.branch.licence_expiry);
      branch.append("lat", this.branch.lat);
      branch.append("lon", this.branch.lon);
      branch.append("address", this.branch.address);

      if (this.upload.name) {
        branch.append("logo", this.upload.name);
      }

      this.$axios
        .post(`/branch/${this.branch.id}`, branch)
        .then(({ data }) => {
          //this.loading = false;

          if (!data.status) {
            this.errors = [];
            if (data.errors) this.errors = data.errors;
            this.color = "red";

            this.snackbar = true;
            this.response = data.message;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Branch updated successfully";
            //this.getDataFromApi();
            this.branchDialog = false;
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
