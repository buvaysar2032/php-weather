<?php
/**
 * @link      https://github.com/himiklab/yii2-recaptcha-widget
 * @copyright Copyright (c) 2014-2019 HimikLab
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace common\widgets\reCaptcha;

use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\httpclient\{Client as HttpClient, Request};
use yii\validators\Validator;

/**
 * ReCaptcha widget validator base class.
 *
 * @author  HimikLab
 * @package common\widgets\reCaptcha
 */
abstract class ReCaptchaBaseValidator extends Validator
{
    public $skipOnEmpty = false;

    public string $secret;

    /**
     * Use ReCaptchaConfig::SITE_VERIFY_URL_ALTERNATIVE when ReCaptchaConfig::SITE_VERIFY_URL_DEFAULT
     * is not accessible. Default is ReCaptchaConfig::SITE_VERIFY_URL_DEFAULT.
     */
    public string $siteVerifyUrl;

    public Request $httpClientRequest;

    public string $configComponentName = 'reCaptcha';

    /** Check host name. Default is false. */
    public bool $checkHostName;

    protected bool $isValid;

    public function __construct(
        $siteVerifyUrl,
        $checkHostName,
        $httpClientRequest,
        $config
    ) {
        if ($siteVerifyUrl && !isset($this->siteVerifyUrl)) {
            $this->siteVerifyUrl = $siteVerifyUrl;
        }
        if ($checkHostName && !isset($this->checkHostName)) {
            $this->checkHostName = $checkHostName;
        }
        if ($httpClientRequest && !isset($this->httpClientRequest)) {
            $this->httpClientRequest = $httpClientRequest;
        }

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        if ($this->message === null) {
            $this->message = Yii::t('yii', 'The verification code is incorrect.');
        }
    }

    /**
     * @throws Exception
     */
    protected function getResponse(string $value): array
    {
        $response = $this->httpClientRequest
            ->setMethod('GET')
            ->setUrl($this->siteVerifyUrl)
            ->setData(['secret' => $this->secret, 'response' => $value, 'remoteip' => Yii::$app->request->userIP])
            ->send();
        if (!$response->isOk) {
            throw new Exception('Unable connection to the captcha server. Status code ' . $response->statusCode);
        }

        return $response->data;
    }

    /**
     * @throws InvalidConfigException
     */
    protected function configComponentProcess(): void
    {
        /** @var ReCaptchaConfig $reCaptchaConfig */
        $reCaptchaConfig = Yii::$app->get($this->configComponentName, false);

        if (empty($this->siteVerifyUrl)) {
            if ($reCaptchaConfig && !empty($reCaptchaConfig->siteVerifyUrl)) {
                $this->siteVerifyUrl = $reCaptchaConfig->siteVerifyUrl;
            } else {
                $this->siteVerifyUrl = ReCaptchaConfig::SITE_VERIFY_URL_DEFAULT;
            }
        }

        if (empty($this->checkHostName)) {
            if ($reCaptchaConfig && !empty($reCaptchaConfig->checkHostName)) {
                $this->checkHostName = $reCaptchaConfig->checkHostName;
            } else {
                $this->checkHostName = false;
            }
        }

        if (empty($this->httpClientRequest)) {
            if ($reCaptchaConfig && !empty($reCaptchaConfig->httpClientRequest)) {
                $this->httpClientRequest = $reCaptchaConfig->httpClientRequest;
            } else {
                $this->httpClientRequest = (new HttpClient())->createRequest();
            }
        }
    }
}
