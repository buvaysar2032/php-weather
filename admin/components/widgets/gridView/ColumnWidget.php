<?php

namespace admin\components\widgets\gridView;

use admin\components\widgets\ArrayWidget;
use admin\modules\rbac\components\ActionFilterTrait;
use Closure;
use Exception;
use yii\base\InvalidConfigException;
use yii\web\JsExpression;

/**
 * Class GridViewWidget
 *
 * @package admin\components\widgets\gridView
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
abstract class ColumnWidget extends ArrayWidget
{
    use ActionFilterTrait;

    /**
     * Название атрибута модели
     */
    public string $attr;

    /**
     * Минимально допустима ширина столбца
     */
    public string|int $width;

    /**
     * Возможность редактирования колонки
     */
    public bool $editable = true;

    /**
     * Whether to prevent rendering the editable behavior and display a readonly data.
     *
     * You can also set this up as an anonymous function of the form `function($model, $key, $index, $widget)`
     * that will return a boolean value, where:
     * - `$model`: _\yii\base\Model_, is the data model.
     * - `$key`: _string|object_, is the primary key value associated with the data model.
     * - `$index`: _integer_, is the zero-based index of the data model among the model array returned by [[dataProvider]].
     * - `$column`: _EditableColumn_, is the column object instance.
     */
    public Closure|bool $readonly = false;

    /**
     * JS функция вызываемая после успешного редактирования в событии editableSuccess
     */
    public JsExpression $jsCallback;

    /**
     * ID контейнера pjax для обновления виджета после обновления данных через Pjax
     */
    public string $pjaxContainerId = 'grid-view';

    /**
     * Имя действия контроллера для редактирования
     *
     * Например, `change` сгенерирует ссылку `<контроллер>/change`
     */
    public string $action = 'change';

    /**
     * Иконка в начале заголовка окна редактирования
     */
    public string $preHeader = '<i class="fa fa-edit"></i>';

    /**
     * Длина строки, чтобы уместилась в ширину столбца
     */
    protected int $stringLength;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function init(): void
    {
        if ($this->editable && !self::isAvailable($this->action)) {
            $this->editable = false;
        }
        if (empty($this->attr)) {
            throw new InvalidConfigException('`attr` is not defined');
        }
        if (isset($this->width) && !is_string($this->width)) {
            $this->width .= 'px';
            $this->stringLength = (int)(preg_replace('/\D/', '', $this->width) / 12);
        }
        parent::init();
    }

    /**
     * Добавить jsCallback в конфигурацию
     *
     * @param array $editableOptions Конфигурация Editable колонки
     */
    final protected function _addJsCallbackToConfig(array &$editableOptions): void
    {
        if (isset($this->jsCallback)) {
            $editableOptions['pluginEvents'] = ['editableSuccess' => $this->jsCallback];
        }
    }
}
