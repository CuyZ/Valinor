<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_74
    ]);

    $rectorConfig->parallel();
    $rectorConfig->skip([
        StringClassNameToClassConstantRector::class,
        JsonThrowOnErrorRector::class,
        RestoreDefaultNullToNullableTypePropertyRector::class => [
            // anonymous class in test
            __DIR__ . '/tests/Unit/Utility/Reflection/ReflectionTest.php',
        ],
        __DIR__ . '/tests/Fixture',
        AddLiteralSeparatorToNumberRector::class,
    ]);
};
