<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition;

use CuyZ\Valinor\Definition\AttributeDefinition;
use stdClass;

final class FakeAttributeDefinition
{
    /**
     * @param class-string $name
     */
    public static function new(string $name = stdClass::class): AttributeDefinition
    {
        return new AttributeDefinition(
            FakeClassDefinition::new($name),
            [],
            [
                'class',
                $name,
            ],
            0,
        );
    }
}
