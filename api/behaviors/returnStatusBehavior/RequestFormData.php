<?php

namespace api\behaviors\returnStatusBehavior;

use Attribute;
use OpenApi\Attributes\{AdditionalProperties, MediaType, RequestBody, Schema};

/**
 * Class RequestFormData
 *
 * @package api\behaviors\returnStatusBehavior
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
class RequestFormData extends RequestBody
{
    public function __construct(
        object|string|null $ref = null,
        ?string $request = null,
        ?string $description = null,
        ?bool $required = null,
        ?array $requiredProps = null,
        ?array $properties = null,
        ?array $x = null,
        ?array $attachables = null,
        ?AdditionalProperties $additionalProperties = null
    ) {
        $schema = new Schema(
            required:             $requiredProps,
            properties:           $properties,
            type:                 'object',
            additionalProperties: $additionalProperties
        );
        $content = [new MediaType(mediaType: 'multipart/form-data', schema: $schema)];
        parent::__construct($ref, $request, $description, $required, $content, $x, $attachables);
    }
}