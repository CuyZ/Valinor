<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Cache;

use CuyZ\Valinor\Cache\TypeFilesWatcher;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Tests\Unit\Cache\Fixtures\SomeClassToTestTypeFilesWatcherA;
use CuyZ\Valinor\Tests\Unit\Cache\Fixtures\SomeClassToTestTypeFilesWatcherD;
use CuyZ\Valinor\Tests\Unit\Cache\Fixtures\SomeClassToTestTypeFilesWatcherE;
use CuyZ\Valinor\Tests\Unit\Cache\Fixtures\SomeClassToTestTypeFilesWatcherF;
use CuyZ\Valinor\Tests\Unit\Cache\Fixtures\SomeClassToTestTypeFilesWatcherWithGenerics;
use CuyZ\Valinor\Tests\Unit\Cache\Fixtures\SomeClassWithAttributeToTestTypeFilesWatcher;
use CuyZ\Valinor\Tests\Unit\Cache\Fixtures\SomeClassWithAttributeWithParameterAndReturnTypeToTestTypeFilesWatcher;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Types\NativeClassType;
use stdClass;

use function realpath;

final class TypeFilesWatcherTest extends UnitTestCase
{
    public function test_files_to_watch_are_fetched_properly(): void
    {
        $files = $this->typeFilesWatcher()->for(new NativeClassType(SomeClassToTestTypeFilesWatcherA::class));

        self::assertSame([
            realpath(__DIR__ . '/Fixtures/SomeClassToTestTypeFilesWatcherA.php'),
            realpath(__DIR__ . '/Fixtures/SomeClassToTestTypeFilesWatcherB.php'),
            realpath(__DIR__ . '/Fixtures/SomeClassToTestTypeFilesWatcherC.php'),
            realpath(__DIR__ . '/Fixtures/SomeClassToTestTypeFilesWatcherD.php'),
            realpath(__DIR__ . '/Fixtures/SomeClassToTestTypeFilesWatcherF.php'),
            realpath(__DIR__ . '/Fixtures/SomeClassToTestTypeFilesWatcherE.php'),
        ], $files);
    }

    public function test_files_to_watch_for_attributes_are_fetched_properly(): void
    {
        $files = $this->typeFilesWatcher()->for(new NativeClassType(SomeClassWithAttributeToTestTypeFilesWatcher::class));

        self::assertSame([
            realpath(__DIR__ . '/Fixtures/SomeClassWithAttributeToTestTypeFilesWatcher.php'),
            realpath(__DIR__ . '/Fixtures/SomeAttributeForClassToTestTypeFilesWatcher.php'),
            realpath(__DIR__ . '/Fixtures/SomeAttributeForPropertyToTestTypeFilesWatcher.php'),
            realpath(__DIR__ . '/Fixtures/SomeAttributeForMethodToTestTypeFilesWatcher.php'),
            realpath(__DIR__ . '/Fixtures/SomeAttributeForParameterToTestTypeFilesWatcher.php'),
        ], $files);
    }

    public function test_files_to_watch_for_attribute_with_parameter_and_return_type_are_fetched_properly(): void
    {
        $files = $this->typeFilesWatcher()->for(new NativeClassType(SomeClassWithAttributeWithParameterAndReturnTypeToTestTypeFilesWatcher::class));

        self::assertSame([
            realpath(__DIR__ . '/Fixtures/SomeClassWithAttributeWithParameterAndReturnTypeToTestTypeFilesWatcher.php'),
            realpath(__DIR__ . '/Fixtures/SomeAttributeForClassToTestTypeFilesWatcher.php'),
            realpath(__DIR__ . '/Fixtures/SomeAttributeWithParameterAndReturnTypeToTestTypeFilesWatcher.php'),
            realpath(__DIR__ . '/Fixtures/SomeClassToTestTypeFilesWatcherF.php'),
            realpath(__DIR__ . '/Fixtures/SomeClassToTestTypeFilesWatcherE.php'),
        ], $files);
    }

    public function test_files_to_watch_for_generic_class_are_fetched_properly(): void
    {
        $class = new NativeClassType(
            className: SomeClassToTestTypeFilesWatcherWithGenerics::class,
            generics: [
                new NativeClassType(SomeClassToTestTypeFilesWatcherE::class),
                new NativeClassType(SomeClassToTestTypeFilesWatcherF::class),
            ]
        );

        $files = $this->typeFilesWatcher()->for($class);

        self::assertSame([
            realpath(__DIR__ . '/Fixtures/SomeClassToTestTypeFilesWatcherE.php'),
            realpath(__DIR__ . '/Fixtures/SomeClassToTestTypeFilesWatcherF.php'),
            realpath(__DIR__ . '/Fixtures/SomeClassToTestTypeFilesWatcherWithGenerics.php'),
        ], $files);
    }

    public function test_files_to_watch_for_callable_are_fetched_properly(): void
    {
        require_once __DIR__ . '/Fixtures/file-with-callable-1.php';

        $files = $this->typeFilesWatcher()->for(some_function_to_test_type_files_watcher(...));

        self::assertSame([
            realpath(__DIR__ . '/Fixtures/file-with-callable-1.php'),
        ], $files);
    }

    public function test_files_to_watch_for_callable_with_parameter_and_return_type_and_attribute_are_fetched_properly(): void
    {
        require_once __DIR__ . '/Fixtures/file-with-callable-with-parameters-and-return-type.php';

        $files = $this->typeFilesWatcher()->for(some_function_with_parameter_and_return_type_and_attribute_to_test_type_files_watcher(...));

        self::assertSame([
            realpath(__DIR__ . '/Fixtures/file-with-callable-with-parameters-and-return-type.php'),
            realpath(__DIR__ . '/Fixtures/SomeClassToTestTypeFilesWatcherF.php'),
            realpath(__DIR__ . '/Fixtures/SomeAttributeForMethodToTestTypeFilesWatcher.php'),
            realpath(__DIR__ . '/Fixtures/SomeClassToTestTypeFilesWatcherE.php'),
            realpath(__DIR__ . '/Fixtures/SomeAttributeForParameterToTestTypeFilesWatcher.php'),
        ], $files);
    }

    public function test_settings_callable_are_watched_for_type(): void
    {
        require_once __DIR__ . '/Fixtures/file-with-callable-1.php';
        require_once __DIR__ . '/Fixtures/file-with-callable-2.php';

        $settings = new Settings();
        $settings->customConstructors = [
            some_function_to_test_type_files_watcher(...),
            some_other_function_to_test_type_files_watcher(...),
        ];

        $files = $this->typeFilesWatcher($settings)->for(new NativeClassType(stdClass::class));

        self::assertSame([
            realpath(__DIR__ . '/Fixtures/file-with-callable-1.php'),
            realpath(__DIR__ . '/Fixtures/file-with-callable-2.php'),
        ], $files);
    }

    public function test_settings_callable_are_watched_for_callable(): void
    {
        require_once __DIR__ . '/Fixtures/file-with-callable-1.php';
        require_once __DIR__ . '/Fixtures/file-with-callable-2.php';

        $settings = new Settings();
        $settings->customConstructors = [
            some_function_to_test_type_files_watcher(...),
            some_other_function_to_test_type_files_watcher(...),
        ];

        $files = $this->typeFilesWatcher($settings)->for(some_function_to_test_type_files_watcher(...));

        self::assertSame([
            realpath(__DIR__ . '/Fixtures/file-with-callable-1.php'),
            realpath(__DIR__ . '/Fixtures/file-with-callable-2.php'),
        ], $files);
    }

    public function test_circular_references_in_object_does_not_cause_infinite_loop(): void
    {
        $files = $this->typeFilesWatcher()->for(new NativeClassType(SomeClassWithCircularReference::class));

        self::assertSame([
            realpath(__FILE__),
            realpath(__DIR__ . '/Fixtures/SomeClassToTestTypeFilesWatcherD.php'),
        ], $files);
    }

    private function typeFilesWatcher(Settings $settings = new Settings()): TypeFilesWatcher
    {
        return $this->getService(TypeFilesWatcher::class, $settings);
    }
}

final class SomeClassWithCircularReference
{
    public function __construct(
        public SomeClassWithCircularReference $circularReference,
        public SomeClassToTestTypeFilesWatcherD $valueD,
    ) {}
}
