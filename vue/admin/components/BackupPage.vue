<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { default as dAxios } from 'axios'
import TheToastManager from './toasts/TheToastManager.vue'
import { AxiosKey } from '@admin/symbols'

const axios = inject(AxiosKey, dAxios)

interface BackupPageProps {
  /* Список ключ-значение для выпадающего списка резервных копий БД */
  backupsList: string[]
  /* Отображать ли подсказки */
  tooltipsShow?: boolean
  /* Ссылка на получение списка резервных копий БД */
  urlActiveBackups?: string
  /* Ссылка на получение списка таблиц БД */
  urlTables?: string
  /* Ссылка на экспорт таблиц БД */
  urlExport?: string
  /* Ссылка на импорт таблиц БД */
  urlImport?: string
  /* Ссылка на удаление резервной копии БД */
  urlRemove?: string
  /* Ссылка на скачивание резервной копии БД */
  urlDownload?: string
  /* Ссылка на загрузку новой резервной копии БД */
  urlUpload?: string
}

interface FlagsObject {
  [key: string]: boolean
}

const props = withDefaults(defineProps<BackupPageProps>(), {
  backupsList: () => [],
  urlActiveBackups: '/admin/backup/default/active-backups',
  urlTables: '/admin/backup/default/tables',
  urlExport: '/admin/backup/default/export',
  urlImport: '/admin/backup/default/import',
  urlRemove: '/admin/backup/default/remove',
  urlDownload: '/admin/backup/default/download',
  urlUpload: '/admin/backup/default/upload'
})

const toaster = ref<InstanceType<typeof TheToastManager>>()
/* Список резервных копий БД */
let currentBackupsList = ref(props.backupsList)
/* Ключ выбранной резервной копии БД */
let selectedBackup = ref<string | number>()
if (props.backupsList?.length) {
  selectedBackup.value = props.backupsList.length - 1
}
/* Отображение предварительного загрузчика на кнопке импорта */
const importLoading = ref(false)
/* Отображение предварительного загрузчика на кнопке экспорта */
const exportLoading = ref(false)
/* Выключить все кнопки */
const allDisable = ref(false)

const backups = computed(() => {
  let options: { value: number; text: string }[] = []
  currentBackupsList.value.forEach((value, key) => {
    options.push({
      value: key,
      text: value
    })
  })
  return options
})
/* Динамический список таблиц для импорта */
const dbImportAll = ref<FlagsObject>({})
/* Динамический список таблиц для экспорта */
const dbExportAll = ref<FlagsObject>({})

/**
 * Проверка объекта на отсутствие false, null, undefined значений
 */
function isTrue(jsonObj: FlagsObject) {
  if (!jsonObj || count(jsonObj) === 0) return false
  let res = true
  jQuery.each(jsonObj, (key, value) => {
    if (!value) res = false
  })
  return res
}

/**
 * Подсчет количества полей в объекте
 */
function count(c: FlagsObject) {
  let a = 0
  Object.entries(c).forEach((s, b) => b && a++)
  return a
}

/**
 * Вывести сообщения от сервера
 */
function addLog(mess: { [s: string]: Array<string> }) {
  if (mess) {
    for (const [code, messages] of Object.entries(mess)) {
      messages.forEach(message => {
        toaster.value.addToast({ type: code, text: message })
      })
    }
  }
}

/**
 * Вывести одно сообщение
 * @param mess текст сообщения
 * @param status цвет сообщения: success, warning, danger, info, light, dark
 */
function addLogStr(mess: string, status: string) {
  toaster.value.addToast({ type: status, text: mess })
}

/**
 * Обновить список резервных копий
 */
function updateBackupsAction() {
  axios.get(props.urlActiveBackups).then(response => {
    if (response.data.messages) addLog(response.data.messages)
    if (response.data.data) {
      currentBackupsList.value = response.data.data
      selectedBackup.value = currentBackupsList.value.length - 1
    }
  })
}

function tablesAction(handleTablesList: (_tables: string[]) => Promise<void>) {
  axios.post(props.urlTables, null, { timeout: 999999 }).then(async response => {
    if (response.data.messages) addLog(response.data.messages)
    if (response.data.data) await handleTablesList(response.data.data)
  })
}

/**
 * Импорт таблиц из выбранной резервной копии БД
 */
function importAction() {
  if (selectedBackup.value === '' || selectedBackup.value === undefined) {
    addLogStr('Необходимо выбрать бекап!', 'danger')
    return
  }
  const dateStr = currentBackupsList.value[selectedBackup.value]
  allDisable.value = true
  importLoading.value = true
  tablesAction(async tables => {
    tables.forEach(table => {
      dbImportAll.value[table] = false
    })
    for (const i in tables) {
      const table = tables[i]
      // отправляем запрос на импорт
      await axios
        .post(
          props.urlImport,
          { date: dateStr, table },
          { timeout: 999999, headers: { 'Content-Type': 'multipart/form-data' } }
        )
        .then(response => {
          if (response.data.messages) addLog(response.data.messages)
          dbImportAll.value[table] = true
          if (isTrue(dbImportAll.value)) {
            addLogStr('Импорт ВСЕХ таблиц ЗАВЕРШЁН', 'success')
            allDisable.value = false
            importLoading.value = false
          }
        })
    }
  })
}

/**
 * Экспорт таблиц
 */
function exportAction() {
  const date = new Date(),
    month = `0${date.getMonth() + 1}`.slice(-2),
    dateStr =
      `${date.getFullYear()}-${month}-${('0' + date.getDate()).slice(-2)}` +
      `_${date.getHours()}-${date.getMinutes()}-${date.getSeconds()}`
  allDisable.value = true
  exportLoading.value = true
  tablesAction(async tables => {
    tables.forEach(table => {
      dbExportAll.value[table] = false
    })
    for (const i in tables) {
      const table = tables[i]
      // отправляем запрос на Экспорт
      await axios
        .post(
          props.urlExport,
          { date: dateStr, table },
          { timeout: 999999, headers: { 'Content-Type': 'multipart/form-data' } }
        )
        .then(response => {
          if (response.data.messages) addLog(response.data.messages)
          dbExportAll.value[table] = true
          if (isTrue(dbExportAll.value)) {
            addLogStr('Экспорт ВСЕХ таблиц ЗАВЕРШЁН', 'success')
            allDisable.value = false
            exportLoading.value = false
            updateBackupsAction()
          }
        })
    }
  })
}

/**
 * Скачать выбранный бекап
 */
function downloadAction() {
  if (selectedBackup.value === '' || selectedBackup.value === undefined) {
    addLogStr('Необходимо выбрать бекап!', 'danger')
    return
  }
  const dateStr = currentBackupsList.value[selectedBackup.value]
  window.location.href = `${props.urlDownload}?date=${dateStr}`
}

/**
 * Загрузить бекап
 */
function uploadAction(event: { target: HTMLInputElement }) {
  let file = event.target.files?.[0]
  if (!file) return
  allDisable.value = true
  const formData = new FormData()
  formData.append('file', file, file.name)
  axios.post(props.urlUpload, formData, { timeout: 999999 }).then(response => {
    if (response.data.messages) addLog(response.data.messages)
    event.target.value = ''
    allDisable.value = false
    updateBackupsAction()
  })
}

/**
 * Удалить все резервные копии БД
 */
function removeAllAction() {
  axios.post(props.urlRemove, null, { timeout: 999999 }).then(response => {
    if (response.data.messages) addLog(response.data.messages)
    addLogStr('Файлы резервной копии удалены', 'success')
    updateBackupsAction()
  })
}
</script>

<template>
  <div class="row">
    <div class="col-5">
      <p>
        <button
          class="btn btn-info"
          :disabled="allDisable"
          @click="exportAction"
        >
          <FontAwesomeIcon icon="file-export" />
          Экспортировать ВСЕ таблицы из БД
        </button>
      </p>
      <p>
        <button
          class="btn btn-danger"
          :disabled="allDisable"
          @click="importAction"
        >
          <FontAwesomeIcon icon="file-import" />
          Импортировать ВСЕ таблицы в БД из выбранной резервной копии
          <span
            data-bs-toggle="tooltip"
            data-trigger="hover"
            :hidden="!tooltipsShow"
            title="Если настроен префикс таблиц, то он должен совпадать"
          >
            <FontAwesomeIcon icon="question-circle" />
          </span>
        </button>
      </p>
      <p class="input-group">
        <select
          v-model="selectedBackup"
          class="form-select"
          :disabled="allDisable"
        >
          <option
            v-for="(option, key) in backups"
            :key="key"
            :value="option.value"
          >
            {{ option.text }}
          </option>
        </select>
        <button
          class="btn btn-outline-secondary input-group-text"
          style="margin: 0; height: 36px; padding: 0.5rem 1.5rem"
          :disabled="allDisable"
          @click="updateBackupsAction"
        >
          <FontAwesomeIcon icon="redo-alt" />
        </button>
      </p>
      <p>
        <button
          class="btn btn-info"
          :disabled="allDisable"
          @click="downloadAction"
        >
          <FontAwesomeIcon icon="download" />
          Скачать выбранную резервную копию
        </button>
      </p>
      <p>
        <span>
          <label
            for="uploadForm"
            class="form-label"
          >
            Загрузка архива с резервной копей
            <span
              data-bs-toggle="tooltip"
              :hidden="!tooltipsShow"
              title="Структура таблиц должна быть одинаковой!"
            >
              <FontAwesomeIcon icon="question-circle" />
            </span>
          </label>
          <input
            id="uploadForm"
            class="form-control"
            type="file"
            :disabled="allDisable"
            accept="*.zip"
            placeholder="Выберите архив..."
            @change="uploadAction"
          />
        </span>
      </p>
      <hr />
      <p>
        <button
          class="btn btn-warning"
          :disabled="allDisable"
          @click="removeAllAction"
        >
          <FontAwesomeIcon icon="trash-alt" />
          Удалить все резервные копии
        </button>
      </p>
    </div>
    <div class="col">
      <div
        class="card"
        style="height: 350px; overflow-y: scroll"
      >
        <div class="card-body">
          <TheToastManager ref="toaster" />
        </div>
      </div>
    </div>
  </div>
</template>
