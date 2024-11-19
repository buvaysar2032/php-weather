<?php

namespace common\models;

use admin\widgets\ckfinder\CKFinderInputFile;
use admin\widgets\input\{DatePicker, DateTimePicker, TimePicker, YesNoSwitch};
use common\components\helpers\UserUrl;
use common\enums\{Boolean, SettingType};
use Exception;
use kartik\color\ColorInput;
use kartik\editable\Editable;
use Yii;
use yii\bootstrap5\Html;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "{{%setting}}".
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property int                         $id          [int] ID
 * @property string                      $parameter   [varchar(100)] Название параметра
 * @property string                      $value       [varchar(255)] Значение
 * @property string                      $description [varchar(255)] Описание параметра
 * @property string                      $type        [varchar(255)] Тип значения настройки
 *
 * @property-read null|bool|string|array $columnValue
 * @property-read null|string|array      $inputWidget
 */
class Setting extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%setting}}';
    }

    /**
     * Получить значение параметра с помощью кеша.
     *
     * @param bool $calm        Не выбрасывать исключений
     * @param bool $updateCache Обновить кеш
     *
     * @throws NotFoundHttpException
     */
    public static function getParameterValue(string $parameter, bool $calm = false, bool $updateCache = false): ?string
    {
        static $cache;
        $value = $cache[$parameter] ?? false;
        if (!$value || $updateCache) {
            /** @var self|null $setting */
            if (!$setting = self::find()->select(['value'])->where(['parameter' => $parameter])->asArray()->one()) {
                if ($calm) {
                    return null;
                }
                throw new NotFoundHttpException("Настройка '$parameter' не найдена");
            }
            $cache[$parameter] = $value = $setting['value'];
        }
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    final public function rules(): array
    {
        return [
            [['parameter', 'description'], 'required'],
            [['parameter', 'value', 'description'], 'string', 'max' => 255],
            ['type', 'string', 'max' => 10],
            SettingType::validator('type'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function beforeValidate(): bool
    {
        if ($this->value === null) {
            $this->value = '';
        }
        return parent::beforeValidate();
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'parameter' => Yii::t('app', 'Parameter'),
            'value' => Yii::t('app', 'Value'),
            'description' => Yii::t('app', 'Description'),
        ];
    }

    /**
     * @throws Exception
     */
    final public function getColumnValue(): bool|array|string|null
    {
        $filename = Yii::getAlias('@root/htdocs/') . ltrim($this->value, '/');
        return match (SettingType::from($this->type)) {
            SettingType::Image => str_starts_with($filename, 'http')
            || (file_exists($filename) && !is_dir($filename))
                ? Html::img(
                    UserUrl::toAbsolute($this->value),
                    ['style' => 'max-width:150px;max-height:50px;', 'alt' => ''],
                )
                : null,
            SettingType::File => str_starts_with($filename, 'http')
            || (file_exists($filename) && !is_dir($filename))
                ? Html::a(basename($this->value), UserUrl::toAbsolute($this->value), ['target' => '_blank'])
                : null,
            SettingType::Switch => Boolean::from($this->value)->description(),
            SettingType::Color => Yii::$app->formatter->asColor($this->value),
            SettingType::Date => Yii::$app->formatter->asDate($this->value),
            SettingType::Datetime => Yii::$app->formatter->asDatetime($this->value),
            SettingType::Time => Yii::$app->formatter->asDuration($this->value),
            SettingType::Password => preg_replace('/./u', '●', $this->value),
            default => $this->value,
        };
    }

    /**
     * Получить имя класса виджета или его полный конфиг
     *
     * Выбор виджета для поля ввода основан на типе параметра
     */
    final public function getInputWidget(): array|string|null
    {
        return match (SettingType::from($this->type)) {
            SettingType::File => [
                'class' => CKFinderInputFile::class,
                'resourceType' => 'Files',
                'isImage' => false,
            ],
            SettingType::Image => CKFinderInputFile::class,
            SettingType::Color => [
                'class' => ColorInput::class,
                'useNative' => true,
            ],
            SettingType::Switch => YesNoSwitch::class,
            SettingType::Date => DatePicker::class,
            SettingType::Datetime => DateTimePicker::class,
            SettingType::Time => TimePicker::class,
            SettingType::Password => [
                'inputType' => Editable::INPUT_PASSWORD,
                'displayValue' => preg_replace('/./u', '●', $this->value),
            ],
            SettingType::Text => Editable::INPUT_TEXTAREA,
            SettingType::Number => [
                'inputType' => Editable::INPUT_HTML5,
                'options' => ['type' => 'number'],
            ],
            default => Editable::INPUT_TEXT,
        };
    }
}
