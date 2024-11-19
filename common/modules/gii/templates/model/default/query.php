<?php

use yii\helpers\StringHelper;

/**
 * This is the template for generating the ActiveQuery class.
 *
 * @var $this           yii\web\View
 * @var $generator      common\modules\gii\generators\model\Generator
 * @var $tableName      string full table name
 * @var $className      string class name
 * @var $tableSchema    yii\db\TableSchema
 * @var $labels         string[] list of attribute labels (name => label)
 * @var $rules          string[] list of validation rules
 * @var $relations      array list of relations (name => relation declaration)
 * @var $className      string class name
 * @var $modelClassName string related model class name
 */

$modelFullClassName = $modelClassName;

$queryBaseClass = StringHelper::basename($generator->queryBaseClass);
$queryBaseNs = rtrim(str_replace($queryBaseClass, '', $generator->queryBaseClass), '\\');

$includes = [];
if ($generator->ns !== $generator->queryNs) {
    $includes[] = $generator->ns . '\\' . $modelFullClassName;
}
if ($queryBaseNs !== $generator->queryNs) {
    $includes[] = ltrim($generator->queryBaseClass, '\\');
}
sort($includes, SORT_NATURAL | SORT_FLAG_CASE);

echo "<?php\n";
?>

namespace <?= $generator->queryNs ?>;

<?php if (!empty($includes)): ?>
use <?= implode(";\nuse ", $includes) ?>;

<?php endif ?>
/**
 * This is the ActiveQuery class for [[<?= $modelFullClassName ?>]].
 *
 * @see <?= $modelFullClassName . "\n" ?>
 */
final class <?= $className ?> extends <?= $queryBaseClass . "\n" ?>
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return <?= $modelFullClassName ?>[]|array
     */
    public function all($db = null): array
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     */
    public function one($db = null): <?= $modelClassName ?>|array|null
    {
        return parent::one($db);
    }
}
