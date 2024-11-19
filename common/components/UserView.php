<?php

namespace common\components;

use Yii;
use yii\helpers\Url;
use yii\web\View;

class UserView extends View
{
    public const SCRIPT_REGEX = '/<script(.*?)/i';
    public string $script_replacement;

    /**
     * {@inheritdoc}
     */
    public function registerLinkTag($options, $key = null): void
    {
        if (!empty($options['href'])) {
            $this->addSha256Integrity($options['href'], $options);
        }
        parent::registerLinkTag($options, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function registerJsFile($url, $options = [], $key = null): void
    {
        $this->addSha256Integrity($url, $options);
        parent::registerJsFile($url, $options, $key);
    }


    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $cspNonce = Yii::$app->environment->CSP_NONCE;
        if (!empty($cspNonce)) {
            $this->script_replacement = '<script nonce="' . $cspNonce . '"$1';
            $this->registerMetaTag(['property' => 'csp-nonce', 'content' => $cspNonce]);
        }
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    protected function renderHeadHtml(): string
    {
        $head = parent::renderHeadHtml();
        if (!empty($this->script_replacement)) {
            $head = preg_replace(self::SCRIPT_REGEX, $this->script_replacement, $head);
        }
        return $head;
    }

    /**
     * {@inheritdoc}
     */
    protected function renderBodyBeginHtml(): string
    {
        $bodyBegin = parent::renderBodyBeginHtml();
        if (!empty($this->script_replacement)) {
            $bodyBegin = preg_replace(self::SCRIPT_REGEX, $this->script_replacement, $bodyBegin);
        }
        return $bodyBegin;
    }

    /**
     * {@inheritdoc}
     */
    protected function renderBodyEndHtml($ajaxMode): string
    {
        $bodyEnd = parent::renderBodyEndHtml($ajaxMode);
        if (!empty($this->script_replacement)) {
            $bodyEnd = preg_replace(self::SCRIPT_REGEX, $this->script_replacement, $bodyEnd);
        }
        return $bodyEnd;
    }

    private function addSha256Integrity(string $url, array &$options): void
    {
        if (Url::isRelative($url) && file_exists($filename = Yii::getAlias('@htdocs') . preg_replace('/\?.*$/', '', $url))) {
            $options['integrity'] = 'sha256-' . base64_encode(hash('sha256', file_get_contents($filename), true));
        }
    }
}
