<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter;

use Attribute;
use Closure;
use CuyZ\Valinor\Mapper\AsConverter;

#[Attribute, AsConverter]
final class ConverterWithCallable
{
    public function __construct(
        private Closure $callback,
    ) {}

    /**
     * PHP8.5 move to:
     * @see \CuyZ\Valinor\Tests\Integration\Mapping\Converter\AttributeWithCallableValueConverterMappingTest::test_can_use_converter_attribute_with_object_parameter_on_callable_parameter
     */
    public static function get(): callable
    {
        return
            #[ConverterWithCallable(static function () { return ['foo' => 'bar']; })]
            function (string $foo) {};
    }

    /**
     * @template T
     * @param T $value
     * @return T
     */
    public function map(mixed $value): mixed
    {
        return ($this->callback)($value);
    }
}
