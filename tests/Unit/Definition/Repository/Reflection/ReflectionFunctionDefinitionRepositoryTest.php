<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\UnresolvableType;

final class ReflectionFunctionDefinitionRepositoryTest extends UnitTestCase
{
    public function test_function_data_can_be_retrieved(): void
    {
        /**
         * @param string $parameterWithDocBlockType
         * @phpstan-ignore binaryOp.invalid (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
         */
        $callback = fn (string $foo, $parameterWithDocBlockType): string => $foo . $parameterWithDocBlockType;

        $function = $this->getFunction($callback);
        $parameters = $function->parameters;

        if (PHP_VERSION_ID < 8_04_00) {
            self::assertSame(__NAMESPACE__ . '\{closure}', $function->name);
        } else {
            self::assertSame('{closure:' . self::class . '::' . __FUNCTION__ . '():21}', $function->name);
        }
        self::assertInstanceOf(NativeStringType::class, $function->returnType);

        self::assertTrue($parameters->has('foo'));
        self::assertTrue($parameters->has('parameterWithDocBlockType'));
        self::assertInstanceOf(NativeStringType::class, $parameters->get('foo')->type);
        self::assertInstanceOf(NativeStringType::class, $parameters->get('parameterWithDocBlockType')->type);
    }

    public function test_function_return_type_is_fetched_from_docblock(): void
    {
        /**
         * @return string
         */
        $callback = fn () => 'foo';

        $function = $this->getFunction($callback);

        self::assertInstanceOf(NativeStringType::class, $function->returnType);
    }

    public function test_function_signatures_are_correct(): void
    {
        /** @var array<string, callable> $functions */
        $functions = require_once 'FakeFunctions.php';

        $classStaticMethod = $this->getFunction($functions['class_static_method'])->signature;
        $functionOnOneLine = $this->getFunction($functions['function_on_one_line'])->signature;
        $closureOnOneLine = $this->getFunction($functions['closure_on_one_line'])->signature;
        $closureOnSeveralLines = $this->getFunction($functions['closure_on_several_lines'])->signature;

        self::assertSame(SomeClassWithOneMethod::class . '::method()', $classStaticMethod);
        self::assertSame(__NAMESPACE__ . '\function_on_one_line()', $functionOnOneLine);
        self::assertSame('Closure (line 17 of ' . __DIR__ . '/FakeFunctions.php)', $closureOnOneLine);
        self::assertSame('Closure (lines 18 to 24 of ' . __DIR__ . '/FakeFunctions.php)', $closureOnSeveralLines);
    }

    public function test_function_with_non_matching_return_types_throws_exception(): void
    {
        /**
         * @return int
         */
        $callback = fn (): string => 'foo';

        $returnType = $this->getFunction($callback)->returnType;

        self::assertInstanceOf(UnresolvableType::class, $returnType);
        self::assertMatchesRegularExpression('/The return type `int` of function `.*` could not be resolved: `int` \(docblock\) does not accept `string` \(native\)./', $returnType->message());
    }

    private function getFunction(callable $callable): FunctionDefinition
    {
        return $this->getService(FunctionDefinitionRepository::class)->for($callable);
    }
}
