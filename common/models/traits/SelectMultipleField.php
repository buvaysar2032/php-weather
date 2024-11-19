<?php

namespace common\models\traits;

use common\components\exceptions\ModelSaveException;
use common\models\AppActiveRecord;
use Throwable;
use yii\db\StaleObjectException;

/**
 * Trait SelectMultipleField
 *
 * @package common\models\traits
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
trait SelectMultipleField
{
    /**
     * @param int[]|string[]    $selectedList
     * @param AppActiveRecord[] $currentList
     *
     * @throws Throwable
     * @throws ModelSaveException
     * @throws StaleObjectException
     */
    private function updateSelectList(
        SelectMultipleJunction|string $class,
        array $selectedList,
        array $currentList,
        bool $inverse = false
    ): void {
        $this->clearSelectList($class, $selectedList, $currentList, $inverse);
        $this->fillSelectList($class, $selectedList, $currentList, $inverse);
    }

    /**
     * @param int[]|string[]    $selectedList
     * @param AppActiveRecord[] $currentList
     *
     * @throws StaleObjectException
     * @throws Throwable
     */
    private function clearSelectList(
        SelectMultipleJunction|string $class,
        array $selectedList,
        array $currentList,
        bool $inverse
    ): void {
        foreach ($currentList as $currentItem) {
            foreach ($selectedList as $selectedItem) {
                if ($currentItem->primaryId === $selectedItem) {
                    continue 2;
                }
            }
            if ($inverse) {
                $class::remove($currentItem->primaryId, $this->primaryId);
            } else {
                $class::remove($this->primaryId, $currentItem->primaryId);
            }
        }
    }

    /**
     * @param int[]|string[]    $selectedList
     * @param AppActiveRecord[] $currentList
     *
     * @throws ModelSaveException
     */
    private function fillSelectList(
        SelectMultipleJunction|string $class,
        array $selectedList,
        array $currentList,
        bool $inverse
    ): void {
        foreach ($selectedList as $selectedItem) {
            foreach ($currentList as $currentItem) {
                if ($currentItem->primaryId === $selectedItem) {
                    continue 2;
                }
            }
            if (!empty($selectedItem)) {
                if ($inverse) {
                    $class::add($selectedItem, $this->primaryId);
                } else {
                    $class::add($this->primaryId, $selectedItem);
                }
            }
        }
    }
}
