<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter;

use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\RequiresPhp;

// PHP8.5 move to AttributeValueConverterMappingTest
#[RequiresPhp('>=8.5')]
final class AttributeWithCallableValueConverterMappingTest extends IntegrationTestCase
{
    public function test_can_use_converter_attribute_with_object_parameter_on_callable_parameter(): void
    {
        $callable = ConverterWithCallable::get();

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($callable, ['foo' => 'foo']);

        // @phpstan-ignore staticMethod.impossibleType (PHP8.5 remove)
        self::assertSame([
            'foo' => 'bar',
        ], $result);
    }
}
