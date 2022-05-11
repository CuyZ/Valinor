<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper;

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
        $this->expectExceptionCode(1_630_959_692);
        $this->expectExceptionMessageMatches('/^Could not parse the type `foo` that should be mapped: .*/');

        $this->mapper->map('foo', []);
    }
}
