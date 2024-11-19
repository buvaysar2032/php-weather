### Настройка SortableAction
В контроллере добавить SortableAction в метод action():
```php
use admin\widgets\sortable_grid_view\SortableAction;

public function actions()
{
    return [
        'sort' => [
            'class' => SortableAction::class,
            'modelClass' => YourActiveRecordClass::class
        ],
        // your other actions
    ];
}
```

### Настройка SortableGridView
В представлении необходимо использовать SortableGridView или RuleBasedGridView из модуля rbacAdmin как обычный GridView.
Для активации сортировки необходимо обязательно указать sortUrl. Без него это будет обычный GridView (Сделано для совместимости с RuleBasedGridView).
```php
use admin\widgets\sortable_grid_view\SortableGridView;

echo SortableGridView::widget([
    'dataProvider' => $dataProvider,
    
    // you can choose how the URL look like,
    // but it must match the one you put in the array of controller's action()
    'sortUrl' => Url::to(['sort']),
    
    'columns' => [
        // Data Columns
    ],
])
```

Можно так же выключить пагинацию, чтобы позволить сортировать элементы между страницами.
```php
$dataProvider->pagination = false;
```
## Конфигурация
### SortableAction
Пример:
```php
use admin\widgets\sortable_grid_view\SortableAction;

public function actions()
{
    return [
        'sortItem' => [
            'class' => SortableAction::class,
            'modelClass' => Articles::class,
            'orderColumn' => 'position'
        ],
        // your other actions
    ];
}
```

* **modelClass** (required) Название класса ActiveRecord.
* **orderColumn** Название столбца в котором хранятся данные по сортировке. Тип данных должен быть integer. По умолчанию - `position`

---

### SortableGridView
Пример:
```php
use admin\widgets\sortable_grid_view\SortableGridView;

echo SortableGridView::widget([
    'dataProvider' => $dataProvider,
    
    // SortableGridView Configurations
    'sortUrl' => Url::to(['sort']),
    'sortingPromptText' => 'Loading...',
    'failText' => 'Fail to sort',
    
    'columns' => [
        // Data Columns
    ],
]);
```

* **sortUrl** (required) URL ссылка на SortableAction.
* **sortingPromptText** (optional) Текст, показываемый когда сервер выполняет сортировку. По умолчанию "Loading...".
* **failText** (optional) Текст показываемый когда сортировка не удалась "Fail to sort".
