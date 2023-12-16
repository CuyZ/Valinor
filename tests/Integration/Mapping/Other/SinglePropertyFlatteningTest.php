<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

class SinglePropertyFlatteningTest extends IntegrationTest
{
    private TreeMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = (new MapperBuilder())->disableSinglePropertyFlattening()->mapper();
    }

    public function test_single_property_object_flattening_disabled(): void
    {
        $source = [
            'foo' => [
                'fiz' => 1337.404,
            ],
            'bar' => 'bar',
        ];

        try {
            $result = $this->mapper->map(SomeFooAndBarObject::class, $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(SomeFooAndBarObject::class, $result);
        self::assertInstanceOf(SomeFizObject::class, $result->foo);
    }
}

final class SomeFizObject
{
    public float $fiz;
}

final class SomeFooAndBarObject
{
    public SomeFizObject $foo;

    public string $bar;
}
