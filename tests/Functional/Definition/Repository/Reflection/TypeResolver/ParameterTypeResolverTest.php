<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ParameterTypeResolver;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ReflectionTypeResolver;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;

final class ParameterTypeResolverTest extends TestCase
{
    private ParameterTypeResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new ParameterTypeResolver(
            new ReflectionTypeResolver(
                (new LexingTypeParserFactory())->buildDefaultTypeParser(),
                (new LexingTypeParserFactory())->buildAdvancedTypeParserForClass(new NativeClassType(self::class)),
            ),
        );
    }

    #[DataProvider('parameter_type_is_resolved_properly_data_provider')]
    public function test_parameter_type_is_resolved_properly(ReflectionParameter $reflection, string $expectedType): void
    {
        $type = $this->resolver->resolveTypeFor($reflection);

        self::assertNotInstanceOf(UnresolvableType::class, $type);
        self::assertSame($expectedType, $type->toString());
    }

    public static function parameter_type_is_resolved_properly_data_provider(): iterable
    {
        yield 'phpdoc @param' => [
            new ReflectionParameter(
                /** @param string $value */
                static function ($value): void {},
                'value',
            ),
            'string',
        ];

        yield 'phpdoc @param with comment' => [
            new ReflectionParameter(
                /**
                 * @param string $value Some comment
                 */
                static function ($value): void {},
                'value',
            ),
            'string',
        ];

        yield 'psalm @param standalone' => [
            new ReflectionParameter(
                /** @psalm-param string $value */
                static function ($value): void {},
                'value',
            ),
            'string',
        ];

        yield 'psalm @param leading' => [
            new ReflectionParameter(
                /**
                 * @psalm-param non-empty-string $value
                 * @param string $value
                 */
                static function ($value): void {},
                'value',
            ),
            'non-empty-string',
        ];

        yield 'psalm @param trailing' => [
            new ReflectionParameter(
                /**
                 * @param string $value
                 * @psalm-param non-empty-string $value
                 */
                static function ($value): void {},
                'value',
            ),
            'non-empty-string',
        ];

        yield 'phpstan @param standalone' => [
            new ReflectionParameter(
                /** @phpstan-param string $value */
                static function ($value): void {},
                'value',
            ),
            'string',
        ];

        yield 'phpstan @param leading' => [
            new ReflectionParameter(
                /**
                 * @phpstan-param non-empty-string $value
                 * @param string $value
                 */
                static function ($value): void {},
                'value',
            ),
            'non-empty-string',
        ];

        yield 'phpstan @param trailing' => [
            new ReflectionParameter(
                /**
                 * @param string $value
                 * @phpstan-param non-empty-string $value
                 */
                static function ($value): void {},
                'value',
            ),
            'non-empty-string',
        ];

        yield 'phpstan @param trailing after psalm' => [
            new ReflectionParameter(
                /**
                 * @psalm-param string $value
                 * @phpstan-param non-empty-string $value
                 */
                static function ($value): void {},
                'value',
            ),
            'non-empty-string',
        ];

        yield 'phpdoc several incomplete @param' => [
            new ReflectionParameter(
                /**
                 * @param string No name
                 * @param string $value Some comment
                 * @param string Still no Name
                 * @param &value Name of the parameter but without
                 *
                 * @phpstan-ignore-next-line / Invalid annotations are here on purpose to test them
                 */
                static function ($value): void {},
                'value',
            ),
            'string',
        ];
    }

    public function test_invalid_parameter_type_stays_invalid_when_variadic(): void
    {
        $reflection = new ReflectionParameter(
            /**
             * @param InvalidValue $value
             */
            static function (...$value): void {},
            'value',
        );

        $type = $this->resolver->resolveTypeFor($reflection);

        self::assertInstanceOf(UnresolvableType::class, $type);
    }
}
