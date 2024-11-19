<?php

namespace common\modules\user\actions;

use api\modules\v1\controllers\AppController;
use common\modules\user\Module;
use common\modules\user\social\{controllers\SocAuthController, SocInterface};
use Throwable;
use Yii;
use yii\base\{Action, Exception, InvalidArgumentException, InvalidConfigException};
use yii\db\StaleObjectException;
use yii\web\{Cookie, Response};

/**
 * Базовый класс для экшенов
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property AppController $controller
 */
class BaseAction extends Action
{
    /**
     * Реализация логики авторизации в соц. сетях
     *
     * @throws Throwable
     * @throws Exception
     * @throws InvalidConfigException
     * @throws StaleObjectException
     */
    final protected function socAuth(string $type): Response|string
    {
        // получаем тип соц. сети ('fb', 'vk' и т.д.)
        $soc_id = Yii::$app->request->getParameter('soc');

        //сохраняем куки для повторного захода в метод после редиректа
        $soc_id = $this->getOrSetSocCookie($soc_id);
        if (!$soc_id) {
            throw new InvalidArgumentException('Social id not found in cookies');
        }

        //сохраняем в куки токен для доступа к api
        $this->setAccessCookie();

        //получаем нужную модель
        $socName = 'common\modules\user\social\models\\' . ucfirst($soc_id);
        if (!class_exists($socName)) {
            throw new InvalidConfigException('Unknown social network ' . $soc_id);
        }
        /** @var SocInterface $soc */
        $soc = new $socName();

        $code = Yii::$app->request->get('code');
        // Первый заход в метод - перенаправляем в социалку с redirect_url
        if ($code === null) {
            $url = $soc->getLoginUrl();
            return $this->controller->redirect($url);
        }

        //Второй заход в метод - получаем результат взаимодействия с БД
        $result = $this->getSocAuthResult($soc_id, $soc, $type);

        return $this->controller->returnOpenerResponse($result);
    }

    /**
     * Установка/извлечение куки-файла с идентификатором соц. сети
     */
    private function getOrSetSocCookie(string $soc_id = null): ?string
    {
        if (!$soc_id) {
            $soc_id = Yii::$app->request->cookies->get('soc')->value;
            Yii::$app->response->cookies->remove('soc');
        } else {
            Yii::$app->response->cookies->add(
                new Cookie(['name' => 'soc', 'value' => $soc_id])
            );
        }
        return $soc_id;
    }

    /**
     * Установка куки-файла для обеспечения доступа (при авторизации через соц. сеть)
     */
    private function setAccessCookie(): void
    {
        if ($access_token = Yii::$app->request->getParameter('access_token')) {
            Yii::$app->response->cookies->add(
                new Cookie(['name' => 'access_token', 'value' => $access_token, 'expire' => time() + 30 * 60,])
            );
        }
    }

    /**
     * Получение результата авторизации в соц. сетях
     *
     * @throws Throwable
     * @throws Exception
     * @throws InvalidConfigException
     * @throws StaleObjectException
     */
    private function getSocAuthResult(string $soc_id, SocInterface $soc, string $type): array
    {
        //При втором заходе в метод реализуем логику взаимодействия с БД
        $socAuth = new SocAuthController();

        if ($response = $socAuth->auth($soc, $type)) {
            if (is_array($response) && array_key_exists('error', $response)) {
                $result = $response;
            } else { // Если успешно авторизовались через социалку
                return $response;
            }
        } else {
            $result = $this->controller->returnError(
                'soc:error',
                Yii::t(Module::MODULE_ERROR_MESSAGES, 'You have not assigned this social network')
            );
        }
        $result['oauth_client'] = $soc_id;
        return $result;
    }
}