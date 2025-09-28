<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\Exception\TypeErrorDuringArgumentsMapping;
use CuyZ\Valinor\Mapper\Exception\TypeErrorDuringMapping;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class TypeErrorDuringMappingTest extends IntegrationTestCase
{
    public function test_property_with_non_matching_types_throws_exception(): void
    {
        $class = (new class () {
            /**
             * @var string
             * @phpstan-ignore property.phpDocType
             */
            public bool $propertyWithNotMatchingTypes;
        })::class;

        $this->expectException(TypeErrorDuringMapping::class);
        $this->expectExceptionMessage("Error while trying to map to `$class`: the type `string` for property `$class::\$propertyWithNotMatchingTypes` could not be resolved: `string` (docblock) does not accept `bool` (native).");

        $this->mapperBuilder()->mapper()->map($class, ['propertyWithNotMatchingTypes' => true]);
    }

    public function test_parameter_with_non_matching_types_throws_exception(): void
    {
        $class = (new class (true) {
            /**
             * @param string $parameterWithNotMatchingTypes
             * @phpstan-ignore-next-line
             */
            public function __construct(public bool $parameterWithNotMatchingTypes) {}
        })::class;

        $this->expectException(TypeErrorDuringMapping::class);
        $this->expectExceptionMessage("Error while trying to map to `$class`: the type `string` for parameter `$class::__construct(\$parameterWithNotMatchingTypes)` could not be resolved: `string` (docblock) does not accept `bool` (native).");

        $this->mapperBuilder()->mapper()->map($class, ['parameterWithNotMatchingTypes' => true]);
    }

    public function test_property_with_unresolvable_type_throws_exception(): void
    {
        $class = (new class () {
            /** @var array<InvalidType> */
            public $propertyWithInvalidType; // @phpstan-ignore-line
        })::class;

        $this->expectException(TypeErrorDuringMapping::class);
        $this->expectExceptionMessage("Error while trying to map to `$class`: the type `array<InvalidType>` for property `$class::\$propertyWithInvalidType` could not be resolved: cannot parse unknown symbol `InvalidType`.");

        $this->mapperBuilder()->mapper()->map($class, 'foo');
    }

    public function test_parameter_with_unresolvable_type_throws_exception(): void
    {
        $class = (new class () {
            public function __construct(
                /** @var array<InvalidType> */
                public $parameterWithInvalidType = 'foo', // @phpstan-ignore-line
            ) {}
        })::class;

        $this->expectException(TypeErrorDuringMapping::class);
        $this->expectExceptionMessage("Error while trying to map to `$class`: the type `array<InvalidType>` for parameter `$class::__construct(\$parameterWithInvalidType)` could not be resolved: cannot parse unknown symbol `InvalidType`.");

        $this->mapperBuilder()->mapper()->map($class, 'foo');
    }

    public function test_function_parameter_with_non_matching_types_throws_exception(): void
    {
        $function =
            /**
             * @param string $parameterWithNotMatchingTypes
             */
            fn (bool $parameterWithNotMatchingTypes): string => 'foo';

        $this->expectException(TypeErrorDuringArgumentsMapping::class);
        $this->expectExceptionMessageMatches("/Could not map arguments of `.*`: the type `string` for parameter `.*` could not be resolved: `string` \(docblock\) does not accept `bool` \(native\)\./");

        $this->mapperBuilder()->argumentsMapper()->mapArguments($function, ['parameterWithNotMatchingTypes' => true]);
    }
}
