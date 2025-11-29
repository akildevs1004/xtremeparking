<template>
  <div>
    <div @click="submit" style="cursor: pointer">
      <v-icon color="red" small> mdi-delete </v-icon>
      Delete
    </div>
  </div>
</template>
<script>
export default {
  props: ["id", "endpoint"],
  methods: {
    async submit() {
      if (confirm("Are you sure want to Delete Document?")) {
        try {
          await this.$axios.delete(this.endpoint + "/" + this.id);
          this.$emit("response", "Record has been delete");
        } catch ({ response }) {
          console.log(response);
          // console.error("Error adding income:", response.data.message);
        }
      }
    },
  },
};
</script>
