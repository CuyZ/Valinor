<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;
use Stringable;

use function implode;
use function ltrim;

final class ClassSignature implements Stringable
{
    /** @var class-string */
    private string $className;

    /** @var array<string, Type> */
    private array $generics;

    private string $signature;

    /**
     * @param class-string $className
     * @param array<string, Type> $generics
     */
    public function __construct(string $className, array $generics = [])
    {
        $this->className = ltrim($className, '\\'); // @phpstan-ignore-line
        $this->generics = $generics;
        $this->signature = empty($this->generics)
            ? $this->className
            : $this->className . '<' . implode(', ', $this->generics) . '>';
    }

    /**
     * @return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * @return array<string, Type>
     */
    public function generics(): array
    {
        return $this->generics;
    }

    public function toString(): string
    {
        return $this->signature;
    }

    public function __toString(): string
    {
        return $this->signature;
    }
}
