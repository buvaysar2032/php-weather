<?php

namespace console\controllers;

use RequirementChecker;
use yii\console\ExitCode;

/**
 * Class RequirementsController
 *
 * @package console\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class RequirementsController extends ConsoleController
{
    public function actionIndex(): int
    {
        require dirname(__DIR__, 2) . '/requirements/RequirementChecker.php';
        $requirementsChecker = new RequirementChecker();
        $requirementsChecker->checkYii()->render();
        return ExitCode::OK;
    }
}