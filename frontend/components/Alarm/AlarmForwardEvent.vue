<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row v-if="loading">
      <v-col>
        <v-progress-linear
          indeterminate
          color="primary darken-2"
        ></v-progress-linear>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="1"></v-col>
      <v-col cols="3" style="padding-left: 5px">
        <v-text-field
          type="email"
          label="CC Email"
          hide-details
          outlined
          dense
          v-model="external_cc_email"
      /></v-col>
      <v-col class="text-right">
        <v-btn x-small title="New" color="primary" @click="addItem()"
          >Add
        </v-btn>
      </v-col>
    </v-row>

    <v-row
      v-for="(d, index) in forward?.contacts"
      :key="index"
      style="border: 0px solid black"
      ><v-col
        cols="1"
        style="min-width: 20px; max-width: 110px; padding-top: 10px"
      >
        {{ index + 1 }} )
      </v-col>
      <v-col cols="3" style="min-width: 60px; padding: 4px">
        <v-text-field
          style="display: none"
          label="Name"
          hide-details
          outlined
          dense
          v-model="d.name"
        />
        <v-combobox
          v-model="d.address_type"
          label="Name"
          :items="customerContactList"
          item-value="address_type"
          :item-text="displayAddressType"
          dense
          outlined
          :hide-details="true"
          @change="onAddressTypeChange(d)"
        />
      </v-col>
      <v-col cols="4" style="min-width: 50px; padding: 4px">
        <!-- <v-combobox
          v-model="d.email_code"
          label="Email"
          :items="customerContactList"
          item-value="email"
          item-text="full_name"
          :return-object="false"
          dense
          outlined
          :hide-details="true"
          @change="setEmailCode(d, d.email_code)"
        ></v-combobox> -->
        <v-text-field
          type="email"
          label="Email"
          hide-details
          outlined
          dense
          v-model="d.email"
        />
      </v-col>
      <v-col cols="3" style="min-width: 100px; padding: 4px">
        <v-text-field
          type="number"
          label="Whatsapp Number"
          hide-details
          outlined
          dense
          v-model="d.whatsapp_number"
        />
      </v-col>

      <v-col cols="1" style="min-width: 50px; max-width: 50px; padding: 4px">
        <v-icon
          v-if="forward.contacts.length > 1"
          dark
          fab
          style="padding-top: 10px"
          outlined
          @click="removeItem(index)"
          size="20"
          >mdi-delete</v-icon
        >
      </v-col>
    </v-row>

    <v-row class="pr-3">
      <v-col cols="9" class="text-right">
        <div :style="errors ? 'color:red' : 'color:green'">
          {{ response }}
        </div>
      </v-col>
      <v-col cols="2" class="text-right">
        <v-btn class="primary" small @click="save_forward_info"> Submit</v-btn>
      </v-col>
      <v-col cols="1" class="text-left"> </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  props: ["alarm_id", "customer"],
  data() {
    return {
      external_cc_email: "",
      customerContactList: [],
      forwardTypes: [],
      SensorTypes: [],
      displayEditform: false,
      loading: false,
      forward_info: false,
      menu: false,
      date: false,
      snackbar: false,
      response: "",
      errors: null,
      zonesErrors: [],
      forward: {
        contacts: [],
      },
      oneTOsixty: [],
    };
  },
  created() {
    // for (let index = 0; index <= 60; index++) {
    //   this.oneTOsixty.push(index);
    // }
    // if (this.$store.state.storeAlarmControlPanel?.forwardTypes) {
    //   this.forwardTypes = this.$store.state.storeAlarmControlPanel.forwardTypes;
    // }
    // if (this.$store.state.storeAlarmControlPanel?.SensorTypes) {
    //   this.SensorTypes = this.$store.state.storeAlarmControlPanel.SensorTypes;
    // }
    this.addItem();
    //this.getInfo();

    if (this.customer) this.customerContactList = this.customer.contacts;
  },
  computed: {},
  methods: {
    displayAddressType(item) {
      if (!item || !item.address_type) return "";
      return (
        item.address_type.charAt(0).toUpperCase() +
        item.address_type.slice(1).toLowerCase()
      );
    },
    onAddressTypeChange(d) {
      const selectedAddressType = this.customerContactList.find(
        (item) => item.address_type === d.address_type.address_type
      );

      if (selectedAddressType) {
        d.email = selectedAddressType.email || "";
        d.whatsapp_number = selectedAddressType.whatsapp || "";
        d.name = selectedAddressType.address_type || "";
      } else {
        d.email = "";
        d.whatsapp_number = "";
      }
    },
    // getEmailCode(email_code) {
    //   const code = this.customerContactList.find(
    //     (item) => item.email === email_code
    //   );
    //   return code || email_code;
    // },
    // setEmailCode(contact, value) {
    //   contact.email_code = value;
    //   this.updateWhatsAppNumber(contact);
    // },
    // updateWhatsAppNumber(contact) {
    //   // Find the selected contact's details in the customerContactList
    //   const selectedContact = this.customerContactList.find(
    //     (item) => item.email === contact.email_code
    //   );

    //   if (selectedContact) {
    //     contact.whatsapp_number = selectedContact.whatsapp || "";
    //     contact.name = selectedContact.full_name;
    //   } else {
    //     contact.whatsapp_number = "";
    //   }
    // },
    displayEdit() {
      this.displayEditform = true;
    },
    cancel() {
      this.displayEditform = false;
    },
    removeItem(index) {
      this.forward.contacts.splice(index, 1);
    },
    addItem() {
      let obj = {
        name: "",
        email: "",
        whatsapp_number: "",
      };
      this.forward.contacts.push(obj);
    },
    // getInfo() {
    //   this.loading = true;
    //   //let zones = this.editforward.contacts;
    //   if (this.editforward) {
    //     this.editforward.contacts.forEach((element) => {
    //       let obj = {
    //         sensor_name: element.sensor_name,
    //         wired: element.wired,
    //         location: element.location,
    //         area_code: element.area_code,
    //         zone_code: element.zone_code,
    //         delay: parseInt(element.delay),
    //         hours24: element.hours24,
    //       };
    //       this.forward.contacts.push(obj);
    //     });
    //   } else {
    //     this.editforward.contacts = [];
    //   }

    //   if (this.editforward.contacts.length == 0) {
    //     this.addItem();
    //   }
    // },
    can(item) {
      return true;
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

    save_forward_info() {
      this.errors = null;

      let payload = {
        ...this.forward,

        company_id: this.$auth?.user?.company?.id,
        customer_id: this.customer_id,
        alarm_id: this.alarm_id,
        external_cc_email: this.external_cc_email,
      };
      this.response = "";

      this.loading = true;

      this.$axios
        .post(`/alarm_forward_notification`, payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data;
            this.response = data.message;
          } else {
            this.errors = null;
            this.snackbar = true;
            this.response = data.message;
            //this.forward = data.record || { zones: [] };
            //this.close_forward_info();
            this.$emit("closeDialog");

            this.forward = {
              contacts: [],
            };
            this.addItem();
          }
        })
        .catch((e) => {
          this.errors = e;
        });
    },
    close_forward_info() {
      this.forward_info = false;
      this.errors = [];
      this.cancel();
      this.$emit("close-popup");
    },
  },
};
</script>
