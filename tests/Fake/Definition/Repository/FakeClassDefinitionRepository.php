<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition\Repository;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\ClassSignature;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Tests\Fake\Definition\FakeClassDefinition;

final class FakeClassDefinitionRepository implements ClassDefinitionRepository
{
    public function for(ClassSignature $signature): ClassDefinition
    {
        return FakeClassDefinition::new();
    }
}
