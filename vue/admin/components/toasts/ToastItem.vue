<script setup lang="ts">
import { Toast } from '@admin/components/interfaces'

defineProps<{ item: Toast }>()
</script>

<template>
  <div :class="['c-toasts__item', `c-toasts__item--${item.type}`]">
    <span class="c-toasts__item-text">
      {{ item.text }}
    </span>
  </div>
</template>

<style lang="sass">
.c-toasts__item
  display: flex
  align-items: center

  width: 100%
  margin-bottom: 10px
  padding: 15px
  border-radius: 5px

  color: #fff
  background-color: rgba(0, 0, 0, 0.8)

  &-text
    font-size: 14px
    line-height: 1.5

  &--success
    background-color: #390

  &--danger
    background-color: #c30

  &--warning
    background-color: #fc0

  &--info
    background-color: #40a6ce
</style>
