<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use DateTime;
use stdClass;

final class PermissiveTypesMappingTest extends IntegrationTestCase
{
    private TreeMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = (new MapperBuilder())->allowPermissiveTypes()->mapper();
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
}
