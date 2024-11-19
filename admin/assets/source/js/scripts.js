$(function () {
  $('[data-bs-toggle="tooltip"]').tooltip()
  $('[data-bs-toggle="popover"]').popover()

  // Bootstrap 4 tooltip pjax compatibility
  $(document).on('pjax:start', function(event) {
    $('[data-bs-toggle="tooltip"]').tooltip('dispose')
    $('[data-bs-toggle="popover"]').popover('dispose')
  });
  $(document).on('pjax:complete', function(event) {
    $('[data-bs-toggle="tooltip"]').tooltip();
    $('[data-bs-toggle="popover"]').popover()
  });

  // Dynamic Form select2 work restore fix
  window.initS2Open = function (id, val) {
    initS2ToggleAll(id)
  }
})

/**
 * Закрыть все открытые модальники. Использовать в местах, где они зависают
 */
function killAllModals() {
  $('.modal').modal('hide')
  $('body').removeClass('modal-open')
  $('.modal-backdrop').remove()
}