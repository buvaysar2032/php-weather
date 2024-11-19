<script setup lang="ts">
import { inject } from 'vue'

defineProps<{ text?: string }>()

const tooltipsShow = inject<boolean>('tooltipsShow')
</script>

<template>
  <span
    v-show="text && tooltipsShow"
    v-bs-tooltip
    class="tooltip-icon"
    :title="text"
  >
    <FontAwesomeIcon icon="question-circle" />
  </span>
</template>
