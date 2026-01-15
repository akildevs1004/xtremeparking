<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-card class="elevation-0 p-2" style="padding: 5px">
      <v-row>
        <v-col cols="12">
          <v-row class="pt-0">
            <v-col>
              <h3>Subject: {{ payload_ticket.subject }}</h3>
              <span>Created:
                {{
                  $dateFormat.formatDateMonthYear(
                    payload_ticket.created_datetime
                  )
                }}</span>
            </v-col>
            <v-col cols="12" dense style="height: 150px; border: 1px solid #ddd; overflow-y: scroll">
              <div v-html="payload_ticket.description"></div>
            </v-col>
          </v-row>
        </v-col>
      </v-row>

      <v-row style="margin-top: 15px">
        <v-col cols="6" style="padding-bottom: 0px">
          <h4>Attachments({{ editItem?.attachments?.length ?? 0 }})</h4>
        </v-col>
        <v-col cols="12">
          <div v-if="editItem?.attachments" v-for="(attachment, index) in editItem.attachments">
            {{ ++index }}:
            <a style="text-decoration: none" title="Download Profile Picture" :href="getDonwloadLink(attachment.ticket_id, attachment.attachment)
              ">{{ attachment.title }}
              <v-icon color="violet">mdi-arrow-down-bold-circle</v-icon></a>
          </div>
        </v-col>
      </v-row>
    </v-card>
    <v-divider class="mt-5"></v-divider>
    <v-row>
      <v-col cols="12" class="text-right">
        <TicketResponses :expandPanels="true" v-if="editItem" :ticket="editItem"
          @updateTicketReadStatus="updateTickets" />
      </v-col>
    </v-row>
  </div>
</template>

<script>
import TicketResponses from "./TicketResponses.vue";
export default {
  props: ["editItem", "editId"],
  components: { TicketResponses },
  data: () => ({
    snack: false,
    snackColor: "",
    snackText: "",
    snackbar: false,
    response: "",
    //end editor
  }),
  created() {
    this.payload_ticket = { subject: "", description: "" };

    if (this.editId != "" && this.editItem) {
      this.payload_ticket.subject = this.editItem.subject;
      this.payload_ticket.description = this.editItem.description;
      this.payload_ticket.created_datetime = this.editItem.created_datetime;
    }
  },

  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    getDonwloadLink(ticket_id, file_name) {
      return (
        this.$env.settings.BACKEND_URL +
        "/download_ticket_atachment/" +
        ticket_id +
        "/" +
        file_name
      );
    },
    updateTickets() {
      this.$emit("refreshTickets");
    },
  },
};
</script>
