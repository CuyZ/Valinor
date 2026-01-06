<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection\TypeResolver;

use Countable;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ReflectionTypeResolver;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use DateTime;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use ReflectionType;

final class ReflectionTypeResolverTest extends TestCase
{
    private ReflectionTypeResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new ReflectionTypeResolver(
            (new TypeParserFactory())->buildDefaultTypeParser(),
            (new TypeParserFactory())->buildDefaultTypeParser(),
        );
    }

    #[DataProvider('native_type_is_resolved_properly_data_provider')]
    public function test_native_type_is_resolved_properly(ReflectionType $reflectionType, string $expectedType): void
    {
        $type = $this->resolver->resolveNativeType($reflectionType);

        self::assertSame($expectedType, $type->toString());
    }

    public static function native_type_is_resolved_properly_data_provider(): iterable
    {
        yield 'scalar type' => [
            'reflectionType' => (new ReflectionProperty(
                new class () {
                    public string $someProperty;
                },
                'someProperty'
            )
            )->getType(),
            'expectedType' => 'string',
        ];

        yield 'nullable scalar type' => [
            'reflectionType' => (new ReflectionProperty(
                new class () {
                    public ?string $someProperty = null;
                },
                'someProperty'
            )
            )->getType(),
            'expectedType' => 'string|null',
        ];

        yield 'union type' => [
            'reflectionType' => (new ReflectionProperty(
                new class () {
                    public int|float $someProperty;
                },
                'someProperty'
            )
            )->getType(),
            'expectedType' => 'int|float',
        ];

        yield 'mixed type' => [
            'reflectionType' => (new ReflectionProperty(
                new class () {
                    public mixed $someProperty;
                },
                'someProperty'
            )
            )->getType(),
            'expectedType' => 'mixed',
        ];

        yield 'intersection type' => [
            'reflectionType' => (new ReflectionProperty(
                new class () {
                    /** @var Countable&Iterator<mixed> */
                    public Countable&Iterator $someProperty;
                },
                'someProperty'
            )
            )->getType(),
            'expectedType' => 'Countable&Iterator',
        ];

        yield 'disjunctive normal form type type' => [
            'reflectionType' => (new ReflectionProperty(
                new class () {
                    public (Countable&Iterator)|(Countable&DateTime) $someProperty;
                },
                'someProperty'
            )
            )->getType(),
            'expectedType' => 'Countable&Iterator|Countable&DateTime',
        ];

        yield 'native null type' => [
            'reflectionType' => (new ReflectionProperty(
                new class () {
                    public null $someProperty = null;
                },
                'someProperty'
            )
            )->getType(),
            'expectedType' => 'null',
        ];

        yield 'native true type' => [
            'reflectionType' => (new ReflectionProperty(
                new class () {
                    public true $someProperty;
                },
                'someProperty'
            )
            )->getType(),
            'expectedType' => 'true',
        ];

        yield 'native false type' => [
            'reflectionType' => (new ReflectionProperty(
                new class () {
                    public false $someProperty;
                },
                'someProperty'
            )
            )->getType(),
            'expectedType' => 'false',
        ];
    }
}
