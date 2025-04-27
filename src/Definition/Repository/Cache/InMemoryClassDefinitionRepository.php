<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Type\ObjectType;

/** @internal */
final class InMemoryClassDefinitionRepository implements ClassDefinitionRepository
{
    /** @var array<string, ClassDefinition> */
    private array $classDefinitions = [];

    public function __construct(
        private ClassDefinitionRepository $delegate,
    ) {}

    public function for(ObjectType $type): ClassDefinition
    {
        return $this->classDefinitions[$type->toString()] ??= $this->delegate->for($type);
    }
}
