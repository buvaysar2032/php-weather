<?php
/**
 * @link      https://github.com/himiklab/yii2-recaptcha-widget
 * @copyright Copyright (c) 2014-2019 HimikLab
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace common\widgets\reCaptcha;

use Yii;
use yii\base\Component;
use yii\httpclient\Request;

/**
 * Yii2 Google reCAPTCHA widget global config.
 *
 * @see     https://developers.google.com/recaptcha
 * @author  HimikLab
 * @package common\widgets\reCaptcha
 */
class ReCaptchaConfig extends Component
{
    public const JS_API_URL_DEFAULT = '//www.google.com/recaptcha/api.js';
    public const JS_API_URL_ALTERNATIVE = '//www.recaptcha.net/recaptcha/api.js';

    public const SITE_VERIFY_URL_DEFAULT = 'https://www.google.com/recaptcha/api/siteverify';
    public const SITE_VERIFY_URL_ALTERNATIVE = 'https://www.recaptcha.net/recaptcha/api/siteverify';

    /** Your siteKey for reCAPTCHA v2. */
    public ?string $siteKeyV2;

    /** Your secret for reCAPTCHA v2. */
    public ?string $secretV2;

    /** Your v3 siteKey for reCAPTCHA v3. */
    public ?string $siteKeyV3;

    /** Your secret for reCAPTCHA v3. */
    public ?string $secretV3;

    /** Use [[JS_API_URL_ALTERNATIVE]] when [[JS_API_URL_DEFAULT]] is not accessible. */
    public string $jsApiUrl;

    /** Use [[SITE_VERIFY_URL_ALTERNATIVE]] when [[SITE_VERIFY_URL_DEFAULT]] is not accessible. */
    public string $siteVerifyUrl;

    /** Check the host name. */
    public bool $checkHostName;

    public Request $httpClientRequest;

    public function init(): void
    {
        $this->siteKeyV2 = Yii::$app->environment->RECAPTCHA_V2_KEY;
        $this->secretV2 = Yii::$app->environment->RECAPTCHA_V2_SECRET;
        $this->siteKeyV3 = Yii::$app->environment->RECAPTCHA_V3_KEY;
        $this->secretV3 = Yii::$app->environment->RECAPTCHA_V3_SECRET;
        parent::init();
    }
}
