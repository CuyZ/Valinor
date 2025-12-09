<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Http;

use Attribute;

/**
 * Marks a parameter or property to be mapped from query parameters of an HTTP
 * request.
 *
 * By default, each parameter marked with this attribute will be mapped from a
 * single query parameter with the same name:
 *
 * For more information, {@see \CuyZ\Valinor\Mapper\Http\HttpRequest}.
 *
 * @api
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final class FromQuery
{
    public function __construct(
        public readonly bool $mapAll = false,
    ) {}
}
