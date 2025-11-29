<template>
  <date-picker :class="$vuetify.theme.dark ? 'dark-theme' : 'light-theme'" style="z-index: 99" value-type="format"
    format="YYYY-MM-DD" type="date" v-model="time3" @change="CustomFilter()" @clear="clearDates()" range
    :disabled="disabled ? disabled : false"></date-picker>
</template>

<script>
import DatePicker from "vue2-datepicker";
import "vue2-datepicker/index.css";
export default {
  components: {
    DatePicker,
  },
  props: [
    "disabled",
    "defaultFilterType",
    "height",
    "width",
    "default_date_from",
    "default_date_to",

  ],
  data() {
    return {
      // -------------------end chart ----------------
      time3: null,
      from_date: "",
      from_menu: false,

      to_date: "",
      to_menu: false,
      loading: false,
      showTimePanel: false,
      filterType: 1,
      search: "",
    };
  },

  watch: {
    filterType() {
      this.showTimePanel = true;
      this.FilterData();
    },
  },

  mounted() {
    if (this.filterType == 5) document.querySelector(".mx-input").focus();

    const elementsArray = document.getElementsByClassName("mx-input");
    //console.log(elementsArray);
    // Loop through the elements and change their height
    for (let i = 0; i < elementsArray.length; i++) {
      const element = elementsArray[i];
      // Set the height to 50 pixels (adjust as needed)
      element.style.height = this.height + "px!important";

      //console.log("element.style.height", element, element.style.height);
    }
    // if (this.height && this.height != "") {
    //    elementsArray[0].style.height = this.height;
    //   //elementsArray[0].height = "500"; //this.height;
    //   console.log(this.height, "elementsArray[0] ", elementsArray[0]);
    //   console.log(
    //     this.height,
    //     "elementsArray[0].height",
    //     elementsArray[0].height
    //   );
    //   console.log(
    //     this.height,
    //     "elementsArray[0].style.height",
    //     elementsArray[0].style.height
    //   );
    // }
    if (this.width && this.width != "") {
      elementsArray[0].style.width = this.width;
    }

    if (this.default_date_from && this.default_date_to) {
      this.from_date = this.default_date_from;

      this.to_date = this.default_date_to;
    }

    this.time3 = [this.from_date, this.to_date];
  },
  created() {
    if (this.defaultFilterType) {
      this.filterType = this.defaultFilterType;
    }
    if (this.default_date_from) {
      const today = new Date();

      if (!this.default_date_from) {
        this.from_date = today.toISOString().slice(0, 10);
      }

      if (!this.default_date_to) {
        this.to_date = today.toISOString().slice(0, 10);
      }

      if (this.default_date_from && this.default_date_to) {
        this.from_date = this.default_date_from;

        this.to_date = this.default_date_to;
      }
    }

    this.time3 = [this.from_date, this.to_date];

    let data = {
      from: this.from_date,
      to: this.to_date,
      type: 1,
      search: "this.search",
    };

    this.$emit("filter-attr", data);
  },
  methods: {
    commonMethod() {
      if (this.from_date && this.to_date) {
      }
    },
    clearDates() {

      let data = {
        from: null,
        to: null,
        type: this.filterType,
        search: this.search,
      };

      this.$emit("filter-attr", data);

    },
    CustomFilter() {
      this.from_date = this.time3[0];
      this.to_date = this.time3[1];
      if (this.from_date && this.to_date) {
        let data = {
          from: this.from_date,
          to: this.to_date,
          type: this.filterType,
          search: this.search,
        };

        this.$emit("filter-attr", data);
      }
    },
    FilterData() {
      this.from_date = this.time3[0];
      this.to_date = this.time3[1];
      const today = new Date();

      if (this.filterType == 1) {
        // Get today's date
        this.from_date = today.toISOString().slice(0, 10);
        this.to_date = today.toISOString().slice(0, 10);
      } else if (this.filterType == 2) {
        // Get yesterday's date
        const yesterday = new Date();
        yesterday.setDate(today.getDate() - 1);
        this.from_date = yesterday.toISOString().slice(0, 10);
        this.to_date = yesterday.toISOString().slice(0, 10);
      } else if (this.filterType == 3) {
        // Get the first day of the current week (Sunday)
        const firstDayOfWeek = new Date(today);
        firstDayOfWeek.setDate(today.getDate() - today.getDay());

        // Get the last day of the current week (Saturday)
        const lastDayOfWeek = new Date(today);
        lastDayOfWeek.setDate(today.getDate() - today.getDay() + 6);

        this.from_date = firstDayOfWeek.toISOString().slice(0, 10);
        this.to_date = lastDayOfWeek.toISOString().slice(0, 10);
      } else if (this.filterType == 4) {
        // const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

        // // Get the last day of the current month
        // const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

        // this.from_date = firstDayOfMonth.toISOString().slice(0, 10);
        // this.to_date = lastDayOfMonth.toISOString().slice(0, 10);

        this.from_date = this.formatDate(
          new Date(today.getFullYear(), today.getMonth(), 1)
        );
        this.to_date = this.formatDate(
          new Date(today.getFullYear(), today.getMonth() + 1, 0)
        );
      } else if (this.filterType == 5) {
        this.time3 = [];

        return;
      }

      if (this.from_date && this.to_date) {
        let data = {
          from: this.from_date,
          to: this.to_date,
          type: this.filterType,
          search: this.search,
        };

        this.$emit("filter-attr", data);
      }
    },

    formatDate(date) {
      var day = date.getDate();
      var month = date.getMonth() + 1; // Months are zero-based
      var year = date.getFullYear();
      return (
        year +
        "-" +
        (month < 10 ? "0" : "") +
        month +
        "-" +
        (day < 10 ? "0" : "") +
        day
      );
    },
  },
};
</script>

<style>
.dark-theme .mx-input {
  background: transparent !important;
  color: #FFF !important;
}

.dark-theme .mx-icon-calendar {

  color: #FFF !important;
}

.mx-input {
  /*height: 45px !important;*/
  border: 1px solid #9e9e9e !important;
  color: black !important;

  margin-top: 2px !important;
  height: 30px !important;
}

.mx-datepicker {
  width: 200px;
}

.mx-table-date td,
.mx-table-date th {
  text-align: center !important;
}
</style>
