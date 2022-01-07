<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;

/** @internal */
interface TypeParser
{
    /**
     * @throws InvalidType
     */
    public function parse(string $raw): Type;
}
