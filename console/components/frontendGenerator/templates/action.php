<?php

use yii\helpers\Inflector;

/**
 * @var $this       yii\base\View
 * @var $actionName string
 */

?>
    public function action<?= $actionName ?>(): string
    {
        return $this->render('<?= Inflector::camel2id($actionName) ?>');
    }

}