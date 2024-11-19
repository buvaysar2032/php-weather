<?php

namespace common\modules\mail\models;

use common\enums\{AppType};
use common\models\AppActiveRecord;
use common\modules\mail\{enums\LogStatus, Mail};
use common\modules\user\models\User;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%mailing_log}}".
 *
 * @package mail\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property int             $id              [int] ID
 * @property string          $template        [varchar(255)] Шаблон письма
 * @property string          $mailing_subject [varchar(255)] Тема
 * @property string          $mail_to         [varchar(128)] Получатель
 * @property int             $user_id         [int] ID пользователя
 * @property int             $date            [int] Дата отправки
 * @property int             $status          [int] Статус отправки
 * @property string          $description     Описание
 * @property int             $app_type        [int] Инициатор отправки
 * @property int             $mailing_log_id  [int] ID родительского лога
 * @property string          $data            JSON массив данных в письме
 *
 * @property-read User       $user
 * @property-read MailingLog $mailingLog
 */
class MailingLog extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%mailing_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['app_type'], 'required'],
            [['user_id', 'date', 'status', 'app_type', 'mailing_log_id'], 'integer'],
            LogStatus::validator('status'),
            AppType::validator('app_type'),
            [['template', 'mailing_subject', 'description'], 'string', 'max' => 255],
            ['mail_to', 'string', 'max' => 128],
            ['data', 'string'],
            [
                'user_id',
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
            ],
            [
                'mailing_log_id',
                'exist',
                'skipOnError' => true,
                'targetClass' => self::class,
                'targetAttribute' => ['mailing_log_id' => 'id']
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t(Mail::MODULE_MESSAGES, 'ID'),
            'template' => Yii::t(Mail::MODULE_MESSAGES, 'Template'),
            'mailing_subject' => Yii::t(Mail::MODULE_MESSAGES, 'Mailing Subject'),
            'mail_to' => Yii::t(Mail::MODULE_MESSAGES, 'Mail To'),
            'user_id' => Yii::t('app', 'User ID'),
            'date' => Yii::t(Mail::MODULE_MESSAGES, 'Date'),
            'status' => Yii::t(Mail::MODULE_MESSAGES, 'Status'),
            'description' => Yii::t(Mail::MODULE_MESSAGES, 'Description'),
            'app_type' => Yii::t(Mail::MODULE_MESSAGES, 'App Type'),
            'mailing_log_id' => Yii::t(Mail::MODULE_MESSAGES, 'Mailing Log Id'),
            'data' => Yii::t(Mail::MODULE_MESSAGES, 'Mail Data')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function externalAttributes(): array
    {
        return ['user.username'];
    }

    final public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    final public function getMailingLog(): ActiveQuery
    {
        return $this->hasOne(self::class, ['id' => 'mailing_log_id']);
    }
}
