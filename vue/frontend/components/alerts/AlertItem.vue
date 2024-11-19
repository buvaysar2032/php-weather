<script setup lang="ts">
import { nextTick, onMounted, onUnmounted, ref } from 'vue'
import { Alert } from 'bootstrap'

defineProps<{ variant?: string; text?: string }>()

const emit = defineEmits<{ (e: 'close'): void }>()

const alert = ref<HTMLElement>()
let bsAlert: Alert | undefined
onMounted(() =>
  nextTick(() => {
    if (alert.value) {
      bsAlert = new Alert(alert.value)
      alert.value.addEventListener('closed.bs.alert', () => emit('close'))
    }
  })
)

onUnmounted(() => {
  bsAlert?.dispose()
})
</script>

<template>
  <div
    ref="alert"
    :class="`alert alert-${variant} alert-dismissible fade show shadow-lg`"
    role="alert"
  >
    <div v-dompurify-html="text" />
    <button
      type="button"
      class="btn-close"
      data-bs-dismiss="alert"
      aria-label="Close"
    />
  </div>
</template>

<style lang="sass">
#yii-vue-alerts
  position: fixed
  right: 1rem
  bottom: 2.5rem
  min-width: 300px
  max-width: 820px
  z-index: 1100

  > div
    text-align: right

    .alert
      display: inline-block
      min-width: 400px
      max-width: 800px
      text-align: left
      animation: .3s show cubic-bezier(0.22, 0.54, 0.51, 1.36)

      > div
        max-height: 200px
        overflow: auto

@keyframes show
  from
    opacity: 0
    right: -510px
  to
    opacity: 1
    right: 0
</style>
