<?php

use api\modules\v1\swagger\Asset;

/**
 * @var string $apiJsonUrl
 */

Asset::register($this);
?>

<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="UTF-8">
        <title>Swagger UI</title>
        <?php $this->head() ?>
    </head>

    <body style="margin:0;">
    <?php $this->beginBody() ?>
    <div id="swagger-ui"></div>
    <script>
      window.onload = function () {
        // Begin Swagger UI call region
        window.ui = SwaggerUIBundle({
          url: '<?= $apiJsonUrl ?>',
          dom_id: '#swagger-ui',
          deepLinking: true,
          presets: [
            SwaggerUIBundle.presets.apis,
            SwaggerUIStandalonePreset
          ],
          plugins: [
            SwaggerUIBundle.plugins.DownloadUrl
          ],
          layout: 'StandaloneLayout'
        })
      }
    </script>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>