<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionFunctionDefinitionRepository;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeAttributesRepository;
use CuyZ\Valinor\Tests\Fake\Type\Parser\Factory\FakeTypeParserFactory;
use CuyZ\Valinor\Type\Types\NativeStringType;
use PHPUnit\Framework\TestCase;

final class ReflectionFunctionDefinitionRepositoryTest extends TestCase
{
    private ReflectionFunctionDefinitionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ReflectionFunctionDefinitionRepository(
            new FakeTypeParserFactory(),
            new FakeAttributesRepository(),
        );
    }

    public function test_function_data_can_be_retrieved(): void
    {
        /**
         * @param string $parameterWithDocBlockType
         */
        $callback = fn (string $foo, $parameterWithDocBlockType): string => $foo . $parameterWithDocBlockType;

        $function = $this->repository->for($callback);
        $parameters = $function->parameters();

        self::assertSame(__NAMESPACE__ . '\{closure}', $function->name());
        self::assertInstanceOf(NativeStringType::class, $function->returnType());

        self::assertTrue($parameters->has('foo'));
        self::assertTrue($parameters->has('parameterWithDocBlockType'));
        self::assertInstanceOf(NativeStringType::class, $parameters->get('foo')->type());
        self::assertInstanceOf(NativeStringType::class, $parameters->get('parameterWithDocBlockType')->type());
    }

    public function test_function_return_type_is_fetched_from_docblock(): void
    {
        /**
         * @return string
         */
        $callback = fn (): string => 'foo';

        $function = $this->repository->for($callback);

        self::assertInstanceOf(NativeStringType::class, $function->returnType());
    }
}
