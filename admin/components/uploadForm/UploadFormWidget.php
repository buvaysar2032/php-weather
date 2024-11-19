<?php

namespace admin\components\uploadForm;

use admin\modules\rbac\components\ActionFilterTrait;
use Exception;
use yii\bootstrap5\Widget;

/**
 * Виджет модального окна для загрузки файлов-таблиц
 *
 * Пример:
 * ```php
 * echo UploadFormWidget::widget([
 *     'action' => Url::to(['quiz/upload']),
 *     'btnMessage' => 'Загрузить из файла',
 *     'title' => 'Загрузить викторину',
 *     'exampleLinks' => [
 *         [
 *             'link' => Url::to(['area/download-example', 'type' => 'xlsx']),
 *             'description' => 'Скачать пример xlsx-файла'
 *         ]
 *     ]
 * ])
 * ```
 *
 * @package admin\components\uploadForm
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class UploadFormWidget extends Widget
{
    use ActionFilterTrait;

    /**
     * Заголовок модального окна
     */
    public string $title = '';

    /**
     * Url для отправки формы
     */
    public string $action = 'upload';

    /**
     * Список ссылок на файлы с примера структуры
     *
     * Пример
     * ```php
     *  [
     *      [
     *          'link' => Url::to(['area/download-example', 'type' => 'json']),
     *          'description' => 'Скачать пример json-файла'
     *      ]
     *  ]
     * ```
     */
    public array $exampleLinks = [];

    /**
     * Надпись на кнопке
     */
    public string $btnMessage = '';

    /**
     * Название представления модального окна
     */
    public string $view = '_upload_form';

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    final public function run(): string
    {
        if (!self::isAvailable($this->action)) {
            return '';
        }
        return $this->render(
            "@admin/components/uploadForm/views/$this->view",
            [
                'title' => $this->title,
                'action' => $this->action,
                'example_links' => $this->exampleLinks,
                'btn_message' => $this->btnMessage
            ]
        );
    }
}
