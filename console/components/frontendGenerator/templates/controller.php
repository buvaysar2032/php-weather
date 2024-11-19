<?php

/**
 * @var $this      yii\base\View
 * @var $namespace string
 * @var $includes  array
 * @var $generator console\components\frontendGenerator\Generator
 */

use yii\helpers\StringHelper;

echo "<?php\n";
?>

namespace <?= $namespace ?>;

use <?= implode(";\nuse ", $includes) ?>;

/**
 *
 * @package <?= "$namespace\n" ?>
 */
final class <?= $generator->controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
}
