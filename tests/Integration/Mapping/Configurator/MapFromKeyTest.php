<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Configurator;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\Mapper\Configurator\MapFromKey;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Exception\SeveralAttributesMapToSameKey;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

use function strtolower;

final class MapFromKeyTest extends IntegrationTestCase
{
    public function test_maps_object_property_from_source_key(): void
    {
        $class = new class () {
            #[MapFromKey('another_key')]
            public string $someKey;

            public string $name;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, [
                    'another_key' => 'hello',
                    'name' => 'John',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result->someKey);
        self::assertSame('John', $result->name);
    }

    public function test_maps_constructor_argument_from_source_key(): void
    {
        $class = new class ('') {
            public function __construct(
                #[MapFromKey('another_key')]
                public string $someKey,
            ) {}
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['another_key' => 'hello']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result->someKey);
    }

    public function test_maps_single_property_object_from_array_source(): void
    {
        $class = new class () {
            #[MapFromKey('another_key')]
            public string $someKey;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['another_key' => 'hello']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result->someKey);
    }

    public function test_single_property_object_from_bare_scalar_ignores_map_from_key(): void
    {
        // A bare scalar passed to a single-property object is wrapped directly
        // under the property: there is no source key to remap, so the attribute
        // is a harmless no-op.
        $class = new class () {
            #[MapFromKey('another_key')]
            public string $someKey;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, 'hello');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result->someKey);
    }

    public function test_maps_from_source_key_in_nested_object(): void
    {
        $class = new class () {
            #[MapFromKey('another_key')]
            public string $someKey;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('array{parent: ' . $class::class . '}', [
                    'parent' => ['another_key' => 'hello'],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result['parent']->someKey);
    }

    public function test_maps_from_source_key_in_list_of_objects(): void
    {
        $class = new class () {
            #[MapFromKey('another_key')]
            public string $someKey;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('list<' . $class::class . '>', [
                    ['another_key' => 'foo'],
                    ['another_key' => 'bar'],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result[0]->someKey);
        self::assertSame('bar', $result[1]->someKey);
    }

    public function test_source_key_bypasses_global_key_converters(): void
    {
        // The global converter would lowercase the property name `myKey` to
        // `mykey`; the attribute-resolved key must not be re-converted, while
        // the other keys still go through the global converter.
        $class = new class () {
            #[MapFromKey('src')]
            public string $myKey;

            public string $other;
        };

        try {
            $result = $this->mapperBuilder()
                ->registerKeyConverter(fn (string $key): string => strtolower($key))
                ->mapper()
                ->map($class::class, [
                    'src' => 'hello',
                    'OTHER' => 'world',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result->myKey);
        self::assertSame('world', $result->other);
    }

    public function test_error_path_uses_source_key(): void
    {
        $class = new class () {
            #[MapFromKey('another_key')]
            public string $someKey;
        };

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['another_key' => 42]);

            self::fail('Expected MappingError');
        } catch (MappingError $error) {
            $this->assertMappingErrors($error, [
                'another_key' => '[invalid_string] Value 42 is not a valid string.',
            ]);
        }
    }

    public function test_error_path_uses_source_key_in_nested_object(): void
    {
        $class = new class () {
            #[MapFromKey('another_key')]
            public string $someKey;
        };

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map('array{parent: ' . $class::class . '}', [
                    'parent' => ['another_key' => 42],
                ]);

            self::fail('Expected MappingError');
        } catch (MappingError $error) {
            $this->assertMappingErrors($error, [
                'parent.another_key' => '[invalid_string] Value 42 is not a valid string.',
            ]);
        }
    }

    public function test_literal_property_name_key_is_unexpected_when_property_is_remapped(): void
    {
        // Property `a` is mapped from `b`; the property no longer accepts its
        // own name, so a literal `a` in the source is an unexpected key.
        $class = new class () {
            #[MapFromKey('b')]
            public string $a;
        };

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['a' => 'first', 'b' => 'second']);

            self::fail('Expected MappingError');
        } catch (MappingError $error) {
            $this->assertMappingErrors($error, [
                'a' => '[unexpected_key] Unexpected key `a`.',
            ]);
        }
    }

    public function test_property_name_key_is_unexpected_even_when_source_key_is_absent(): void
    {
        // Even when the mapped source key `b` is absent, the literal
        // property-name key `a` is not silently used as a fallback.
        $class = new class () {
            #[MapFromKey('b')]
            public string $a;
        };

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['a' => 'value']);

            self::fail('Expected MappingError');
        } catch (MappingError $error) {
            $this->assertMappingErrors($error, [
                'a' => '[unexpected_key] Unexpected key `a`.',
            ]);
        }
    }

    public function test_literal_property_name_key_is_silenced_when_superfluous_keys_allowed(): void
    {
        $class = new class () {
            #[MapFromKey('b')]
            public string $a;
        };

        try {
            $result = $this->mapperBuilder()
                ->allowSuperfluousKeys()
                ->mapper()
                ->map($class::class, ['a' => 'ignored', 'b' => 'kept']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('kept', $result->a);
    }

    public function test_two_properties_mapped_from_same_source_key_throws_exception(): void
    {
        // Two attributes resolving to the same source key is a configuration
        // error, thrown regardless of the source data, not a mapping error.
        $this->expectException(SeveralAttributesMapToSameKey::class);
        $this->expectExceptionMessage('Attributes on `a` and `b` both map from the source key `shared`.');

        $class = new class () {
            #[MapFromKey('shared')]
            public string $a;

            #[MapFromKey('shared')]
            public string $b;
        };

        $this->mapperBuilder()
            ->mapper()
            ->map($class::class, ['shared' => 'value']);
    }

    public function test_unattributed_property_declared_before_a_remapped_one(): void
    {
        // The unattributed `name` is declared *before* the remapped `someKey`;
        // resolution must skip `name` and still process `someKey`'s attribute.
        $class = new class () {
            public string $name;

            #[MapFromKey('another_key')]
            public string $someKey;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, [
                    'name' => 'John',
                    'another_key' => 'hello',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('John', $result->name);
        self::assertSame('hello', $result->someKey);
    }

    public function test_several_key_mapping_attributes_are_chained(): void
    {
        // Two different `mapKey` attributes chain in declaration order:
        // `MapFromKey` sets the key to `base`, then `MapWithPrefix` turns it
        // into `prefixed_base` (rather than restarting from the property name).
        $class = new class () {
            #[MapFromKey('base')]
            #[MapWithPrefix('prefixed_')]
            public string $key;

            public string $other;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, [
                    'prefixed_base' => 'value',
                    'other' => 'kept',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('value', $result->key);
        self::assertSame('kept', $result->other);
    }

    public function test_inference_argument_name_is_an_allowed_superfluous_key(): void
    {
        // The interface is inferred from a `type` argument; the concrete class
        // remaps its own `type` property from `actual`. The literal `type` key
        // consumed by the inference must be treated as an allowed superfluous
        // key, not reported as an unexpected key.
        try {
            $result = $this->mapperBuilder()
                ->infer(
                    SomeInferredInterface::class,
                    /** @return class-string<SomeInferredClass> */
                    static fn (string $type): string => SomeInferredClass::class,
                )
                ->mapper()
                ->map(SomeInferredInterface::class, [
                    'type' => 'inference-only',
                    'actual' => 'value',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(SomeInferredClass::class, $result);
        self::assertSame('value', $result->type);
    }

    public function test_maps_property_using_custom_prefix_attribute(): void
    {
        $class = new class () {
            #[MapWithPrefix('input_')]
            public string $firstName;

            #[MapWithPrefix('input_')]
            public string $lastName;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, [
                    'input_firstName' => 'John',
                    'input_lastName' => 'Doe',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('John', $result->firstName);
        self::assertSame('Doe', $result->lastName);
    }

    public function test_custom_prefix_attribute_error_path_uses_prefixed_key(): void
    {
        $class = new class () {
            #[MapWithPrefix('input_')]
            public string $firstName;
        };

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['input_firstName' => 42]);

            self::fail('Expected MappingError');
        } catch (MappingError $error) {
            $this->assertMappingErrors($error, [
                'input_firstName' => '[invalid_string] Value 42 is not a valid string.',
            ]);
        }
    }
}

/**
 * A custom key-mapping attribute that reads a property from a source key built
 * by prefixing the property name. Demonstrates the `mapKey(string): string`
 * protocol as a composable transform (as opposed to the absolute override of
 * {@see MapFromKey}).
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER), AsConverter]
final class MapWithPrefix
{
    public function __construct(
        private string $prefix,
    ) {}

    public function mapKey(string $key): string
    {
        return $this->prefix . $key;
    }
}

interface SomeInferredInterface {}

final class SomeInferredClass implements SomeInferredInterface
{
    #[MapFromKey('actual')]
    public string $type;
}
