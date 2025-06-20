<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Cache;

use CuyZ\Valinor\Definition\Repository\Cache\CompiledFunctionDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\FunctionDefinitionCompiler;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeFunctionDefinitionRepository;
use PHPUnit\Framework\TestCase;

final class CompiledFunctionDefinitionRepositoryTest extends TestCase
{
    public function test_function_is_saved_in_cache(): void
    {
        $repository = new CompiledFunctionDefinitionRepository(
            new FakeFunctionDefinitionRepository(),
            $cache = new FakeCache(),
            new FunctionDefinitionCompiler(),
        );

        $callableA = fn (): int => 42;
        $callableB = fn (): int => 1337;

        $repository->for($callableA);
        $repository->for($callableA);
        $repository->for($callableB);
        $repository->for($callableB);

        self::assertSame(2, $cache->timeSetWasCalled());
    }

    public function test_files_to_watch_are_correct(): void
    {
        $repository = new CompiledFunctionDefinitionRepository(
            new FakeFunctionDefinitionRepository(),
            $cache = new FakeCache(),
            new FunctionDefinitionCompiler(),
        );

        $callable = fn (): int => 42;

        $repository->for($callable);

        $cacheEntries = $cache->entriesThatWereSet();

        self::assertCount(1, $cacheEntries);
        self::assertSame([
            'foo/bar'
        ], reset($cacheEntries)->filesToWatch);
    }
}
