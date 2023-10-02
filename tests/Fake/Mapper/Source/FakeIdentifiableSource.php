<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Source;

use CuyZ\Valinor\Mapper\Source\IdentifiableSource;

final class FakeIdentifiableSource implements IdentifiableSource
{
    public function __construct(private string $sourceName) {}

    public function sourceName(): string
    {
        return $this->sourceName;
    }
}
