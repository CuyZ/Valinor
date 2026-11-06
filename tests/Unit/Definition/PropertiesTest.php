<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\Properties;
use CuyZ\Valinor\Definition\PropertyDefinition;
use CuyZ\Valinor\Tests\Fake\Definition\FakePropertyDefinition;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Types\IntegerValueType;

use function array_values;
use function iterator_to_array;

final class PropertiesTest extends UnitTestCase
{
    public function test_property_can_be_found(): void
    {
        $property = FakePropertyDefinition::new();
        $properties = new Properties($property);

        self::assertFalse($properties->has('unknownProperty'));

        self::assertTrue($properties->has($property->name));
        self::assertSame($property, $properties->get($property->name));
    }

    public function test_properties_are_countable(): void
    {
        $properties = new Properties(
            FakePropertyDefinition::new('propertyA'),
            FakePropertyDefinition::new('propertyB'),
            FakePropertyDefinition::new('propertyC'),
        );

        self::assertCount(3, $properties);
    }

    public function test_fixed_properties_can_be_filtered_out(): void
    {
        $property = FakePropertyDefinition::new('notFixed');
        $properties = new Properties(
            $property,
            new PropertyDefinition(
                name: 'fixed',
                signature: 'someSignature',
                type: new IntegerValueType(42),
                nativeType: new IntegerValueType(42),
                hasDefaultValue: false,
                defaultValue: null,
                isPublic: true,
                attributes: new Attributes(),
                isFixed: true,
            ),
        );

        self::assertSame(['notFixed' => $property], iterator_to_array($properties->withoutFixed()));
    }

    public function test_properties_are_iterable(): void
    {
        $propertiesInstances = [
            'propertyA' => FakePropertyDefinition::new('propertyA'),
            'propertyB' => FakePropertyDefinition::new('propertyB'),
            'propertyC' => FakePropertyDefinition::new('propertyC'),
        ];

        $properties = new Properties(...array_values($propertiesInstances));

        self::assertSame($propertiesInstances, iterator_to_array($properties));
    }
}
