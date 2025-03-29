<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition\Repository;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Tests\Fake\Definition\FakeFunctionDefinition;

final class FakeFunctionDefinitionRepository implements FunctionDefinitionRepository
{
    public int $callCount = 0;

    public function for(callable $function): FunctionDefinition
    {
        $this->callCount++;

        return FakeFunctionDefinition::new();
    }
}
