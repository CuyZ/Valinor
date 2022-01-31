<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Cache;

use CuyZ\Valinor\Definition\Repository\Cache\CacheFunctionDefinitionRepository;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeFunctionDefinitionRepository;
use PHPUnit\Framework\TestCase;

final class CacheFunctionDefinitionRepositoryTest extends TestCase
{
    private CacheFunctionDefinitionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new CacheFunctionDefinitionRepository(
            new FakeFunctionDefinitionRepository(),
            new FakeCache()
        );
    }

    public function test_function_is_saved_in_cache(): void
    {
        $callableA = fn (): int => 42;
        $callableB = fn (): int => 1337;

        $functionA = $this->repository->for($callableA);
        $functionB = $this->repository->for($callableA);
        $functionC = $this->repository->for($callableB);

        self::assertSame($functionA, $functionB);
        self::assertNotSame($functionA, $functionC);
    }
}
