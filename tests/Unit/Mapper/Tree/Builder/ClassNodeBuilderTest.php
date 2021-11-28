<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Builder;

use AssertionError;
use CuyZ\Valinor\Mapper\Tree\Builder\ClassNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeClassDefinitionRepository;
use CuyZ\Valinor\Tests\Fake\Mapper\Object\Factory\FakeObjectBuilderFactory;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use PHPUnit\Framework\TestCase;

final class ClassNodeBuilderTest extends TestCase
{
    public function test_invalid_type_fails_assertion(): void
    {
        $this->expectException(AssertionError::class);

        $builder = new ClassNodeBuilder(new FakeClassDefinitionRepository(), new FakeObjectBuilderFactory());
        (new RootNodeBuilder($builder))->build(Shell::root(new FakeType(), []));
    }
}
