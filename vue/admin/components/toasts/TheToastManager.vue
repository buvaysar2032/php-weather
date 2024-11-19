<script setup lang="ts">
import { ref } from 'vue'
import ToastItem from './ToastItem.vue'
import { Toast } from '../interfaces'

let toastID = ref(0),
  toasts = ref<Toast[]>([])

function removeToast(id: number) {
  toasts.value = toasts.value.filter(m => m.id !== id)
}

function addToast({ type = 'info', duration = 4000, text }: { type: string; duration?: number; text: string }) {
  const id: number = toastID.value++

  toasts.value.unshift({ id, type, text })

  setTimeout(() => {
    removeToast(id)
  }, duration)
}

defineExpose({ addToast })
</script>

<template>
  <TransitionGroup
    name="toasts"
    tag="div"
    class="c-toasts"
  >
    <ToastItem
      v-for="toast in toasts"
      :key="toast.id"
      class="toasts-item"
      :item="toast"
    />
  </TransitionGroup>
</template>

<style lang="sass">
.c-toasts
  width: 100%
  pointer-events: none

.toasts
  &-item
    transition: all 0.5s

  &-enter,
  &-leave-to
    opacity: 0
    transform: scale(0.9)

  &-leave-active
    position: absolute
    z-index: -1
</style>
