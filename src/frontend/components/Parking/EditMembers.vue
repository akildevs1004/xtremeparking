<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row>
      <v-col md="12" sm="12" cols="12" dense>
        <v-card class="elevation-0 p-2" style="padding: 5px">
          <v-row>
            <v-col cols="4">
              <div class="text-center mt-0">
                <v-img
                  style="
                    height: auto;
                    min-height: 200px;
                    max-height: 200px;

                    width: 200px;
                    border-radius: 10%;
                    margin: 0 auto;
                  "
                  :src="primary_previewImage || '/no-business_profile.png'"
                ></v-img>
                <v-btn
                  class="mt-2"
                  color="primary"
                  style="width: 50%"
                  small
                  @click="onpick_primary_attachment"
                  >{{ !editId ? "Upload" : "Change" }}
                  <v-icon right dark color="primary">mdi-cloud-upload</v-icon>
                </v-btn>

                <input
                  required
                  type="file"
                  @change="primary_attachment"
                  style="display: none"
                  accept="image/*"
                  ref="primary_attachment_input"
                />

                <span
                  v-if="primary_errors && primary_errors.logo"
                  class="text-danger mt-2"
                  >{{ primary_errors.logo[0] }}</span
                >
              </div>
            </v-col>

            <v-col cols="8">
              <v-row class="pt-0">
                <v-col cols="6" dense>
                  <v-text-field
                    label="First Name"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="payload_members.first_name"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.first_name"
                    class="text-danger mt-2"
                    >{{ primary_errors.first_name[0] }}</span
                  >
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    label="Last Name"
                    dense
                    small
                    outlined
                    type="text"
                    v-model="payload_members.last_name"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.last_name"
                    class="text-danger mt-2"
                    >{{ primary_errors.last_name[0] }}</span
                  >
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    label="Contact Number"
                    placeholder="971xxxxxxxxx"
                    dense
                    small
                    outlined
                    type="number"
                    v-model="payload_members.phone"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.phone"
                    class="text-danger mt-2"
                    >{{ primary_errors.phone[0] }}</span
                  >
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    label="Email ID(Login name)"
                    placeholder="youremail@gmail.com"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload_members.email"
                    hide-details
                    :readonly="!editable"
                    :filled="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.email"
                    class="text-danger mt-2"
                    >{{ primary_errors.email[0] }}</span
                  >
                </v-col>

                <v-col cols="6" dense>
                  <v-autocomplete
                    :disabled="!editable"
                    label="Member Type"
                    v-model="payload_members.member_type"
                    @change="onMemberTypeChange()"
                    :items="[
                      {
                        name: null,
                        description: 'Select Member Type',
                      },
                      {
                        name: 'Tenant',
                        description: 'Tenant',
                      },
                      {
                        name: 'Membership',
                        description: 'Paid Member',
                      },
                    ]"
                    dense
                    placeholder="Membsership"
                    outlined
                    :hide-details="true"
                    item-text="description"
                    item-value="name"
                  ></v-autocomplete>

                  <span
                    v-if="primary_errors && primary_errors.member_type"
                    class="text-danger mt-2"
                    >{{ primary_errors.member_type[0] }}</span
                  >
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    :filled="!editable"
                    label="Address"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload_members.address"
                    hide-details
                    :readonly="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.address"
                    class="text-danger mt-2"
                    >{{ primary_errors.address[0] }}</span
                  >
                </v-col>

                <v-col cols="4" dense>
                  <v-autocomplete
                    :disabled="!editable"
                    label="Parking Floor"
                    v-model="payload_members.floor_no"
                    @change="loadChildList(payload_members.floor_no)"
                    :items="floorList"
                    dense
                    placeholder="Floor Number"
                    outlined
                    :hide-details="true"
                    item-text="name"
                    item-value="id"
                  ></v-autocomplete>
                  <span
                    v-if="primary_errors && primary_errors.floor_no"
                    class="text-danger mt-2"
                    >{{ primary_errors.floor_no[0] }}</span
                  >
                </v-col>

                <v-col cols="4" dense>
                  <v-autocomplete
                    :disabled="!editable"
                    label="Parking Number"
                    v-model="payload_members.slot_number"
                    :items="parkingSlots"
                    dense
                    placeholder="Parking Number"
                    outlined
                    :hide-details="true"
                    item-text="name"
                    item-value="id"
                  ></v-autocomplete>

                  <span
                    v-if="primary_errors && primary_errors.slot_number"
                    class="text-danger mt-2"
                    >{{ primary_errors.slot_number[0] }}</span
                  >
                </v-col>

                <v-col cols="4" dense>
                  <v-autocomplete
                    :disabled="!editable"
                    label="Room Number"
                    v-model="payload_members.unit_number"
                    :items="roomNumbers"
                    dense
                    placeholder="Room Number"
                    outlined
                    :hide-details="true"
                    item-text="name"
                    item-value="id"
                  ></v-autocomplete>

                  <span
                    v-if="primary_errors && primary_errors.unit_number"
                    class="text-danger mt-2"
                    >{{ primary_errors.unit_number[0] }}</span
                  >
                </v-col>

                <v-col cols="6" v-if="member_type == 'Tenant'">
                  <v-menu
                    v-model="from_menu"
                    :close-on-content-click="false"
                    transition="scale-transition"
                    offset-y
                    max-width="290px"
                    min-width="auto"
                  >
                    <template v-slot:activator="{ on, attrs }">
                      <v-text-field
                        label="Start Date"
                        hide-details
                        v-model="payload_members.membership_start"
                        persistent-hint
                        append-icon="mdi-calendar"
                        readonly
                        outlined
                        dense
                        v-bind="attrs"
                        v-on="on"
                        :disabled="!editable"
                      ></v-text-field>
                    </template>
                    <v-date-picker
                      style="min-height: 320px"
                      v-model="payload_members.membership_start"
                      no-title
                      @input="from_menu = false"
                    ></v-date-picker>
                  </v-menu>
                </v-col>

                <!-- TO -->
                <v-col cols="6" v-if="member_type == 'Tenant'">
                  <v-menu
                    v-model="to_menu"
                    :close-on-content-click="false"
                    transition="scale-transition"
                    offset-y
                    max-width="290px"
                    min-width="auto"
                  >
                    <template v-slot:activator="{ on, attrs }">
                      <v-text-field
                        label="End Date"
                        hide-details
                        v-model="payload_members.membership_end"
                        persistent-hint
                        append-icon="mdi-calendar"
                        readonly
                        outlined
                        dense
                        v-bind="attrs"
                        v-on="on"
                        :disabled="!editable"
                      ></v-text-field>
                    </template>
                    <v-date-picker
                      style="min-height: 320px"
                      v-model="payload_members.membership_end"
                      no-title
                      @input="to_menu = false"
                    ></v-date-picker>
                  </v-menu>
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    :filled="!editable"
                    label="Prefix"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload_members.prefix"
                    hide-details
                    :readonly="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.prefix"
                    class="text-danger mt-2"
                    >{{ primary_errors.prefix[0] }}</span
                  >
                </v-col>

                <v-col cols="6" dense>
                  <v-text-field
                    :filled="!editable"
                    label="Plate Number"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload_members.plate_number"
                    hide-details
                    :readonly="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.plate_number"
                    class="text-danger mt-2"
                    >{{ primary_errors.plate_number[0] }}</span
                  >
                </v-col>

                <v-col cols="6" dense>
                  <v-text-field
                    :filled="!editable"
                    label="Vehicle Country Region/Country"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload_members.vehicle_country_region"
                    hide-details
                    :readonly="!editable"
                  ></v-text-field>
                  <span
                    v-if="
                      primary_errors && primary_errors.vehicle_country_region
                    "
                    class="text-danger mt-2"
                    >{{ primary_errors.vehicle_country_region[0] }}</span
                  >
                </v-col>

                <v-col cols="6" dense>
                  <v-text-field
                    :filled="!editable"
                    label="Plate Type(Private/Public etc.)"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload_members.vehicle_plate_type"
                    hide-details
                    :readonly="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.vehicle_plate_type"
                    class="text-danger mt-2"
                    >{{ primary_errors.vehicle_plate_type[0] }}</span
                  >
                </v-col>

                <v-col cols="6" dense>
                  <v-text-field
                    :filled="!editable"
                    label="Plate Color"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload_members.vehicle_plate_color"
                    hide-details
                    :readonly="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.vehicle_plate_color"
                    class="text-danger mt-2"
                    >{{ primary_errors.vehicle_plate_color[0] }}</span
                  >
                </v-col>
                <v-col cols="6" dense>
                  <v-autocomplete
                    label="Plate Size"
                    :disabled="!editable"
                    v-model="payload_members.plate_size"
                    :items="[
                      {
                        name: 'small',
                        description: 'Small',
                      },
                      {
                        name: 'large',
                        description: 'Large',
                      },
                    ]"
                    dense
                    placeholder="Number Plate Size"
                    outlined
                    :hide-details="true"
                    item-text="description"
                    item-value="name"
                  ></v-autocomplete>
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    :filled="!editable"
                    label="Vehicle Type/Model"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload_members.vehicle_type"
                    hide-details
                    :readonly="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.vehicle_type"
                    class="text-danger mt-2"
                    >{{ primary_errors.vehicle_type[0] }}</span
                  >
                </v-col>
                <v-col cols="6" dense>
                  <v-text-field
                    :filled="!editable"
                    label="Vehicle   Color"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload_members.vehicle_color"
                    hide-details
                    :readonly="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.vehicle_color"
                    class="text-danger mt-2"
                    >{{ primary_errors.vehicle_color[0] }}</span
                  >
                </v-col>

                <v-col cols="6" dense>
                  <v-text-field
                    :filled="!editable"
                    label="Password"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload_members.password"
                    hide-details
                    type="password"
                    :readonly="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.password"
                    class="text-danger mt-2"
                    >{{ primary_errors.password[0] }}</span
                  > </v-col
                ><v-col cols="6" dense>
                  <v-text-field
                    :filled="!editable"
                    label="Confirm Password"
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload_members.confirm_password"
                    hide-details
                    type="password"
                    :readonly="!editable"
                  ></v-text-field>
                  <span
                    v-if="primary_errors && primary_errors.confirm_password"
                    class="text-danger mt-2"
                    >{{ primary_errors.confirm_password[0] }}</span
                  >
                </v-col>
                <v-col cols="6" dense>
                  <v-radio-group
                    :filled="!editable"
                    class="radiogroup1"
                    v-model="is_active"
                  >
                    <v-radio label="Parking Allowed" :value="true" />
                    <v-radio
                      label="Blocked"
                      :value="false"
                      style="font-size: 10px"
                    />
                  </v-radio-group>

                  <!-- <v-switch :filled="!editable" v-model="is_active" label="Active" color="indigo"
                    :value="payload_members.is_active" hide-details></v-switch> -->
                </v-col>

                <v-col cols="6" dense v-if="!is_active">
                  <v-text-field
                    :filled="!editable"
                    label="Reason for Blocking "
                    dense
                    small
                    outlined
                    clearable
                    autocomplete="off"
                    v-model="payload_members.blocked_reason"
                    hide-details
                    :readonly="!editable"
                  ></v-text-field>

                  <!-- <v-switch :filled="!editable" v-model="is_active" label="Active" color="indigo"
                    :value="payload_members.is_active" hide-details></v-switch> -->
                </v-col>
              </v-row>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" class="text-right">
              <v-btn
                v-if="editable"
                small
                :loading="loading"
                color="primary"
                @click="submit_primary"
              >
                Submit
              </v-btn></v-col
            >
          </v-row>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import { on } from "ws";

export default {
  props: ["customer_id", "editId", "item", "editable"],
  data: () => ({
    from_menu: false,
    to_menu: false,
    from_date: null, // ISO "YYYY-MM-DD" from v-date-picker
    to_date: null, // ISO "YYYY-MM-DD"
    show1: false,
    contactTypes: [],
    branchesList: [],
    floorList: [],
    parkingSlots: [],
    roomNumbers: [],
    startDateMenuOpen: "",
    endDateMenuOpen: "",
    preloader: false,
    loading: false,
    primary_upload: {
      name: "",
    },
    secondary_upload: {
      name: "",
    },
    building_upload: {
      name: "",
    },
    member_type: null,

    end_date: "",
    payload_members: {
      description: "",
      member_type: "Membership",
      display_order: "",
      plate_size: "small",
      plate_number: "",
      parking_slot: "",

      floor_no: null,
      slot_number: null,
      unit_number: null,
      prefix: null,
    },

    e1: 1,
    primary_errors: [],
    primary_previewImage: null,
    secondary_errors: [],
    secondary_previewImage: null,
    building_errors: [],
    building_previewImage: null,
    response: "",
    snackbar: false,
    errors: [],
    selectedItem: null,
    items: ["Apple", "Banana", "Orange"],
    web_login_access: 1,
    tab: 0,
    is_active: true,
  }),
  computed: {
    from_date_display() {
      return this.formatDate(this.from_date);
    },
    to_date_display() {
      return this.formatDate(this.to_date);
    },
  },
  created() {
    this.loadFloorList();

    this.primary_previewImage = null;
    this.payload_members = {
      member_type: "Tenant",

      plate_size: "small",
      plate_number: "",
      parking_slot: "",
    };
    this.preloader = false;
    // this.getBranchesList();

    if (this.$store.state.storeAlarmControlPanel?.AddressTypes) {
      this.addressTypes = this.$store.state.storeAlarmControlPanel.AddressTypes;
    }

    // setTimeout(() => {
    //console.log(this.editAddressType);
    if (this.editId != "" && this.item) {
      this.payload_members = {};
      this.payload_members.editId = this.editId;
      this.payload_members.first_name = this.item.first_name;
      this.payload_members.last_name = this.item.last_name;
      this.payload_members.phone = this.item.phone;
      this.payload_members.email = this.item.email;
      this.payload_members.address = this.item.address;
      this.payload_members.member_type = this.item.member_type;
      this.payload_members.plate_size = this.item.plate_size;
      this.payload_members.parking_slot = this.item.parking_slot;

      this.payload_members.plate_number = this.item.plate_number;
      this.primary_previewImage = this.item.picture;
      this.is_active = this.payload_members.is_active = this.item.is_active
        ? true
        : false;

      this.member_type = this.payload_members.member_type;

      this.payload_members.membership_start = this.item.membership_start;
      this.payload_members.membership_end = this.item.membership_end;

      this.payload_members.vehicle_country_region =
        this.item.vehicle_country_region;
      this.payload_members.vehicle_plate_type = this.item.vehicle_plate_type;
      this.payload_members.vehicle_plate_color = this.item.vehicle_plate_color;
      this.payload_members.plate_size = this.item.plate_size;

      this.payload_members.vehicle_type = this.item.vehicle_type;
      this.payload_members.vehicle_color = this.item.vehicle_color;

      this.payload_members.blocked_reason = this.item.blocked_reason;

      this.payload_members.floor_no = this.item.floor_no;
      this.payload_members.prefix = this.item.prefix;

      this.payload_members.slot_number = parseInt(this.item.slot_number); // Convert string to integer
      this.payload_members.unit_number = this.item.unit_number; // Convert string to integer

      if (this.item.floor_no) {
        this.loadChildList(this.item.floor_no);
      }
    }
  },
  watch: {
    // Keep range valid if user picks an earlier FROM than current TO
    from_date(newVal) {
      if (this.to_date && newVal && this.to_date < newVal) {
        this.to_date = newVal;
      }
    },
    // Keep range valid if user picks a TO earlier than FROM
    to_date(newVal) {
      if (this.from_date && newVal && newVal < this.from_date) {
        this.from_date = newVal;
      }
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
          this.floorList = data;
        })
        .catch((e) => {
          console.log("Floor load error", e);
        });
    },

    loadChildList(floor_no) {
      this.loadSlotList(floor_no);
      this.loadRoomList(floor_no);
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
          this.parkingSlots = data;
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
          this.roomNumbers = data;
        })
        .catch((e) => {
          console.log("Rooms load error", e);
        });
    },

    formatDate(iso) {
      if (!iso) return "";
      // iso is "YYYY-MM-DD" -> display "DD-MM-YYYY"
      const [y, m, d] = iso.split("-");
      return `${d}-${m}-${y}`;
    },
    clearFrom() {
      this.from_date = null;
    },
    clearTo() {
      this.to_date = null;
    },

    onMemberTypeChange() {
      this.member_type = this.payload_members.member_type;
      if (this.member_type != "Tenant") {
        this.payload_members.membership_start = "";
        this.payload_members.membership_end = "";
      }
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    changeStatus(status) {
      if (this.editable) this.web_login_access = status;
    },
    //primary
    onpick_primary_attachment() {
      this.$refs.primary_attachment_input.click();
    },
    primary_attachment(e) {
      this.primary_upload.name = e.target.files[0] || "";

      let input = this.$refs.primary_attachment_input;
      let file = input.files;
      //console.log(file);

      this.primary_errors["logo"] = [];
      if (file[0] && file[0].size > 1024 * 1024) {
        e.preventDefault();
        this.primary_errors["logo"] = [
          "File too big (> 1MB). Upload less than 1MB",
        ];
        return;
      }

      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
          this.primary_previewImage = e.target.result;
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);
      }
    },

    submit_primary() {
      let customer = new FormData();

      for (const key in this.payload_members) {
        // if (this.payload_members[key] != "")
        if (this.payload_members[key] != null)
          customer.append(key, this.payload_members[key]);
      }

      customer.append("is_active", this.is_active ? 1 : 0);

      //console.log(this.payload_members["is_active"]);

      if (this.primary_upload.name) {
        customer.append("attachment", this.primary_upload.name);
      }

      customer.append("company_id", this.$auth.user.company_id);

      // if (this.editAddressType != "") customer.append("editAddressType", true);

      if (this.editId) {
        customer.append("editId", this.editId);
      }

      this.$axios
        .post("/parking_members", customer)
        .then(({ data }) => {
          //this.loading = false;

          if (!data.status) {
            this.primary_errors = [];
            if (data.errors) this.primary_errors = data.errors;
            this.color = "red";

            this.snackbar = true;
            this.response = data.message;
          } else {
            this.color = "background";
            this.primary_errors = [];
            this.snackbar = true;
            this.response = data.message;
            setTimeout(() => {
              this.$emit("closeDialog");
            }, 1000);
          }
        })
        .catch((e) => {
          if (e.response.data.errors) {
            this.primary_errors = e.response.data.errors;
            this.color = "red";

            this.snackbar = true;
            this.response = e.response.data.message;
          }
        });
    },
  },
};
</script>
<style scoped>
/deep/ input[type="number"] {
  -webkit-appearance: none;
  -moz-appearance: textfield;
}
/deep/ input[type="number"]::-webkit-outer-spin-button,
/deep/ input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>
