import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useTooltip = defineStore('tooltip', () => {
  const state = ref(localStorage.getItem('tooltip_status') !== 'hidden')
  watch(state, value => {
    localStorage.setItem('tooltip_status', value ? 'show' : 'hidden')
  })

  return { state }
})
