<script setup lang="ts">
import { computed, inject, ref, toRef } from 'vue'
import { ApexOptions } from 'apexcharts'
import { ActiveLoader, PluginApi, useLoading } from 'vue-loading-overlay'
import { AxiosKey } from '@admin/symbols'
import { default as dAxios } from 'axios'

const props = withDefaults(defineProps<{
  type: 'bar' | 'line',
  blockId: string,
  chartOptions?: ApexOptions,
  url: string,
  isDark?: boolean
}>(), {
  chartOptions: () => ({}),
  url: '/admin/statistic/index'
})
const type = toRef(props.type)
const chartOptions = computed<ApexOptions>(() => ({
  ...{
    chart: { toolbar: { show: true, autoSelected: 'zoom', export: { csv: { headerCategory: 'Дата' } } } },
    xaxis: { type: 'datetime' },
    tooltip: { x: { format: 'd.M.yyyy' } },
    colors: [
      '#f44336', '#03a9f4', '#ff9800', '#009688',
      '#e91e63', '#00bcd4', '#ff5722', '#ffeb3b',
      '#9c27b0', '#cddc39', '#2196f3', '#4caf50',
      '#673ab7', '#ffc107', '#3f51b5', '#8bc34a',
      '#795548'
    ],
    dataLabels: { enabled: false },
    stroke: { show: true, width: 2, curve: 'smooth' },
    theme: { mode: props.isDark ? 'dark' : 'light' }
  },
  ...props.chartOptions
}))

const axios = inject(AxiosKey, dAxios)
const series = ref([])

// Preloader
const loaderBlock = ref<HTMLDivElement | undefined>()
const loading: PluginApi = useLoading()
let loader: ActiveLoader | undefined
const loaded = ref(false)
if (!loaded.value) {
  setTimeout(() => {
    loader = loading.show({ container: loaderBlock.value })
    axios.get(props.url, { params: { id: props.blockId } })
      .then(response => {
        series.value = response.data.series
      })
      .finally(() => {
        loaded.value = true
        loader?.hide()
      })
  }, 10)
}
</script>

<template>
  <div :id="blockId" ref="loaderBlock" :style="{ 'min-height': '350px', 'position': 'relative' }">
    <template v-if="loaded">
      <apexchart
        v-if="type === 'line' || type === 'bar'"
        :series="series"
        :options="chartOptions"
        :type="type"
        height="350"
      />
    </template>
  </div>
</template>
