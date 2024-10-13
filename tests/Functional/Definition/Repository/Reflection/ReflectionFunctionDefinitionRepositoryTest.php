<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionAttributesRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionFunctionDefinitionRepository;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use PHPUnit\Framework\TestCase;

final class ReflectionFunctionDefinitionRepositoryTest extends TestCase
{
    private ReflectionFunctionDefinitionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ReflectionFunctionDefinitionRepository(
            new LexingTypeParserFactory(),
            new ReflectionAttributesRepository(
                new ReflectionClassDefinitionRepository(new LexingTypeParserFactory(), []),
                []
            ),
        );
    }

    public function test_function_data_can_be_retrieved(): void
    {
        /**
         * @param string $parameterWithDocBlockType
         */
        $callback = fn (string $foo, $parameterWithDocBlockType): string => $foo . $parameterWithDocBlockType;

        $function = $this->repository->for($callback);
        $parameters = $function->parameters;

        self::assertSame(__NAMESPACE__ . '\{closure}', $function->name);
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

        $function = $this->repository->for($callback);

        self::assertInstanceOf(NativeStringType::class, $function->returnType);
    }

    public function test_function_signatures_are_correct(): void
    {
        $functions = require_once 'FakeFunctions.php';

        $classStaticMethod = $this->repository->for($functions['class_static_method'])->signature;
        $functionOnOneLine = $this->repository->for($functions['function_on_one_line'])->signature;
        $closureOnOneLine = $this->repository->for($functions['closure_on_one_line'])->signature;
        $closureOnSeveralLines = $this->repository->for($functions['closure_on_several_lines'])->signature;

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

        $returnType = $this->repository->for($callback)->returnType;

        self::assertInstanceOf(UnresolvableType::class, $returnType);
        self::assertMatchesRegularExpression('/^Return types for function `.*` do not match: `int` \(docblock\) does not accept `string` \(native\).$/', $returnType->message());
    }
}
