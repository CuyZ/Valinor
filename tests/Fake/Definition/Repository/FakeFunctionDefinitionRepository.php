<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition\Repository;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Tests\Fake\Definition\FakeFunctionDefinition;

final class FakeFunctionDefinitionRepository implements FunctionDefinitionRepository
{
    public function for(callable $function): FunctionDefinition
    {
        return FakeFunctionDefinition::new();
    }
}
