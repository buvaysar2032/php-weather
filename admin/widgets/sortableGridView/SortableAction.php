<?php

namespace admin\widgets\sortableGridView;

use common\models\AppActiveRecord;
use Yii;
use yii\base\{Action, InvalidConfigException};
use yii\web\HttpException;

/**
 * Действие контроллера для сортировки записей
 *
 * @package admin\widgets\sortableGridView
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class SortableAction extends Action
{
    /**
     * (required) The ActiveRecord Class name
     */
    public AppActiveRecord|string $modelClass;

    /**
     * The attribute name where your store the sort order.
     */
    public string $orderColumn = 'position';

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        if (!isset($this->modelClass)) {
            throw new InvalidConfigException('You must specify the activeRecordClassName');
        }
    }

    /**
     * @throws HttpException
     */
    public function run(): void
    {
        if (!Yii::$app->request->isAjax) {
            throw new HttpException(404);
        }
        $post = Yii::$app->request->post();
        if (isset($post['items']) && is_array($post['items'])) {
            foreach ($post['items'] as $i => $item) {
                if ($page = $this->modelClass::findOne($item)) {
                    $page->updateAttributes([$this->orderColumn => $i]);
                }
            }
        }
    }
}
