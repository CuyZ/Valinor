<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Tests\Fake\Mapper\Object\FakeObjectBuilder;

final class FakeObjectBuilderFactory implements ObjectBuilderFactory
{
    public function for(ClassDefinition $class): iterable
    {
        return [new FakeObjectBuilder()];
    }
}
