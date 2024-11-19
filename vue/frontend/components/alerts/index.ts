import { App, createApp, Plugin } from 'vue'
import AlertItem from './AlertItem.vue'
import { IAlert } from './types'

let $alert: IAlert | undefined = undefined
const Alerts: Plugin = {
  install(app: App) {
    app.component('AlertItem', AlertItem)

    const createContainer = (): HTMLDivElement => document.createElement('div')

    const id = 'yii-vue-alerts'

    let container = app._container.querySelector(`#${id}`)
    if (!container) {
      container = createContainer()
      container.setAttribute('id', id)
      app._container.append(container)
    }
    $alert = (variant: string, text: string) => {
      const alertContainer = createContainer()
      if (variant === 'error') variant = 'danger'
      const node = createApp(AlertItem, {
        variant,
        text,
        onClose() {
          node.unmount()
          alertContainer.parentNode?.removeChild(alertContainer)
        },
      })
      node.mount(alertContainer)
      container.append(alertContainer)
    }
    app.config.globalProperties.$alert = $alert
    app.provide('alert', $alert)
  },
}

export default Alerts

export const useAlert = () => $alert
