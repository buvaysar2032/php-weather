<?php

use admin\widgets\faq\FaqWidget;

/**
 * @var $this         yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = Yii::t('app', 'FAQ');
$this->params['breadcrumbs'][] = $this->title;
?>

<?= FaqWidget::widget([
    'config' => [
//        'Вопрос 1' => 'Ответ 1',
//        'Вопрос 2' => 'Ответ 2',
//        'Вопрос 3' => 'Ответ 3',
    ]
]) ?>