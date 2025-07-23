<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\Properties;
use CuyZ\Valinor\Tests\Fake\Definition\FakePropertyDefinition;
use CuyZ\Valinor\Tests\Traits\IteratorTester;
use PHPUnit\Framework\TestCase;

use function array_values;

final class PropertiesTest extends TestCase
{
    use IteratorTester;

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

    public function test_properties_are_iterable(): void
    {
        $propertiesInstances = [
            'propertyA' => FakePropertyDefinition::new('propertyA'),
            'propertyB' => FakePropertyDefinition::new('propertyB'),
            'propertyC' => FakePropertyDefinition::new('propertyC'),
        ];

        $properties = new Properties(...array_values($propertiesInstances));

        $this->checkIterable($properties, $propertiesInstances);
    }
}
