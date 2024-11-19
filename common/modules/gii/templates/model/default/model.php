<?php
/**
 * This is the template for generating the model class of a specified table.
 *
 * @var $this           yii\web\View
 * @var $generator      yii\gii\generators\model\Generator
 * @var $tableName      string full table name
 * @var $className      string class name
 * @var $queryClassName string query class name
 * @var $tableSchema    yii\db\TableSchema
 * @var $properties     array list of properties (property => [type, name. comment])
 * @var $labels         string[] list of attribute labels (name => label)
 * @var $rules          string[] list of validation rules
 * @var $relations      array list of relations (name => relation declaration)
 */

$includes = [
    ltrim($generator->baseClass, '\\'),
    Yii::class,
    yii\helpers\ArrayHelper::class
];
if ($relations) {
    $includes[] = yii\db\ActiveQuery::class;
}
if ($generator->db !== 'db') {
    $dbClass = get_class(Yii::$app->get($generator->db));
    $includes[] = $dbClass;
}
if ($queryClassName && $generator->ns !== $generator->queryNs) {
    $includes[] = $generator->queryNs . '\\' . $queryClassName;
}

$hasCreatedAt = false;
$hasUpdatedAt = false;
foreach ($properties as $property => $data) {
    if ($property === 'created_at') {
        $hasCreatedAt = true;
    }
    if ($property === 'updated_at') {
        $hasUpdatedAt = true;
    }
}
if ($hasCreatedAt || $hasUpdatedAt) {
    $includes[] = yii\behaviors\TimestampBehavior::class;
}

sort($includes, SORT_NATURAL | SORT_FLAG_CASE);

$longestType = $longestProperty = 0;
foreach ($properties as $property => &$data) {
    $data['propLen'] = strlen($property) + 1;
    if ($longestProperty < $data['propLen']) {
        $longestProperty = $data['propLen'];
    }
    $data['typeLen'] = strlen($data['type']);
    if ($longestType < $data['typeLen']) {
        $longestType = $data['typeLen'];
    }
}
unset($data);
if (!empty($relations)) {
    foreach ($relations as &$relation) {
        $relation['typeLen'] = strlen($relation[1] . ($relation[2] ? '[]' : ''));
        if ($longestType < $relation['typeLen']) {
            $longestType = $relation['typeLen'];
        }
    }
    unset($relation);
}

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

use <?= implode(";\nuse ", $includes) ?>;

/**
 * This is the model class for table "<?= $generator->generateTableName($tableName) ?>".
 *
<?php foreach ($properties as $property => $data): ?>
 * @property <?= $data['type'] . str_repeat(' ', $longestType - $data['typeLen'] + (!empty($relations) ? 6 : 1)) . '$' . $property .
    ($data['comment'] ? str_repeat(' ', $longestProperty - $data['propLen'] + 1) . strtr($data['comment'], ["\n" => ' ']) : '') . "\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property-read <?= $relation[1] . ($relation[2] ? '[]' : '') .
        str_repeat(' ', $longestType - $relation['typeLen'] + 1) .
        '$' . lcfirst($name) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?= $className ?> extends <?= basename(str_replace('\\', DIRECTORY_SEPARATOR, $generator->baseClass)) . "\n" ?>
{
<?php if ($hasCreatedAt || $hasUpdatedAt): ?>
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'timestamp' => [
                'class' => TimestampBehavior::class<?= !$generator->useClassConstant ? 'Name()' : null  ?>,
<?= (!$hasCreatedAt) ? str_repeat(' ', 16) . "'createdAtAttribute' => false,\n" : null ?>
<?= (!$hasUpdatedAt) ? str_repeat(' ', 16) ."'updatedAtAttribute' => false,\n" : null ?>
            ]
        ]);
    }

<?php endif; ?>
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '<?= $generator->generateTableName($tableName) ?>';
    }
<?php if (isset($dbClass)): ?>

    /**
     * @return <?= basename($dbClass) ?> the database connection used by this AR class.
     */
    public static function getDb(): <?= basename($dbClass) ?>
    {
        return Yii::$app->get('<?= $generator->db ?>');
    }
<?php endif; ?>

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [<?= empty($rules) ? '' : ("\n            " . implode(",\n            ", $rules) . "\n        ") ?>];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>
        ];
    }
<?php foreach ($relations as $name => $relation): ?>

    final public function get<?= $name ?>(): ActiveQuery
    {
        <?= $relation[0] . "\n" ?>
    }
<?php endforeach; ?>
<?php if ($queryClassName): ?>

    /**
     * {@inheritdoc}
     */
    public static function find(): <?= $queryClassName . "\n" ?>
    {
        return new <?= $queryClassName ?>(self::class<?= !$generator->useClassConstant ? 'Name()' : null  ?>);
    }
<?php endif; ?>
}
