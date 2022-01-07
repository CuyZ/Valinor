<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

/** @api */
interface CanCompile extends ObjectBuilder
{
    /**
     * @param string[] $arguments
     */
    public function compile(object $object, array $arguments): string;
}
