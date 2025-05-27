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
        ClassPropertyAssignToConstructorPromotionRector::class,
        NullToStrictStringFuncCallArgRector::class,
        ReadOnlyPropertyRector::class,
        RestoreDefaultNullToNullableTypePropertyRector::class => [
            __DIR__ . '/tests/Integration/Mapping/Other/UndefinedValuesMappingTest.php',
            __DIR__ . '/tests/Integration/Mapping/SingleNodeMappingTest',
        ],
    ]);
};
