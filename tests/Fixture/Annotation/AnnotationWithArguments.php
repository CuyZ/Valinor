<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Annotation;

/**
 * @Annotation
 */
final class AnnotationWithArguments
{
    /** @var mixed[] */
    private array $value;

    /**
     * @param mixed[] $value
     */
    public function __construct(array $value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed[]
     */
    public function value(): array
    {
        return $this->value;
    }
}
