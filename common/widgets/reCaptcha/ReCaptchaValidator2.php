<?php

namespace common\widgets\reCaptcha;

use Yii;
use yii\base\{Exception, InvalidConfigException, Model};
use yii\httpclient\Request;
use yii\web\View;

/**
 * ReCaptcha widget validator.
 *
 * @author  HimikLab
 * @package common\widgets\reCaptcha
 *
 * @property-read mixed $hostName
 */
class ReCaptchaValidator2 extends ReCaptchaBaseValidator
{
    public string $uncheckedMessage;

    public function __construct(
        $secret = null,
        $siteVerifyUrl = null,
        $checkHostName = null,
        Request $httpClientRequest = null,
        $config = []
    ) {
        if ($secret && empty($this->secret)) {
            $this->secret = $secret;
        }

        parent::__construct($siteVerifyUrl, $checkHostName, $httpClientRequest, $config);
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->configComponentProcess();
    }

    /**
     * @param Model $model
     * @param string $attribute
     * @param View $view
     */
    public function clientValidateAttribute($model, $attribute, $view): string
    {
        $message = addslashes(
            $this->uncheckedMessage ?: Yii::t(
                'yii',
                '{attribute} cannot be blank.',
                ['attribute' => $model->getAttributeLabel($attribute)]
            )
        );

        return <<<JS
if (!value) {
     messages.push("$message");
}
JS;
    }

    /**
     * @param string|array $value
     *
     * @throws Exception
     */
    protected function validateValue($value): ?array
    {
        if (!isset($this->isValid)) {
            if (!$value) {
                $this->isValid = false;
            } else {
                $response = $this->getResponse($value);
                if (
                    !isset($response['success'], $response['hostname'])
                    || ($this->checkHostName && $response['hostname'] !== $this->getHostName())
                ) {
                    throw new Exception('Invalid recaptcha verify response.');
                }

                $this->isValid = $response['success'] === true;
            }
        }

        return $this->isValid ? null : [$this->message, []];
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    protected function configComponentProcess(): void
    {
        parent::configComponentProcess();

        /** @var ReCaptchaConfig $reCaptchaConfig */
        $reCaptchaConfig = Yii::$app->get($this->configComponentName, false);

        if (empty($this->secret)) {
            if ($reCaptchaConfig && !empty($reCaptchaConfig->secretV2)) {
                $this->secret = $reCaptchaConfig->secretV2;
            } else {
                throw new InvalidConfigException('Required `secret` param isn\'t set.');
            }
        }
    }

    protected function getHostName()
    {
        return Yii::$app->request->hostName;
    }
}
