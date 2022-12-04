<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory;

use CuyZ\Valinor\Type\Parser\Factory\Specifications\TypeParserSpecification;
use CuyZ\Valinor\Type\Parser\TypeParser;

/** @internal */
interface TypeParserFactory
{
    public function get(TypeParserSpecification ...$specifications): TypeParser;
}
