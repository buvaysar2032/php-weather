<?php

namespace common\modules\mail\models;

use common\components\helpers\UserFileHelper;
use common\enums\Boolean;
use common\models\AppModel;
use common\modules\mail\Mail;
use common\modules\user\enums\Status;
use common\modules\user\models\{Email, User, UserExt};
use Yii;
use yii\base\Exception;
use yii\bootstrap5\Html;

/**
 * This is the model class for table "{{%mail_template}}".
 *
 * @package mail\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class Template extends AppModel
{
    public const DEFAULT_PHP = <<<'PHP'
<?php

/**
 * @var $this    yii\web\View
 * @var $message common\modules\mail\components\Message
 * @var $user    common\modules\user\models\User|null
 * @var $data    array
 */

$data = $data ?? [];
$this->registerCss(file_get_contents(__DIR__ . '/' . str_replace('-html.php', '.css', basename($this->viewFile))));
echo $this->render(str_replace('-html.php', '.pug', basename($this->viewFile)), $data);
PHP;

    public ?string $name = null;

    /**
     * Pug Layout шаблон всех страниц
     */
    public ?string $pugLayout = null;

    /**
     * Стили всех страниц
     */
    public ?string $layoutStyle = null;

    /**
     * HTML шаблон
     */
    public ?string $pugHtml = null;

    /**
     * Стили шаблона
     */
    public ?string $style = null;

    /**
     * Текстовый шаблон
     */
    public ?string $text = null;

    private static array $_templates;

    public static function findAll(): array
    {
        if (!isset(self::$_templates)) {
            self::$_templates = UserFileHelper::findFiles(
                Yii::$app->mailer->viewPath,
                ['only' => ['*-html.php'], 'recursive' => false]
            );
            foreach (self::$_templates as &$template) {
                $template = preg_replace('/(.*?)-html.php/', '$1', basename($template));
            }
            unset($template);
        }
        return self::$_templates;
    }

    /**
     * Возвращает содержимое шаблонов, если они есть.
     */
    public static function findFiles(string $name): self
    {
        $model = new self();
        $model->name = $name;
        $htmlFilename = self::getPugHtmlFilename($name);
        if (file_exists($htmlFilename)) {
            $model->pugHtml = file_get_contents($htmlFilename);
        } else {
            $model->pugHtml = '';
        }

        $styleFilename = self::getStyleFilename($name);
        if (file_exists($styleFilename)) {
            $model->style = file_get_contents($styleFilename);
        } else {
            $model->style = '';
        }

        $textFilename = self::getTextFilename($name);
        if (file_exists($textFilename)) {
            $model->text = file_get_contents($textFilename);
        } else {
            $model->text = '';
        }

        $pugLayoutFilename = self::getPugLayoutFilename();
        if (file_exists($pugLayoutFilename)) {
            $model->pugLayout = file_get_contents($pugLayoutFilename);
        } else {
            $model->pugLayout = '';
        }

        $layoutStyleFilename = self::getLayoutStyleFilename();
        if (file_exists($layoutStyleFilename)) {
            $model->layoutStyle = file_get_contents($layoutStyleFilename);
        } else {
            $model->layoutStyle = '';
        }

        return $model;
    }

    /**
     * Возвращает путь к pug layout файлу
     */
    public static function getPugLayoutFilename(): string
    {
        return sprintf('%s/layouts/html.pug', Yii::$app->mailer->viewPath);
    }

    /**
     * Возвращает путь к css стилям
     */
    public static function getLayoutStyleFilename(): string
    {
        return sprintf('%s/layouts/style.css', Yii::$app->mailer->viewPath);
    }

    /**
     * Возвращает путь к pug шаблону
     */
    private  static function getPugHtmlFilename(string $name): string
    {
        return sprintf('%s/%s.pug', Yii::$app->mailer->viewPath, $name);
    }

    /**
     * Возвращает путь к стилям шаблона
     */
    private  static function getStyleFilename(string $name): string
    {
        return sprintf('%s/%s.css', Yii::$app->mailer->viewPath, $name);
    }

    /**
     * Возвращает путь к основному html шаблону
     */
    private static function getHtmlFilename(string $name): string
    {
        return sprintf('%s/%s-html.php', Yii::$app->mailer->viewPath, $name);
    }

    /**
     * Возвращает путь к текстовому шаблону
     */
    public static function getTextFilename(string $name): string
    {
        return sprintf('%s/%s-text.php', Yii::$app->mailer->viewPath, $name);
    }

    /**
     * Удаляет файлы шаблонов, если они есть.
     */
    public static function deleteFiles(string $name): void
    {
        $filename = self::getHtmlFilename($name);
        if (file_exists($filename)) {
            unlink($filename);
        }

        $filename1 = self::getPugHtmlFilename($name);
        if (file_exists($filename1)) {
            unlink($filename1);
        }

        $filename2 = self::getTextFilename($name);
        if (file_exists($filename2)) {
            unlink($filename2);
        }

        $filename3 = self::getStyleFilename($name);
        if (file_exists($filename3)) {
            unlink($filename3);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name', 'pugLayout', 'layoutStyle', 'pugHtml', 'style', 'text'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t(Mail::MODULE_MESSAGES, 'Name'),
            'pugLayout' => Yii::t(Mail::MODULE_MESSAGES, 'Pug layout'),
            'layoutStyle' => Yii::t(Mail::MODULE_MESSAGES, 'Layout Style'),
            'pugHtml' => Yii::t(Mail::MODULE_MESSAGES, 'Html Content Template'),
            'style' => Yii::t(Mail::MODULE_MESSAGES, 'Style'),
            'text' => Yii::t(Mail::MODULE_MESSAGES, 'Text Template')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeHints(): array
    {
        return [
            'pugLayout' => Yii::t(Mail::MODULE_MESSAGES, 'Layout is same for every letter!'),
            'layoutStyle' => Yii::t(Mail::MODULE_MESSAGES, 'Styles for every letter!'),
            'pugHtml' => Yii::t(Mail::MODULE_MESSAGES, 'Use pug markup'),
            'style' => Yii::t(Mail::MODULE_MESSAGES, 'Some mail clients may cutoff these styles!') .
                ' ' . Html::a(
                    'Туториал',
                    'https://www.unisender.com/ru/blog/sovety/kak-sverstat-pismo-instruktsiya-dlya-chaynikov/',
                    ['target' => '_blank']
                ),
            'text' => Yii::t(Mail::MODULE_MESSAGES, 'Use plain text'),
        ];
    }

    /**
     * Переименует файлы шаблонов.
     */
    public function renameFiles(string $oldName, string $name): void
    {
        if ($oldName !== $name) {
            $filename = self::getHtmlFilename($oldName);
            $newFilename = self::getHtmlFilename($name);
            if (file_exists($filename)) {
                rename($filename, $newFilename);
            } else {
                file_put_contents($newFilename, self::DEFAULT_PHP);
            }

            $filename = self::getPugHtmlFilename($oldName);
            if (file_exists($filename)) {
                unlink($filename);
            }
            $filename = self::getPugHtmlFilename($name);
            file_put_contents($filename, $this->pugHtml);

            $filename = self::getTextFilename($oldName);
            if (file_exists($filename)) {
                unlink($filename);
            }
            $filename = self::getTextFilename($name);
            file_put_contents($filename, $this->text);

            $filename = self::getStyleFilename($oldName);
            if (file_exists($filename)) {
                unlink($filename);
            }
            $filename = self::getStyleFilename($name);
            file_put_contents($filename, $this->style);
        } else {
            $this->saveFiles($name);
        }
    }

    /**
     * Сохраняет файлы шаблонов.
     */
    public function saveFiles(): void
    {
        $filename = self::getHtmlFilename($this->name);
        if (!file_exists($filename)) {
            file_put_contents($filename, self::DEFAULT_PHP);
        }
        $filename = self::getPugHtmlFilename($this->name);
        file_put_contents($filename, $this->pugHtml);

        $filename = self::getStyleFilename($this->name);
        file_put_contents($filename, $this->style);

        $filename = self::getTextFilename($this->name);
        file_put_contents($filename, $this->text);

        if (!empty($this->pugLayout)) {
            file_put_contents(self::getPugLayoutFilename(), $this->pugLayout);
        }
        file_put_contents(self::getLayoutStyleFilename(), $this->layoutStyle);
    }

    /**
     * @throws Exception
     */
    public static function getDummyUser(): User
    {
        if (!$user = User::findOne(['username' => 'Username'])) {
            $user = new User();
            $user->id = 1;
            $user->username = 'Username';
            $user->status = Status::Active->value;
            $user->auth_source = 'admin-testing';
            $user->password_reset_token = Yii::$app->security->generateRandomString();
            $user->last_login_at = time();
        }
        if (!UserExt::findOne(['user_id' => $user->id])) {
            $userExt = new UserExt();
            $userExt->id = 1;
            $userExt->user_id = $user->id;
            $userExt->first_name = 'Иван';
            $userExt->middle_name = 'Иванов';
            $userExt->last_name = 'Иванович';
            $userExt->phone = '+79998887766';
            $userExt->populateRelation('user', $user);
            $user->populateRelation('userExt', $userExt);
        }
        if (!Email::findOne(['user_id' => $user->id])) {
            $email = new Email();
            $email->id = 1;
            $email->user_id = $user->id;
            $email->value = 'test@example.com';
            $email->generateConfirmToken();
            $email->is_confirmed = Boolean::Yes->value;
            $email->populateRelation('user', $user);
            $user->populateRelation('email', $email);
        }
        return $user;
    }
}
