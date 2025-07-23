<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Exception\ConverterHasInvalidCallableParameter;
use CuyZ\Valinor\Mapper\Tree\Exception\ConverterHasNoParameter;
use CuyZ\Valinor\Mapper\Tree\Exception\ConverterHasTooManyParameters;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use InvalidArgumentException;
use Throwable;

use function is_int;
use function is_string;

final class AttributeValueConverterMappingTest extends IntegrationTestCase
{
    public function test_cannot_use_unregistered_converter_attribute_on_property(): void
    {
        $class = new class () {
            #[UppercaseConverter]
            public string $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, 'foo');

            self::assertSame('foo', $result->value);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    public function test_can_use_registered_converter_attribute_on_property(): void
    {
        $class = new class () {
            #[UppercaseConverter]
            public string $value;
        };

        $result = $this->mapperBuilder()
            ->registerConverter(UppercaseConverter::class)
            ->mapper()
            ->map($class::class, 'foo');

        self::assertSame('FOO', $result->value);
    }

    public function test_can_use_self_registered_converter_attribute_on_property(): void
    {
        $class = new class () {
            #[UppercaseSelfRegisteredConverter]
            public string $value;
        };

        $result = $this->mapperBuilder()
            ->mapper()
            ->map($class::class, 'foo');

        self::assertSame('FOO', $result->value);
    }

    public function test_can_use_converter_on_class(): void
    {
        $class = new #[ClassConverter] class () {
            #[UppercaseSelfRegisteredConverter]
            public string $value;
        };

        $result = $this->mapperBuilder()
            ->mapper()
            ->map($class::class, 'foo');

        self::assertSame('FOO!', $result->value);
    }

    public function test_can_use_converter_on_callable(): void
    {
        $callable = #[FunctionConverter] function (
            #[UppercaseSelfRegisteredConverter]
            string $foo,
            int $bar,
        ) {};

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($callable, [
                'foo' => 'foo',
                'bar' => 42,
            ]);

        self::assertSame([
            'foo' => 'FOO!',
            'bar' => 43,
        ], $result);
    }

    public function test_can_use_converter_attribute_implementing_registered_interface(): void
    {
        $class = new class () {
            #[ConverterThatImplementsInterface]
            public string $value;
        };

        $result = $this->mapperBuilder()
            ->registerConverter(ConverterInterface::class)
            ->mapper()
            ->map($class::class, 'bar');

        self::assertSame('foo', $result->value);
    }

    public function test_attributes_are_handled_on_class_inferred_by_interface(): void
    {
        $result = $this->mapperBuilder()
            ->infer(SomeInterface::class, fn () => SomeClassThatImplementsInterface::class)
            ->mapper()
            ->map(SomeInterface::class, 'foo');

        self::assertInstanceOf(SomeClassThatImplementsInterface::class, $result);
        self::assertSame('foo!', $result->value);
    }

    public function test_converter_attribute_returning_invalid_value_makes_mapping_fail(): void
    {
        $class = new class () {
            /** @var non-empty-string */
            #[ConverterReturningEmptyString]
            public string $value;
        };

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map($class::class, 'foo');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[invalid_non_empty_string] Value '' is not a valid non-empty string.",
            ]);
        }
    }

    public function test_converter_attribute_throwing_message_makes_mapping_fail(): void
    {
        try {
            $class = new class () {
                #[ConverterThrowingMessage('some attribute error')]
                public string $value;
            };

            $this->mapperBuilder()
                ->mapper()
                ->map($class::class, 'foo');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[1652883436] some attribute error",
            ], assertErrorsBodiesAreRegistered: false);
        }
    }

    public function test_converter_attribute_throwing_unregistered_exception_throws_exception(): void
    {
        $class = new class () {
            #[ConverterThrowingException('some attribute error')]
            public string $value;
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('some attribute error');

        $this->mapperBuilder()
            ->mapper()
            ->map($class::class, 'foo');
    }

    public function test_converter_attribute_throwing_registered_exception_makes_mapping_fail(): void
    {
        try {
            $class = new class () {
                #[ConverterThrowingException('some attribute error')]
                public string $value;
            };

            $this->mapperBuilder()
                ->filterExceptions(fn (Throwable $exception) => MessageBuilder::from($exception))
                ->mapper()
                ->map($class::class, 'foo');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[1752075944] some attribute error",
            ], assertErrorsBodiesAreRegistered: false);
        }
    }

    public function test_converter_attribute_with_no_parameter_throws_exception(): void
    {
        $class = new class () {
            #[ConverterWithNoParameter]
            public string $value;
        };

        $this->expectException(ConverterHasNoParameter::class);
        $this->expectExceptionCode(1746449489);
        $this->expectExceptionMessageMatches('/The value converter `.*` has no parameter to convert the value to, a typed parameter is required\./');

        $this->mapperBuilder()
            ->mapper()
            ->map($class::class, 'foo');
    }

    public function test_converter_attribute_with_too_many_parameters_throws_exception(): void
    {
        $class = new class () {
            #[ConverterWithTooManyParameters]
            public string $value;
        };

        $this->expectException(ConverterHasTooManyParameters::class);
        $this->expectExceptionCode(1751296711);
        $this->expectExceptionMessageMatches('/Converter must have at most 2 parameters, 3 given for `.*`\./');

        $this->mapperBuilder()
            ->mapper()
            ->map($class::class, 'foo');
    }

    public function test_converter_attribute_with_invalid_callable_parameter_throws_exception(): void
    {
        $class = new class () {
            #[ConverterWithInvalidCallableParameter]
            public string $value;
        };

        $this->expectException(ConverterHasInvalidCallableParameter::class);
        $this->expectExceptionCode(1751296766);
        $this->expectExceptionMessageMatches('/Converter\'s second parameter must be a callable, `int` given for `.*`\./');

        $this->mapperBuilder()
            ->mapper()
            ->map($class::class, 'foo');
    }
}

#[Attribute]
final class UppercaseConverter
{
    /**
     * @param callable(string): string $next
     */
    public function map(string $value, callable $next): string
    {
        return strtoupper($next($value));
    }
}

#[Attribute, AsConverter]
final class UppercaseSelfRegisteredConverter
{
    /**
     * @param callable(string): string $next
     */
    public function map(string $value, callable $next): string
    {
        return strtoupper($next($value));
    }
}

#[Attribute, AsConverter]
final class ClassConverter
{
    /**
     * @param callable(string): object $next
     */
    public function map(string $value, callable $next): object
    {
        return $next($value . '!');
    }
}

#[Attribute, AsConverter]
final class FunctionConverter
{
    /**
     * @param array<mixed> $values
     * @param callable(array<mixed>): array<mixed> $next
     * @return array<mixed>
     */
    public function map(array $values, callable $next): array
    {
        foreach ($values as $key => $value) {
            if (is_string($value)) {
                $values[$key] = $value . '!';
            } elseif (is_int($value)) {
                $values[$key] = $value + 1;
            }
        }

        return $next($values);
    }
}

interface ConverterInterface {}

#[Attribute]
final class ConverterThatImplementsInterface implements ConverterInterface
{
    public function map(string $foo): string
    {
        return 'foo';
    }
}

interface SomeInterface {}

#[ClassConverter]
final class SomeClassThatImplementsInterface implements SomeInterface
{
    public string $value;
}

#[Attribute, AsConverter]
final class ConverterReturningEmptyString
{
    public function map(string $value): string
    {
        return '';
    }
}

#[Attribute, AsConverter]
final class ConverterThrowingMessage
{
    public function __construct(
        private string $message,
    ) {}

    public function map(string $value): string
    {
        throw new FakeErrorMessage($this->message);
    }
}

#[Attribute, AsConverter]
final class ConverterThrowingException
{
    public function __construct(
        private string $message,
    ) {}

    public function map(string $value): string
    {
        throw new InvalidArgumentException($this->message, 1752075944);
    }
}

#[Attribute, AsConverter]
final class ConverterWithNoParameter
{
    public function map(): string
    {
        return 'foo';
    }
}

#[Attribute, AsConverter]
final class ConverterWithTooManyParameters
{
    public function map(string $foo, callable $next, int $bar): string
    {
        return 'foo';
    }
}

#[Attribute, AsConverter]
final class ConverterWithInvalidCallableParameter
{
    public function map(string $foo, int $next): string
    {
        return 'foo';
    }
}
