<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Source;

use CuyZ\Valinor\Mapper\Source\IdentifiableSource;

final class FakeIdentifiableSource implements IdentifiableSource
{
    private string $sourceName;

    public function __construct(string $sourceName)
    {
        $this->sourceName = $sourceName;
    }

    public function sourceName(): string
    {
        return $this->sourceName;
    }
}
