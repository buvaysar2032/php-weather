<?php

namespace common\modules\user\models;

use common\components\{exceptions\ModelSaveException, helpers\UserUrl};
use common\modules\user\{enums\Status, helpers\UserHelper};
use Throwable;
use Yii;
use yii\base\Exception;
use yii\db\StaleObjectException;
use yii\web\HttpException;

/**
 * Trait Identity
 *
 * @package user\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
trait Identity
{
    /**
     * Get user by username
     *
     * @throws HttpException
     */
    public static function findIdentityByUsername(?string $username): ?self
    {
        if (empty($username)) {
            return null;
        }
        $user = self::findOne(['username' => $username]);
        return UserHelper::checkUserStatus($user);
    }

    /**
     * Get user by email
     */
    public static function findIdentityByEmail(?string $email): ?User
    {
        if (empty($email)) {
            return null;
        }
        $user = null;
        if ($emailModel = Email::findOne(['value' => $email])) {
            $user = $emailModel->user;
        }
        return $user;
    }

    /**
     * {@inheritdoc}
     *
     * @throws HttpException
     */
    public static function findIdentityByAccessToken($token, $type = null): ?User
    {
        $user_agent = Yii::$app->request->shortUserAgent;
        if (!empty($token) && $userAgent = UserAgent::findOne(['auth_key' => $token, 'value' => $user_agent])) {
            $user = self::findOne([$userAgent->user_id]);
            return UserHelper::checkUserStatus($user);
        }
        return null;
    }

    /**
     * Find identity
     *
     * @throws HttpException
     */
    public static function findIdentity($id, bool $with_ext = false): ?self
    {
        $user = self::findOne($id);
        /** @var User $user */
        return UserHelper::checkUserStatus($user);
    }

    /**
     * Logout
     *
     * @throws Throwable
     * @throws ModelSaveException
     * @throws StaleObjectException
     */
    public static function logout(): bool
    {
        /** @var User $user */
        if ($user = Yii::$app->user->identity) {
            Yii::$app->user->logout();
            if (
                $user_key =
                UserAgent::findOne(['user_id' => $user->id, 'value' => Yii::$app->request->shortUserAgent])
            ) {
                $user_key->delete();
            }
            if (!$user->save()) {
                throw new ModelSaveException($user);
            }
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    final public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    final public function validateAuthKey($authKey): bool
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * {@inheritdoc}
     */
    final public function getAuthKey(): ?string
    {
        return UserAgent::getAuthKey($this->id);
    }

    /**
     * @throws ModelSaveException
     * @throws Exception
     * @throws HttpException
     */
    final public function login(string $authSource = User::AUTH_SOURCE_EMAIL, int $duration = 0): bool
    {
        UserHelper::checkUserStatus($this);
        if (UserAgent::getAuthKey($this->id) === null) {
            $this->generateAuthKey();
        }
        $this->auth_source = $authSource;
        $this->last_login_at = time();
        $this->last_ip = Yii::$app->request->longUserIp;
        if ($this->save(false)) {
            return Yii::$app->user->login($this, $duration);
        }
        return false;
    }

    /**
     * Generates "remember me" authentication key
     *
     * @throws ModelSaveException
     * @throws Exception
     */
    final public function generateAuthKey(): void
    {
        $shortUserAgent = Yii::$app->request->shortUserAgent;
        if (!$userAgent = UserAgent::findOne(['user_id' => $this->id, 'value' => $shortUserAgent])) {
            $userAgent = new UserAgent();
            $userAgent->user_id = $this->id;
            $userAgent->value = $shortUserAgent;
        }
        $userAgent->auth_key = Yii::$app->security->generateRandomString();
        if (!$userAgent->save()) {
            throw new ModelSaveException($userAgent);
        }
    }

    /**
     * Блокировка пользователя
     *
     * @throws ModelSaveException
     */
    final public function ban(): void
    {
        $this->status = Status::Blocked->value;
        if (!$this->save()) {
            throw new ModelSaveException($this);
        }
    }
}
