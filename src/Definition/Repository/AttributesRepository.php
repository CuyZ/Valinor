<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository;

use CuyZ\Valinor\Definition\Attributes;
use Reflector;

interface AttributesRepository
{
    public function for(Reflector $reflector): Attributes;
}
