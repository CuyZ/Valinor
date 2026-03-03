<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;

return RectorConfig::configure()
    ->withPaths([
        __FILE__,
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // @see https://github.com/rectorphp/rector/issues/7341
    ->withCache(__DIR__ . '/var/cache/rector', FileCacheStorage::class)
    ->withPhpSets(php82: true)
    ->withParallel()
    ->withSkip([
        ClassPropertyAssignToConstructorPromotionRector::class,
        NullToStrictStringFuncCallArgRector::class,
        ReadOnlyPropertyRector::class,
        RestoreDefaultNullToNullableTypePropertyRector::class => [
            __DIR__ . '/tests/Integration/Mapping/Other/UndefinedValuesMappingTest.php',
            __DIR__ . '/tests/Integration/Mapping/SingleNodeMappingTest',
        ],
        // PHP8.5 remove
        ClosureToArrowFunctionRector::class => [
            __DIR__ . '/tests/Integration/Mapping/Converter/ConverterWithCallable.php',
            __DIR__ . '/tests/Unit/Definition/Repository/Reflection/ClassWithAttributeWithClosure.php',
            __DIR__ . '/tests/Integration/Normalizer/TemporaryPHP85/ClassWithPropertyTransformerWithCallable.php',
            __DIR__ . '/tests/Integration/Normalizer/TemporaryPHP85/ClassWithTransformerWithCallable.php',
        ]
    ]);
