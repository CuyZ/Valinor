<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition\Repository;

use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributeDefinition;
use ReflectionAttribute;

final class FakeAttributesRepository implements AttributesRepository
{
    public function for(ReflectionAttribute $reflection): AttributeDefinition
    {
        return FakeAttributeDefinition::new();
    }
}
