<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\StaticAnalysis;

use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\Tests\StaticAnalysis\Stub\A;

/** @param mixed $input */
function mappingClassWillInferObjectOfSameType(TreeMapper $mapper, $input): A
{
    return $mapper->map(A::class, $input);
}
