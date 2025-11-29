<template>
  <div>
    <v-card elevation="0">
      <v-timeline dense>
        <v-timeline-item
          v-if="fallowupList"
          v-for="(item, index) in fallowupList"
          :key="index"
          color=""
        >
          <span slot="icon"
            ><v-icon :color="item.status == 'Closed' ? 'red' : 'primary'"
              >mdi-clock-outline</v-icon
            ></span
          >
          <v-card outlined>
            <v-card-text>
              <div>
                {{ item.created_datetime }}
              </div>
              <small>{{ item.message }}</small>

              <br /><small
                >By <strong> {{ item?.user?.email }}</strong></small
              >

              <br /><small
                >Status <strong> {{ item.status }}</strong></small
              >
            </v-card-text>
          </v-card>
        </v-timeline-item>
      </v-timeline>
    </v-card>
  </div>
</template>
<script>
export default {
  props: ["fallowupList"],
  data: () => ({
    dialog: false,
  }),
};
</script>
