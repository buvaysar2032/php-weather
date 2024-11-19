<?php

namespace common\widgets\reCaptcha;

use Closure;
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\httpclient\Request;

/**
 * reCaptcha v3 widget validator.
 *
 * @see     https://developers.google.com/recaptcha/docs/v3
 * @author  HimikLab
 * @package common\widgets\reCaptcha
 */
class ReCaptchaValidator3 extends ReCaptchaBaseValidator
{
    public float|Closure $threshold = 0.5;

    /** Set to false if you don`t need to check action. */
    public string|bool|null $action = null;

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

        if ($this->action === null) {
            $this->action = preg_replace('/[^a-zA-Z\d\/]/', '', urldecode(Yii::$app->request->url));
        }
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    protected function validateValue($value): ?array
    {
        if (!isset($this->isValid)) {
            if (!$value) {
                $this->isValid = false;
            } else {
                $response = $this->getResponse($value);
                if (isset($response['error-codes'])) {
                    $this->isValid = false;
                } else {
                    if (!isset($response['success'], $response['action'], $response['hostname'], $response['score']) ||
                        $response['success'] !== true ||
                        ($this->action !== false && $response['action'] !== $this->action) ||
                        ($this->checkHostName && $response['hostname'] !== Yii::$app->request->hostName)
                    ) {
                        throw new Exception('Invalid recaptcha verify response.');
                    }

                    if (is_callable($this->threshold)) {
                        $this->isValid = (bool)call_user_func($this->threshold, $response['score']);
                    } else {
                        $this->isValid = $response['score'] >= $this->threshold;
                    }
                }
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
            if ($reCaptchaConfig && !empty($reCaptchaConfig->secretV3)) {
                $this->secret = $reCaptchaConfig->secretV3;
            } else {
                throw new InvalidConfigException('Required `secret` param isn\'t set.');
            }
        }
    }
}
