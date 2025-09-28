<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\PropertyTypeResolver;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ReflectionTypeResolver;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class PropertyTypeResolverTest extends TestCase
{
    private PropertyTypeResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new PropertyTypeResolver(
            new ReflectionTypeResolver(
                (new TypeParserFactory())->buildDefaultTypeParser(),
                (new TypeParserFactory())->buildAdvancedTypeParserForClass(self::class),
            ),
        );
    }

    #[DataProvider('property_type_is_resolved_properly_data_provider')]
    public function test_property_type_is_resolved_properly(ReflectionProperty $reflection, string $expectedType): void
    {
        $type = $this->resolver->resolveTypeFor($reflection);

        self::assertNotInstanceOf(UnresolvableType::class, $type);
        self::assertSame($expectedType, $type->toString());
    }

    public static function property_type_is_resolved_properly_data_provider(): iterable
    {
        yield 'phpdoc @var' => [
            new ReflectionProperty(new class () {
                /** @var string */
                public $foo;
            }, 'foo'),
            'string',
        ];

        yield 'phpdoc @var followed by new line' => [
            new ReflectionProperty(new class () {
                /**
                 * @var string
                 *
                 */
                public $foo;
            }, 'foo'),
            'string',
        ];

        yield 'phpdoc @var with comment' => [
            new ReflectionProperty(new class () {
                /**
                 * @var string Some comment
                 */
                public $foo;
            }, 'foo'),
            'string',
        ];

        yield 'psalm @var standalone' => [
            new ReflectionProperty(new class () {
                /** @psalm-var string */
                public $foo;
            }, 'foo'),
            'string',
        ];

        yield 'psalm @var leading' => [
            new ReflectionProperty(new class () {
                /**
                 * @psalm-var non-empty-string
                 * @var string
                 */
                public $foo;
            }, 'foo'),
            'non-empty-string',
        ];

        yield 'psalm @var trailing' => [
            new ReflectionProperty(new class () {
                /**
                 * @var string
                 * @psalm-var non-empty-string
                 */
                public $foo;
            }, 'foo'),
            'non-empty-string',
        ];

        yield 'phpstan @var standalone' => [
            new ReflectionProperty(new class () {
                /** @phpstan-var string */
                public $foo;
            }, 'foo'),
            'string',
        ];

        yield 'phpstan @var leading' => [
            new ReflectionProperty(new class () {
                /**
                 * @phpstan-var non-empty-string
                 * @var string
                 */
                public $foo;
            }, 'foo'),
            'non-empty-string',
        ];

        yield 'phpstan @var trailing' => [
            new ReflectionProperty(new class () {
                /**
                 * @var string
                 * @phpstan-var non-empty-string
                 */
                public $foo;
            }, 'foo'),
            'non-empty-string',
        ];

        yield 'phpstan @var trailing after psalm' => [
            new ReflectionProperty(new class () {
                /**
                 * @psalm-var string
                 * @phpstan-var non-empty-string
                 */
                public $foo;
            }, 'foo'),
            'non-empty-string',
        ];

        yield 'docBlock present but no @var annotation' => [
            new ReflectionProperty(new class () {
                /**
                 * Some comment
                 */
                public string $foo;
            }, 'foo'),
            'string',
        ];
    }
}
