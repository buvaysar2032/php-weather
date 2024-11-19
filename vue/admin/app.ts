import { createApp } from 'vue'
import VueAxios from 'vue-axios'
import axios from 'axios'
import { createPinia, storeToRefs } from 'pinia'
import VueApexCharts from 'vue3-apexcharts'
import 'vue-loading-overlay/dist/css/index.css'
import VueDOMPurifyHTML from 'vue-dompurify-html'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import './icons'
import { useTooltip } from './stores/tooltip'
// App components install
import BackupPage from './components/BackupPage.vue'
import MainJumbotron from './components/MainJumbotron.vue'
import NotificationBell from './components/NotificationBell.vue'
import MailPreview from './components/MailPreview.vue'
import StatisticBlock from './components/StatisticBlock.vue';
import { AxiosKey } from './symbols'

const pinia = createPinia()
const app = createApp({
  components: { BackupPage, MainJumbotron, NotificationBell },
  setup() {
    const tooltip = useTooltip()
    const { state: tooltipsShow } = storeToRefs(tooltip)
    return { tooltipsShow }
  },
})
app.config.compilerOptions.isCustomElement = (tag: string) => tag === 'font'
const token = jQuery('meta[name="csrf-token"]').attr('content')
axios.defaults.headers.common['X-CSRF-TOKEN'] = token ? token : ''
app
  .use(VueAxios, axios)
  .provide(AxiosKey, app.config.globalProperties.axios)
  .use(pinia)
  .use(VueApexCharts)
  .use(VueDOMPurifyHTML)
  .component('FontAwesomeIcon', FontAwesomeIcon)
  .component('MailPreview', MailPreview)
  .component('StatisticBlock', StatisticBlock)
app.mount('#app')
