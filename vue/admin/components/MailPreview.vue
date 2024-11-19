<script setup lang="ts">
import { computed, inject, onMounted, ref, watch } from 'vue'
import { default as dAxios } from 'axios'
import { debounce } from 'lodash'
import { ActiveLoader, PluginApi, useLoading } from 'vue-loading-overlay'
import { AxiosKey } from '@admin/symbols'

const axios = inject(AxiosKey, dAxios)

interface MailPreviewProps {
  layoutStyleInput: string
  layoutInput: string
  contentInput: string
  styleInput: string
  renderUrl?: string
}

const props = withDefaults(defineProps<MailPreviewProps>(), {
  renderUrl: '/admin/mail/template/render-pug'
})

const layoutTextarea = ref<HTMLTextAreaElement>()
const layoutStyleTextarea = ref<HTMLTextAreaElement>()
const contentTextarea = ref<HTMLTextAreaElement>()
const styleTextarea = ref<HTMLTextAreaElement>()
const iframe = ref<HTMLIFrameElement>()
const iframeHeight = ref('600px')
const iframeWidth = ref('600px')
const width = ref(0)
const previewBlock = ref<HTMLElement>()

const rendered = ref('')
const isLoading = ref(false)

const computedWidth = computed(() => width.value ? width.value + 'px' : iframeWidth.value)

const renderPug = debounce(() => {
  isLoading.value = true
  layoutTextarea.value = document.getElementById(props.layoutInput) as HTMLTextAreaElement
  layoutStyleTextarea.value = document.getElementById(props.layoutStyleInput) as HTMLTextAreaElement
  contentTextarea.value = document.getElementById(props.contentInput) as HTMLTextAreaElement
  styleTextarea.value = document.getElementById(props.styleInput) as HTMLTextAreaElement
  axios
    .post(props.renderUrl, {
      layout: layoutTextarea.value.value,
      layoutStyle: layoutStyleTextarea.value.value,
      content: contentTextarea.value.value,
      style: styleTextarea.value.value
    })
    .then(({ data }) => (rendered.value = data))
    .catch(({ response: { data } }) => (rendered.value = data))
    .finally(() => (isLoading.value = false))
}, 500)

watch(rendered, renderedValue => {
  if (iframe.value) {
    const document = iframe.value.contentWindow?.document || iframe.value.contentDocument
    if (document) {
      document.open()
      document.write(renderedValue)
      document.close()
    }
  }
})

// Preloader
const loading: PluginApi = useLoading()
let loader = ref<ActiveLoader>()
watch(isLoading, val => {
  if (val && previewBlock.value) loader.value = loading.show({ container: previewBlock.value })
  else if (loader.value !== undefined) loader.value.hide()
})

function resize() {
  setTimeout(() => {
    const document = iframe.value?.contentWindow?.document || iframe.value?.contentDocument
    if (document) {
      const { body } = document
      iframeWidth.value = body.offsetWidth + body.scrollWidth - body.clientWidth + 'px'
      iframeHeight.value = body.offsetHeight + body.scrollHeight - body.clientHeight + 'px'
    }
  }, 50)
}

function downloadSource() {
  const pom = document.createElement('a')
  pom.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(rendered.value))
  pom.setAttribute('download', 'source.html')

  if (document.createEvent) {
    const event = document.createEvent('MouseEvents')
    event.initEvent('click', true, true)
    pom.dispatchEvent(event)
  } else {
    pom.click()
  }
}

onMounted(() => {
  if (iframe.value) iframe.value.addEventListener('load', resize)
  renderPug()
})
</script>

<template>
  <div class="row">
    <div class="col-2">
      <label for="widthInput" class="form-label">Ширина iframe</label>
      <input
        id="widthInput"
        v-model="width"
        name="width"
        class="form-control form-control-sm"
        type="number"
        min="0"
        max="1900"
        @keydown.enter.prevent
      />
    </div>
  </div>
  <button
    class="btn btn-sm btn-primary refresh-button"
    type="button"
    :disabled="isLoading"
    @click="renderPug"
  >
    <FontAwesomeIcon icon="redo" />
  </button>
  <button
    class="btn btn-sm btn-primary refresh-button"
    type="button"
    :disabled="isLoading"
    @click="downloadSource"
  >
    <FontAwesomeIcon icon="download" />
  </button>
  <div
    ref="previewBlock"
    class="mail-preview"
  >
    <iframe ref="iframe" />
  </div>
</template>

<style scoped lang="sass">
.mail-preview
  position: relative

  iframe
    width: v-bind('computedWidth')
    max-height: 80vh
    height: v-bind('iframeHeight')
</style>
