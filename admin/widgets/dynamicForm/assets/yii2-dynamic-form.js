/**
 * yii2-dynamic-form
 *
 * A jQuery plugin to clone form elements in a nested manner, maintaining
 * accessibility.
 *
 * @author Wanderson Bragança <wanderson.wbc@gmail.com>
 */
(function ($) {
  const pluginName = 'yiiDynamicForm'

  const regexID = /^(.+?)(-[\d-]+)(.+)$/i

  const regexName = /(^.+?)([\[\d{1,}\]]+)(\[.+]$)/i

  $.fn.yiiDynamicForm = function (method) {
    if (methods[method]) {
      return methods[method].apply(this, Array.prototype.slice.call(arguments, 1))
    }
    else if (typeof method === 'object' || !method) {
      return methods.init.apply(this, arguments)
    }
    else {
      $.error(`Method ${method} does not exist on jQuery.yiiDynamicForm`)
      return false
    }
  }

  const events = {
    beforeInsert: 'beforeInsert',
    afterInsert: 'afterInsert',
    beforeDelete: 'beforeDelete',
    afterDelete: 'afterDelete',
    limitReached: 'limitReached'
  }

  const methods = {
    init: function (widgetOptions) {
      return this.each(function () {
        widgetOptions.template = _parseTemplate(widgetOptions)
      })
    },

    addItem: function (widgetOptions, e, $elem) {
      _addItem(widgetOptions, e, $elem)
    },

    deleteItem: function (widgetOptions, e, $elem) {
      _deleteItem(widgetOptions, e, $elem)
    },

    updateContainer: function () {
      const widgetOptions = eval($(this).attr('data-dynamicform'))
      _updateAttributes(widgetOptions)
      _restoreSpecialJs(widgetOptions)
      _fixFormValidaton(widgetOptions)
    }
  }

  const _parseTemplate = function (widgetOptions) {
    const $template = $(widgetOptions.template)
    $template.find('input, textarea, select').each(function () {
      const $item = $(this)
      if ($item.is(':checkbox') || $item.is(':radio')) {
        const type = ($item.is(':checkbox')) ? 'checkbox' : 'radio'
        const inputName = $item.attr('name')
        const $inputHidden = $template.find(`input[type="hidden"][name="${inputName}"]`).first()
        const count = $template.find(`input[type="${type}"][name="${inputName}"]`).length

        if ($inputHidden && count === 1) {
          $item.val(1)
          $inputHidden.val(0)
        }

        $item.prop('checked', false)
      }
      else if ($item.is('select')) {
        $item.find('option:selected').removeAttr('selected')
      }
      else {
        $item.val('')
      }
    })

    // remove "error/success" css class
    const yiiActiveFormData = $(`#${widgetOptions.formId}`).yiiActiveForm('data')
    let $errorCssClass = 'has-error'
    let $successCssClass = 'has-success' // init default yii class

    // seek settings data only if object is not undefined.
    if (typeof yiiActiveFormData != 'undefined') {
      $errorCssClass = yiiActiveFormData.settings.errorCssClass
      $successCssClass = yiiActiveFormData.settings.successCssClass
    }
    $template.find('.' + $errorCssClass).removeClass($errorCssClass)
    $template.find('.' + $successCssClass).removeClass($successCssClass)

    return $template
  }

  const _getWidgetOptionsRoot = function (widgetOptions) {
    return eval($(widgetOptions.widgetBody).parents('div[data-dynamicform]').last().attr('data-dynamicform'))
  }

  const _getLevel = function ($elem) {
    let level = $elem.parents('div[data-dynamicform]').length
    level = (level < 0) ? 0 : level
    return level
  }

  const _count = function ($elem, widgetOptions) {
    return $elem.closest(`.${widgetOptions.widgetContainer}`).find(widgetOptions.widgetItem).length
  }

  const _createIdentifiers = function (level) {
    return new Array(level + 2).join('0').split('')
  }

  const _addItem = function (widgetOptions, e, $elem) {
    const count = _count($elem, widgetOptions)
    const containerClass = `.${widgetOptions.widgetContainer}`
    if (count < widgetOptions.limit) {
      const $toclone = $(widgetOptions.template)
      const $newclone = $toclone.clone(false, false)

      if (widgetOptions.insertPosition === 'top') {
        $elem.closest(containerClass).find(widgetOptions.widgetBody).prepend($newclone)
      }
      else {
        $elem.closest(containerClass).find(widgetOptions.widgetBody).append($newclone)
      }

      _updateAttributes(widgetOptions)
      _restoreSpecialJs(widgetOptions)
      _fixFormValidaton(widgetOptions)
      $elem.closest(containerClass).triggerHandler(events.afterInsert, $newclone)
    }
    else {
      // trigger a custom event for hooking
      $elem.closest(containerClass).triggerHandler(events.limitReached, widgetOptions.limit)
    }
  }

  const _removeValidations = function ($elem, widgetOptions, count) {
    if (count > 1) {
      $elem.find('div[data-dynamicform]').each(function () {
        const $item = $(this)
        const currentWidgetOptions = eval($item.attr('data-dynamicform'))
        const level = _getLevel($item)
        const identifiers = _createIdentifiers(level)
        const numItems = $item.find(currentWidgetOptions.widgetItem).length

        const form = $(`#${currentWidgetOptions.formId}`)
        for (let i = 1; i <= numItems - 1; i++) {
          let aux = identifiers
          aux[level] = i
          currentWidgetOptions.fields.forEach(function (input) {
            const id = input.id.replace('{}', aux.join('-'))
            if (form.yiiActiveForm('find', id) !== 'undefined') {
              form.yiiActiveForm('remove', id)
            }
          })
        }
      })

      const level = _getLevel($elem.closest(`.${widgetOptions.widgetContainer}`))
      const widgetOptionsRoot = _getWidgetOptionsRoot(widgetOptions)
      let identifiers = _createIdentifiers(level)
      identifiers[0] = $(widgetOptionsRoot.widgetItem).length - 1
      identifiers[level] = count - 1

      const form = $(`#${widgetOptions.formId}`)
      widgetOptions.fields.forEach(function (input) {
        const id = input.id.replace('{}', identifiers.join('-'))
        if (form.yiiActiveForm('find', id) !== 'undefined') {
          form.yiiActiveForm('remove', id)
        }
      })
    }
  }

  const _deleteItem = function (widgetOptions, e, $elem) {
    const count = _count($elem, widgetOptions)

    if (count > widgetOptions.min) {
      const $todelete = $elem.closest(widgetOptions.widgetItem)

      // trigger a custom event for hooking
      const container = $(`.${widgetOptions.widgetContainer}`)
      const eventResult = container.triggerHandler(events.beforeDelete, $todelete)
      if (eventResult !== false) {
        _removeValidations($todelete, widgetOptions, count)
        $todelete.remove()
        _updateAttributes(widgetOptions)
        _restoreSpecialJs(widgetOptions)
        _fixFormValidaton(widgetOptions)
        container.triggerHandler(events.afterDelete)
      }
    }
  }

  const _updateAttrID = function ($elem, index) {
    const widgetOptions = eval($elem.closest('div[data-dynamicform]').attr('data-dynamicform'))
    const id = $elem.attr('id')
    let newID = id

    if (id !== undefined) {
      const matches = id.match(regexID)
      if (matches && matches.length === 4) {
        matches[2] = matches[2].substring(1, matches[2].length - 1)
        let identifiers = matches[2].split('-')
        identifiers[0] = index

        if (identifiers.length > 1) {
          let widgetsOptions = []
          $elem.parents('div[data-dynamicform]').each(function (i) {
            widgetsOptions[i] = eval($(this).attr('data-dynamicform'))
          })

          widgetsOptions = widgetsOptions.reverse()
          for (let i = identifiers.length - 1; i >= 1; i--) {
            identifiers[i] = $elem.closest(widgetsOptions[i].widgetItem).index()
          }
        }

        newID = `${matches[1]}-${identifiers.join('-')}-${matches[3]}`
        $elem.attr('id', newID)
      }
      else {
        newID = id + index
        $elem.attr('id', newID)
      }
    }

    if (id !== newID) {
      $elem.closest(widgetOptions.widgetItem).find(`.field-${id}`)
        .each(function () {
          $(this).removeClass(`field-${id}`).addClass(`field-${newID}`)
        })
      // update "for" attribute
      $elem.closest(widgetOptions.widgetItem).find(`label[for='${id}']`).attr('for', newID)
    }

    return newID
  }

  const _updateAttrName = function ($elem, index) {
    let name = $elem.attr('name')

    if (name !== undefined) {
      const matches = name.match(regexName)

      if (matches && matches.length === 4) {
        matches[2] = matches[2].replace(/\]\[/g, '-').replace(/]|\[/g, '')
        let identifiers = matches[2].split('-')
        identifiers[0] = index

        if (identifiers.length > 1) {
          let widgetsOptions = []
          $elem.parents('div[data-dynamicform]').each(function (i) {
            widgetsOptions[i] = eval($(this).attr('data-dynamicform'))
          })

          widgetsOptions = widgetsOptions.reverse()
          for (let i = identifiers.length - 1; i >= 1; i--) {
            identifiers[i] = $elem.closest(widgetsOptions[i].widgetItem).index()
          }
        }

        name = `${matches[1]}[${identifiers.join('][')}]${matches[3]}`
        $elem.attr('name', name)
      }
    }

    return name
  }

  const _updateCKFinderFileInput = function ($elem) {
    if (!$elem.hasClass('file-input') && !$elem.hasClass('input-group')) return
    let input = $elem.find('input')
    let id = input.attr('id')
    let number = id ? id.match(/[-\d]+/) : false
    if (!number) return
    let countSeparators = number[0].split('-').length - 1
    let i = 2
    let regex = '-\\\d+-'
    while (i < countSeparators) {
      regex += '\\\d+-'
      i++
    }
    regex = new RegExp(regex)
    $elem.find('button.file-input').each(function () {
      $(this).attr('onclick', $(this).attr('onclick').replace(regex, number))
    })
  }

  const _updateAttributes = function (widgetOptions) {
    const widgetOptionsRoot = _getWidgetOptionsRoot(widgetOptions)

    $(widgetOptionsRoot.widgetItem).each(function (index) {
      $(this).find('*').each(function () {
        const $item = $(this)
        // update "id" attribute
        _updateAttrID($item, index)
        // update "name" attribute
        _updateAttrName($item, index)
      })
    })
    $(widgetOptionsRoot.widgetItem).each(function (index) {
      $(this).find('*').each(function () {
        _updateCKFinderFileInput($(this))
      })
    })
  }

  const _fixFormValidatonInput = function (widgetOptions, attribute, id, name) {
    if (attribute !== undefined) {
      attribute = $.extend(true, {}, attribute)
      attribute.id = id
      attribute.container = `.field-${id}`
      attribute.input = `#${id}`
      attribute.name = name
      attribute.value = $(`#${id}`).val()
      attribute.status = 0
      const form = $(`#${widgetOptions.formId}`)
      if (form.yiiActiveForm('find', id) !== 'undefined') {
        form.yiiActiveForm('remove', id)
      }
      form.yiiActiveForm('add', attribute)
    }
  }

  const _fixFormValidaton = function (widgetOptions) {
    const widgetOptionsRoot = _getWidgetOptionsRoot(widgetOptions)

    $(widgetOptionsRoot.widgetBody).find('input, textarea, select').each(function () {
      const $item = $(this)
      const id = $item.attr('id')
      const name = $item.attr('name')

      if (id !== undefined && name !== undefined) {
        const currentWidgetOptions = eval($item.closest('div[data-dynamicform]').attr('data-dynamicform'))
        const matches = id.match(regexID)

        if (matches && matches.length === 4) {
          matches[2] = matches[2].substring(1, matches[2].length - 1)
          const level = _getLevel($item)
          const identifiers = _createIdentifiers(level - 1)
          const baseID = `${matches[1]}-${identifiers.join('-')}-${matches[3]}`
          const attribute = $(`#${currentWidgetOptions.formId}`).yiiActiveForm('find', baseID)
          _fixFormValidatonInput(currentWidgetOptions, attribute, id, name)
        }
      }
    })
  }

  const _restoreKrajeeDepdrop = function ($elem) {
    const configDepdrop = $.extend(true, {}, eval($elem.attr('data-krajee-depdrop')))
    const inputID = $elem.attr('id')
    const matchID = inputID.match(regexID)

    if (matchID && matchID.length === 4) {
      for (let index = 0; index < configDepdrop.depends.length; ++index) {
        const match = configDepdrop.depends[index].match(regexID)
        if (match && match.length === 4) {
          configDepdrop.depends[index] = match[1] + matchID[2] + match[3]
        }
      }
    }

    $elem.depdrop(configDepdrop)
  }

  const _restoreSpecialJs = function (widgetOptions) {
    const widgetOptionsRoot = _getWidgetOptionsRoot(widgetOptions)

    // "jquery.inputmask"
    const $hasInputmask = $(widgetOptionsRoot.widgetItem).find('[data-plugin-inputmask]')
    if ($hasInputmask.length > 0) {
      $hasInputmask.each(function () {
        const $item = $(this)
        $item.inputmask('remove')
        $item.inputmask(eval($item.attr('data-plugin-inputmask')))
      })
    }

    // "kartik-v/yii2-widget-datepicker"
    const $hasDatepicker = $(widgetOptionsRoot.widgetItem).find('[data-krajee-datepicker]')
    if ($hasDatepicker.length > 0) {
      $hasDatepicker.each(function () {
        const $item = $(this)
        $item.parent().removeData().datepicker('remove')
        $item.parent().datepicker(eval($item.attr('data-krajee-datepicker')))
      })
    }

    // "kartik-v/yii2-widget-timepicker"
    const $hasTimepicker = $(widgetOptionsRoot.widgetItem).find('[data-krajee-timepicker]')
    if ($hasTimepicker.length > 0) {
      $hasTimepicker.each(function () {
        const $item = $(this)
        $item.removeData().off()
        $item.parent().find('.bootstrap-timepicker-widget').remove()
        $item.unbind()
        $item.timepicker(eval($item.attr('data-krajee-timepicker')))
      })
    }

    // "kartik-v/yii2-money"
    const $hasMaskmoney = $(widgetOptionsRoot.widgetItem).find('[data-krajee-maskMoney]')
    if ($hasMaskmoney.length > 0) {
      $hasMaskmoney.each(function () {
        const $item = $(this)
        $item.parent().find('input').removeData().off()
        const id = `#${$item.attr('id')}`
        const input = $(id)
        const display = $(`${id}-disp`)
        display.maskMoney('destroy')
        display.maskMoney(eval($item.attr('data-krajee-maskMoney')))
        display.maskMoney('mask', parseFloat(input.val()))
        display.on('change', function () {
          const numDecimal = display.maskMoney('unmasked')[0]
          input.val(numDecimal)
          input.trigger('change')
        })
      })
    }

    // "kartik-v/yii2-widget-fileinput"
    const $hasFileinput = $(widgetOptionsRoot.widgetItem).find('[data-krajee-fileinput]')
    if ($hasFileinput.length > 0) {
      $hasFileinput.each(function () {
        const $item = $(this)
        $item.fileinput(eval($item.attr('data-krajee-fileinput')))
      })
    }

    // "kartik-v/yii2-widget-touchspin"
    const $hasTouchSpin = $(widgetOptionsRoot.widgetItem).find('[data-krajee-TouchSpin]')
    if ($hasTouchSpin.length > 0) {
      $hasTouchSpin.each(function () {
        const $item = $(this)
        $item.TouchSpin('destroy')
        $item.TouchSpin(eval($item.attr('data-krajee-TouchSpin')))
      })
    }

    // "kartik-v/yii2-widget-colorinput"
    const $hasSpectrum = $(widgetOptionsRoot.widgetItem).find('[data-krajee-spectrum]')
    if ($hasSpectrum.length > 0) {
      $hasSpectrum.each(function () {
        const $item = $(this)
        const id = `#${$item.attr('id')}`
        const sourceID = `${id}-source`
        $(sourceID).spectrum('destroy')
        $(sourceID).unbind()
        $(id).unbind()
        const configSpectrum = eval($item.attr('data-krajee-spectrum'))
        configSpectrum.change = function (color) {
          jQuery(id).val(color.toString())
        }
        $(sourceID).attr('name', $(sourceID).attr('id'))
        $(sourceID).spectrum(configSpectrum)
        $(sourceID).spectrum('set', jQuery(id).val())
        $(id).on('change', function () {
          $(sourceID).spectrum('set', jQuery(id).val())
        })
      })
    }

    // "kartik-v/yii2-widget-depdrop"
    const $hasDepdrop = $(widgetOptionsRoot.widgetItem).find('[data-krajee-depdrop]')
    if ($hasDepdrop.length > 0) {
      $hasDepdrop.each(function () {
        const $item = $(this)
        if ($item.data('select2') === undefined) {
          $item.removeData().off()
          $item.unbind()
          _restoreKrajeeDepdrop($item)
        }
      })
    }

    // "kartik-v/yii2-widget-select2"
    const $hasSelect2 = $(widgetOptionsRoot.widgetItem).find('[data-krajee-select2]')
    if ($hasSelect2.length > 0) {
      $hasSelect2.each(function () {
        const $item = $(this)
        const id = $item.attr('id')
        const configSelect2 = eval($item.attr('data-krajee-select2'))

        if ($item.data('select2')) {
          $item.select2('destroy')
        }

        let configDepdrop = $item.data('depdrop')
        if (configDepdrop) {
          configDepdrop = $.extend(true, {}, configDepdrop)
          $item.removeData().off()
          $item.unbind()
          _restoreKrajeeDepdrop($item)
        }

        const s2LoadingFunc = typeof initSelect2Loading != 'undefined' ? initSelect2Loading : initS2Loading
        const s2OpenFunc = typeof initSelect2DropStyle != 'undefined' ? initSelect2Loading : initS2Loading
        $.when($item.select2(configSelect2)).done(s2LoadingFunc(id, '.select2-container--krajee'))

        const kvClose = 'kv_close_' + id.replace(/-/g, '_')

        $item.on('select2:opening', function (ev) {
          s2OpenFunc(id, kvClose, ev)
        })

        $item.on('select2:unselect', function () {
          window[kvClose] = true
        })

        if (configDepdrop) {
          initDepdropS2(id, (configDepdrop.loadingText) ? configDepdrop.loadingText : 'Loading ...')
        }
      })
    }

    const $hasAceEditor = $(widgetOptionsRoot.widgetItem).find('.ace_editor')
    if ($hasAceEditor.length > 0) {
      const firstAce = ace.edit($hasAceEditor.first()[0])
      const newAce = ace.edit($hasAceEditor.last()[0])
      newAce.setTheme(firstAce.getTheme())
      newAce.getSession().setMode(firstAce.getSession().getMode())
      newAce.setReadOnly(firstAce.getReadOnly())
      const textarea = $hasAceEditor.last().find('textarea')
      newAce.getSession().setValue(textarea.val())
      newAce.getSession().on('change', function () {
        textarea.val(newAce.getSession().getValue())
      })
    }

    const $hasCKEditor = $(widgetOptionsRoot.widgetItem).find('[data-ckeditor-type]')
    if ($hasCKEditor.length > 0) {
      $hasCKEditor.each(function () {
        const $item = $(this)
        let editorType = $item.attr('data-ckeditor-type')
        const clientOptions = JSON.parse($item.attr('data-ckeditor-options'))
        if (typeof editorType == 'undefined' || !editorType) {
          editorType = {}
        }
        window[`${editorType}Editor`].create(this, clientOptions)
          .catch(error => {console.error(error)})
        this.removeAttribute('data-ckeditor-type')
        this.removeAttribute('data-ckeditor-options')
      })
    }
  }
})(window.jQuery)