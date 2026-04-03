<template>
  <div>
    <v-row>
      <v-col>
        <v-carousel
          v-model="currentSlide"
          v-if="customer"
          hide-delimiter-background
          hide-arrows-on-hover
          hide-delimiters
          hide-arrows
          :height="browserHeight - 210"
        >
          <v-carousel-item
            v-for="(item, index) in customer.profile_pictures"
            :key="i"
            style="margin: auto"
          >
            <v-img
              :style="{
                height: browserHeight - 230 + 'px',
                width: browserWidth - 700 + 'px',
                margin: auto,
              }"
              :src="item?.picture ? item.picture : '/no-profile-image.jpg'"
              contain
            />
          </v-carousel-item>
        </v-carousel>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <div
          style="
            max-width: 750px;
            width: 100%;
            overflow-x: auto;
            white-space: nowrap;
            margin: auto;
            text-align: center;
          "
        >
          <div
            style="
              display: inline-block;
              width: 140px;
              text-align: center;
              margin-right: 10px;
            "
            v-for="(item, index) in customer.profile_pictures"
            :key="index"
          >
            <div
              style="height: 100px; width: 150px; margin: auto"
              :class="{ 'active-thumbnail': index === currentSlide }"
            >
              <v-img
                :src="item.picture"
                :alt="item.title"
                contain
                width="140px"
                max-height="100px"
                height="100px"
                style="max-height: 100px"
                @click="currentSlide = index"
              ></v-img>
            </div>
            <label style="font-size: 10px">{{ item.title }}</label>
          </div>
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  props: ["customer", "browserHeight", "browserWidth"],
  data: () => ({
    tab: "",
    currentSlide: 0,
    imageLoaded: {},
    auto: null,
    i: null,
  }),
  computed: {},
  mounted() {},
  created() {},
  watch: {},
  methods: {
    changeSlideImage(index) {
      this.currentSlide = index;
    },
  },
};
</script>
