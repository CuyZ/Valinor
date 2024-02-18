<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Object\Constructor;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class EnumConstructorAttributeMappingTest extends IntegrationTestCase
{
    public function test_can_map_enum_with_native_constructor_when_other_constructors_with_attributes_have_patterns(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map(SomeEnumWithConstructorAttribute::class, 'FOO');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(SomeEnumWithConstructorAttribute::FOO, $result);
    }

    public function test_can_map_enum_with_constructor_method_with_attribute_and_pattern(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map(SomeEnumWithConstructorAttribute::class . '::BA*', 'Z');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(SomeEnumWithConstructorAttribute::BAZ, $result);
    }
}

enum SomeEnumWithConstructorAttribute: string
{
    case FOO = 'FOO';
    case FOZ = 'FOZ';
    case BAR = 'BAR';
    case BAZ = 'BAZ';

    /**
     * @return self::FO*
     */
    #[Constructor]
    public static function someConstructorWithFoPattern(string $value): self
    {
        /** @var self::FO* */
        return self::from('FO' . $value);
    }

    /**
     * @return self::BA*
     */
    #[Constructor]
    public static function someConstructorWithBaPattern(string $value): self
    {
        /** @var self::BA* */
        return self::from('BA' . $value);
    }
}
