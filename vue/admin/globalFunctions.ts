/**
 * Перевести строку в диапазон дат
 * @param str строка с двумя датами, разделенными дефисом
 */
export function strToDateRange(str: any): { startDate: Date | null; endDate: Date | null } {
  if (str === '' || str === undefined || str === null) {
    return {
      startDate: null,
      endDate: null,
    }
  }
  const parts = str.split(' - ')
  if (parts.length === 2) {
    return {
      startDate: new Date(parts[0].replace(/(\d{2})\.(\d{2})\.(\d{4}), (.*)/, '$2/$1/$3 $4')),
      endDate: new Date(parts[1].replace(/(\d{2})\.(\d{2})\.(\d{4}), (.*)/, '$2/$1/$3 $4')),
    }
  }
  return {
    startDate: null,
    endDate: null,
  }
}

/**
 * Перевести диапазон дат в строку
 */
export function dateRangeToStr({
  startDate = null,
  endDate = null,
}: {
  startDate: Date | null
  endDate: Date | null
}): string {
  let str = ''
  const options: Intl.DateTimeFormatOptions = {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: 'numeric',
    minute: '2-digit',
  }
  if (startDate) {
    str += startDate.toLocaleDateString('ru-RU', options)
  }
  if (endDate) {
    str += ' - ' + endDate.toLocaleDateString('ru-RU', options)
  }
  return str
}

/**
 * Получить значение get параметра из query
 * @param name название параметра
 * @param url ссылка
 */
export function getParameterByName(name: string, url: string = window.location.href): string | null {
  name = encodeURI(name)
  name = name.replace(/[[\]]/g, '\\$&')
  const regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
    results = regex.exec(url)
  if (!results) {
    return null
  }
  if (!results[2]) {
    return ''
  }
  return decodeURIComponent(results[2].replace(/\+/g, ' '))
}

/**
 * Установить значение параметра в query
 * @param key название параметра
 * @param value значение
 * @param url ссылка
 */
export function setParameterByName(key: string, value: string, url: string = window.location.href): string {
  key = encodeURI(key)
  value = encodeURI(value)
  let baseUrl: RegExpMatchArray | string | null | undefined = url.match(/(^.+)\?/),
    urlQueryString: RegExpMatchArray | string | null | undefined = url.match(/\?.+$/)
  const newParam = key + '=' + value
  let params = '?' + newParam
  if (!baseUrl) {
    baseUrl = url
  } else {
    baseUrl = baseUrl.at(1)
  }
  if (!urlQueryString) {
    urlQueryString = ''
  } else {
    urlQueryString = urlQueryString.at(0)
  }
  // If the "search" string exists, then build params from it
  if (urlQueryString) {
    const keyRegex = new RegExp('([?&])' + key + '[^&]*')
    // If param exists already, update it
    if (urlQueryString.match(keyRegex) !== null) {
      params = urlQueryString.replace(keyRegex, '$1' + newParam)
    } else {
      // Otherwise, add it to end of query string
      params = urlQueryString + '&' + newParam
    }
  }
  return baseUrl + params
}

/**
 * Обновить get параметр в адресной строке браузера
 * @param key ключ параметра
 * @param value значение параметра
 */
export function updateQueryStringParam(key: string, value: string): void {
  window.history.replaceState({}, '', setParameterByName(key, value))
}

/**
 * Преобразовать timestamp бекенда в строку
 * @param timestamp временная метка в виде числа секунд от "рождества линукса"
 * @param withTime вывести время
 */
export function timestampToDatetime(timestamp: number, withTime = true): string {
  if (!timestamp) {
    return ''
  }
  const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: '2-digit', day: '2-digit' }
  if (withTime) {
    options.hour = 'numeric'
    options.minute = '2-digit'
  }
  return new Date(timestamp * 1000).toLocaleDateString('ru-RU', options)
}

export function timestampToDuration(timestamp: number, max = false) {
  let sec = timestamp
  let min = 0
  let hour = 0
  let day = 0
  let month = 0
  let year = 0

  function _getDouble(v: number) {
    return v < 10 ? '0' + v : v
  }

  function _getResult() {
    let s = ''
    if (year) {
      if (max) return year + (month / 12).toPrecision(1) + 'лет'
      else s += year + 'лет '
    }
    if (month) {
      if (max) return month + (day / 30).toPrecision(1) + 'мес'
      else s += month + 'мес '
    }
    if (day) {
      if (max) return day + (hour / 24).toPrecision(1) + 'д'
      else s += day + 'д '
    }
    if (hour) {
      if (max) return hour + (min / 60).toPrecision(1) + 'ч'
      else s += _getDouble(hour) + ':'
    }
    s += _getDouble(min) + ':' + _getDouble(sec)
    return s
  }

  if (sec >= 60) {
    min = ~~(sec / 60)
    sec -= min * 60
  } else return _getResult()
  if (min >= 60) {
    hour = ~~(min / 60)
    min -= hour * 60
  } else return _getResult()
  if (hour >= 24) {
    day = ~~(hour / 24)
    hour -= day * 24
  } else return _getResult()
  if (day >= 365) {
    year = ~~(day / 365)
    day -= year * 365
  }
  if (day >= 30) {
    month = ~~(day / 30)
    day -= month * 30
  }
  return _getResult()
}
