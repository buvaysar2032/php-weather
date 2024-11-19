<script setup lang="ts">
import { computed, reactive } from 'vue'
import { MenuItem } from './types'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

const props = defineProps<{ item: MenuItem }>()

const emit = defineEmits<{ (_e: 'option-clicked'): void }>()

const hasChild = computed(() => props.item.children !== undefined)

const itemClass = reactive({ 'dropdown-item': true, disabled: props.item.disabled })

function onClick() {
  props.item.onClick()
  emit('option-clicked')
}

function onChildClick() {
  emit('option-clicked')
}
</script>

<template>
  <li
    :class="itemClass"
    @click="onClick"
  >
    <div :class="{ icon: true, [`text-${item.variant}`]: !!item.variant }">
      <FontAwesomeIcon
        v-if="item.icon"
        :icon="item.icon"
      />
    </div>
    <div class="text">
      {{ item.label }}
      <span v-if="hasChild">&raquo;</span>
    </div>
    <ul
      v-if="hasChild"
      class="submenu dropdown-menu shadow-lg"
    >
      <ContextMenuItem
        v-for="(subItem, key) in item.children"
        :key="key"
        :item="subItem"
        @option-clicked="onChildClick"
      />
    </ul>
  </li>
</template>

<style lang="sass">
.dropdown-item
  padding: 0.25rem 0.5rem

  div
    display: inline-block

    &.icon
      padding-right: 0.5rem
      width: 2rem
      text-align: center

    &.text
      position: relative
      min-width: calc(100% - 2rem)

      span
        position: absolute
        right: 0

  &:hover > .submenu
    display: block

.submenu
  display: none
  position: absolute
  left: 100%
  top: -7px
</style>
