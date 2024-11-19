<?php

namespace admin\modules\rbac\models;

use admin\modules\rbac\Module;
use Exception;
use Yii;
use yii\base\Model;
use yii\helpers\Json;
use yii\rbac\{Item, ManagerInterface, Rule};

/**
 * Class AuthItemModel
 *
 * @property string       $name
 * @property int          $type
 * @property string       $description
 * @property string       $ruleName
 * @property string       $data
 * @property Item         $item
 * @property-read array[] $items
 * @property-read bool    $isNewRecord
 */
class AuthItemModel extends Model
{
    /**
     * Auth item name
     */
    public ?string $name = null;

    /**
     * Auth item type
     */
    public int $type;

    /**
     * Auth item description
     */
    public ?string $description = null;

    /**
     * Biz rule name
     */
    public ?string $ruleName = null;

    /**
     * Additional data
     */
    public ?string $data = null;

    protected ManagerInterface $manager;

    private ?Item $_item;

    /**
     * AuthItemModel constructor.
     */
    public function __construct(Item $item = null, array $config = [])
    {
        $this->_item = $item;
        $this->manager = Yii::$app->authManager;

        if ($item !== null) {
            $this->name = $item->name;
            $this->type = $item->type;
            $this->description = $item->description;
            $this->ruleName = $item->ruleName;
            $this->data = $item->data === null ? null : Json::encode($item->data);
        }

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name', 'description', 'data', 'ruleName'], 'trim'],
            [['name', 'type'], 'required'],
            ['ruleName', 'checkRule'],
            [
                'name',
                'validateName',
                'when' => fn () => $this->getIsNewRecord() || ($this->_item->name !== $this->name)
            ],
            ['type', 'integer'],
            [['description', 'data', 'ruleName'], 'default'],
            ['name', 'string', 'max' => 64],
        ];
    }

    /**
     * Validate item name
     */
    public function validateName(): void
    {
        $value = $this->name;
        if ($this->manager->getRole($value) !== null || $this->manager->getPermission($value) !== null) {
            $message = Yii::t('yii', '{attribute} "{value}" has already been taken.');
            $params = [
                'attribute' => $this->getAttributeLabel('name'),
                'value' => $value,
            ];
            $this->addError('name', Yii::$app->getI18n()->format($message, $params, Yii::$app->language));
        }
    }

    /**
     * Check for rule
     */
    public function checkRule(): void
    {
        $name = $this->ruleName;

        if (!$this->manager->getRule($name)) {
            try {
                $rule = Yii::createObject($name);
                if ($rule instanceof Rule) {
                    $rule->name = $name;
                    $this->manager->add($rule);
                } else {
                    $this->addError(
                        'ruleName',
                        Yii::t(Module::MODULE_MESSAGES, 'Invalid rule "{value}"', ['value' => $name])
                    );
                }
            } catch (Exception) {
                $this->addError(
                    'ruleName',
                    Yii::t(Module::MODULE_MESSAGES, 'Rule "{value}" does not exists', ['value' => $name])
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t(Module::MODULE_MESSAGES, 'Name'),
            'type' => Yii::t(Module::MODULE_MESSAGES, 'Type'),
            'description' => Yii::t(Module::MODULE_MESSAGES, 'Description'),
            'ruleName' => Yii::t(Module::MODULE_MESSAGES, 'Rule Name'),
            'data' => Yii::t(Module::MODULE_MESSAGES, 'Data'),
        ];
    }

    /**
     * Check if is new record.
     */
    public function getIsNewRecord(): bool
    {
        return $this->_item === null;
    }

    /**
     * Find role
     */
    public static function find(string $id): ?AuthItemModel
    {
        $item = Yii::$app->authManager->getRole($id);

        if ($item !== null) {
            return new self($item);
        }

        return null;
    }

    /**
     * Save role to [[\yii\rbac\authManager]]
     *
     * @throws Exception
     */
    public function save(): bool
    {
        if ($this->validate()) {
            if ($this->_item === null) {
                if ($this->type === Item::TYPE_ROLE) {
                    $this->_item = $this->manager->createRole($this->name);
                } else {
                    $this->_item = $this->manager->createPermission($this->name);
                }
                $isNew = true;
                $oldName = false;
            } else {
                $isNew = false;
                $oldName = $this->_item->name;
            }

            $this->_item->name = $this->name;
            $this->_item->description = $this->description;
            $this->_item->ruleName = $this->ruleName;
            $this->_item->data = Json::decode($this->data);

            if ($isNew) {
                $this->manager->add($this->_item);
            } else {
                $this->manager->update($oldName, $this->_item);
            }

            return true;
        }

        return false;
    }

    /**
     * Add child to Item
     *
     * @throws \yii\base\Exception
     */
    public function addChildren(array $items): bool
    {
        if ($this->_item) {
            foreach ($items as $name) {
                $child = $this->manager->getPermission($name);
                if ($child === null && $this->type === Item::TYPE_ROLE) {
                    $child = $this->manager->getRole($name);
                }
                $this->manager->addChild($this->_item, $child);
            }
        }

        return true;
    }

    /**
     * Remove child from an item
     */
    public function removeChildren(array $items): bool
    {
        if ($this->_item !== null) {
            foreach ($items as $name) {
                $child = $this->manager->getPermission($name);
                if ($child === null && $this->type === Item::TYPE_ROLE) {
                    $child = $this->manager->getRole($name);
                }
                $this->manager->removeChild($this->_item, $child);
            }
        }

        return true;
    }

    /**
     * Get all available and assigned roles, permission and routes
     */
    public function getItems(): array
    {
        $available = [];
        $assigned = [];

        if ($this->type === Item::TYPE_ROLE) {
            foreach (array_keys($this->manager->getRoles()) as $name) {
                $available[$name] = 'role';
            }
        }
        foreach (array_keys($this->manager->getPermissions()) as $name) {
            $available[$name] = $name[0] === '/' ? 'route' : 'permission';
        }

        foreach ($this->manager->getChildren($this->_item->name) as $item) {
            $assigned[$item->name] = $item->type === 1 ? 'role' : ($item->name[0] === '/' ? 'route' : 'permission');
            unset($available[$item->name]);
        }

        unset($available[$this->name]);

        return [
            'available' => $available,
            'assigned' => $assigned,
        ];
    }

    public function getItem(): ?Item
    {
        return $this->_item;
    }

    /**
     * Get type name
     */
    public static function getTypeName(int $type = null): array|string
    {
        $result = [
            Item::TYPE_PERMISSION => 'Permission',
            Item::TYPE_ROLE => 'Role',
        ];

        if ($type === null) {
            return $result;
        }

        return $result[$type];
    }
}
