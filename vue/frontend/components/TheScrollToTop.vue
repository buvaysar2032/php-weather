<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'

const visible = ref(false)

function scrollTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function scrollListener() {
  visible.value = window.scrollY > 150
}

onMounted(() => window.addEventListener('scroll', scrollListener))

onBeforeUnmount(() => window.removeEventListener('scroll', scrollListener))
</script>

<template>
  <a
    v-show="visible"
    class="scroll-to-top btn bg-secondary bg-gradient bg-opacity-25 shadow position-fixed h-100 bottom-0 start-0"
    @click="scrollTop"
  >
    <slot />
  </a>
</template>
