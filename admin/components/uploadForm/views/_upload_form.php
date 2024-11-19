<?php

use admin\components\uploadForm\models\UploadForm;
use yii\bootstrap5\{ActiveForm, Html, Modal};

/**
 * Представление модальной формы для загрузки файлов-таблиц
 *
 * Пример:
 * ```php
 * echo $this->render(
 *     '../../components/uploadForm/views/_upload_form',
 *     [
 *         'title' => 'Загрузить данные районов',
 *         'action' => 'upload',
 *         'example_links' => [
 *             [
 *                 'link' => Url::to(['area/download-example', 'type' => 'xlsx']),
 *                 'description' => 'Скачать пример xlsx-файла'
 *             ],
 *             [
 *                 'link' => Url::to(['area/download-example', 'type' => 'csv']),
 *                 'description' => 'Скачать пример csv-файла'
 *             ]
 *         ],
 *         'btn_message' => Yii::t('app', 'Upload Areas From Json')
 *     ]
 * )
 * ```
 *
 * @var string $title         Заголовок модального окна
 * @var string $action        Название действия
 * @var string $btn_message   Надпись на кнопке скачивания
 * @var array  $example_links Ссылки на скачивание
 */

if (!$action) {
    $action = 'upload';
}
$model = new UploadForm();

Modal::begin([
    'id' => 'file-upload',
    'title' => 'Загрузить файлы',
    'toggleButton' => [
        'label' => $btn_message,
        'class' => 'btn btn-success'
    ]
]) ?>

    <div class="file-upload row">
        <div class='col'>
            <?php $form = ActiveForm::begin(['action' => $action, 'options' => ['enctype' => 'multipart/form-data']]) ?>

            <?= $form->field($model, 'file')->label(false)->fileInput() ?>

            <div class='form-group'>
                <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end() ?>
        </div>
        <?php if (!empty($example_links)): ?>
            <div class='col'>
                <div class='card card-body bg-body'>
                    <?php foreach ($example_links as $example_link): ?>
                        <div class='row'>
                            <?= Html::a($example_link['description'], $example_link['link']) ?>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        <?php endif ?>
    </div>

<?php Modal::end() ?>