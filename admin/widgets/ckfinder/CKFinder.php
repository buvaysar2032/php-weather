<?php

namespace admin\widgets\ckfinder;

use yii\bootstrap5\Widget;

/**
 * Class CKFinder
 *
 * Отдельно работающий виджет для взаимодействия с файлами на сервере
 *
 * @package admin\widgets\ckfinder
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class CKFinder extends Widget
{
    /**
     * Тип ресурса из /htdocs/ckfinder/config.php
     */
    public string $resourceType = '';

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        CKFinderAsset::register($this->view);
        return $this->render('editor', ['resourceType' => $this->resourceType]);
    }
}
