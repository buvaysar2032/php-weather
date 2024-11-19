<?php

/**
 * Creates a call for the method `yii\db\Migration::createTable()`.
 *
 * @var $table       string the name table
 * @var $fields      array the fields
 * @var $foreignKeys array the foreign keys
 */

$hasCreatedAt = false;
$hasUpdatedAt = false;
foreach ($fields as $field) {
    if ($field['property'] === 'created_at') {
        $hasCreatedAt = true;
    }
    if ($field['property'] === 'updated_at') {
        $hasUpdatedAt = true;
    }
}
if (!$hasCreatedAt) {
    $fields[] = [
        'property' => 'created_at',
        'decorators' => 'integer()->notNull()->comment(\'Дата создания\')'
    ];
}
if (!$hasUpdatedAt) {
    $fields[] = [
        'property' => 'updated_at',
        'decorators' => 'integer()->notNull()->comment(\'Дата изменения\')'
    ];
}
?>
<?= str_repeat(' ', 8) ?>$this->createTable('<?= $table ?>', [
<?php foreach ($fields as $field) {
    if (empty($field['decorators'])) {
        echo str_repeat(' ', 12) . "'" . $field['property'] . "'," . PHP_EOL;
    } else {
        echo str_repeat(' ', 12) . "'{$field['property']}' => \$this->{$field['decorators']}," . PHP_EOL;
    }
} ?>
<?= str_repeat(' ', 8) ?>]);
<?= $this->render(
    '@yii/views/_addForeignKeys',
    ['table' => $table, 'foreignKeys' => $foreignKeys]
) ?>
