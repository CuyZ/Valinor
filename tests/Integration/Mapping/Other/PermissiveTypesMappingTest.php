<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use DateTime;
use stdClass;

final class PermissiveTypesMappingTest extends IntegrationTestCase
{
    private TreeMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->mapperBuilder()->allowPermissiveTypes()->mapper();
    }

    public function test_can_map_to_mixed_type(): void
    {
        try {
            $result = $this->mapper->map('mixed[]', [
                'foo' => 'foo',
                'bar' => 'bar',
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo' => 'foo', 'bar' => 'bar'], $result);
    }

    public function test_can_map_to_undefined_object_type(): void
    {
        $source = [new stdClass(), new DateTime()];

        try {
            $result = $this->mapper->map('object[]', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_can_map_to_unsealed_shaped_array_without_type(): void
    {
        $source = ['foo' => 'foo', 'bar' => 'bar', 42 => 42];

        try {
            $result = $this->mapper->map('array{foo: string, ...}', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result); // @phpstan-ignore-line / PHPStan does not (yet) understand the unsealed shaped array syntax
    }

    public function test_can_map_to_unsealed_shaped_array_without_type_or_string(): void
    {
        $source = 'foo';

        try {
            $result = $this->mapper->map('array{foo: string, ...}|string', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }
}
