<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

/** @internal */
final class ClassAliasSpecification
{
    /** @var class-string */
    private string $className;

    /**
     * @param class-string $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * @return class-string
     */
    public function className(): string
    {
        return $this->className;
    }
}
