<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

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

    <v-dialog v-model="dialogEditEmergency" width="600px">
      <v-card>
        <v-card-title dense class="popup_background_noviolet">
          <span style="color: black111">Contact Information </span>
          <v-spacer></v-spacer>
          <v-icon
            style="color: black3333"
            @click="
              closeDialog();
              dialogEditEmergency = false;
            "
            outlined
          >
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text>
          <v-container style="min-height: 100px">
            <AlarmEditEmergencyContact
              :key="key"
              :customer_id="customer_id"
              :customer_contacts="customer_contacts"
              :customer="customer"
              @closeDialog="closeDialog"
              :editId="editId"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <div
      style="height: 50px"
      v-if="!customer_contacts || customer_contacts.length == 0"
      class="text-center"
    >
      No Contacts Available
    </div>
    <v-row>
      <v-col cols="12" class="text-right" style="padding-top: 0px">
        <v-btn
          color="primary"
          @click="
            editId = null;
            key = key + 1;

            dialogEditEmergency = true;
          "
          dense
          x-small
        >
          Add
        </v-btn>
      </v-col>
      <v-carousel>
        <template v-for="(item, index) in customer_contacts" :name="item.id">
          <v-carousel-item
            v-if="(index + 1) % columns === 1 || columns === 1"
            :key="index"
          >
            <v-row class="flex-nowrap" style="height: 100%">
              <template v-for="(n, i) in columns">
                <template v-if="+index + i < customer_contacts.length">
                  <v-col :key="i">
                    <v-sheet v-if="+index + i < customer_contacts.length">
                      <v-card class="elevation-1 pl-1">
                        <v-row>
                          <v-col cols="10"
                            ><h3 style="">
                              {{ customer_contacts[+index + i].address_type }}
                            </h3></v-col
                          >

                          <v-col cols="2" class="text-right">
                            <v-menu bottom left>
                              <template v-slot:activator="{ on, attrs }">
                                <v-btn dark-2 icon v-bind="attrs" v-on="on">
                                  <v-icon>mdi-dots-vertical</v-icon>
                                </v-btn>
                              </template>
                              <v-list width="120" dense>
                                <v-list-item
                                  v-if="can('device_notification_contnet_view')"
                                  @click="
                                    editContactDetails(
                                      customer_contacts[+index + i].id
                                    )
                                  "
                                >
                                  <v-list-item-title style="cursor: pointer">
                                    <v-icon color="secondary" small>
                                      mdi-eye
                                    </v-icon>
                                    Edit
                                  </v-list-item-title>
                                </v-list-item>

                                <v-list-item
                                  v-if="
                                    can('device_notification_contnet_delete')
                                  "
                                  @click="
                                    deleteContactDetails(
                                      customer_contacts[+index + i].id
                                    )
                                  "
                                >
                                  <v-list-item-title style="cursor: pointer">
                                    <v-icon color="error" small>
                                      mdi-delete
                                    </v-icon>
                                    Delete
                                  </v-list-item-title>
                                </v-list-item>
                              </v-list>
                            </v-menu>
                          </v-col>
                        </v-row>
                        <v-divider></v-divider>
                        <v-row>
                          <v-col cols="4">Address</v-col>
                          <v-col cols="8" class="bold">
                            {{ customer_contacts[+index + i].address }}
                          </v-col>
                        </v-row>
                        <v-divider></v-divider>
                        <v-row>
                          <v-col cols="4">Contact Person</v-col>
                          <v-col cols="8" class="bold">
                            {{ customer_contacts[+index + i].first_name }}
                            {{ customer_contacts[+index + i].last_name }}
                          </v-col>
                        </v-row>
                        <v-divider></v-divider>
                        <v-row>
                          <v-col cols="4">
                            <!-- <v-icon color="primary" size="18" style="line-height: 2px"
              >mdi mdi-phone-classic</v-icon
            > -->
                            Office</v-col
                          >
                          <v-col cols="8" class="bold">{{
                            customer_contacts[+index + i].office_phone
                          }}</v-col>
                        </v-row>
                        <v-divider></v-divider>
                        <v-row>
                          <v-col cols="4">
                            <!-- <v-icon color="primary" style="line-height: 2px"
              >mdi mdi-cellphone-basic</v-icon
            > -->
                            Phone 1
                          </v-col>
                          <v-col cols="8" class="bold"
                            >{{ customer_contacts[+index + i].phone1 }}
                          </v-col>
                        </v-row>
                        <v-divider></v-divider>
                        <v-row>
                          <v-col cols="4">
                            <!-- <v-icon color="primary" style="line-height: 2px"
              >mdi mdi-cellphone-basic</v-icon
            > -->
                            Phone 2</v-col
                          >
                          <v-col cols="8" class="bold">
                            {{ customer_contacts[+index + i].phone2 }}</v-col
                          >
                        </v-row>

                        <v-divider></v-divider>
                        <v-row>
                          <v-col cols="4" class="p1-0">
                            <!-- <v-icon color="primary" size="18" style="line-height: 2px"
              >mdi mdi-at</v-icon
            > -->
                            Email</v-col
                          >
                          <v-col cols="8" class="bold">
                            {{ customer_contacts[+index + i].email }}</v-col
                          >
                        </v-row>
                        <v-divider></v-divider>

                        <v-row>
                          <v-col cols="4" class="p1-0">
                            <!-- <v-icon color="primary" size="18" style="line-height: 2px"
              >mdi mdi-whatsapp</v-icon
            > -->
                            Whatsapp</v-col
                          >
                          <v-col cols="8" class="bold">
                            {{ customer_contacts[+index + i].whatsapp }}
                          </v-col>
                        </v-row>
                        <v-divider></v-divider>
                        <v-row>
                          <v-col cols="4" class="p1-0"> Distance </v-col>
                          <v-col cols="8" class="bold">
                            {{ customer_contacts[+index + i].distance }}</v-col
                          >
                        </v-row>
                        <v-divider></v-divider>
                        <v-row>
                          <v-col cols="4" class="p1-0">Map Positions</v-col>
                          <v-col cols="8" class="bold pr-0">
                            {{ customer_contacts[+index + i]?.latitude }}
                            <br />
                            {{
                              customer_contacts[+index + i]?.longitude
                            }}</v-col
                          >
                        </v-row>
                      </v-card>
                    </v-sheet>
                  </v-col>
                </template>
              </template>
            </v-row>
          </v-carousel-item>
        </template>
      </v-carousel>
      <v-col
        v-for="(item, index) in customer_contacts"
        :key="item.id"
        cols="4"
        class="mt-3"
        style="line-height: 35px; border-right: #ddd 0px solid"
      >
        <v-card class="elevation-1 pl-1">
          <v-row>
            <v-col cols="10"
              ><h3 style="">{{ item.address_type }}</h3></v-col
            >

            <v-col cols="2" class="text-right">
              <!-- <v-icon v-if="item.address_type == 'Police Station'" color="red"
                >mdi mdi-car-emergency</v-icon
              >
              <v-icon
                v-else-if="item.address_type == 'Fire/Civil Department'"
                color="red"
                >mdi mdi-fire-truck</v-icon
              >
              <v-icon v-else-if="item.address_type == 'Hopsital'" color="red"
                >mdi mdi-hospital-building</v-icon
              >
              <v-icon
                v-else-if="item.address_type == 'Building Security'"
                color="red"
                >mdi mdi-security</v-icon
              >
              <v-icon
                v-else-if="item.address_type == 'Community Security'"
                color="red"
                >mdi mdi-server-security</v-icon
              > -->

              <v-menu bottom left>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn dark-2 icon v-bind="attrs" v-on="on">
                    <v-icon>mdi-dots-vertical</v-icon>
                  </v-btn>
                </template>
                <v-list width="120" dense>
                  <v-list-item
                    v-if="can('device_notification_contnet_view')"
                    @click="editContactDetails(item.id)"
                  >
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="secondary" small> mdi-eye </v-icon>
                      Edit
                    </v-list-item-title>
                  </v-list-item>

                  <v-list-item
                    v-if="can('device_notification_contnet_delete')"
                    @click="deleteContactDetails(item.id)"
                  >
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="error" small> mdi-delete </v-icon>
                      Delete
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </v-col>
          </v-row>
          <v-divider></v-divider>
          <v-row>
            <v-col cols="4">Address</v-col>
            <v-col cols="8" class="bold">
              {{ item.address }}
            </v-col>
          </v-row>
          <v-divider></v-divider>
          <v-row>
            <v-col cols="4">Contact Person</v-col>
            <v-col cols="8" class="bold">
              {{ item.first_name }} {{ item.last_name }}
            </v-col>
          </v-row>
          <v-divider></v-divider>
          <v-row>
            <v-col cols="4">
              <!-- <v-icon color="primary" size="18" style="line-height: 2px"
              >mdi mdi-phone-classic</v-icon
            > -->
              Office</v-col
            >
            <v-col cols="8" class="bold">{{ item.office_phone }}</v-col>
          </v-row>
          <v-divider></v-divider>
          <v-row>
            <v-col cols="4">
              <!-- <v-icon color="primary" style="line-height: 2px"
              >mdi mdi-cellphone-basic</v-icon
            > -->
              Phone 1
            </v-col>
            <v-col cols="8" class="bold">{{ item.phone1 }} </v-col>
          </v-row>
          <v-divider></v-divider>
          <v-row>
            <v-col cols="4">
              <!-- <v-icon color="primary" style="line-height: 2px"
              >mdi mdi-cellphone-basic</v-icon
            > -->
              Phone 2</v-col
            >
            <v-col cols="8" class="bold"> {{ item.phone2 }}</v-col>
          </v-row>

          <v-divider></v-divider>
          <v-row>
            <v-col cols="4" class="p1-0">
              <!-- <v-icon color="primary" size="18" style="line-height: 2px"
              >mdi mdi-at</v-icon
            > -->
              Email</v-col
            >
            <v-col cols="8" class="bold"> {{ item.email }}</v-col>
          </v-row>
          <v-divider></v-divider>

          <v-row>
            <v-col cols="4" class="p1-0">
              <!-- <v-icon color="primary" size="18" style="line-height: 2px"
              >mdi mdi-whatsapp</v-icon
            > -->
              Whatsapp</v-col
            >
            <v-col cols="8" class="bold"> {{ item.whatsapp }} </v-col>
          </v-row>
          <v-divider></v-divider>
          <v-row>
            <v-col cols="4" class="p1-0"> Distance </v-col>
            <v-col cols="8" class="bold"> {{ item.distance }}</v-col>
          </v-row>
          <v-divider></v-divider>
          <v-row>
            <v-col cols="4" class="p1-0">Map Positions</v-col>
            <v-col cols="8" class="bold pr-0">
              {{ item?.latitude }} <br />
              {{ item?.longitude }}</v-col
            >
          </v-row>
          <!-- <v-row>
            <v-col cols="4" class="p1-0">Google Map Link</v-col>
            <v-col cols="3" class="bold pr-0">
              <v-btn
                @click="dialogGoogleMap = true"
                text
                outlined
                color="primary"
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
                  'http://maps.google.com/?q=' + item
                    ? item?.latitude + ',' + item?.longitude
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
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import AlarmEditEmergencyContact from "../../components/Alarm/EditEmergencyContact.vue";

export default {
  components: { AlarmEditEmergencyContact },
  props: ["customer", "customer_id", "customer_contacts"],
  data: () => ({
    slider: [
      "red",
      "green",
      "orange",
      "blue",
      "pink",
      "purple",
      "indigo",
      "cyan",
      "deep-purple",
      "light-green",
      "deep-orange",
      "blue-grey",
    ],
    key: 1,
    dialogEditEmergency: false,
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
    editId: "",
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
    snackbar: false,
    response: "",
  }),
  created() {
    this.preloader = false;
    this.getBranchesList();
  },
  computed: {
    columns() {
      if (this.$vuetify.breakpoint.xl) {
        return 4;
      }

      if (this.$vuetify.breakpoint.lg) {
        return 3;
      }

      if (this.$vuetify.breakpoint.md) {
        return 2;
      }

      return 1;
    },
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    deleteContactDetails(id) {
      if (confirm("Are you sure you wish to delete ?")) {
        this.$axios
          .delete(`customer_contact/${id}`)

          .then(({ data }) => {
            this.color = "background";
            this.errors = [];
            this.snackbar = true;
            this.response = "Contact Details are deleted successfully";

            this.$emit("closeDialog");
            //this.branch_id = this.$auth.user.branch_id || "";
          });
      }
    },
    editContactDetails(editId) {
      this.key = this.key + 1;
      this.editId = editId;
      this.dialogEditEmergency = true;
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
            this.response = "Address Details Updated successfully";

            this.$emit("closeDialog");
          }
        })
        .catch((e) => console.log(e));
    },
    closeDialog() {
      //this.key = this.key + 1;
      this.$emit("closeDialog");
      this.dialogEditEmergency = false;
      // location.reload();
      //  this.dialogEditEmergency = false;
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
            this.getDataFromApi();
            this.branchDialog = false;
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
