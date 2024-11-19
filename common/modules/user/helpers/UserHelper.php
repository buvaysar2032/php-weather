<?php

namespace common\modules\user\helpers;

use common\components\{exceptions\ModelSaveException, Faker, helpers\UserFileHelper};
use common\enums\Boolean;
use common\modules\user\{enums\Status,
    models\Email,
    models\SocialNetwork,
    models\User,
    models\UserAgent,
    models\UserExt,
    Module};
use Exception;
use ReflectionClass;
use ReflectionException;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\{ActiveRecord, StaleObjectException};
use yii\helpers\ArrayHelper;
use yii\web\{HttpException, IdentityInterface};

/**
 * Class UserHelper
 *
 * @package user
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class UserHelper
{
    /**
     * Получаем данные пользователя
     *
     * @throws ModelSaveException
     * @throws \yii\base\Exception
     * @throws HttpException
     */
    public static function getProfile(?User $user = null): array
    {
        $profile = [];
        if (!$user) {
            if (!Yii::$app->user->isGuest) {
                /** @var User $identity */
                $identity = Yii::$app->user->identity;
                $ip = Yii::$app->request->longUserIp;
                if ($ip !== $identity->last_ip) {
                    $identity->last_ip = $ip;
                    if (!$identity->save()) {
                        throw new ModelSaveException($identity);
                    }
                }
                $profile = $identity->profile;
            }
        } else {
            if (!UserAgent::getAuthKey($user->id)) {
                $user->generateAuthKey();
            }
            $profile = $user->profile;
        }
        return $profile;
    }

    /**
     * Создание пользователя из e-mail.
     *
     * @throws ModelSaveException
     * @throws \yii\base\Exception
     */
    public static function createNewUser(string $username, string $password): User
    {
        $user = new User();
        $user->setPassword($password);
        $user->username = $username;
        if (!$user->username) {
            $user->generateUsername();
        }
        $user->auth_source = User::AUTH_SOURCE_EMAIL;
        $user->status = Status::New->value;
        $user->last_login_at = time();
        if (!$user->save()) {
            throw new ModelSaveException($user);
        }
        $user->refresh();
        $user->generateAuthKey();
        return $user;
    }

    /**
     * Создание пользователя из соц. сети
     *
     * @throws Throwable
     * @throws ModelSaveException
     * @throws \yii\base\Exception
     * @throws InvalidConfigException
     * @throws StaleObjectException
     */
    public static function createNewUserBySoc(string $soc_id, array $params = null, int $scenario = null): array|User
    {
        if (!$params) {
            return ['error' => ['createUser' => Yii::t('app', 'Data required')]];
        }

        $params['auth_source'] = $soc_id;
        $user = new User();
        $user->load($params, '');
        if (isset($params['password'])) {
            $user->setPassword($params['password']);
        }
        self::checkEmailIsChanged($user, $params);
        if ($scenario === 1) {
            $user->auth_source = ArrayHelper::getValue($params, 'soc_id');
        }
        $user->last_login_at = time();
        if (!$user->save()) {
            throw new ModelSaveException($user);
        }
        $user->refresh();
        $user->generateAuthKey();
        $userExt = new UserExt();
        $params['user_id'] = $user->id;
        $userExt->load($params, '');
        if (!$userExt->save()) {
            $user->delete();
            return ['error' => ['createUser' => $userExt->errors]];
        }
        $email = $params['email'] ?: $params['unconfirmed_email'];
        if ($email) {
            self::createUserEmail($user, $email, true);
        }
        self::createUserSocialNetwork($user, $params);
        return $user;
    }

    /**
     * Проверка e-mail на изменение
     */
    public static function checkEmailIsChanged(User $user, array &$params): void
    {
        if (!$user->auth_source && isset($params['email'])) {
            $user->auth_source = User::AUTH_SOURCE_EMAIL;
            $params['unconfirmed_email'] = $params['email'];
            unset($params['email']);
        }
    }

    /**
     * Создание e-mail'а пользователя
     *
     * @throws \yii\base\Exception
     */
    public static function createUserEmail(
        ActiveRecord|IdentityInterface|User $user,
        string $email,
        bool $verify = false,
        bool $send = true
    ): Email {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        if (!$user_email = Email::findOne(['user_id' => $user->id])) {
            $user_email = new Email();
            $user_email->user_id = $user->id;
        }
        $user_email->value = $email;
        $user_email->is_confirmed = Boolean::No->value;

        //Если подтверждение почты не требуется, сразу считаем подтверждённой
        if (
            !$userModule->enableEmailVerification ||
            ($userModule->autoVerifyEmailFromSocNet === true && $verify === true)
        ) {
            $user_email->is_confirmed = Boolean::Yes->value;
        } elseif ($send === true && $userModule->autoSendVerificationEmail === true) { //Если включена авто рассылка писем подтверждения
            $user_email->sendVerificationEmail();
        }

        if (!$user_email->save()) {
            throw new ModelSaveException($user_email);
        }
        return $user_email;
    }

    /**
     * Создать соц. сеть у пользователя
     *
     * @throws ModelSaveException
     */
    public static function createUserSocialNetwork(User $user, array $user_data): SocialNetwork
    {
        $social_network = new SocialNetwork();
        $social_network->user_id = $user->id;
        $social_network->social_network_id = $user_data['soc_id'];
        $social_network->user_auth_id = $user_data['soc_user_id'];
        $social_network->access_token = $user_data['auth_code'];
        $social_network->last_auth_date = time();
        if (!$social_network->save()) {
            throw new ModelSaveException($social_network);
        }
        return $social_network;
    }

    /**
     * Создание записи в UserExt
     *
     * @throws ModelSaveException
     */
    public static function createUserExt(
        ActiveRecord|IdentityInterface|User $user,
        Boolean $rules_accepted = Boolean::No
    ): UserExt {
        $userExt = new UserExt();
        $userExt->user_id = $user->id;
        $userExt->rules_accepted = $rules_accepted->value;
        if (!$userExt->save()) {
            throw new ModelSaveException($userExt);
        }
        return $userExt;
    }

    /**
     * Обновление данных пользователя
     *
     * @throws Throwable
     * @throws ModelSaveException
     * @throws InvalidConfigException
     * @throws StaleObjectException
     */
    public static function updateUserData(User $user, array $params): array|User
    {
        $user->load($params, '');
        self::checkEmailIsChanged($user, $params);
        if ($user->save()) {
            $user->refresh();
            $userExt = $user->userExt;
            $userExt->load($params, '');
            if (!$userExt->save()) {
                $user->delete();
                return ['error' => ['updateData' => $userExt->errors]];
            }
            self::updateUserSocialNetwork($user, $params);
            return $user;
        }
        return ['error' => ['updateData' => $user->errors]];
    }

    /**
     * Обновление соц. сети у пользователя
     *
     * @throws ModelSaveException
     */
    public static function updateUserSocialNetwork(User $user, array $user_data): void
    {
        $soc_id = $user_data['soc_id'];
        $socialNetwork = SocialNetwork::findOne(['user_id' => $user->id, 'social_network_id' => $soc_id]);
        if ($socialNetwork) {
            $socialNetwork->access_token = $user_data['auth_code'];
            $socialNetwork->last_auth_date = time();
            if (!$socialNetwork->save()) {
                throw new ModelSaveException($socialNetwork);
            }
        }
    }

    /**
     * Проверка наличия соц. сети у пользователя
     *
     * @throws ModelSaveException
     */
    public static function checkUserSocialNetwork(
        int $userId,
        string $soc_id,
        string $soc_user_id,
        string $auth_code
    ): void {
        $socialNetwork = SocialNetwork::find()
            ->where([
                'user_id' => $userId,
                'social_network_id' => $soc_id,
                'user_auth_id' => $soc_user_id
            ])->one();
        if (!$socialNetwork) {
            $socialNetwork = new SocialNetwork();
            $socialNetwork->user_id = $userId;
            $socialNetwork->social_network_id = $soc_id;
            $socialNetwork->user_auth_id = $soc_user_id;
        }
        $socialNetwork->access_token = $auth_code;
        $socialNetwork->last_auth_date = time();
        if (!$socialNetwork->save()) {
            throw new ModelSaveException($socialNetwork);
        }
    }

    /**
     * Авторизация пользователя
     *
     * @throws ModelSaveException
     * @throws \yii\base\Exception
     * @throws HttpException
     * @throws Exception
     */
    public static function loginUser(
        User $user,
        string $authSource = User::AUTH_SOURCE_EMAIL,
        bool $remember_me = false
    ): ?IdentityInterface {
        $remember_me_duration = ArrayHelper::getValue(Yii::$app->params, 'api.loginRememberMeDuration');
        if (!Yii::$app->user->identity) {
            $user->login($authSource, $remember_me ? $remember_me_duration : 60 * 30);
        }
        return Yii::$app->user->identity;
    }

    /**
     * Получение пользователя из куки-файлов
     */
    public static function getUserFromCookie(): ?User
    {
        if (!$cookie = Yii::$app->request->cookies->get('access_token')) {
            return null;
        }
        Yii::$app->response->cookies->remove('access_token');
        return UserAgent::findOne(['value' => $cookie->value])?->user;
    }

    /**
     * Получение пользователя по id в соц. сети
     */
    public static function getUserBySocId(string $soc_id, string $user_soc_id): ?User
    {
        return SocialNetwork::findOne(['social_network_id' => $soc_id, 'user_auth_id' => $user_soc_id])?->user;
    }

    /**
     * Получить список доступных соц. сетей
     *
     * @throws ReflectionException
     */
    public static function getSocialList(): array
    {
        $namespace = '/common/modules/user/social/models';
        $path = Yii::getAlias('@root') . $namespace;
        $files = UserFileHelper::findFiles($path, ['only' => ['*.php']]);
        $list = [];
        foreach ($files as $file) {
            $controllerPath = strstr($file, $namespace);
            $controllerPath = substr($controllerPath, 0, -4);
            $controllerPath = str_replace('/', '\\', $controllerPath);
            $class = new ReflectionClass($controllerPath);
            if (!$class->isAbstract() && $class->hasProperty('soc_name')) {
                $soc = $class->getProperty('soc_name')->getValue(new $controllerPath());
                $list[$soc] = $soc;
            }
        }
        return $list;
    }

    /**
     * Проверка статуса пользователя
     *
     * @throws HttpException 403 ошибка, если пользователь заблокирован
     */
    public static function checkUserStatus(?User $user): ?User
    {
        if ($user && ($user->status !== Status::Active->value)) {
            throw new HttpException(403, Yii::t(Module::MODULE_MESSAGES, 'User is Blocked'));
        }
        return $user;
    }

    /**
     * Блокировка текущего пользователя
     *
     * @throws ModelSaveException
     */
    public static function blockCurrentUser(): void
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $user->ban();
    }

    /**
     * @throws ModelSaveException
     * @throws \yii\base\Exception
     */
    public static function createFake(int $count = 1): array
    {
        $faker = new Faker();
        $users = $faker->fill(User::class, $count, [
            'rules' => [
                'auth_source' => 'fake',
                'password_reset_token' => Yii::$app->security->generateRandomString(),
                'status' => 0,
                'last_ip' => Yii::$app->request->longUserIp
            ]
        ]);
        foreach ($users as $user) {
            $faker->fill(UserExt::class, 1, [
                'rules' => [
                    'rules_accepted' => Boolean::Yes->value,
                    'service_data' => '{}',
                ],
                User::class => $user
            ]);
            $faker->fill(Email::class, 1, [
                'rules' => [
                    'value' => $faker->generator->email,
                    'confirm_token' => Yii::$app->security->generateRandomString() . '_' . time(),
                    'is_confirmed' => Boolean::No->value,
                ],
                User::class => $user
            ]);
            $faker->fill(UserAgent::class, 1, [
                'rules' => [
                    'auth_key' => Yii::$app->security->generateRandomString(),
                    'value' => Yii::$app->request->shortUserAgent
                ],
                User::class => $user
            ]);
            $user->refresh();
        }
        return $users;
    }
}
