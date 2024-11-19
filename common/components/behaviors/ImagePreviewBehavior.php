<?php

namespace common\components\behaviors;

use common\components\helpers\{UserFileHelper, UserUrl};
use common\models\AppActiveRecord;
use Imagine\Image\Box;
use skeeks\imagine\Image as Imagine;
use Yii;
use yii\base\{Behavior, Exception};
use yii\db\BaseActiveRecord;

/**
 * Поведение ImagePreviewBehavior
 *
 * Позволяет динамически создавать поля от атрибутов с изображениями, в которых будут уменьшенные копии этих изображений
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 * @property AppActiveRecord $owner
 */
class ImagePreviewBehavior extends Behavior
{
    /**
     * Массив, где значение - это поле исходной картинки, а ключ - название поля для получения превью.
     */
    public array $fields = [];

    /**
     * Ширина превью картинки.
     */
    public int $width = 390;

    /**
     * Качество превью картинки.
     */
    public int $quality = 80;

    private ?string $_rootPath;

    /**
     * {@inheritdoc}
     */
    final public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_AFTER_DELETE => 'deletePreviews'
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function canGetProperty($name, $checkVars = true): bool
    {
        return parent::canGetProperty($name, $checkVars) || array_key_exists($name, $this->fields);
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->fields)) {
            $miniFile = $this->getFilePreview($this->fields[$name]);
            if (!empty($miniFile) && $this->owner->canSetProperty($name)) {
                $this->owner->$name = $miniFile;
                $this->owner->save();
            }
        }
        return parent::__get($name);
    }

    /**
     * {@inheritdoc}
     */
    final public function init(): void
    {
        $this->_rootPath = Yii::getAlias('@root/htdocs');
        parent::init();
    }

    /**
     * Получить превью файл.
     *
     * @throws Exception
     */
    final public function getFilePreview(string $attr): ?string
    {
        if (!file_exists($this->_rootPath . $this->owner->$attr)) {
            return null;
        }
        $miniFile = $this->generateMiniFilename($this->owner->$attr);
        if (!file_exists($this->_rootPath . $miniFile)) {
            $this->generateFilePreview($attr);
        }
        return $miniFile;
    }

    /**
     * Удаление созданных превью.
     */
    final public function deletePreviews(): void
    {
        foreach ($this->fields as $field) {
            $filename = $this->_rootPath . $this->generateMiniFilename($this->owner->$field);
            if (file_exists($filename) && is_writable($filename)) {
                unlink($filename);
            }
        }
    }

    /**
     * Генерация мини превью.
     *
     * @throws Exception
     */
    private function generateFilePreview(string $attr): void
    {
        $imagine = Imagine::getImagine();
        $filename = $this->_rootPath . $this->owner->$attr;
        $image = $imagine->open($filename);
        // Расчет размера
        $sizes = getimagesize($filename);
        if ($this->width < $sizes[0]) {
            $height = round($sizes[1] * $this->width / $sizes[0]); // Пропорциональный расчет новой высоты
            $image->resize(new Box($this->width, $height)); // Уменьшение изображения
        }
        $path = $this->_rootPath . $this->generateMiniFilename($this->owner->$attr);
        UserFileHelper::createDirectory(str_replace(basename($path), '', $path));
        $image->save($path, ['quality' => $this->quality]);
    }

    /**
     * Генерация имени для уменьшенной копии.
     */
    private function generateMiniFilename(string $filename): string
    {
        return str_replace(UserUrl::UPLOADS, UserUrl::UPLOADS . '/mini', $filename);
    }
}
