<?php

namespace admin\controllers;

/**
 * Контроллер для вывода страницы FAQ
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class FaqController extends AdminController
{
    public function actionIndex(): string
    {
        return $this->render('index');
    }
}
