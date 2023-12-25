<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\FunctionLike\MixedTypeRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $config): void {
    $config->paths([
        __FILE__,
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $config->parameters()->set(Option::CACHE_DIR, './var/cache/rector');

    // @see https://github.com/rectorphp/rector/issues/7341
    $config->parameters()->set(Option::CACHE_CLASS, FileCacheStorage::class);

    $config->sets([
        LevelSetList::UP_TO_PHP_80,
    ]);

    $config->parallel();
    $config->skip([
        AddLiteralSeparatorToNumberRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,
        MixedTypeRector::class => [
            __DIR__ . '/tests/Unit/Definition/Repository/Reflection/ReflectionClassDefinitionRepositoryTest',
        ],
        RestoreDefaultNullToNullableTypePropertyRector::class => [
            __DIR__ . '/tests/Integration/Mapping/Other/FlexibleCastingMappingTest.php',
            __DIR__ . '/tests/Integration/Mapping/SingleNodeMappingTest',
        ],
    ]);
};
