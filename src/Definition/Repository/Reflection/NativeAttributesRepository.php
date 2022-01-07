<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\NativeAttributes;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use Reflector;

/** @internal */
final class NativeAttributesRepository implements AttributesRepository
{
    public function for(Reflector $reflector): NativeAttributes
    {
        return new NativeAttributes($reflector);
    }
}
