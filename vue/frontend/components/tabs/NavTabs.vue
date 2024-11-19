<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { TabList } from './index'
import { useRouter } from 'vue-router'

const props = defineProps<{
  tabs: TabList
}>()

const $router = useRouter()

function getStartTab(): string {
  let initTab: string | undefined = undefined
  if (window.location.hash) {
    const hash = window.location.hash.substring(1)
    const tab = props.tabs.find(tab => tab.tab === hash)
    if (tab) initTab = tab.tab
  }
  if (initTab === undefined) initTab = props.tabs[0].tab
  return initTab
}

const currentTabId = ref(getStartTab())
const animated = ref(false)
const currentTab = computed(() => props.tabs.find(tab => tab.tab === currentTabId.value))

watch(currentTabId, async newTab => {
  await $router.push(`#${newTab}`)
  window.history.replaceState(window.history.state, '')
})

function updateTab() {
  const hash = window.location.hash.substring(1)
  const tab = props.tabs.find(tab => tab.tab === hash)
  if (tab) changeTab(tab.tab)
}

function changeTab(tab: string) {
  if (!animated.value) {
    setStartAnimate()
    currentTabId.value = tab
    setTimeout(setEndAnimate, 1000)
  }
}

const setStartAnimate = () => (animated.value = true)
const setEndAnimate = () => (animated.value = false)

onMounted(() => window.addEventListener('hashchange', updateTab))

onUnmounted(() => window.removeEventListener('hashchange', updateTab))
</script>

<template>
  <ul
    class="nav nav-tabs"
    role="tablist"
  >
    <li
      v-for="(tab, key) in tabs"
      :key="key"
      class="nav-item"
      role="presentation"
    >
      <button
        :id="`${tab.tab}-tab`"
        :class="{ 'nav-link': true, active: currentTabId === tab.tab }"
        type="button"
        aria-selected="true"
        role="tab"
        data-bs-toggle="tab"
        @click.prevent="() => changeTab(tab.tab)"
      >
        <FontAwesomeIcon
          v-if="tab.icon"
          icon="tab.icon"
        />
        &nbsp;
        {{ tab.title }}
      </button>
    </li>
  </ul>
  <div class="tab-content">
    <div
      v-if="currentTab"
      :id="currentTab.tab"
      class="tab-pane fade show active"
      role="tabpanel"
    >
      <Transition
        name="tab-fade"
        mode="out-in"
        @before-leave="setStartAnimate"
        @after-enter="setEndAnimate"
      >
        <KeepAlive>
          <Suspense @pending="setStartAnimate">
            <template #fallback>
              <h2>Загрузка...</h2>
            </template>
            <Component
              :is="currentTab.component"
              v-bind="currentTab.props"
            />
          </Suspense>
        </KeepAlive>
      </Transition>
    </div>
  </div>
</template>

<style lang="sass">
.tab-fade
  &-enter-active,
  &-leave-active
    transition: opacity .1s ease

  &-enter-from,
  &-leave-to
    opacity: 0
</style>
