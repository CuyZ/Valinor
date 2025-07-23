<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionAttributesRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionClassDefinitionRepository;
use CuyZ\Valinor\Tests\Fixture\Attribute\AttributeWithArguments;
use CuyZ\Valinor\Tests\Fixture\Attribute\BasicAttribute;
use CuyZ\Valinor\Tests\Fixture\Attribute\PropertyTargetAttribute;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionParameter;

final class ReflectionAttributesRepositoryTest extends TestCase
{
    public function test_only_allowed_attributes_are_selected(): void
    {
        $class =
            new #[BasicAttribute(), AttributeWithArguments('foo', 'bar')] class () {};

        $reflection = new ReflectionClass($class::class);

        $attributes = $this->attributesRepository(
            allowedAttributes: [AttributeWithArguments::class]
        )->for($reflection);

        self::assertCount(1, $attributes);
        self::assertSame(AttributeWithArguments::class, $attributes[0]->class->name);
    }

    public function test_attributes_for_promoted_parameter_with_property_target_attribute_is_not_selected(): void
    {
        $class = new class (true) {
            public function __construct(
                #[PropertyTargetAttribute] public bool $promotedProperty,
            ) {}
        };

        $reflection = new ReflectionParameter([$class::class, '__construct'], 'promotedProperty');

        $attributes = $this->attributesRepository(
            allowedAttributes: [PropertyTargetAttribute::class]
        )->for($reflection);

        self::assertSame([], $attributes);
    }

    /**
     * @param list<class-string> $allowedAttributes
     */
    private function attributesRepository(array $allowedAttributes = []): ReflectionAttributesRepository
    {
        return new ReflectionAttributesRepository(
            new ReflectionClassDefinitionRepository(new LexingTypeParserFactory(), $allowedAttributes),
            $allowedAttributes,
        );
    }
}
