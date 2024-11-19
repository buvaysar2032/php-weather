-------------------
ИСПОЛЬЗОВАНИЕ МОДУЛЯ
-------------------
Применить миграции yii migrate --migrationPath=common\modules\log\migrations
Подключить поведение к отслеживаемой модели ActiveRecord:

```php
...
use common\modules\log\behaviors\Logger;
...

class AppActiveRecord extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            'logger' => [
                'class' => Logger::class,
            ],
        ];
    }
    ...
}
```
-------------------
