<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

use CuyZ\Valinor\Definition\ClassSignature;

interface ObjectType extends Type
{
    public function signature(): ClassSignature;
}
