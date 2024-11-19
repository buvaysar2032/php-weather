<?php

use yii\helpers\Inflector;

/**
 * @var $this yii\base\View
 * @var $id   string
 */

echo "<?php\n";
?>

use yii\bootstrap5\Html;

/**
 * @var $this yii\web\View
 */

$this->title = Yii::t('app', '<?= Inflector::id2camel($id) ?>');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php echo "<?=" ?> $this->render('<?= $id ?>.pug', ['title' => Html::encode($this->title)]) ?>