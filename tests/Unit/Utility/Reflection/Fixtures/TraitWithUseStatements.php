<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures;

use DateTimeImmutable;
use stdClass as stdClassAlias;

trait TraitWithUseStatements
{
    public function __construct(
        public DateTimeImmutable $classInRootNamespaceWithoutAlias,
        public stdClassAlias $classInRootNamespaceWithAlias
    ) {}
}
