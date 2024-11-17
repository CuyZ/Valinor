<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\MixedType;
use ReflectionMethod;
use ReflectionParameter;

use function array_map;

final class FakeMethodDefinition
{
    private function __construct() {}

    /**
     * @param non-empty-string $name
     */
    public static function new(string $name = 'someMethod'): MethodDefinition
    {
        return new MethodDefinition(
            $name,
            $name,
            new Attributes(),
            new Parameters(),
            false,
            true,
            new FakeType()
        );
    }

    public static function constructor(): MethodDefinition
    {
        return self::new('__construct');
    }

    public static function fromReflection(ReflectionMethod $reflection): MethodDefinition
    {
        $name = $reflection->name;
        $returnType = new MixedType();

        if ($reflection->hasReturnType()) {
            $returnType = FakeType::from($reflection->getReturnType()->getName()); // @phpstan-ignore-line
        }

        $parameters = array_map(
            static fn (ReflectionParameter $reflection) => FakeParameterDefinition::fromReflection($reflection),
            $reflection->getParameters()
        );

        return new MethodDefinition(
            $name,
            'Signature::' . $reflection->name,
            new Attributes(),
            new Parameters(...$parameters),
            $reflection->isStatic(),
            $reflection->isPublic(),
            $returnType
        );
    }
}
