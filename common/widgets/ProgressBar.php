<?php

namespace common\widgets;

use Yii;
use yii\bootstrap5\Progress;
use yii\helpers\{Inflector, Url};
use yii\caching\CacheInterface;
use yii\redis\Cache;

/**
 * Виджет прогресс бара
 *
 * Для работы необходимо добавить действие контроллеру:
 * ```php
 * use common\widgets\ProgressAction;
 *
 * public function actions(): array
 * {
 *     return [
 *         'progress' => ProgressAction::class
 *     ];
 * }
 * ```
 *
 * Вывести прогресс бар на странице:
 * ```php
 * ProgressBar::widget([
 *     'id' => $name,
 *     'updateAction' => 'progress' // по умолчанию ведет в '/site/progress'
 * ]);
 * ```
 *
 * В продолжительной работе вызывать обновление счетчика:
 * ```php
 * // При создании и/или обновлении счетчика
 * ProgressBar::updateCounter($name, $current, $max);
 * // При прекращении работы
 * ProgressBar::deleteCounter($name);
 * ```
 *
 * @package widgets
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @see     ProgressAction
 */
class ProgressBar extends Progress
{
    private const CATEGORY = 'progress';

    /**
     * Maximum count value
     */
    public ?int $max;

    /**
     * Current count value
     */
    public ?int $current;

    /**
     * Url to update action
     */
    public ?string $updateAction;

    /**
     * Progress bar update period in milliseconds
     */
    public int $refreshPeriod = 3000;

    /**
     * JS код, вызываемый по окончанию работы прогресс бара
     */
    public string $endJsCallback;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        if (!isset($this->updateAction)) {
            $this->updateAction = '/site/progress';
        }
    }

    public static function getCache(): CacheInterface
    {
        if (Yii::$app->cache instanceof Cache) {
            return Yii::$app->cache;
        } else {
            return Yii::$app->dbCache;
        }
    }

    /**
     * Обновление счетчика
     *
     * При большом числе рекомендуется разбивать процесс на куски, например, обновлять прогресс каждые 10, 100 итераций и т.д.
     */
    public static function updateCounter(string $name, int $current = 0, int $max = null, array $customData = []): void
    {
        if (!$max) {
            $data = (array)self::findCounter($name);
            $data['current'] = $current;
            if (!empty($data['max'])) {
                if ($data['max'] < $data['current']) {
                    $data['updateTime'] = time();
                    $data = array_merge($data, $customData);
                    self::getCache()->set(self::CATEGORY . '_' . $name, $data);
                } else {
                    self::deleteCounter($name);
                }
            }
        } else {
            self::getCache()->set(self::CATEGORY . '_' . $name, [
                'max' => $max,
                'current' => $current,
                'startTime' => time(),
                'updateTime' => time(),
                ...$customData,
            ]);
        }
    }

    /**
     * Получить текущие данные счетчика
     */
    public static function findCounter(string $name): bool|array|string
    {
        return self::getCache()->get(self::CATEGORY . '_' . $name);
    }

    /**
     * Остановить счетчик (удалить)
     */
    public static function deleteCounter(string $name): void
    {
        self::getCache()->delete(self::CATEGORY . '_' . $name);
    }

    /**
     * {@inheritdoc}
     */
    final public function beforeRun(): bool
    {
        $data = self::findCounter($this->id);
        $this->max = $data['max'] ?? null;
        $this->current = $data['current'] ?? null;
        if (is_null($this->max) || is_null($this->current)) {
            return false;
        }
        $this->label = "$this->current из $this->max";
        $this->percent = ($this->current / $this->max) * 100;

        if (isset($this->updateAction)) {
            $this->updateAction = Url::to([$this->updateAction, 'name' => $this->id]);
            $var = Inflector::camelize($this->id) . 'RefreshIntervalId';
            $this->view->registerJs(
                <<<JS
                    let $var = setInterval(function () {
                      $.ajax({ url: "$this->updateAction" })
                        .done(function (data) {
                          if (data) {
                            const process = $('#$this->id div[role="progressbar"]'),
                                width = (data.current / data.max) * 100;
                            process.attr('aria-valuenow', width);
                            process.width(width + '%');
                            process.text(data.current + ' из ' + data.max);
                          } else {
                            $('#$this->id').hide();
                            $this->endJsCallback
                            clearInterval($var);
                          }
                        })
                    }, $this->refreshPeriod);
                    JS,
            );
        }
        return parent::beforeRun();
    }
}
