<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

/** @internal */
final class ClassContextSpecification
{
    public function __construct(
        /** @var class-string */
        private string $className
    ) {
    }

    /**
     * @return class-string
     */
    public function className(): string
    {
        return $this->className;
    }
}
