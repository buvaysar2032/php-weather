import { IconName } from '@fortawesome/fontawesome-svg-core'
import { defineComponent } from 'vue'

export type TabList = {
  tab: string
  title: string
  icon?: IconName
  component: ReturnType<typeof defineComponent>
  props?: Record<string, any>
}[]
export { default as Tabs } from './NavTabs.vue'
