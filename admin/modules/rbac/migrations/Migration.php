<?php

namespace admin\modules\rbac\migrations;

use Exception;
use Yii;
use yii\base\{Component, InvalidArgumentException, InvalidConfigException};
use yii\db\MigrationInterface;
use yii\di\Instance;
use yii\rbac\{DbManager, Item, Permission, Role, Rule};

/**
 * Class Migration
 *
 * @package admin\modules\rbac\migrations
 */
class Migration extends Component implements MigrationInterface
{
    /**
     * The auth manager component ID that this migration should work with
     */
    public DbManager|string $authManager = 'authManager';

    /**
     * Initializes the migration.
     * This method will set [[authManager]] to be the 'authManager' application component, if it is `null`.
     *
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        $this->authManager = Instance::ensure($this->authManager, DbManager::class);

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function up(): bool
    {
        if ($transaction = $this->authManager->db->beginTransaction()) {
            try {
                if ($this->safeUp() === false) {
                    $transaction->rollBack();

                    return false;
                }
                $transaction->commit();
                $this->authManager->invalidateCache();

                return true;
            } catch (Exception $e) {
                echo "Rolling transaction back\n";
                echo 'Exception: ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ")\n";
                echo $e->getTraceAsString() . "\n";
                $transaction->rollBack();

                return false;
            }
        }
        echo "Failed to start transaction\n";
        return false;
    }

    /**
     * @inheritdoc
     */
    public function down(): bool
    {
        if ($transaction = $this->authManager->db->beginTransaction()) {
            try {
                if ($this->safeDown() === false) {
                    $transaction->rollBack();

                    return false;
                }
                $transaction->commit();
                $this->authManager->invalidateCache();

                return true;
            } catch (Exception $e) {
                echo "Rolling transaction back\n";
                echo 'Exception: ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ")\n";
                echo $e->getTraceAsString() . "\n";
                $transaction->rollBack();

                return false;
            }
        }
        echo "Failed to start transaction\n";
        return false;
    }

    /**
     * This method contains the logic to be executed when applying this migration.
     *
     * @return bool|void return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds
     */
    public function safeUp()
    {
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     *
     * @return bool|void return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds
     */
    public function safeDown()
    {
    }

    /**
     * Creates new permission.
     *
     * @param string      $name        The name of the permission
     * @param string      $description The description of the permission
     * @param string|null $ruleName    The rule associated with the permission
     * @param mixed|null  $data        The additional data associated with the permission
     *
     * @throws Exception
     */
    protected function createPermission(
        string $name,
        string $description = '',
        string $ruleName = null,
        mixed $data = null
    ): Permission {
        echo "    > create permission $name ...";
        $time = microtime(true);
        $permission = $this->authManager->createPermission($name);
        $permission->description = $description;
        $permission->ruleName = $ruleName;
        $permission->data = $data;
        $this->authManager->add($permission);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";

        return $permission;
    }

    /**
     * Creates new role.
     *
     * @param string      $name        The name of the role
     * @param string      $description The description of the role
     * @param string|null $ruleName    The rule associated with the role
     * @param mixed|null  $data        The additional data associated with the role
     *
     * @throws Exception
     */
    protected function createRole(
        string $name,
        string $description = '',
        string $ruleName = null,
        mixed $data = null
    ): Role {
        echo "    > create role $name ...";
        $time = microtime(true);
        $role = $this->authManager->createRole($name);
        $role->description = $description;
        $role->ruleName = $ruleName;
        $role->data = $data;
        $this->authManager->add($role);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";

        return $role;
    }

    /**
     * Creates new rule.
     *
     * @param string       $ruleName   The name of the rule
     * @param array|string $definition The class of the rule
     *
     * @throws InvalidConfigException
     */
    protected function createRule(string $ruleName, array|string $definition): Rule
    {
        echo "    > create rule $ruleName ...";
        $time = microtime(true);
        if (is_array($definition)) {
            $definition['name'] = $ruleName;
        } else {
            $definition = [
                'class' => $definition,
                'name' => $ruleName,
            ];
        }
        /** @var Rule $rule */
        $rule = Yii::createObject($definition);
        $this->authManager->add($rule);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";

        return $rule;
    }

    /**
     * Finds either role or permission or throws an exception if it is not found.
     */
    protected function findItem(string $name): Role|Permission|null
    {
        $item = $this->authManager->getRole($name);
        if ($item instanceof Role) {
            return $item;
        }
        $item = $this->authManager->getPermission($name);
        if ($item instanceof Permission) {
            return $item;
        }

        return null;
    }

    /**
     * Finds the role or throws an exception if it is not found.
     */
    protected function findRole(string $name): ?Role
    {
        $role = $this->authManager->getRole($name);
        if ($role instanceof Role) {
            return $role;
        }

        return null;
    }

    /**
     * Finds the permission or throws an exception if it is not found.
     */
    protected function findPermission(string $name): ?Permission
    {
        $permission = $this->authManager->getPermission($name);
        if ($permission instanceof Permission) {
            return $permission;
        }

        return null;
    }

    /**
     * Adds child.
     *
     * @param string|Item $parent Either name or Item instance which is parent
     * @param string|Item $child  Either name or Item instance which is child
     *
     * @throws \yii\base\Exception
     */
    protected function addChild(string|Item $parent, string|Item $child): void
    {
        if (is_string($parent)) {
            $parent = $this->findItem($parent);
        }
        if (is_string($child)) {
            $child = $this->findItem($child);
        }
        echo "    > adding $child->name as child to $parent->name ...";
        $time = microtime(true);
        $this->authManager->addChild($parent, $child);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * Removes child.
     *
     * @param string|Item $parent Either name or Item instance which is parent
     * @param string|Item $child  Either name or Item instance which is child
     */
    protected function removeChild(string|Item $parent, string|Item $child): void
    {
        if (is_string($parent)) {
            $parent = $this->findItem($parent);
        }
        if (is_string($child)) {
            $child = $this->findItem($child);
        }
        echo "    > removing $child->name from $parent->name ...";
        $time = microtime(true);
        $this->authManager->removeChild($parent, $child);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * Assigns a role to a user.
     *
     * @throws Exception
     */
    protected function assign(Role|string $role, int|string $userId): void
    {
        if (is_string($role)) {
            $role = $this->findRole($role);
        }
        echo "    > assigning $role->name to user $userId ...";
        $time = microtime(true);
        $this->authManager->assign($role, $userId);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * Updates role.
     *
     * @throws Exception
     */
    protected function updateRole(
        Role|string $role,
        string $description = '',
        string $ruleName = null,
        mixed $data = null
    ): Role {
        if (is_string($role)) {
            $role = $this->findRole($role);
        }
        echo "    > update role $role->name ...";
        $time = microtime(true);
        $role->description = $description;
        $role->ruleName = $ruleName;
        $role->data = $data;
        $this->authManager->update($role->name, $role);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";

        return $role;
    }

    /**
     * Remove role.
     *
     * @param string $name
     */
    protected function removeRole(string $name): void
    {
        $role = $this->authManager->getRole($name);
        if ($role === null) {
            throw new InvalidArgumentException("Role '$name' does not exists");
        }
        echo "    > removing role $role->name ...";
        $time = microtime(true);
        $this->authManager->remove($role);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * Updates permission.
     *
     * @throws Exception
     */
    protected function updatePermission(
        string|Permission $permission,
        string $description = '',
        string $ruleName = null,
        mixed $data = null
    ): Permission {
        if (is_string($permission)) {
            $permission = $this->findPermission($permission);
        }
        echo "    > update permission $permission->name ...";
        $time = microtime(true);
        $permission->description = $description;
        $permission->ruleName = $ruleName;
        $permission->data = $data;
        $this->authManager->update($permission->name, $permission);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";

        return $permission;
    }

    /**
     * Remove permission.
     */
    protected function removePermission(string $name): void
    {
        $permission = $this->authManager->getPermission($name);
        if ($permission === null) {
            throw new InvalidArgumentException("Permission '$name' does not exists");
        }
        echo "    > removing permission $permission->name ...";
        $time = microtime(true);
        $this->authManager->remove($permission);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * Updates rule.
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    protected function updateRule(string $ruleName, string $className): Rule
    {
        echo "    > update rule $ruleName ...";
        $time = microtime(true);
        /** @var Rule $rule */
        $rule = Yii::createObject(['class' => $className, 'name' => $ruleName]);
        $this->authManager->update($ruleName, $rule);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";

        return $rule;
    }

    /**
     * Remove rule.
     */
    protected function removeRule(string $ruleName): void
    {
        $rule = $this->authManager->getRule($ruleName);
        if (empty($rule)) {
            throw new InvalidArgumentException("Rule '$ruleName' does not exists");
        }
        echo "    > removing rule $rule->name ...";
        $time = microtime(true);
        $this->authManager->remove($rule);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }
}
