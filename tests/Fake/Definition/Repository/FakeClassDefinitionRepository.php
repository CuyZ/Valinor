<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition\Repository;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Tests\Fake\Definition\FakeClassDefinition;
use CuyZ\Valinor\Type\ObjectType;

final class FakeClassDefinitionRepository implements ClassDefinitionRepository
{
    public function for(ObjectType $type): ClassDefinition
    {
        return FakeClassDefinition::new();
    }
}
