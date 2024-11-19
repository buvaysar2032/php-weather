<?php

namespace console\controllers;

use yii\console\Controller;

/**
 * Class ConsoleController
 *
 * @package console\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
abstract class ConsoleController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $color = DIRECTORY_SEPARATOR === '\\' ?: null;
}