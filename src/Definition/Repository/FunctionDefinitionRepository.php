<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository;

use CuyZ\Valinor\Definition\FunctionDefinition;

/** @internal */
interface FunctionDefinitionRepository
{
    public function for(callable $function): FunctionDefinition;
}
