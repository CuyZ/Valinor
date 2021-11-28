<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper;

use CuyZ\Valinor\Mapper\Exception\InvalidMappingType;
use CuyZ\Valinor\Mapper\Exception\InvalidMappingTypeSignature;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Mapper\TreeMapperContainer;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Builder\FakeNodeBuilder;
use CuyZ\Valinor\Tests\Fake\Type\Parser\FakeTypeParser;
use PHPUnit\Framework\TestCase;

final class TreeMapperContainerTest extends TestCase
{
    private TreeMapperContainer $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = new TreeMapperContainer(
            new FakeTypeParser(),
            new RootNodeBuilder(new FakeNodeBuilder()),
        );
    }

    public function test_invalid_mapping_type_signature_throws_exception(): void
    {
        $this->expectException(InvalidMappingTypeSignature::class);
        $this->expectExceptionCode(1630959692);
        $this->expectExceptionMessageMatches('/^Could not parse the type `foo` that should be mapped: .*/');

        $this->mapper->map('foo', []); // @phpstan-ignore-line
    }

    public function test_invalid_mapping_type_throws_exception(): void
    {
        $this->expectException(InvalidMappingType::class);
        $this->expectExceptionCode(1630959731);
        $this->expectExceptionMessage("Can not map type `string`, it must be an object type.");

        $this->mapper->map('string', []); // @phpstan-ignore-line
    }
}
