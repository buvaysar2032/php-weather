import { createApp } from 'vue'
import VueAxios from 'vue-axios'
import axios from 'axios'
import { createPinia } from 'pinia'
import VueApexCharts from 'vue3-apexcharts'
import VueDOMPurifyHTML from 'vue-dompurify-html'
import 'vue-loading-overlay/dist/css/index.css'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Context from './components/contextmenu'
import MainJumbotron from './components/MainJumbotron.vue'
import TheScrollToTop from './components/TheScrollToTop.vue'
import Alerts from './components/alerts'
import { vBsPopover, vBsTooltip } from './directives'
import './icons'

const pinia = createPinia()
const app = createApp({
  components: { MainJumbotron, TheScrollToTop },
})
app.config.compilerOptions.isCustomElement = tag => tag === 'font'
app
  .use(VueAxios, axios)
  .provide('axios', app.config.globalProperties.axios)
  .component('FontAwesomeIcon', FontAwesomeIcon)
  .directive('bs-tooltip', vBsTooltip)
  .directive('bs-popover', vBsPopover)
  .use(pinia)
  .use(Context)
  .use(VueApexCharts)
  .use(VueDOMPurifyHTML)
app.mount('#app')
app.use(Alerts)
