<?php

namespace console\components\frontendGenerator;

use common\components\helpers\UserFileHelper;
use Yii;
use yii\base\Component;
use yii\helpers\{BaseConsole, Inflector};
use yii\base\Exception;
use yii\web\Controller;

/**
 * Class Generator
 *
 * @package components\frontendGenerator
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read bool $hasError
 */
class Generator extends Component
{
    public array $errors = [];

    public array $out = [];

    public string $path;

    public string $baseControllerClass = Controller::class;

    public string $controllerNamespace = 'frontend\controllers';

    public string $viewPath = '@frontend/views';

    public string $controllerClass;

    private string $_templatesPath = '@console/components/frontendGenerator/templates/';

    private array $_parts;

    private string $_controllerPath;

    private string $_controllerFile;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        if (!isset($this->path)) {
            $this->addError('Не задан путь');
            return;
        }
        $this->_parts = explode('/', $this->path);
        if (count($this->_parts) > 2) {
            $this->addError('Слишком длинный путь.');
        }

        $this->_controllerPath = Yii::getAlias('@frontend/controllers');
    }

    private function addError(string $description): void
    {
        $this->errors[] = $description;
    }

    private function addOutput(string $text, int $style = BaseConsole::FG_GREEN): void
    {
        $this->out[] = ['text' => $text, 'style' => $style];
    }

    public function getHasError(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Генерация контроллера, его экшена и view файла
     *
     * @throws Exception
     */
    public function generate(): void
    {
        if ($this->hasError) {
            return;
        }
        if (count($this->_parts) === 1 || $this->_parts[0] === 'site') {
            $this->controllerClass = 'SiteController';
            $this->viewPath .= '/site';
        } else {
            $ucController = Inflector::camelize($this->_parts[0]);
            $this->controllerClass = $ucController . 'Controller';
            $this->viewPath .= '/' . Inflector::camel2id($ucController);
        }
        $this->_controllerFile = "$this->_controllerPath/$this->controllerClass.php";
        if (!file_exists($this->_controllerFile)) {
            $this->generateController();
        }
        if (count($this->_parts) === 1) {
            $actionName = $this->_parts[0];
        } else {
            $actionName = $this->_parts[1];
        }
        $this->generateAction($actionName);
        $this->addOutput('Страница доступна по адресу - /' . implode('/', $this->_parts), BaseConsole::FG_CYAN);
    }

    /**
     * Генерация нового класса контроллера
     */
    public function generateController(): void
    {
        $includes = [$this->baseControllerClass];
        $content = Yii::$app->view->renderFile(
            "{$this->_templatesPath}controller.php",
            ['namespace' => $this->controllerNamespace, 'includes' => $includes, 'generator' => $this]
        );
        file_put_contents($this->_controllerFile, $content);
        $this->addOutput("Создан файл '$this->_controllerFile'");
    }

    /**
     * Генерация нового экшена для контроллера
     *
     * @throws Exception
     */
    public function generateAction(string $name): void
    {
        $controller = file_get_contents($this->_controllerFile);

        $ucName = Inflector::camelize($name);
        if (preg_match("/function action$ucName/", $controller)) {
            $this->addOutput(
                "Обнаружен уже созданный метод 'action$ucName' в контроллере '$this->controllerClass', пропуск генерации экшена",
                BaseConsole::FG_CYAN
            );
            return;
        }
        $action = Yii::$app->view->renderFile($this->_templatesPath . 'action.php', ['actionName' => $ucName]);
        $controller = preg_replace('/}\s*$/', $action, $controller);
        file_put_contents($this->_controllerFile, $controller);
        $id = Inflector::camel2id($ucName);
        $this->addOutput("Создан новый экшен '$id' в контроллере '$this->controllerClass'");
        $this->generateView($id);
    }

    /**
     * Генерация view файла и pug шаблона для него
     *
     * @throws Exception
     */
    public function generateView(string $name): void
    {
        $aViewPath = Yii::getAlias($this->viewPath);
        UserFileHelper::createDirectory($aViewPath);
        $viewFile = "$aViewPath/$name.php";
        $pugFile = "$aViewPath/$name.pug";
        if (!file_exists($viewFile)) {
            $content = Yii::$app->view->renderFile($this->_templatesPath . 'view.php', ['id' => $name]);
            file_put_contents($viewFile, $content);
            $this->addOutput("Создан новый view файл - '$viewFile'");
        }
        if (!file_exists($pugFile)) {
            file_put_contents($pugFile, "//- $name template file\nh1=title");
            $this->addOutput("Создан новый pug шаблон - '$pugFile'");
        }
    }
}