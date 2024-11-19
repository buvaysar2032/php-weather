<?php

namespace common\components\export;

use common\components\queue\AppQueue;
use common\widgets\ProgressBar;
use Exception;
use kartik\export\ExportMenu as KartikExportMenu;
use ReflectionException;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/**
 * Class ExportMenu
 *
 * @package common\components\export
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ExportMenu extends KartikExportMenu
{
    /**
     * ID виджета прогресс бара
     */
    public string $progressId;

    public string|ExportConfig $staticConfig;

    /**
     * Использование кастомной очереди
     */
    public bool $useQueue = false;

    /**
     * Жесткое лимитирование числа экспортируемых строк
     */
    public int $limit = 0;

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        if (!isset($this->progressId)) {
            $this->progressId = $this->id . '-progress';
        }
        $this->exportConfig[KartikExportMenu::FORMAT_TEXT] = false;
        $this->exportConfig[KartikExportMenu::FORMAT_HTML] = false;
        $this->exportConfig[KartikExportMenu::FORMAT_PDF] = false;
        $this->exportConfig[KartikExportMenu::FORMAT_EXCEL] = false;
        if ($this->useQueue) {
            $this->stream = false;
            if (empty($this->staticConfig)) {
                throw new InvalidConfigException('`staticConfig` property must be set');
            }
        }
        if (isset($this->staticConfig) && empty($this->columns)) {
            $this->columns = $this->staticConfig::getColumns();
        }
        parent::init();
    }

    /**
     * @throws ReflectionException
     * @throws InvalidConfigException
     * @throws Exception|Throwable
     */
    public function run()
    {
        if (!$this->useQueue) {
            parent::run();
            return null;
        }
        $this->initI18N(dirname(__DIR__, 3) . '/vendor/kartik-v/yii2-export/src');
        $this->initColumnSelector();
        $this->setVisibleColumns();
        $this->initExport();
        $this->registerAssets();
        if (
            !ExportJob::isExportInProcess($this->progressId)
            && Yii::$app->request->post($this->exportRequestParam, $this->triggerDownload)
        ) {
            if (Yii::$app->queue instanceof AppQueue) {
                Yii::$app->queue->priority(1);
            }
            $exportType = Yii::$app->request->post($this->exportTypeParam, $this->exportType);
            $this->filename = match (Yii::$app->request->post($this->exportTypeParam, $this->exportType)) {
                self::FORMAT_CSV => "$this->filename.csv",
                self::FORMAT_EXCEL_X => "$this->filename.xlsx",
                default => $this->filename
            };
            $totalCount = $this->dataProvider->getTotalCount();
            if (!empty($this->limit)) {
                $totalCount = min($totalCount, $this->limit);
            }
            ProgressBar::updateCounter(
                name: $this->progressId,
                max: $totalCount,
                customData: ['filename' => $this->filename]
            );
            Yii::$app->queue->push(
                new ExportJob([
                    'id' => $this->progressId,
                    'dataProvider' => $this->dataProvider,
                    'staticConfig' => $this->staticConfig,
                    'filename' => $this->filename,
                    'limit' => $this->limit,
                    'exportType' => $exportType,
                    'counter' => ProgressBar::findCounter($this->progressId)
                ])
            );
        }

        if ($data = ProgressBar::findCounter($this->progressId)) {
            echo $this->renderProgressBar($data);
            $this->dropdownOptions = ['id' => "$this->id-export-dropdown", 'disabled' => true];
        }
        return $this->renderExportMenu();
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    final public function initColumnSelector(): void
    {
        parent::initColumnSelector();
        if (array_key_exists('data-toggle', $this->columnSelectorOptions) && $this->isBs(5)) {
            unset($this->columnSelectorOptions['data-toggle']);
            $this->columnSelectorOptions['data-bs-toggle'] = 'dropdown';
        }
    }

    /**
     * @throws Throwable
     */
    public function renderProgressBar($data): string
    {
        $startTime = date('d.m.Y H:i', $data['startTime']);
        echo Html::tag('span', "Экспорт начат: $startTime", ['id' => "$this->id-export-process-label"]);
        $downloadAction = Url::to(['/export/download', 'filename' => $data['filename']]);
        return ProgressBar::widget([
            'id' => $this->progressId,
            'barOptions' => ['class' => 'progress-bar-warning'],
            'options' => ['class' => 'progress-striped'],
            'endJsCallback' => <<<JS
$('#$this->id-export-process-label').hide();
$('#$this->id-export-dropdown').prop('disabled', false);
$.ajax({
  url: '$downloadAction',
  dataType: 'binary',
  xhrFields: {
      'responseType': 'blob'
  },
  success: function (data, status, xhr) {
      const contentDisposition = xhr.getResponseHeader('content-disposition');
      let fileName = 'file';
      const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
      if (fileNameMatch.length === 2) {
        fileName = fileNameMatch[1];
      }
      const link = document.createElement('a');
      link.href = window.URL.createObjectURL(new Blob([data]));
      link.download = fileName;
      link.click();
  }
})
JS
        ]);
    }
}
