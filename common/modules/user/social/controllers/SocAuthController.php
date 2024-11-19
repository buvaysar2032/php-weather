<?php

namespace common\modules\user\social\controllers;

use api\behaviors\returnStatusBehavior\ReturnStatusBehavior;
use common\components\exceptions\ModelSaveException;
use common\modules\user\{helpers\UserHelper, models\SocialNetwork, models\User, Module, social\SocInterface};
use Throwable;
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\httpclient\{Client, Response as ClientResponse};
use yii\web\{HttpException, IdentityInterface, Response};

/**
 * Класс SocAuthController реализующий авторизацию через соц. сети
 *
 * @package user\social
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class SocAuthController
{
    /**
     * Логика авторизации
     *
     * @throws ModelSaveException
     * @throws Exception
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws Throwable
     */
    final public function auth(SocInterface $soc, string $type): ?array
    {
        // Парсим данные пользователя
        $user_data = $this->getUserData($soc);

        // Получаем пользователя
        $user = $this->getUser($type, $user_data);
        $soc_id = $soc::$soc_name;
        $soc_user_id = $user_data['soc_user_id'];
        $auth_code = $user_data['auth_code'];

        if ($user === null) {
            $message = $type === 'signup' ? 'User was not created' : 'User not found';
            $response = ['error' => true, 'soc' => Yii::t(Module::MODULE_ERROR_MESSAGES, $message)];
        } else {
            UserHelper::checkUserSocialNetwork($user->id, $soc_id, $soc_user_id, $auth_code);
            $response = $this->createUserSession($user, true, $user_data, $user, $soc_id);
        }
        return $this->getResultFromResponse($soc_id, $response);
    }

    /**
     * Получение данных пользователя
     *
     * @throws InvalidConfigException
     * @throws \yii\httpclient\Exception
     * @throws \Exception
     */
    private function getUserData(SocInterface $soc): array
    {
        // Сначала авторизуемся в соц. сети
        $authArgs = $soc->getAuthArgs();
        $auth_response = $this->sendRequest(...$authArgs);

        $userArgs = $soc->getUserArgs($auth_response);
        $user_response = $this->sendRequest(...$userArgs);
        $user_data = $soc->getUserDataFromResponse($user_response);

        // Получаем почту из vk т.к. она возвращает в auth_response
        if (!ArrayHelper::getValue($user_data, 'email')) {
            $user_data['email'] = ArrayHelper::getValue($auth_response->data, 'email');
        }

        // Получаем токен доступа к соц. сети
        $access_token = $soc->getAccessTokenFromResponse($auth_response);
        $user_data['auth_code'] = $access_token;

        // Возвращаем данные пользователя
        $soc_id = $soc::$soc_name;
        $user_data['soc_id'] = $soc_id;
        $user_data['soc_user_id'] = $this->getSocUserId($soc_id, $user_data);
        return $user_data;
    }

    /**
     * Отправка запроса
     *
     * @throws InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    private function sendRequest(string $url, Client $client, array $params, string $method = 'GET'): ClientResponse
    {
        return $client->createRequest()
            ->setMethod($method)
            ->setUrl($url)
            ->setData($params)
            ->send();
    }

    /**
     * Получение id пользователя в соц. сети
     */
    private function getSocUserId(string $soc_id, array $user_data): string
    {
        if ($soc_id === 'ok') {
            $soc_user_id = (string)$user_data['uid'];
        } else {
            $soc_user_id = (string)$user_data['id'];
        }
        return $soc_user_id;
    }

    /**
     * Получение пользователя из БД
     *
     * @throws Throwable
     * @throws ModelSaveException
     * @throws Exception
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws HttpException
     */
    private function getUser(string $type, array $user_data): array|ActiveRecord|IdentityInterface|User|null
    {
        /** @var Module|null $userModule */
        $module = Yii::$app->getModule('user');
        if (!$module) {
            throw new InvalidConfigException('`user` module not found');
        }
        $user_social_data = SocialNetwork::findOne(
            ['social_network_id' => $user_data['soc_id'], 'user_auth_id' => $user_data['soc_user_id']]
        );
        //Пытаемся получить пользователя
        if ($user_social_data) {
            $user = User::findOne($user_social_data->user_id);
        } else {
            $user = User::findIdentity(Yii::$app->user->id) ?? UserHelper::getUserFromCookie() ?? null;
        }
        //Если при попытке авторизации данные не найдены и проставлен флаг, создаём нового пользователя
        if (!$user && $type === 'login' && $module->registerIfNot) {
            $user = UserHelper::createNewUserBySoc($user_data['soc_id'], $user_data, 2);
        }
        return $user;
    }

    /**
     * Создание пользовательской сессии
     *
     * @throws ModelSaveException
     * @throws Exception
     * @throws HttpException
     */
    private function createUserSession(User $user, bool $success, array $user_data, User $auth, string $soc_id): array|string
    {
        if ($success) {
            UserHelper::loginUser($user, $soc_id);
            $response = $user->authKey;
        } else {
            return [
                'success' => false,
                'user' => $user,
                'user_data' => $user_data,
                'auth' => $auth,
            ];
        }
        // TODO: Валидацию данных пользователя настроить
        Yii::$app->response->format = Response::FORMAT_HTML;
        return $response;
    }

    /**
     * Разбор ответа соц. сети
     *
     * @throws ModelSaveException
     * @throws Exception
     * @throws HttpException
     */
    private function getResultFromResponse(string $soc_id, $response = null): array
    {
        if ($response) {
            if (is_array($response) && array_key_exists('error', $response)) {
                $result = $response;
            } elseif ($user = User::findIdentityByAccessToken($response)) {
                $result = [
                    'success' => true,
                    'data' => ['profile' => UserHelper::getProfile($user)],
                ];
            }
        } else {
            $result = (new ReturnStatusBehavior())
                ->returnError(
                    ['soc' => Yii::t(Module::MODULE_ERROR_MESSAGES, 'You have not assigned this social network')]
                );
        }
        $result['oauth_client'] = $soc_id;
        return $result;
    }
}
