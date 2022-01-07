<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\DoctrineAnnotations;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use Reflector;

/** @internal */
final class DoctrineAnnotationsRepository implements AttributesRepository
{
    public function for(Reflector $reflector): DoctrineAnnotations
    {
        return new DoctrineAnnotations($reflector);
    }
}
