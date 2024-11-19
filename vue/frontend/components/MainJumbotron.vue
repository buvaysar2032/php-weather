<script setup lang="ts">
defineProps<{ title?: string; lead?: string }>()
</script>

<template>
  <div class="jumbotron p-5 rounded-lg m-3">
    <h1
      v-dompurify-html="title"
      class="display-4"
    />
    <p
      v-dompurify-html="lead"
      class="lead"
    />
  </div>
</template>

<style lang="sass">
.jumbotron
  text-align: center
</style>
