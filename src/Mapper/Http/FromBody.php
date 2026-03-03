<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Http;

use Attribute;

/**
 * Marks a parameter or property to be mapped from body values of an HTTP
 * request.
 *
 * By default, each parameter marked with this attribute will be mapped from a
 * single body value with the same name.
 *
 * For more information, {@see HttpRequest}.
 *
 * @api
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final readonly class FromBody
{
    public function __construct(
        public bool $mapAll = false,
    ) {}
}
