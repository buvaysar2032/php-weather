<?php

namespace console\controllers;

use admin\models\UserAdmin;
use admin\modules\rbacAdmin\{models\RbacRole, models\RbacUserAdmin};
use common\components\exceptions\ModelSaveException;
use console\models\SignupForm;
use Exception;
use yii\base\InvalidConfigException;
use yii\console\ExitCode;
use yii\helpers\BaseConsole;

/**
 * Взаимодействие с администраторами
 *
 * @package console\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class UserAdminController extends ConsoleController
{
    /**
     * Создание нового администратора
     *
     * @throws ModelSaveException
     * @throws \yii\base\Exception
     * @throws InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionCreate(string $username = null, string $email = null, string $password = null): int
    {
        $model = new SignupForm();
        $model->username = $username ?: $this->prompt('Введите имя пользователя:', ['required' => true]);
        $model->email = $email ?: $this->prompt('Введите имя e-mail:', ['required' => true]);
        $model->password = $password ?: $this->prompt('Введите пароль:', ['required' => true]);
        if ($model->signup()) {
            $this->stdout(sprintf('Пользователь %s добавлен%s', $model->username, PHP_EOL), BaseConsole::FG_GREEN);
        } elseif (is_null($username) && is_null($email) && is_null($password)) {
            $this->stdout(
                sprintf('ОШИБКА %s%s%s', PHP_EOL, print_r($model->errors, true), PHP_EOL),
                BaseConsole::BG_RED
            );
            return ExitCode::UNSPECIFIED_ERROR;
        } else {
            $this->stdout(sprintf('Пользователь %s не добавлен, возможно он уже есть в БД%s', $model->username, PHP_EOL), BaseConsole::FG_GREEN);
        }

        return ExitCode::OK;
    }
}