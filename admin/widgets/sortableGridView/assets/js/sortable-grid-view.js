(function ($) {
  $.SortableGridView = function (options) {
    const defaultOptions = {
      id: 'sortable-grid-view',
      action: 'sortItem',
      sortingPromptText: 'Loading...',
      sortingFailText: 'Fail to sort',
      csrfTokenName: '',
      csrfToken: '',
    }

    $.extend({}, defaultOptions, options)

    $('body').append('<div class="modal fade" id="' + options.id + '-sorting-modal" tabindex="-1" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-body">' + options.sortingPromptText + '</div></div></div></div>')

    $('#' + options.id + ' .sortable-grid-view tbody').sortable({
      update: function () {
        const modal = $('#' + options.id + '-sorting-modal')
        modal.modal('show')
        let serial = []

        $('#' + options.id + ' .sortable-grid-view tbody .ui-sortable-handle').each(function () {
          serial.push($(this).data('key'))
        })

        const length = serial.length
        let currentRecordNo = 0
        let successRecordNo = 0
        let data = []

        if (length > 0) {
          for (let i = 0; i < length; i++) {
            const itemID = serial[i]
            data.push(itemID)
            currentRecordNo++

            if (currentRecordNo === 500 || i === (length - 1)) {

              (function (currentRecordNo) {
                $.ajax({
                  'url': options.action,
                  'type': 'post',
                  'data': {
                    'items': data,
                    [options.csrfTokenName]: options.csrfToken
                  },
                  success: function () {
                    checkSuccess(currentRecordNo)
                  },
                  error: function () {
                    hideModal()
                    alert(options.sortingFailText)
                  }
                })
              })(currentRecordNo)

              currentRecordNo = 0
              data = []
            }
          }
        }

        function checkSuccess (count) {
          successRecordNo += count

          if (successRecordNo >= length) {
            hideModal()
          }
        }

        function hideModal () {
          modal.on('shown.bs.modal', function () {
            modal.modal('hide')
          })
          modal.modal('hide')
        }
      },
    })
  }
})(jQuery);