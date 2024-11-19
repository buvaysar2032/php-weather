<?php

namespace api\behaviors\returnStatusBehavior;

use Attribute;
use OpenApi\Attributes\{JsonContent, Property, Response};

/**
 * Class JsonError
 *
 * @package api\behaviors\returnStatusBehavior
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class JsonError extends Response
{
    public function __construct(
        object|string|null $ref = null,
        int|string $response = 500,
        ?string $description = 'Unknown error',
        ?array $headers = null,
        JsonContent|array|null $content = null,
        ?array $links = null,
        ?array $x = null,
        ?array $attachables = null
    ) {
        $data = [
            new Property(property: 'status', type: 'integer', example: $response),
            new Property(property: 'name', type: 'string', example: $description),
            new Property(property: 'message', properties: $content, type: 'object')
        ];
        $properties = [
            new Property(property: 'success', type: 'boolean', example: false),
            new Property(property: 'data', properties: $data, type: 'object')
        ];
        $content = new JsonContent(properties: $properties, type: 'object');
        parent::__construct($ref, $response, $description, $headers, $content, $links, $x, $attachables);
    }
}