<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\StaticAnalysis;

use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\Tests\StaticAnalysis\Stub\A;

function mappingClassWillInferObjectOfSameType(TreeMapper $mapper, mixed $input): A
{
    return $mapper->map(A::class, $input);
}
