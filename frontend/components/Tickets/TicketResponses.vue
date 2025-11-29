<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <template class="pt-3 text-left align-left">
      <h3 style="text-align: left">
        All Responses({{ ticket ? ticket.responses?.length : 0 }})
      </h3>

      <!-- <div v-if="ticket.responses.length == 0">0 New Messages</div> -->
      <v-expansion-panels
        v-model="panel"
        multiple
        focusable
        v-if="ticket?.responses"
      >
        <v-expansion-panel
          style="margin-bottom: 10px"
          v-for="(item, i) in ticket.responses"
          :key="i"
        >
          <v-expansion-panel-header
            style="min-height: 25px; background-color: #8f8f8f; color: #fff"
          >
            <v-row :class="item.is_read ? '' : 'bold'">
              <v-col>
                <div
                  v-if="
                    item.customer_id &&
                    item.customer_id == $auth.user.customer?.id
                  "
                >
                  You (Customer)
                </div>
                <div
                  v-else-if="
                    item.security_id &&
                    item.security_id == $auth.user.security?.id
                  "
                >
                  You(Operator)
                </div>
                <div v-else-if="item.security">
                  Operator: {{ item.security.first_name }}
                  {{ item.security.last_name }}
                </div>
                <div v-else-if="item.customer">
                  Customer: {{ item.customer.building_name }}
                </div>
                <div v-else-if="item.technician">
                  Technician: {{ item.technician.first_name }}
                  {{ item.technician.last_name }}
                </div>
                <div v-else>Admin/Unknown</div>
              </v-col>

              <v-col style="max-width: 50px">
                <v-icon v-if="item?.attachments?.length" color="#FFF"
                  >mdi-arrow-down-bold-circle</v-icon
                ></v-col
              >
              <v-col style="text-align: right; max-width: 180px">
                <v-icon size="20" color="#FFF"
                  >mdi-clock-time-four-outline</v-icon
                >
                {{ item.created_datetime }}</v-col
              >
            </v-row>
          </v-expansion-panel-header>
          <v-expansion-panel-content>
            <div style="text-align: left" v-html="item.description"></div>

            <v-row
              ><v-col
                ><v-row style="margin-top: 15px">
                  <v-col cols="12">
                    Attachments({{ item?.attachments?.length ?? 0 }})

                    <a
                      class="pr-5"
                      v-if="item?.attachments"
                      v-for="attachment in item.attachments"
                      title="Download Profile Picture"
                      :href="
                        getDonwloadLink(
                          attachment.ticket_response_id,
                          attachment.attachment
                        )
                      "
                      >{{ attachment.title }}
                      <v-icon color="violet"
                        >mdi-arrow-down-bold-circle</v-icon
                      ></a
                    >
                  </v-col>
                </v-row></v-col
              ></v-row
            >
          </v-expansion-panel-content>
        </v-expansion-panel>
      </v-expansion-panels>
    </template>
  </div>
</template>

<script>
export default {
  props: ["ticket", "expandPanels"],

  data: () => ({ snackbar: false, response: "", panel: [] }),
  created() {
    //if (this.ticket && this.expandPanels)
    {
      for (var i = 0; i < this.ticket.responses.length; i++) {
        this.panel.push(i);
      }
    }

    this.updateTicketReadStatus1(this.ticket);
  },

  methods: {
    getDonwloadLink(ticket_reponse_id, file_name) {
      return (
        process.env.BACKEND_URL +
        "/download_ticket_response_atachment/" +
        ticket_reponse_id +
        "/" +
        file_name
      );
    },
    updateTicketReadStatus1(ticket) {
      if (ticket.is_read) return false;

      let options = {
        params: {
          ticket_id: ticket.id,
          user_type: this.$auth.user.user_type,
          is_read_status: true,
        },
      };

      this.$axios
        .post("update_ticket_read_status", options.params)
        .then((data) => {
          this.$emit("updateTicketReadStatus");
        });
    },
    updateTicketReadStatus(item) {
      if (item.status) return false;

      let options = {
        params: {
          ticket_id: item.ticket_id,
          ticket_response_id: item.id,
          is_read_status: true,
          user_type: this.$auth.user.user_type,
        },
      };

      this.$axios
        .post("update_ticket_read_status", options.params)
        .then((data) => {
          this.$emit("updateTicketReadStatus");
        });
    },
  },
};
</script>
