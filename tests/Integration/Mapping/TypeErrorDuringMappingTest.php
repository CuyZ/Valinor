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
        $this->expectExceptionCode(1711526329);
        $this->expectExceptionMessage("Error while trying to map to `$class`: Types for property `$class::\$propertyWithNotMatchingTypes` do not match: `string` (docblock) does not accept `bool` (native).");

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
        $this->expectExceptionCode(1711526329);
        $this->expectExceptionMessage("Error while trying to map to `$class`: Types for parameter `$class::__construct(\$parameterWithNotMatchingTypes)` do not match: `string` (docblock) does not accept `bool` (native).");

        $this->mapperBuilder()->mapper()->map($class, ['parameterWithNotMatchingTypes' => true]);
    }

    public function test_function_parameter_with_non_matching_types_throws_exception(): void
    {
        $function =
            /**
             * @param string $parameterWithNotMatchingTypes
             */
            fn (bool $parameterWithNotMatchingTypes): string => 'foo';

        $this->expectException(TypeErrorDuringArgumentsMapping::class);
        $this->expectExceptionCode(1711534351);
        $this->expectExceptionMessageMatches("/Could not map arguments of `.*`: Types for parameter `.*` do not match: `string` \(docblock\) does not accept `bool` \(native\)\./");

        $this->mapperBuilder()->argumentsMapper()->mapArguments($function, ['parameterWithNotMatchingTypes' => true]);
    }
}
