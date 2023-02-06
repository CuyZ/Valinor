<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Cache;

use CuyZ\Valinor\Definition\Repository\Cache\CacheClassDefinitionRepository;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeClassDefinitionRepository;
use CuyZ\Valinor\Type\Types\NativeClassType;
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
        $typeA = new NativeClassType(stdClass::class);
        $typeB = new NativeClassType(DateTime::class);

        $classA = $this->repository->for($typeA);
        $classB = $this->repository->for($typeA);
        $classC = $this->repository->for($typeB);

        self::assertSame($classA, $classB);
        self::assertNotSame($classA, $classC);
    }
}
