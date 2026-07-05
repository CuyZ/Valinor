<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\TestWith;

final class AllowCastingToScalarMappingTest extends IntegrationTestCase
{
    #[TestWith([1, true])]
    #[TestWith(['1', true])]
    #[TestWith(['true', true])]
    #[TestWith([0, false])]
    #[TestWith(['0', false])]
    #[TestWith(['false', false])]
    public function test_allow_casting_to_boolean_casts_value(mixed $value, bool $expected): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowCastingToBoolean()
                ->mapper()
                ->map('bool', $value);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($expected, $result);
    }

    #[TestWith([1, true])]
    #[TestWith(['1', true])]
    #[TestWith(['true', true])]
    #[TestWith([0, false])]
    #[TestWith(['0', false])]
    #[TestWith(['false', false])]
    public function test_allow_scalar_value_casting_casts_boolean_with_default_representations(mixed $value, bool $expected): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowScalarValueCasting()
                ->mapper()
                ->map('bool', $value);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($expected, $result);
    }

    public function test_allow_casting_to_boolean_uses_custom_representations(): void
    {
        try {
            $mapper = $this->mapperBuilder()
                ->allowCastingToBoolean(['yes', 'on'], ['no', 'off'])
                ->mapper();

            $isTrue = $mapper->map('bool', 'on');
            $isFalse = $mapper->map('bool', 'off');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertTrue($isTrue);
        self::assertFalse($isFalse);
    }

    public function test_allow_casting_to_boolean_with_custom_representations_replaces_defaults(): void
    {
        // Overriding the representations replaces the defaults entirely: the
        // default `'true'`/`'false'` values are no longer recognized.
        try {
            $this->mapperBuilder()
                ->allowCastingToBoolean(['yes'], ['no'])
                ->mapper()
                ->map('bool', 'true');

            self::fail('Expected a mapping error to be raised.');
        } catch (MappingError $error) {
            self::assertMappingErrors($error, [
                '*root*' => "[invalid_boolean] Value 'true' is not a valid boolean.",
            ]);
        }
    }

    #[TestWith([1, true])]
    #[TestWith(['on', true])]
    #[TestWith([0, false])]
    #[TestWith(['off', false])]
    public function test_allow_casting_to_boolean_uses_custom_integer_representations(mixed $value, bool $expected): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowCastingToBoolean([1, 'on'], [0, 'off'])
                ->mapper()
                ->map('bool', $value);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($expected, $result);
    }

    public function test_allow_scalar_value_casting_uses_custom_representations(): void
    {
        try {
            $mapper = $this->mapperBuilder()
                ->allowScalarValueCasting(['yes'], ['no'])
                ->mapper();

            $isTrue = $mapper->map('bool', 'yes');
            $isFalse = $mapper->map('bool', 'no');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertTrue($isTrue);
        self::assertFalse($isFalse);
    }

    public function test_allow_casting_to_integer_casts_value(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowCastingToInteger()
                ->mapper()
                ->map('int', '42');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(42, $result);
    }

    #[TestWith([true, 'Value true is not a valid integer.'])]
    #[TestWith([false, 'Value false is not a valid integer.'])]
    public function test_allow_casting_to_integer_does_not_cast_boolean(bool $value, string $error): void
    {
        try {
            $this->mapperBuilder()
                ->allowCastingToInteger()
                ->mapper()
                ->map('int', $value);

            self::fail('Expected a mapping error to be raised.');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, ['*root*' => "[invalid_integer] $error"]);
        }
    }

    public function test_allow_casting_to_float_casts_value(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowCastingToFloat()
                ->mapper()
                ->map('float', '1337.42');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(1337.42, $result);
    }

    #[TestWith([true, 'Value true is not a valid float.'])]
    #[TestWith([false, 'Value false is not a valid float.'])]
    public function test_allow_casting_to_float_does_not_cast_boolean(bool $value, string $error): void
    {
        try {
            $this->mapperBuilder()
                ->allowCastingToFloat()
                ->mapper()
                ->map('float', $value);

            self::fail('Expected a mapping error to be raised.');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, ['*root*' => "[invalid_float] $error"]);
        }
    }

    public function test_allow_casting_to_string_casts_value(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowCastingToString()
                ->mapper()
                ->map('string', 42);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('42', $result);
    }

    public function test_allow_casting_to_boolean_leaves_valid_scalar_supertype_untouched(): void
    {
        // Enabling a single caster and mapping to a broad scalar target used to
        // raise an uncatchable `TypeError`; the already-valid value must be
        // returned untouched instead.
        try {
            $result = $this->mapperBuilder()
                ->allowCastingToBoolean()
                ->mapper()
                ->map('scalar', 5);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(5, $result);
    }

    public function test_allow_casting_to_integer_does_not_crash_on_broad_array_key_target(): void
    {
        // A value the caster cannot convert is handed over untouched; mapping it
        // to the broad `array-key` target must not raise an uncatchable
        // `TypeError`.
        try {
            $result = $this->mapperBuilder()
                ->allowCastingToInteger()
                ->mapper()
                ->map('array-key', 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result);
    }
}
