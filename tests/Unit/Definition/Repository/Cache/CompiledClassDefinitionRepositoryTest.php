<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Cache;

use CuyZ\Valinor\Definition\Repository\Cache\CompiledClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\ClassDefinitionCompiler;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeClassDefinitionRepository;
use CuyZ\Valinor\Tests\Fixture\Object\Inheritance\ChildObject;
use CuyZ\Valinor\Type\Types\NativeClassType;
use DateTime;
use PHPUnit\Framework\TestCase;
use stdClass;

use function realpath;
use function reset;

final class CompiledClassDefinitionRepositoryTest extends TestCase
{
    public function test_class_is_saved_in_cache(): void
    {
        $repository = new CompiledClassDefinitionRepository(
            new FakeClassDefinitionRepository(),
            $cache = new FakeCache(),
            new ClassDefinitionCompiler(),
        );

        $typeA = new NativeClassType(stdClass::class);
        $typeB = new NativeClassType(DateTime::class);

        $repository->for($typeA);
        $repository->for($typeA);
        $repository->for($typeB);
        $repository->for($typeB);

        self::assertSame(2, $cache->timeSetWasCalled());
    }

    public function test_files_to_watch_are_correct(): void
    {
        $repository = new CompiledClassDefinitionRepository(
            new FakeClassDefinitionRepository(),
            $cache = new FakeCache(),
            new ClassDefinitionCompiler(),
        );

        $type = new NativeClassType(ChildObject::class);

        $repository->for($type);

        $cacheEntries = $cache->entriesThatWereSet();

        self::assertCount(1, $cacheEntries);
        self::assertSame([
            realpath(__DIR__ . '/../../../../Fixture/Object/Inheritance/ChildObject.php'),
            realpath(__DIR__ . '/../../../../Fixture/Object/Inheritance/ParentObject.php'),
            realpath(__DIR__ . '/../../../../Fixture/Object/Inheritance/GrandParentObject.php'),
        ], reset($cacheEntries)->filesToWatch);
    }
}
