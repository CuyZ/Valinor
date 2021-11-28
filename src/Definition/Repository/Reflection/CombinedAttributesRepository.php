<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\CombinedAttributes;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use Reflector;

final class CombinedAttributesRepository implements AttributesRepository
{
    private DoctrineAnnotationsRepository $doctrineAnnotationsFactory;

    private NativeAttributesRepository $nativeAttributesFactory;

    public function __construct()
    {
        $this->doctrineAnnotationsFactory = new DoctrineAnnotationsRepository();
        $this->nativeAttributesFactory = new NativeAttributesRepository();
    }

    public function for(Reflector $reflector): CombinedAttributes
    {
        return new CombinedAttributes(
            $this->doctrineAnnotationsFactory->for($reflector),
            $this->nativeAttributesFactory->for($reflector)
        );
    }
}
