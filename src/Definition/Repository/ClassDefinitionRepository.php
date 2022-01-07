<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\ClassSignature;

/** @internal */
interface ClassDefinitionRepository
{
    public function for(ClassSignature $signature): ClassDefinition;
}
