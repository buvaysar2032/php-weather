function updatePreview(id) {
  let input = $('#' + id)
  if (input) {
    let img = $('#' + id + '-preview')
    if (img) {
      img[0].setAttribute('src', input[0].value)
    }
  }
}

function selectFileWithCKFinder(baseUrl, elementId, isImage = true, resourceType = 'Images', swatch = 'a', callback = null, startupPath = undefined) {
  const rememberLastFolder = !startupPath
  CKFinder.modal({
    skin: 'jquery-mobile', swatch,
    chooseFiles: true,
    connectorPath: baseUrl + '/ckfinder.php',
    resourceType,
    startupPath,
    rememberLastFolder,
    width: 800,
    height: 600,
    onInit: function (finder) {
      finder.on('files:choose', function (evt) {
        let file = evt.data.files.first()
        let output = document.getElementById(elementId)
        output.value = file.getUrl()
        if (isImage) {
          updatePreview(elementId)
        }
        if (typeof callback === 'function') {
          callback()
        }
      })

      finder.on('file:choose:resizedImage', function (evt) {
        let output = document.getElementById(elementId)
        output.value = evt.data.resizedUrl
        if (isImage) {
          updatePreview(elementId)
        }
        if (typeof callback === 'function') {
          callback()
        }
      })
    }
  })
}