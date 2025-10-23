<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Http;

use Attribute;

/**
 * @todo add doc
 *
 * @api
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final class FromBody {}
