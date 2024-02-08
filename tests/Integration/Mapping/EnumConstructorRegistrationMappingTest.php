<?php

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

class EnumConstructorRegistrationMappingTest extends IntegrationTestCase
{
    public function test_constructor_with_no_argument_is_called_when_no_value_is_given(): void
    {
        try {
            $result = (new MapperBuilder())
                ->registerConstructor(
                    BackedStringEnum::class,
                    fn (): BackedStringEnum => BackedStringEnum::FOO
                )
                ->mapper()
                ->map(BackedStringEnum::class, []);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(BackedStringEnum::FOO, $result);
    }

    public function test_constructor_with_no_argument_is_not_called_when_value_is_given(): void
    {
        try {
            $result = (new MapperBuilder())
                ->registerConstructor(
                    BackedStringEnum::class,
                    fn (): BackedStringEnum => BackedStringEnum::FOO
                )
                ->mapper()
                ->map(BackedStringEnum::class, 'bar');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(BackedStringEnum::BAR, $result);
    }

    public function test_constructor_with_one_argument_is_called_when_one_value_is_given(): void
    {
        try {
            $result = (new MapperBuilder())
                ->registerConstructor(fn (string $value): BackedStringEnum => BackedStringEnum::from($value))
                ->mapper()
                ->map(BackedStringEnum::class, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(BackedStringEnum::FOO, $result);
    }

    public function test_constructor_with_several_arguments_is_called_when_values_are_given(): void
    {
        try {
            $result = (new MapperBuilder())
                ->registerConstructor(fn (string $foo, int $bar): BackedStringEnum => BackedStringEnum::FOO)
                ->mapper()
                ->map(BackedStringEnum::class, [
                    'foo' => 'foo',
                    'bar' => 42,
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(BackedStringEnum::FOO, $result);
    }

    public function test_registered_native_constructor_is_called_when_one_argument_is_given(): void
    {
        try {
            $result = (new MapperBuilder())
                ->registerConstructor(
                    BackedStringEnum::class,
                    fn (string $foo, int $bar): BackedStringEnum => BackedStringEnum::BAR
                )
                ->mapper()
                ->map(BackedStringEnum::class, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(BackedStringEnum::FOO, $result);
    }

    public function test_map_to_enum_pattern_fetches_correct_constructor(): void
    {
        try {
            $result = (new MapperBuilder())
                ->registerConstructor(
                    /**
                     * @return BackedStringEnum::BA*
                     */
                    fn (string $value): BackedStringEnum => BackedStringEnum::BAR,

                    /**
                     * @return BackedStringEnum::FO*
                     */
                    fn (string $value): BackedStringEnum => BackedStringEnum::FOO
                )
                ->mapper()
                ->map(BackedStringEnum::class . '::BA*', 'fiz');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(BackedStringEnum::BAR, $result);
    }

    public function test_register_internal_from_constructor_is_overridden_by_library_constructor(): void
    {
        try {
            (new MapperBuilder())
                ->registerConstructor(BackedStringEnum::from(...))
                ->mapper()
                ->map(BackedStringEnum::class, 'fiz');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1607027306', $error->code());
            self::assertSame("Value 'fiz' does not match any of 'foo', 'bar', 'baz'.", (string)$error);
        }
    }

    public function test_register_internal_try_from_constructor_is_overridden_by_library_constructor(): void
    {
        try {
            (new MapperBuilder())
                ->registerConstructor(BackedStringEnum::tryFrom(...))
                ->mapper()
                ->map(BackedStringEnum::class, 'fiz');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1607027306', $error->code());
            self::assertSame("Value 'fiz' does not match any of 'foo', 'bar', 'baz'.", (string)$error);
        }
    }
}
