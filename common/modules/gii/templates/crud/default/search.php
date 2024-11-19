<?php

use common\components\helpers\SearchQueryHelper;
use yii\helpers\StringHelper;

/**
 * This is the template for generating CRUD search class of the specified model.
 *
 * @var $this yii\web\View
 * @var $generator \common\modules\gii\generators\crud\Generator
 */

$modelClass = StringHelper::basename($generator->modelClass);
$modelNs = StringHelper::dirname(ltrim($generator->modelClass, '\\'));
$searchModelClass = StringHelper::basename($generator->searchModelClass);
$searchModelNs = StringHelper::dirname(ltrim($generator->searchModelClass, '\\'));
if ($modelClass === $searchModelClass) {
    $modelAlias = $modelClass . 'Model';
}
$rules = $generator->generateSearchRules();
$labels = $generator->generateSearchLabels();
$searchAttributes = $generator->getSearchAttributes();
$searchConditions = $generator->generateSearchConditions();

$includes = [
    yii\base\InvalidConfigException::class,
    yii\base\Model::class,
    yii\data\ActiveDataProvider::class
];
if ($modelNs !== $searchModelNs) {
    $includes[] = ltrim($generator->modelClass, '\\') . (isset($modelAlias) ? " as $modelAlias" : '');
}

$hasCreatedAt = $hasUpdatedAt = false;
$dateAttrs = [];
foreach ($searchAttributes as $property) {
    if (!$hasCreatedAt && $property === 'created_at') {
        $dateAttrs[] = "'" . $property . "'";
        $hasCreatedAt = true;
    }
    if (!$hasUpdatedAt && $property === 'updated_at') {
        $dateAttrs[] = "'" . $property . "'";
        $hasUpdatedAt = true;
    }
}
if ($hasCreatedAt || $hasUpdatedAt) {
    $includes[] = SearchQueryHelper::class;
}

sort($includes, SORT_NATURAL | SORT_FLAG_CASE);

echo "<?php\n";
?>

namespace <?= $searchModelNs ?>;

use <?= implode(";\nuse ", $includes) ?>;

/**
 * <?= $searchModelClass ?> represents the model behind the search form of `<?= $generator->modelClass ?>`.
 */
final class <?= $searchModelClass ?> extends <?= $modelAlias ?? $modelClass ?>

{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            <?= implode(",\n            ", $rules) ?>

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with a search query applied
     *
     * @throws InvalidConfigException
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = <?= $modelAlias ?? $modelClass ?>::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        <?= implode("\n        ", $searchConditions) ?>
<?php if ($hasCreatedAt || $hasUpdatedAt): ?>

        // date filtering helper
        SearchQueryHelper::filterDataRange([<?= implode(', ', $dateAttrs) ?>], $this, $query);
<?php endif; ?>

        return $dataProvider;
    }
}
