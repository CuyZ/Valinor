<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\ClassSignature;
use CuyZ\Valinor\Definition\Repository\Cache\CacheClassDefinitionRepository;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeClassDefinitionRepository;
use DateTime;
use PHPUnit\Framework\TestCase;
use stdClass;

final class CacheClassDefinitionRepositoryTest extends TestCase
{
    private CacheClassDefinitionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new CacheClassDefinitionRepository(
            new FakeClassDefinitionRepository(),
            new FakeCache()
        );
    }

    public function test_class_is_saved_in_cache(): void
    {
        $signatureA = new ClassSignature(stdClass::class);
        $signatureB = new ClassSignature(DateTime::class);

        $classA = $this->repository->for($signatureA);
        $classB = $this->repository->for($signatureA);
        $classC = $this->repository->for($signatureB);

        self::assertSame($classA, $classB);
        self::assertNotSame($classA, $classC);
    }
}
