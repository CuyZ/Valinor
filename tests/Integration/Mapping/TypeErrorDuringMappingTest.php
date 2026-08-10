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

    public function test_template_with_empty_default_throws_exception(): void
    {
        $class =
            /**
             * @template T =
             */
            (new class () {
                /** @var T */
                public mixed $value; // @phpstan-ignore-line
            })::class;

        $this->expectException(TypeErrorDuringMapping::class);
        $this->expectExceptionMessage("Error while trying to map to `$class`: the type `T` for property `$class::\$value` could not be resolved: the template `T` in `$class` has no default type declared after `=`.");

        $this->mapperBuilder()->mapper()->map($class, ['value' => 'foo']);
    }

    public function test_required_template_after_defaulted_template_throws_exception(): void
    {
        $class =
            /**
             * @template A = string
             * @template B
             */
            (new class () {
                /** @var B */
                public mixed $b; // @phpstan-ignore-line
            })::class;

        $this->expectException(TypeErrorDuringMapping::class);
        $this->expectExceptionMessage("Error while trying to map to `$class<int>`: the type `B` for property `$class::\$b` could not be resolved: the template `B` in `$class` has no default type but is defined after the template `A` which declares one; templates with a default type must be defined last.");

        $this->mapperBuilder()->mapper()->map("$class<int>", ['b' => 'foo']);
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
