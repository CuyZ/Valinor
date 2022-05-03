<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

final class ObjectWithNonEmptyStringProperty
{
    /**
     * @var non-empty-string
     */
    public string $value;

    /**
     * @param non-empty-string $value
     */
    public function __construct(string $value = 'foo')
    {
        $this->value = $value;
    }
}
