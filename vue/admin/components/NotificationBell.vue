<script setup lang="ts">
import { computed, inject, onMounted, onUnmounted, ref } from 'vue'
import { default as dAxios } from 'axios'
import Popper from 'vue3-popper'
import { timestampToDatetime } from '@admin/globalFunctions'
import { AxiosKey } from '@admin/symbols'

const axios = inject(AxiosKey, dAxios)

interface NotificationsResponse {
  notifications: Array<Notification>
}

interface Notification {
  id: number
  type: string
  text: string
  created_at: number
  is_viewed: boolean
}

interface NotificationBellProps {
  notifications?: Notification[]
  urlModule: string
  timeout?: number
}

const props = withDefaults(defineProps<NotificationBellProps>(), {
  notifications: () => [],
  timeout: 2000,
})

const notificationList = ref(props.notifications)
const closedIds = ref<number[]>([])
const updateTimer = ref<NodeJS.Timeout>()

const urlList = computed(() => `${props.urlModule}/index`)
const urlClose = computed(() => `${props.urlModule}/close`)
const urlCloseAll = computed(() => `${props.urlModule}/close-all`)
const urlView = computed(() => `${props.urlModule}/view`)
const urlViewAll = computed(() => `${props.urlModule}/view-all`)

const hasNotifications = computed(() => notificationList.value.length > 0)
const hasNotViewed = computed(() => !!notificationList.value.find(notification => !notification.is_viewed))
const popupTitle = computed(() => (hasNotifications.value ? 'Уведомления' : 'Уведомлений нет!'))
const maxNotificationPriority = computed(() => {
  let priority = 'info'
  const tierList: { [key: string]: number } = { info: 5, success: 10, warning: 15, danger: 20 }
  notificationList.value.forEach(notification => {
    if (!notification.is_viewed && tierList[notification.type] > tierList[priority]) priority = notification.type
  })
  return priority
})
const counterVariant = computed(() => (hasNotViewed.value ? maxNotificationPriority.value : 'light'))

function updateNotifications() {
  if (!document.hidden) {
    const time: number = Date.parse(new Date().toString()) / 1000 - 10
    axios.get<NotificationsResponse>(`${urlList.value}?time=${time}`).then(data => {
      data.data.notifications.forEach((notification: Notification) => {
        const index = notificationList.value.findIndex(item => {
          return item.id === notification.id
        })
        if (index !== -1) notificationList.value[index] = notification
        else if (closedIds.value.indexOf(notification.id) === -1) notificationList.value.unshift(notification)
      })
      updateTimer.value = setTimeout(updateNotifications, props.timeout)
    })
  } else {
    updateTimer.value = setTimeout(updateNotifications, props.timeout)
  }
}

function toDatetime(timestamp: number) {
  return timestampToDatetime(timestamp, true)
}

function closeNotification(notification: Notification) {
  closedIds.value.push(notification.id)
  notificationList.value.splice(notificationList.value.indexOf(notification), 1)
  axios.get(`${urlClose.value}?id=${notification.id}`)
}

function closeAll() {
  notificationList.value = []
  axios.get(urlCloseAll.value)
}

function viewNotification(notification: Notification) {
  if (!notification.is_viewed) {
    notification.is_viewed = true
    axios.get(`${urlView.value}?id=${notification.id}`)
  }
}

function viewAll() {
  notificationList.value.forEach(notification => {
    if (!notification.is_viewed) notification.is_viewed = true
  })
  axios.get(urlViewAll.value)
}

onMounted(() => {
  updateTimer.value = setTimeout(updateNotifications, props.timeout)
})

onUnmounted(() => {
  clearTimeout(updateTimer.value)
})
</script>

<template>
  <div
    v-if="hasNotifications"
    class="notification-bell"
  >
    <Popper>
      <div>
        <span :class="'badge rounded-pill counter bg-' + counterVariant">
          {{ notificationList.length }}
        </span>
        <FontAwesomeIcon
          icon="bell"
          class="notification-icon"
        />
      </div>
      <template #content>
        <div class="card">
          <div class="card-title">
            <div class="notification__title">
              <p class="lead">
                {{ popupTitle }}
              </p>
            </div>
            <div
              v-if="hasNotifications"
              class="notification__link_group"
            >
              <a
                v-if="hasNotViewed"
                class="notification__link"
                @click="viewAll"
              >
                Отметить все как прочитанные
              </a>
              <a
                class="notification__link"
                @click="closeAll"
              >
                Закрыть все
              </a>
            </div>
          </div>
          <div class="card-body notification__popover">
            <div
              v-for="notification in notificationList"
              :key="notification.id"
              @mouseenter="viewNotification(notification)"
            >
              <div :class="`alert alert-dismissible show alert-container alert-${notification.type}`">
                <span
                  v-if="!notification.is_viewed"
                  class="badge rounded-pill bg-primary new-alert"
                >
                  new
                </span>
                <span class="notification__time text-secondary">
                  {{ toDatetime(notification.created_at) }}
                </span>
                <div v-dompurify-html="notification.text" />
                <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="alert"
                  aria-label="Close"
                  @click="closeNotification(notification)"
                />
              </div>
            </div>
          </div>
        </div>
      </template>
    </Popper>
  </div>
</template>

<style lang="sass">
.notification
  &__popover
    overflow: auto
    max-height: 600px
    min-width: 300px

  &__title
    text-align: center
    min-width: 300px

  &__link_group
    text-align: right
    font-size: smaller
    color: darkblue
    font-style: italic
    text-decoration: underline

  &__time
    position: absolute
    font-size: .6rem
    right: 1rem
    bottom: .2rem

  &__link
    display: block

.alert-container,
.notification-bell
  position: relative
  margin: 3px 6px

.new-alert
  position: absolute
  top: -0.1rem
  left: -0.4rem

#notification-popover
  margin: 0.4rem

.notification-bell .counter
  position: absolute
  top: 1.2rem
  left: 1.1rem
  font-size: 0.5rem

.notification-icon
  font-size: 1.6rem
</style>
