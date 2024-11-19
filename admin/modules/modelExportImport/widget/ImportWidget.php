<?php

namespace admin\modules\modelExportImport\widget;

use admin\modules\modelExportImport\{ModelExportImport, models\ImportModel};
use admin\modules\rbac\components\RbacHtml;
use common\widgets\AppActiveForm;
use Yii;
use yii\bootstrap5\{Modal, Widget};
use yii\helpers\Url;

/**
 * Виджет с формой в модальном окне для импорта одной модели
 */
class ImportWidget extends Widget
{
    public string $action;

    private ImportModel $model;

    /**
     * {@inheritdoc}
     */
    final public function beforeRun(): bool
    {
        if (!isset($this->action)) {
            $this->action = Url::to(['import']);
        }
        $this->model = new ImportModel();
        return parent::beforeRun();
    }

    /**
     * {@inheritdoc}
     */
    final public function run(): void
    {
        Modal::begin([
            'title' => Yii::t(ModelExportImport::MODULE_MESSAGES, 'Import'),
            'toggleButton' => [
                'label' => Yii::t(ModelExportImport::MODULE_MESSAGES, 'Import'),
                'class' => 'btn btn-success'
            ]
        ]);
        $form = AppActiveForm::begin(['action' => $this->action]);
        echo $form->field($this->model, 'file')->fileInput();
        echo RbacHtml::tag(
            'div',
            RbacHtml::submitButton('Импортировать', [
                'class' => 'btn btn-success',
                'data-confirm' => 'Внимание, импорт происходит по primaryKey! Изменения сделанные на сервере, могут быть утеряны. Вы уверены что хотите импортировать этот файл?'
            ]),
            ['class' => 'form-group']
        );
        AppActiveForm::end();
        Modal::end();
    }
}
