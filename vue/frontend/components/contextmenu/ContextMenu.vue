<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted } from 'vue'
import { Coordinates, MenuItem } from './types'
import ContextMenuItem from './ContextMenuItem.vue'

const props = defineProps<{
  coordinates: Coordinates
  items: MenuItem[]
}>()

const emit = defineEmits<{
  (e: 'update:show', value: boolean): void
  (e: 'close'): void
}>()

function close() {
  emit('update:show', false)
  emit('close')
}

onMounted(() => {
  setTimeout(() => {
    document.addEventListener('click', close)
    document.addEventListener('contextmenu', close)
    window.addEventListener('scroll', close)
  }, 100)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', close)
  document.removeEventListener('contextmenu', close)
  window.removeEventListener('scroll', close)
})

const x = computed(() => `${props.coordinates.x}px`)
const y = computed(() => `${props.coordinates.y}px`)
</script>

<template>
  <ul class="contextmenu-container dropdown-menu shadow-lg">
    <ContextMenuItem
      v-for="(item, index) in items"
      :key="index"
      :item="item"
      @option-clicked="close"
    />
  </ul>
</template>

<style scoped lang="sass">
.contextmenu-container
  position: fixed
  padding: 0
  z-index: 1100
  top: v-bind('y')
  left: v-bind('x')
  animation: .45s show ease
  overflow: hidden

  &.dropdown-menu
    display: block

    .dropdown-item:not(.disabled)
      cursor: pointer

@keyframes show
  from
    opacity: .8
    max-height: 0
  to
    opacity: 1
    max-height: 100%
</style>
