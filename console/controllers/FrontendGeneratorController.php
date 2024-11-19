<?php

namespace console\controllers;

use console\components\frontendGenerator\Generator;
use yii\base\Exception;
use yii\console\ExitCode;
use yii\helpers\BaseConsole;

/**
 * Class FrontendGeneratorController
 *
 * @package console\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class FrontendGeneratorController extends ConsoleController
{
    /**
     * @throws Exception
     */
    public function actionCreatePage(string $path): int
    {
        $generator = new Generator(['path' => $path]);
        $generator->generate();
        if ($generator->hasError) {
            foreach ($generator->errors as $error) {
                $this->stdout($error . PHP_EOL, BaseConsole::FG_RED);
            }
            return ExitCode::UNSPECIFIED_ERROR;
        }
        foreach ($generator->out as $log) {
            $this->stdout($log['text'] . PHP_EOL, $log['style']);
        }
        return ExitCode::OK;
    }
}