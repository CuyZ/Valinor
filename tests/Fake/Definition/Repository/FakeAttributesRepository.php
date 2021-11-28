<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition\Repository;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributes;
use Reflector;

final class FakeAttributesRepository implements AttributesRepository
{
    public function for(Reflector $reflector): Attributes
    {
        return new FakeAttributes();
    }
}
