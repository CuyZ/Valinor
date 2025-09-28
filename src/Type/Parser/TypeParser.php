<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser;

use CuyZ\Valinor\Type\Type;

/** @internal */
interface TypeParser
{
    public function parse(string $raw): Type;
}
