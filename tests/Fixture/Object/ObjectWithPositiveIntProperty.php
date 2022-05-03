<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

final class ObjectWithPositiveIntProperty
{
    /**
     * @var positive-int
     */
    public int $value;

    /** @param positive-int $value */
    public function __construct(int $value = 1)
    {
        $this->value = $value;
    }
}
