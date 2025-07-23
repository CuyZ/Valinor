<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper;

use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Mapper\Exception\InvalidMappingTypeSignature;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Mapper\TypeTreeMapper;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Builder\FakeNodeBuilder;
use CuyZ\Valinor\Tests\Fake\Type\Parser\FakeTypeParser;
use PHPUnit\Framework\TestCase;

final class TypeTreeMapperTest extends TestCase
{
    private TypeTreeMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = new TypeTreeMapper(
            new FakeTypeParser(),
            new RootNodeBuilder(new FakeNodeBuilder()),
            new Settings(),
        );
    }

    public function test_invalid_mapping_type_signature_throws_exception(): void
    {
        $this->expectException(InvalidMappingTypeSignature::class);
        $this->expectExceptionCode(1630959692);
        $this->expectExceptionMessageMatches('/^Could not parse the type `foo` that should be mapped: .*/');

        $this->mapper->map('foo', []);
    }
}
