<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use Traversable;

/** @internal */
final class CombinedAttributes implements Attributes
{
    private DoctrineAnnotations $doctrineAnnotations;

    private ?NativeAttributes $nativeAttributes;

    private AttributesContainer $delegate;

    public function __construct(DoctrineAnnotations $doctrineAnnotations, ?NativeAttributes $nativeAttributes = null)
    {
        $this->nativeAttributes = $nativeAttributes;
        $this->doctrineAnnotations = $doctrineAnnotations;

        $attributes = $doctrineAnnotations;

        if ($nativeAttributes instanceof NativeAttributes) {
            $attributes = [...$attributes, ...$nativeAttributes];
        }

        $this->delegate = new AttributesContainer(...$attributes);
    }

    public function nativeAttributes(): ?NativeAttributes
    {
        return $this->nativeAttributes;
    }

    public function doctrineAnnotations(): DoctrineAnnotations
    {
        return $this->doctrineAnnotations;
    }

    public function has(string $className): bool
    {
        return $this->delegate->has($className);
    }

    public function ofType(string $className): iterable
    {
        return $this->delegate->ofType($className);
    }

    public function getIterator(): Traversable
    {
        yield from $this->delegate;
    }

    public function count(): int
    {
        return count($this->delegate);
    }
}
