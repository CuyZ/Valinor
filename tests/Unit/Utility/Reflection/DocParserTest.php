<?php

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection;

use Closure;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithConstants;
use CuyZ\Valinor\Type\Parser\Exception\Template\DuplicatedTemplateName;
use CuyZ\Valinor\Utility\Reflection\DocParser;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;
use ReflectionProperty;
use stdClass;

final class DocParserTest extends TestCase
{
    /**
     * @param non-empty-string $expectedType
     * @dataProvider callables_with_docblock_typed_return_type
     */
    public function test_docblock_return_type_is_fetched_correctly(
        callable $dockblockTypedCallable,
        string $expectedType
    ): void {
        $type = DocParser::functionReturnType(new ReflectionFunction(Closure::fromCallable($dockblockTypedCallable)));

        self::assertSame($expectedType, $type);
    }

    /**
     * @return iterable<non-empty-string,array{0:callable,1:non-empty-string}>
     */
    public function callables_with_docblock_typed_return_type(): iterable
    {
        yield 'phpdoc' => [
            /** @return int */
            fn () => 42,
            'int',
        ];

        yield 'phpdoc followed by new line' => [
            /**
             * @return int
             *
             */
            fn () => 42,
            'int',
        ];

        yield 'phpdoc literal string' => [
            /** @return 'foo' */
            fn () => 'foo',
            '\'foo\'',
        ];

        yield 'phpdoc union with space between types' => [
            /** @return int | float Some comment */
            fn (string $foo): int|float => $foo === 'foo' ? 42 : 1337.42,
            'int | float',
        ];

        yield 'phpdoc shaped array on several lines' => [
            /**
             * @return array{
             *     foo: string,
             *     bar: int,
             * } Some comment
             */
            fn () => ['foo' => 'foo', 'bar' => 42],
            'array{ foo: string, bar: int, }',
        ];

        yield 'phpdoc const with joker' => [
            /** @return ObjectWithConstants::CONST_WITH_* */
            fn (): string => ObjectWithConstants::CONST_WITH_STRING_VALUE_A,
            'ObjectWithConstants::CONST_WITH_*',
        ];

        if (PHP_VERSION_ID >= 8_01_00) {
            yield 'phpdoc enum with joker' => [
                /** @return BackedStringEnum::BA* */
                fn () => BackedStringEnum::BAR,
                'BackedStringEnum::BA*',
            ];
        }

        yield 'psalm' => [
            /** @psalm-return int */
            fn () => 42,
            'int',
        ];

        yield 'psalm trailing' => [
            /**
             * @return int
             * @psalm-return positive-int
             */
            fn () => 42,
            'positive-int',
        ];

        yield 'psalm leading' => [
            /**
             * @psalm-return positive-int
             * @return int
             */
            fn () => 42,
            'positive-int',
        ];

        yield 'phpstan' => [
            /** @phpstan-return int */
            fn () => 42,
            'int',
        ];

        yield 'phpstan trailing' => [
            /**
             * @return int
             * @phpstan-return positive-int
             */
            fn () => 42,
            'positive-int',
        ];

        yield 'phpstan leading' => [
            /**
             * @phpstan-return positive-int
             * @return int
             */
            fn () => 42,
            'positive-int',
        ];
    }

    public function test_docblock_return_type_with_no_docblock_returns_null(): void
    {
        $callable = static function (): void {};

        $type = DocParser::functionReturnType(new ReflectionFunction($callable));

        self::assertNull($type);
    }

    /**
     * @param non-empty-string $expectedType
     * @dataProvider objects_with_docblock_typed_properties
     */
    public function test_docblock_var_type_is_fetched_correctly(
        ReflectionParameter|ReflectionProperty $reflection,
        string $expectedType
    ): void {
        $type = $reflection instanceof ReflectionProperty
            ? DocParser::propertyType($reflection)
            : DocParser::parameterType($reflection);

        self::assertEquals($expectedType, $type);
    }

    /**
     * @return iterable<non-empty-string,array{0:ReflectionProperty|ReflectionParameter,1:non-empty-string}>
     */
    public function objects_with_docblock_typed_properties(): iterable
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

        yield 'phpdoc @param' => [
            new ReflectionParameter(
                /** @param string $string */
                static function ($string): void {},
                'string',
            ),
            'string',
        ];

        yield 'psalm @param standalone' => [
            new ReflectionParameter(
                /** @psalm-param string $string */
                static function ($string): void {},
                'string',
            ),
            'string',
        ];

        yield 'psalm @param leading' => [
            new ReflectionParameter(
                /**
                 * @psalm-param non-empty-string $string
                 * @param string $string
                 */
                static function ($string): void {},
                'string',
            ),
            'non-empty-string',
        ];

        yield 'psalm @param trailing' => [
            new ReflectionParameter(
                /**
                 * @param string $string
                 * @psalm-param non-empty-string $string
                 */
                static function ($string): void {},
                'string',
            ),
            'non-empty-string',
        ];

        yield 'phpstan @param standalone' => [
            new ReflectionParameter(
                /** @phpstan-param string $string */
                static function ($string): void {},
                'string',
            ),
            'string',
        ];

        yield 'phpstan @param leading' => [
            new ReflectionParameter(
                /**
                 * @phpstan-param non-empty-string $string
                 * @param string $string
                 */
                static function ($string): void {},
                'string',
            ),
            'non-empty-string',
        ];

        yield 'phpstan @param trailing' => [
            new ReflectionParameter(
                /**
                 * @param string $string
                 * @phpstan-param non-empty-string $string
                 */
                static function ($string): void {},
                'string',
            ),
            'non-empty-string',
        ];
    }

    public function test_no_template_found_for_class_returns_empty_array(): void
    {
        $templates = DocParser::classTemplates(new ReflectionClass(stdClass::class));

        self::assertEmpty($templates);
    }

    public function test_templates_are_parsed_and_returned(): void
    {
        $class =
            /**
             * @template TemplateA
             * @template TemplateB of string
             */
            new class () {};

        $templates = DocParser::classTemplates(new ReflectionClass($class::class));

        self::assertSame([
            'TemplateA' => '',
            'TemplateB' => 'string',
        ], $templates);
    }

    public function test_duplicated_template_name_throws_exception(): void
    {
        $class =
            /**
             * @template TemplateA
             * @template TemplateA of string
             */
            new class () {};

        $className = $class::class;

        $this->expectException(DuplicatedTemplateName::class);
        $this->expectExceptionCode(1604612898);
        $this->expectExceptionMessage("The template `TemplateA` in class `$className` was defined at least twice.");

        DocParser::classTemplates(new ReflectionClass($className));
    }
}
