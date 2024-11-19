<?php

namespace api\modules\v1\swagger;

use OpenApi\{Annotations\OpenApi, Generator, Util};
use Yii;
use yii\base\Action;
use yii\web\Response;

/**
 * Class JsonAction
 *
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class JsonAction extends Action
{
    /**
     * Directory(s) or filename(s) with open api annotations.
     */
    public array $dirs = [];

    /**
     *   exclude: string|array $exclude The directory(s) or filename(s) to exclude (as absolute or relative paths)
     *   analyser: defaults to StaticAnalyser
     *   analysis: defaults to a new Analysis
     *   processors: defaults to the registered processors in Analysis
     */
    public array $scanOptions = [];

    public function run(): ?OpenApi
    {
        $this->initCors();

        Yii::$app->response->format = Response::FORMAT_JSON;
        $exclude = $this->scanOptions['exclude'] ?? [];
        return Generator::scan(Util::finder($this->dirs, $exclude), $this->scanOptions);
    }

    /**
     * Init cors.
     */
    protected function initCors(): void
    {
        $headers = Yii::$app->response->headers;

        $headers->set('Access-Control-Allow-Headers', 'Content-Type');
        $headers->set('Access-Control-Allow-Methods', 'GET, POST, DELETE, PUT');
        $headers->set('Access-Control-Allow-Origin', '*');
        $headers->set('Allow', 'OPTIONS,HEAD,GET');
    }
}