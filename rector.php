<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\FunctionLike\MixedTypeRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $config): void {
    $config->paths([
        __FILE__,
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $config->cacheDirectory(__DIR__ . '/var/cache/rector');

    // @see https://github.com/rectorphp/rector/issues/7341
    $config->cacheClass(FileCacheStorage::class);

    $config->sets([
        LevelSetList::UP_TO_PHP_81,
    ]);

    $config->parallel();
    $config->skip([
        AddLiteralSeparatorToNumberRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,
        ReadOnlyPropertyRector::class,
        MixedTypeRector::class => [
            __DIR__ . '/tests/Unit/Definition/Repository/Reflection/ReflectionClassDefinitionRepositoryTest',
            __DIR__ . '/tests/Integration/Mapping/TypeErrorDuringMappingTest.php',
        ],
        NullToStrictStringFuncCallArgRector::class => [
            __DIR__ . '/tests/Traits/TestIsSingleton.php',
        ],
        RestoreDefaultNullToNullableTypePropertyRector::class => [
            __DIR__ . '/tests/Integration/Mapping/Other/FlexibleCastingMappingTest.php',
            __DIR__ . '/tests/Integration/Mapping/SingleNodeMappingTest',
        ],
    ]);
};
