<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Type\Resolver\Union;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Resolver\Union\UnionNarrower;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnionType;

final class FakeUnionNarrower implements UnionNarrower
{
    private Type $type;

    public function narrow(UnionType $unionType, $source): Type
    {
        return $this->type ?? new FakeType();
    }

    public function willReturn(Type $type): void
    {
        $this->type = $type;
    }
}
