<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures;

use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Bar as BarAlias;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Foo;
use DateTimeImmutable;
use stdClass as stdClassAlias;

final class ClassInSingleNamespace
{
    // @PHP8.0 promoted properties
    public Foo $classInNamespaceWithoutAlias;
    public BarAlias $classInNamespaceWithAlias;
    public DateTimeImmutable $classInRootNamespaceWithoutAlias;
    public stdClassAlias $classInRootNamespaceWithAlias;

    public function __construct(
        Foo $classInNamespaceWithoutAlias,
        BarAlias $classInNamespaceWithAlias,
        DateTimeImmutable $classInRootNamespaceWithoutAlias,
        stdClassAlias $classInRootNamespaceWithAlias
    ) {
        $this->classInNamespaceWithoutAlias = $classInNamespaceWithoutAlias;
        $this->classInNamespaceWithAlias = $classInNamespaceWithAlias;
        $this->classInRootNamespaceWithoutAlias = $classInRootNamespaceWithoutAlias;
        $this->classInRootNamespaceWithAlias = $classInRootNamespaceWithAlias;
    }
}
