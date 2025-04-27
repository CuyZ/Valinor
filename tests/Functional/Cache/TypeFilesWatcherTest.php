<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Cache;

use CuyZ\Valinor\Cache\TypeFilesWatcher;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionClassDefinitionRepository;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use CuyZ\Valinor\Type\Types\NativeClassType;
use PHPUnit\Framework\TestCase;

final class TypeFilesWatcherTest extends TestCase
{
    public function test_files_to_watch_are_fetched_properly(): void
    {
        require_once __DIR__ . '/file-with-callables-1.php';
        require_once __DIR__ . '/file-with-callables-2.php';

        $settings = new Settings();
        $settings->customConstructors = [
            some_function_to_test_type_files_watcher(...),
            some_other_function_to_test_type_files_watcher(...),
        ];
        $settings->transformers = [
            [strtoupper(...)]
        ];

        $classDefinitionRepository = new ReflectionClassDefinitionRepository(
            new LexingTypeParserFactory(),
            []
        );

        $watcher = new TypeFilesWatcher($settings, $classDefinitionRepository);

        $files = $watcher->for(new NativeClassType(SomeClassToTestTypeFilesWatcherA::class));

        self::assertSame([
            realpath(__DIR__ . '/../../../src/Library/Settings.php'),
            realpath(__DIR__ . '/file-with-callables-1.php'),
            realpath(__DIR__ . '/file-with-callables-2.php'),
            realpath(__DIR__ . '/SomeClassToTestTypeFilesWatcherA.php'),
            realpath(__DIR__ . '/SomeClassToTestTypeFilesWatcherB.php'),
            realpath(__DIR__ . '/SomeClassToTestTypeFilesWatcherC.php'),
            realpath(__DIR__ . '/SomeClassToTestTypeFilesWatcherD.php'),
        ], $files);
    }

    public function test_circular_references_in_object_does_not_cause_infinite_loop(): void
    {
        $classDefinitionRepository = new ReflectionClassDefinitionRepository(
            new LexingTypeParserFactory(),
            []
        );

        $watcher = new TypeFilesWatcher(new Settings(), $classDefinitionRepository);

        $files = $watcher->for(new NativeClassType(SomeClassWithCircularReference::class));

        self::assertSame([
            realpath(__DIR__ . '/../../../src/Library/Settings.php'),
            realpath(__FILE__),
            realpath(__DIR__ . '/SomeClassToTestTypeFilesWatcherD.php'),
        ], $files);
    }
}

final class SomeClassWithCircularReference
{
    public function __construct(
        public SomeClassWithCircularReference $circularReference,
        public SomeClassToTestTypeFilesWatcherD $valueD,
    ) {}
}
