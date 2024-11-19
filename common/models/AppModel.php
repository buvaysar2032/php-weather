<?php

namespace common\models;

use yii\base\Model;

/**
 * Class AppModel
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
abstract class AppModel extends Model
{
    use traits\SetTypedAttributesTrait;
    use traits\LoadEventsTrait;
    use traits\ScreenerTrait;

    /**
     * Event an event that is triggered before data is loaded into a model.
     */
    public const EVENT_BEFORE_LOAD = 'beforeLoad';

    /**
     * Event an event that is triggered after data is loaded into a model.
     */
    public const EVENT_AFTER_LOAD = 'afterLoad';

    /**
     * {@inheritdoc}
     * @return array<array<string|int|callable>>
     */
    public function rules(): array
    {
        return parent::rules();
    }

    /**
     * {@inheritdoc}
     * @return string[]
     */
    public function attributeLabels(): array
    {
        return parent::attributeLabels();
    }
}