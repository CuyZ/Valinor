<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\Exception\TypeErrorDuringMapping;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Object\Constructor;
use CuyZ\Valinor\Mapper\Object\Exception\ObjectBuildersCollision;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

use function get_object_vars;

final class GenericFixedValueMappingTest extends IntegrationTestCase
{
    public function test_fixed_generic_values_are_given_to_constructor_when_mapping_scalar_source(): void
    {
        $class =
            (new /**
             * @template Min of int
             * @template Max of int
             */ class ('', 0, 0) {
                public function __construct(
                    public string $value,
                    /** @var Min */
                    public int $min,
                    /** @var Max */
                    public int $max,
                ) {}
            })::class;

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map("$class<50, 100>", 'some text');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([
            'value' => 'some text',
            'min' => 50,
            'max' => 100,
        ], get_object_vars($result));
    }

    public function test_fixed_generic_values_are_given_to_constructor_when_absent_from_array_source(): void
    {
        $class =
            (new /**
             * @template Min of int
             * @template Max of int
             */ class ('', 0, 0) {
                public function __construct(
                    public string $value,
                    /** @var Min */
                    public int $min,
                    /** @var Max */
                    public int $max,
                ) {}
            })::class;

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map("$class<50, 100>", ['value' => 'some text']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([
            'value' => 'some text',
            'min' => 50,
            'max' => 100,
        ], get_object_vars($result));
    }

    public function test_fixed_generic_values_present_in_source_are_unexpected_keys(): void
    {
        $class =
            (new /**
             * @template Min of int
             * @template Max of int
             */ class ('', 0, 0) {
                public function __construct(
                    public string $value,
                    /** @var Min */
                    public int $min,
                    /** @var Max */
                    public int $max,
                ) {}
            })::class;

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map("$class<50, 100>", [
                    'value' => 'some text',
                    'min' => 50,
                    'max' => 60,
                ]);

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'min' => '[unexpected_key] Unexpected key `min`.',
                'max' => '[unexpected_key] Unexpected key `max`.',
            ]);
        }
    }

    public function test_fixed_generic_values_present_in_source_are_ignored_when_superfluous_keys_are_allowed(): void
    {
        $class =
            (new /**
             * @template Min of int
             * @template Max of int
             */ class ('', 0, 0) {
                public function __construct(
                    public string $value,
                    /** @var Min */
                    public int $min,
                    /** @var Max */
                    public int $max,
                ) {}
            })::class;

        try {
            $result = $this->mapperBuilder()
                ->allowSuperfluousKeys()
                ->mapper()
                ->map("$class<50, 100>", [
                    'value' => 'some text',
                    'min' => -50,
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([
            'value' => 'some text',
            'min' => 50,
            'max' => 100,
        ], get_object_vars($result));
    }

    public function test_fixed_generic_values_are_given_to_properties(): void
    {
        $class =
            (new /**
             * @template Min of int
             * @template Max of int
             */ class () {
                public string $value;

                /** @var Min */
                public int $min;

                /** @var Max */
                public int $max;
            })::class;

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map("$class<50, 100>", 'some text');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([
            'value' => 'some text',
            'min' => 50,
            'max' => 100,
        ], get_object_vars($result));
    }

    public function test_fixed_generic_enum_case_is_given_to_property(): void
    {
        $class =
            (new /**
             * @template TFixed of BackedStringEnum
             * @template TEnum of BackedStringEnum
             */ class () {
                public string $value;

                /** @var TFixed */
                public BackedStringEnum $fixed;

                /** @var TEnum */
                public BackedStringEnum $enum;
            })::class;

        $type = "$class<" . BackedStringEnum::class . '::BAR, ' . BackedStringEnum::class . '>';

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($type, ['value' => 'some value', 'enum' => 'foo']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([
            'value' => 'some value',
            'fixed' => BackedStringEnum::BAR,
            'enum' => BackedStringEnum::FOO,
        ], get_object_vars($result));
    }

    public function test_generic_default_value_is_given_to_constructor(): void
    {
        $class =
            (new /**
             * @template Min of int
             * @template Max of int = 100
             */ class ('', 0, 0) {
                /**
                 * @param Min $min
                 * @param Max $max
                 */
                public function __construct(
                    public string $value,
                    public int $min,
                    public int $max,
                ) {}
            })::class;

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map("$class<50>", 'some text');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([
            'value' => 'some text',
            'min' => 50,
            'max' => 100,
        ], get_object_vars($result));
    }

    public function test_all_kinds_of_fixed_generic_values_are_given_to_constructor(): void
    {
        $class =
            (new /**
             * @template TString of string
             * @template TInteger of int
             * @template TFloat of float
             * @template TBoolean of bool
             * @template TNull
             * @template TEnum of BackedStringEnum
             */ class ('', '', 0, 0.0, false, null, BackedStringEnum::FOO) {
                public function __construct(
                    public string $value,
                    /** @var TString */
                    public string $string,
                    /** @var TInteger */
                    public int $integer,
                    /** @var TFloat */
                    public float $float,
                    /** @var TBoolean */
                    public bool $boolean,
                    /** @var TNull */
                    public mixed $null,
                    /** @var TEnum */
                    public BackedStringEnum $enum,
                ) {}
            })::class;

        $type = "$class<'foo', 42, 1337.42, true, null, " . BackedStringEnum::class . '::BAR>';

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($type, 'some value');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([
            'value' => 'some value',
            'string' => 'foo',
            'integer' => 42,
            'float' => 1337.42,
            'boolean' => true,
            'null' => null,
            'enum' => BackedStringEnum::BAR,
        ], get_object_vars($result));
    }

    public function test_fixed_generic_values_are_given_to_constructor_with_traversable_value(): void
    {
        $class =
            (new /** @template Min of int */ class ([], 0) {
                public function __construct(
                    /** @var list<string> */
                    public array $values,
                    /** @var Min */
                    public int $min,
                ) {}
            })::class;

        try {
            $mapper = $this->mapperBuilder()->mapper();

            $fromList = $mapper->map("$class<2>", ['foo', 'bar']);
            $fromArray = $mapper->map("$class<2>", [
                'values' => ['foo', 'bar'],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([
            'values' => ['foo', 'bar'],
            'min' => 2,
        ], get_object_vars($fromList));
        self::assertSame([
            'values' => ['foo', 'bar'],
            'min' => 2,
        ], get_object_vars($fromArray));
    }

    public function test_fixed_generic_values_are_given_to_constructor_with_attribute(): void
    {
        $class =
            (new /** @template Min of int */ class ('', 0) {
                public function __construct(
                    public string $value,
                    /** @var Min */
                    public int $min,
                ) {}

                /**
                 * @template T of int
                 * @param T $min
                 * @return self<T>
                 */
                #[Constructor]
                public static function create(string $value, int $min): self
                {
                    return new self("created: $value", $min);
                }
            })::class;

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map("$class<50>", 'some text');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([
            'value' => 'created: some text',
            'min' => 50,
        ], get_object_vars($result));
    }

    public function test_fixed_generic_values_are_given_to_registered_constructor(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->registerConstructor(TextFactory::create(...))
                ->mapper()
                ->map(TextWithRegisteredConstructor::class . '<50>', 'some text');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([
            'value' => 'created: some text',
            'min' => 50,
        ], get_object_vars($result));
    }

    public function test_fixed_generic_values_are_not_part_of_expected_signature(): void
    {
        $class =
            (new /** @template Min of int */ class ('', '', 0) {
                public function __construct(
                    public string $first,
                    public string $second,
                    /** @var Min */
                    public int $min,
                ) {}
            })::class;

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map("$class<50>", 'some text');

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[value_is_not_iterable] Value 'some text' does not match `array{first: string, second: string}`.",
            ]);
        }
    }

    public function test_single_fixed_generic_value_is_given_to_constructor_when_source_is_empty(): void
    {
        $class =
            (new /** @template T of int */ class (0) {
                public function __construct(
                    /** @var T */
                    public int $value,
                ) {}
            })::class;

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map("$class<42>", []);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([
            'value' => 42,
        ], get_object_vars($result));
    }

    public function test_single_fixed_generic_value_cannot_be_given_by_source(): void
    {
        $class =
            (new /** @template T of int */ class (0) {
                public function __construct(
                    /** @var T */
                    public int $value,
                ) {}
            })::class;

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map("$class<42>", 42);

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => '[value_is_not_iterable] Value 42 does not match `array{}`.',
            ]);
        }
    }

    public function test_constructors_colliding_once_fixed_generic_values_are_ignored_throws_exception(): void
    {
        $this->expectException(ObjectBuildersCollision::class);

        $this->mapperBuilder()
            ->registerConstructor(
                TextWithCollidingConstructors::class,
                CollidingTextFactory::create(...),
            )
            ->mapper()
            ->map(TextWithCollidingConstructors::class . '<50>', 'some text');
    }

    public function test_fixed_generic_values_are_given_to_constructor_with_self_referencing_argument(): void
    {
        $class =
            (new /** @template Min of int */ class ('', 0) {
                public function __construct(
                    /** @var self<Min>|string */
                    public self|string $value,
                    /** @var Min */
                    public int $min,
                ) {}
            })::class;

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map("$class<50>", 'some text');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([
            'value' => 'some text',
            'min' => 50,
        ], get_object_vars($result));
    }

    public function test_literal_value_not_coming_from_generic_is_not_given_to_constructor(): void
    {
        $class = (new class ('', 50) {
            public function __construct(
                public string $value,
                /** @var 50 */
                public int $min,
            ) {}
        })::class;

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map($class, ['value' => 'some text']);

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'min' => '[invalid_integer_value] Value *missing* does not match integer value 50.',
            ]);
        }
    }

    public function test_fixed_generic_value_not_matching_parameter_native_type_throws_exception(): void
    {
        $class =
            (new /** @template Min of int */ class ('', '') {
                public function __construct(
                    public string $value,
                    /** @valinor-var Min */
                    public string $min,
                ) {}
            })::class;

        $this->expectException(TypeErrorDuringMapping::class);
        $this->expectExceptionMessageMatches('/the type `50` for parameter `.*::__construct\(\$min\)` could not be resolved: `50` \(docblock\) does not accept `string` \(native\)\.$/');

        $this->mapperBuilder()
            ->mapper()
            ->map("$class<50>", ['value' => 'some text']);
    }

    public function test_fixed_generic_value_not_matching_property_native_type_throws_exception(): void
    {
        $class =
            (new /** @template Min of int */ class () {
                public string $value;

                /** @valinor-var Min */
                public string $min;
            })::class;

        $this->expectException(TypeErrorDuringMapping::class);
        $this->expectExceptionMessageMatches('/the type `50` for property `.*::\$min` could not be resolved: `50` \(docblock\) does not accept `string` \(native\)\.$/');

        $this->mapperBuilder()
            ->mapper()
            ->map("$class<50>", ['value' => 'some text']);
    }

    public function test_generic_value_within_union_is_not_given_to_constructor(): void
    {
        $class =
            (new /** @template Min of int */ class ('', null) {
                public function __construct(
                    public string $value,
                    /** @var Min|null */
                    public ?int $min,
                ) {}
            })::class;

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map("$class<50>", ['value' => 'some text']);

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'min' => '[missing_value] Cannot be empty and must be filled with a value matching 50, `null`.',
            ]);
        }
    }
}

/**
 * @template Min of int
 */
final class TextWithRegisteredConstructor
{
    public function __construct(
        public string $value,
        /** @var Min */
        public int $min,
    ) {}
}

final class TextFactory
{
    /**
     * @template T of int
     * @param T $min
     * @return TextWithRegisteredConstructor<T>
     */
    public static function create(string $value, int $min): TextWithRegisteredConstructor
    {
        return new TextWithRegisteredConstructor("created: $value", $min);
    }
}

/**
 * @template Min of int
 */
final class TextWithCollidingConstructors
{
    public function __construct(
        public string $value,
        /** @var Min */
        public int $min,
    ) {}
}

final class CollidingTextFactory
{
    /**
     * @pure
     * @return TextWithCollidingConstructors<50>
     */
    public static function create(string $value): TextWithCollidingConstructors
    {
        return new TextWithCollidingConstructors($value, 50);
    }
}
