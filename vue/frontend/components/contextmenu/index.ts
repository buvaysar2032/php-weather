import { App, createApp, Plugin } from 'vue'
import { Coordinates, IContextMenu, MenuItem } from './types'
import ContextMenu from './ContextMenu.vue'

const Context: Plugin = {
  install: (app: App) => {
    const genContainer = () => {
      return document.createElement('div')
    }
    const initInstance = (coordinates: Coordinates, items: MenuItem[], container: HTMLElement) => {
      const node = createApp(ContextMenu, {
        coordinates,
        items,
        onClose() {
          node.unmount()
          container.parentNode?.removeChild(container)
        },
      })
      node.mount(container)
      document.body.appendChild(container.firstElementChild as Node)
    }
    const $contextmenu: IContextMenu = (x: number, y: number, items: MenuItem[]) => {
      const container = genContainer()
      initInstance({ x, y }, items, container)
    }
    app.config.globalProperties.$contextmenu = $contextmenu
    app.provide('contextmenu', $contextmenu)
    app.component('ContextMenu', ContextMenu)
  },
}

export default Context
