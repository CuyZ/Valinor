<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Cache;

use CuyZ\Valinor\Cache\TypeFilesWatcher;
use CuyZ\Valinor\Definition\Repository\Cache\CompiledClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\ClassDefinitionCompiler;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionAttributesRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionFunctionDefinitionRepository;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeClassDefinitionRepository;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeFunctionDefinitionRepository;
use CuyZ\Valinor\Tests\Fixture\Object\Inheritance\ChildObject;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Types\NativeClassType;
use DateTime;
use stdClass;

use function realpath;
use function reset;

final class CompiledClassDefinitionRepositoryTest extends UnitTestCase
{
    public function test_class_is_saved_in_cache(): void
    {
        $repository = new CompiledClassDefinitionRepository(
            new FakeClassDefinitionRepository(),
            $cache = new FakeCache(),
            new TypeFilesWatcher(
                new Settings(),
                new ReflectionClassDefinitionRepository(new TypeParserFactory(), []),
                new FakeFunctionDefinitionRepository(),
            ),
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
        $classDefinitionRepository = new ReflectionClassDefinitionRepository(
            new TypeParserFactory(),
            []
        );
        $functionDefinitionRepository = new ReflectionFunctionDefinitionRepository(new TypeParserFactory(), new ReflectionAttributesRepository($classDefinitionRepository, []));

        $repository = new CompiledClassDefinitionRepository(
            new FakeClassDefinitionRepository(),
            $cache = new FakeCache(),
            new TypeFilesWatcher(
                new Settings(),
                $classDefinitionRepository,
                $functionDefinitionRepository,
            ),
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
        ], (reset($cacheEntries)->filesToWatch)()); // @phpstan-ignore callable.nonCallable
    }
}
