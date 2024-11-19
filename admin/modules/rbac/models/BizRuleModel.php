<?php

namespace admin\modules\rbac\models;

use admin\modules\rbac\Module;
use Exception;
use Yii;
use yii\base\Model;
use yii\rbac\ManagerInterface;
use yii\rbac\Rule;

/**
 * Class BizRuleModel
 *
 * @package admin\modules\rbac\models
 *
 * @property-read null|Rule $item
 * @property-read bool      $isNewRecord
 */
class BizRuleModel extends Model
{
    /**
     * Name of the rule
     */
    public ?string $name = null;

    /**
     * UNIX timestamp representing the rule creation time
     */
    public int $createdAt;

    /**
     * UNIX timestamp representing the rule updating time
     */
    public int $updatedAt;

    /**
     * Rule className
     */
    public ?string $className = null;

    protected ManagerInterface $manager;

    private ?Rule $_item;

    /**
     * BizRuleModel constructor.
     */
    public function __construct(Rule $item = null, array $config = [])
    {
        $this->_item = $item;
        $this->manager = Yii::$app->authManager;

        if ($item !== null) {
            $this->name = $item->name;
            $this->className = get_class($item);
        }

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['name', 'className'], 'trim'],
            [['name', 'className'], 'required'],
            ['className', 'string'],
            ['name', 'string', 'max' => 64],
            ['className', 'classExists'],
        ];
    }

    /**
     * Validate className
     */
    public function classExists(): void
    {
        if (!class_exists($this->className)) {
            $message = Yii::t(Module::MODULE_MESSAGES, "Unknown class '{class}'", ['class' => $this->className]);
            $this->addError('className', $message);

            return;
        }

        if (!is_subclass_of($this->className, Rule::class)) {
            $message = Yii::t(Module::MODULE_MESSAGES, "'{class}' must extend from 'yii\\rbac\\Rule' or its child class", [
                'class' => $this->className, ]);
            $this->addError('className', $message);
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t(Module::MODULE_MESSAGES, 'Name'),
            'className' => Yii::t(Module::MODULE_MESSAGES, 'Class Name'),
        ];
    }

    /**
     * Check if record is new
     *
     * @return bool
     */
    public function getIsNewRecord(): bool
    {
        return $this->_item === null;
    }

    /**
     * Create object
     */
    public static function find(int $id): ?BizRuleModel
    {
        $item = Yii::$app->authManager->getRule($id);

        if ($item !== null) {
            return new static($item);
        }

        return null;
    }

    /**
     * Save rule
     *
     * @throws Exception
     */
    public function save(): bool
    {
        if ($this->validate()) {
            $class = $this->className;
            if ($this->_item === null) {
                $this->_item = new $class();
                $isNew = true;
                $oldName = false;
            } else {
                $isNew = false;
                $oldName = $this->_item->name;
            }

            $this->_item->name = $this->name;

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
     * @return null|Rule
     */
    public function getItem(): ?Rule
    {
        return $this->_item;
    }
}
