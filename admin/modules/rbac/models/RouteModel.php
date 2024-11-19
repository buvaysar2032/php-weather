<?php

namespace admin\modules\rbac\models;

use Exception;
use ReflectionClass;
use Yii;
use yii\base\{BaseObject, Controller, Module};
use yii\caching\{CacheInterface, TagDependency};
use yii\helpers\VarDumper;
use yii\rbac\ManagerInterface;

/**
 * Class RouteModel
 *
 * @package admin\modules\rbac\models
 *
 * @property-read array $availableAndAssignedRoutes
 */
class RouteModel extends BaseObject
{
    /**
     * @var string cache tag
     */
    public const CACHE_TAG = 'rbac.route';

    public CacheInterface $cache;

    /**
     * @var int cache duration
     */
    public int $cacheDuration = 3600;

    /**
     * @var array list of module IDs that will be excluded
     */
    public array $excludeModules = [];

    protected ManagerInterface $manager;

    /**
     * RouteModel constructor.
     */
    public function __construct(array $config = [])
    {
        $this->cache = Yii::$app->cache;
        $this->manager = Yii::$app->authManager;

        parent::__construct($config);
    }

    /**
     * Assign items
     *
     * @throws Exception
     */
    public function addNew(array $routes): bool
    {
        foreach ($routes as $route) {
            $this->manager->add($this->manager->createPermission('/' . trim($route, ' /')));
        }

        $this->invalidate();

        return true;
    }

    /**
     * Remove items
     *
     */
    public function remove(array $routes): bool
    {
        foreach ($routes as $route) {
            $item = $this->manager->createPermission('/' . trim($route, '/'));
            $this->manager->remove($item);
        }
        $this->invalidate();

        return true;
    }

    /**
     * Get available and assigned routes
     */
    public function getAvailableAndAssignedRoutes(): array
    {
        $routes = $this->getAppRoutes();
        $exists = [];

        foreach (array_keys($this->manager->getPermissions()) as $name) {
            if ($name[0] !== '/') {
                continue;
            }
            $exists[] = $name;
            unset($routes[$name]);
        }

        return [
            'available' => array_keys($routes),
            'assigned' => $exists,
        ];
    }

    /**
     * Get list of application routes
     */
    public function getAppRoutes(string|Module $module = null): array
    {
        if ($module === null) {
            $module = Yii::$app;
        } else {
            $module = Yii::$app->getModule($module);
        }

        $key = [__METHOD__, $module->getUniqueId()];
        $result = (isset($this->cache) ? $this->cache->get($key) : false);

        if ($result === false) {
            $result = [];
            $this->getRouteRecursive($module, $result);
            if (isset($this->cache)) {
                $this->cache->set($key, $result, $this->cacheDuration, new TagDependency([
                    'tags' => self::CACHE_TAG,
                ]));
            }
        }

        return $result;
    }

    /**
     * Invalidate the cache
     */
    public function invalidate(): void
    {
        if (isset($this->cache)) {
            TagDependency::invalidate($this->cache, self::CACHE_TAG);
        }
    }

    /**
     * Get route(s) recursive
     *
     * @param Module $module
     * @param array  $result
     */
    protected function getRouteRecursive(Module $module, array &$result): void
    {
        if (!in_array($module->id, $this->excludeModules, true)) {
            $token = "Get Route of '" . get_class($module) . "' with id '" . $module->uniqueId . "'";
            Yii::beginProfile($token, __METHOD__);

            try {
                foreach ($module->getModules() as $id => $child) {
                    if (($child = $module->getModule($id)) !== null) {
                        $this->getRouteRecursive($child, $result);
                    }
                }

                foreach ($module->controllerMap as $id => $type) {
                    $this->getControllerActions($type, $id, $module, $result);
                }

                $namespace = trim($module->controllerNamespace, '\\') . '\\';
                $this->getControllerFiles($module, $namespace, '', $result);
                $all = '/' . ltrim($module->uniqueId . '/*', '/');
                $result[$all] = $all;
            } catch (Exception $exc) {
                Yii::error($exc->getMessage() . $exc->getTraceAsString(), __METHOD__);
            }

            Yii::endProfile($token, __METHOD__);
        }
    }

    /**
     * Get list controllers under module
     */
    protected function getControllerFiles(Module $module, string $namespace, string $prefix, array &$result): void
    {
        $path = Yii::getAlias('@' . str_replace('\\', '/', $namespace), false);
        $token = "Get controllers from '$path'";
        Yii::beginProfile($token, __METHOD__);

        try {
            if (!is_dir($path)) {
                return;
            }

            foreach (scandir($path) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                if (is_dir($path . '/' . $file) && preg_match('%^[a-z0-9_/]+$%i', $file . '/')) {
                    $this->getControllerFiles($module, $namespace . $file . '\\', $prefix . $file . '/', $result);
                } elseif (strcmp(substr($file, -14), 'Controller.php') === 0) {
                    $baseName = substr(basename($file), 0, -14);
                    $name = strtolower(preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $baseName));
                    $id = ltrim(str_replace(' ', '-', $name), '-');
                    $className = $namespace . $baseName . 'Controller';
                    if (
                        is_subclass_of($className, Controller::class)
                        && class_exists($className)
                        && !str_contains($className, '-')
                    ) {
                        $this->getControllerActions($className, $prefix . $id, $module, $result);
                    }
                }
            }
        } catch (Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }

        Yii::endProfile($token, __METHOD__);
    }

    /**
     * Get list actions of controller
     *
     * @throws \ReflectionException
     */
    protected function getControllerActions(
        array|callable|string $type,
        string $id,
        Module $module,
        array &$result
    ): void {
        if (is_string($type) && (new ReflectionClass($type))->isAbstract()) {
            return;
        }
        $token = 'Create controller with config=' . VarDumper::dumpAsString($type) . " and id='$id'";
        Yii::beginProfile($token, __METHOD__);

        try {
            /* @var $controller Controller */
            $controller = Yii::createObject($type, [$id, $module]);
            $this->getActionRoutes($controller, $result);
            $all = "/$controller->uniqueId/*";
            $result[$all] = $all;
        } catch (Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }

        Yii::endProfile($token, __METHOD__);
    }

    /**
     * Get route of action
     *
     * @param Controller $controller
     * @param array      $result all controller action
     */
    protected function getActionRoutes(Controller $controller, array &$result): void
    {
        $token = "Get actions of controller '$controller->uniqueId'";
        Yii::beginProfile($token, __METHOD__);

        try {
            $prefix = "/$controller->uniqueId/";
            foreach ($controller->actions() as $id => $value) {
                $result[$prefix . $id] = $prefix . $id;
            }
            $class = new ReflectionClass($controller);

            foreach ($class->getMethods() as $method) {
                $name = $method->getName();
                if (
                    $name !== 'actions'
                    && $method->isPublic()
                    && !$method->isStatic()
                    && str_starts_with($name, 'action')
                ) {
                    $name = strtolower(preg_replace('/(?<![A-Z])[A-Z]/', ' \0', substr($name, 6)));
                    $id = $prefix . ltrim(str_replace(' ', '-', $name), '-');
                    $result[$id] = $id;
                }
            }
        } catch (Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }

        Yii::endProfile($token, __METHOD__);
    }
}
