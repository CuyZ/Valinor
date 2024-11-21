<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection\TypeResolver;

use Countable;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ReflectionTypeResolver;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithPropertyWithNativeDisjunctiveNormalFormType;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithPropertyWithNativePhp82StandaloneTypes;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
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
            (new LexingTypeParserFactory())->buildDefaultTypeParser(),
            (new LexingTypeParserFactory())->buildDefaultTypeParser(),
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
    }

    // PHP8.2 move to data provider
    #[RequiresPhp('>=8.2')]
    public function test_disjunctive_normal_form_type_is_resolved_properly(): void
    {
        $reflectionType = (new ReflectionProperty(ObjectWithPropertyWithNativeDisjunctiveNormalFormType::class, 'someProperty'))->getType();

        $type = $this->resolver->resolveNativeType($reflectionType);

        self::assertSame('Countable&Iterator|Countable&DateTime', $type->toString());
    }

    // PHP8.2 move to data provider
    #[RequiresPhp('>=8.2')]
    public function test_native_null_type_is_resolved_properly(): void
    {
        $reflectionType = (new ReflectionProperty(ObjectWithPropertyWithNativePhp82StandaloneTypes::class, 'nativeNull'))->getType();

        $type = $this->resolver->resolveNativeType($reflectionType);

        self::assertSame('null', $type->toString());
    }

    // PHP8.2 move to data provider
    #[RequiresPhp('>=8.2')]
    public function test_native_true_type_is_resolved_properly(): void
    {
        $reflectionType = (new ReflectionProperty(ObjectWithPropertyWithNativePhp82StandaloneTypes::class, 'nativeTrue'))->getType();

        $type = $this->resolver->resolveNativeType($reflectionType);

        self::assertSame('true', $type->toString());
    }

    // PHP8.2 move to data provider
    #[RequiresPhp('>=8.2')]
    public function test_native_false_type_is_resolved_properly(): void
    {
        $reflectionType = (new ReflectionProperty(ObjectWithPropertyWithNativePhp82StandaloneTypes::class, 'nativeFalse'))->getType();

        $type = $this->resolver->resolveNativeType($reflectionType);

        self::assertSame('false', $type->toString());
    }
}
