<?php

namespace common\enums;

use common\components\helpers\ModuleHelper;

/**
 * Class AppType
 *
 * @package common\enums
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
enum AppType: int implements DictionaryInterface
{
    use DictionaryTrait;

    case Undefined = 0;
    case Admin = 1;
    case Api = 2;
    case Frontend = 3;

    /**
     * Detect app type by module ID
     */
    public static function fromAppId(string $id): self
    {
        return match ($id) {
            ModuleHelper::ADMIN => self::Admin,
            ModuleHelper::API => self::Api,
            ModuleHelper::FRONTEND => self::Frontend,
            default => self::Undefined,
        };
    }

    /**
     * {@inheritdoc}
     */
    public function description(): string
    {
        return match ($this) {
            self::Undefined => 'Не задано',
            self::Admin => 'CMS',
            self::Api => 'API',
            self::Frontend => 'Сайт'
        };
    }

    /**
     * {@inheritdoc}
     */
    public function color(): string
    {
        return match ($this) {
            self::Undefined => 'var(--bs-gray-600)',
            self::Admin, self::Api, self::Frontend => 'var(--bs-body-color)'
        };
    }
}