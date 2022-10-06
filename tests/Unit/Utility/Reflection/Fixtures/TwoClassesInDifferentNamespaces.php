<?php

declare(strict_types=1);

// A commented namespace should not be parsed
//
// namespace CuyZ\Valinor\Tests\Fixtures\WithAliasA {
//    use DateTime as SomeDateTimeAlias;
// }

namespace CuyZ\Valinor\Tests\Fixtures\WithAliasA {

    use CuyZ\Valinor\Tests\Fixtures\WithAliasB\ClassB;
    use CuyZ\Valinor\Tests\Fixtures\WithAliasB\ClassB as classBAlias;
    use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Bar as BarAlias;
    use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Foo;
    use DateTimeImmutable;
    use stdClass as stdClassAlias;

    function functionA(
        Foo $classInOtherFileWithoutAlias,
        BarAlias $classInOtherFileWithAlias,
        ClassB $classInSameFileWithoutAlias,
        classBAlias $classInSameFileWithAlias,
        DateTimeImmutable $classInRootNamespaceWithoutAlias,
        stdClassAlias $classInRootNamespaceWithAlias
    ): void {
    }

    class ClassA
    {
        // @PHP8.0 promoted properties
        public Foo $classInOtherFileWithoutAlias;
        public BarAlias $classInOtherFileWithAlias;
        public classB $classInSameFileWithoutAlias;
        public classBAlias $classInSameFileWithAlias;
        public DateTimeImmutable $classInRootNamespaceWithoutAlias;
        public stdClassAlias $classInRootNamespaceWithAlias;

        public function __construct(
            Foo $classInOtherFileWithoutAlias,
            BarAlias $classInOtherFileWithAlias,
            ClassB $classInSameFileWithoutAlias,
            classBAlias $classInSameFileWithAlias,
            DateTimeImmutable $classInRootNamespaceWithoutAlias,
            stdClassAlias $classInRootNamespaceWithAlias
        ) {
            $this->classInOtherFileWithoutAlias = $classInOtherFileWithoutAlias;
            $this->classInOtherFileWithAlias = $classInOtherFileWithAlias;
            $this->classInSameFileWithoutAlias = $classInSameFileWithoutAlias;
            $this->classInSameFileWithAlias = $classInSameFileWithAlias;
            $this->classInRootNamespaceWithoutAlias = $classInRootNamespaceWithoutAlias;
            $this->classInRootNamespaceWithAlias = $classInRootNamespaceWithAlias;
        }
    }
}

// First case of a duplicated namespace: the alias `AnotherDateTimeAlias` is not
// accessible in the second case below and should not be fetched by the parser.
namespace CuyZ\Valinor\Tests\Fixtures\WithAliasB {
    use DateTime as AnotherDateTimeAlias;
}

namespace CuyZ\Valinor\Tests\Fixtures\WithAliasB {

    use CuyZ\Valinor\Tests\Fixtures\WithAliasA\ClassA;
    use CuyZ\Valinor\Tests\Fixtures\WithAliasA\ClassA as classAAlias;
    use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Bar as BarAlias;
    use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Foo;
    use DateTimeImmutable;
    use stdClass as stdClassAlias;

    function functionB(
        Foo $classInOtherFileWithoutAlias,
        BarAlias $classInOtherFileWithAlias,
        ClassA $classInSameFileWithoutAlias,
        classAAlias $classInSameFileWithAlias,
        DateTimeImmutable $classInRootNamespaceWithoutAlias,
        stdClassAlias $classInRootNamespaceWithAlias
    ): void {
    }

    class ClassB
    {
        // @PHP8.0 promoted properties
        public Foo $classInOtherFileWithoutAlias;
        public BarAlias $classInOtherFileWithAlias;
        public classA $classInSameFileWithoutAlias;
        public classAAlias $classInSameFileWithAlias;
        public DateTimeImmutable $classInRootNamespaceWithoutAlias;
        public stdClassAlias $classInRootNamespaceWithAlias;

        public function __construct(
            Foo $classInOtherFileWithoutAlias,
            BarAlias $classInOtherFileWithAlias,
            ClassA $classInSameFileWithoutAlias,
            classAAlias $classInSameFileWithAlias,
            DateTimeImmutable $classInRootNamespaceWithoutAlias,
            stdClassAlias $classInRootNamespaceWithAlias
        ) {
            $this->classInOtherFileWithoutAlias = $classInOtherFileWithoutAlias;
            $this->classInOtherFileWithAlias = $classInOtherFileWithAlias;
            $this->classInSameFileWithoutAlias = $classInSameFileWithoutAlias;
            $this->classInSameFileWithAlias = $classInSameFileWithAlias;
            $this->classInRootNamespaceWithoutAlias = $classInRootNamespaceWithoutAlias;
            $this->classInRootNamespaceWithAlias = $classInRootNamespaceWithAlias;
        }
    }
}

// Third case of a duplicated namespace: the alias `YetAnotherDateTimeAlias` is
// not accessible in the second case above and should not be fetched by the
// parser.
namespace CuyZ\Valinor\Tests\Fixtures\WithAliasB {
    use DateTimeImmutable as YetAnotherDateTimeAlias;
}
