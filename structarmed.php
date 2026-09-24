<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    // apply on qa and src directory for now
    // for ease gradual changes
    ->layer('Source', ['qa', 'src'])

    // One layer per top-level namespace. A nested layer is excluded from its
    // parent (third argument), so a class always belongs to a single layer.
    ->layer('Utility', 'src/Utility', [
        'src/Utility/TypeHelper.php',
        'src/Utility/Reflection/Annotations.php',
    ])
    // keeps the rest of `Utility` unaware of `Type`
    ->layer('UtilityType', [
        'src/Utility/TypeHelper.php',
        'src/Utility/Reflection/Annotations.php',
    ])

    ->layer('Compiler', 'src/Compiler', 'src/Compiler/Library')
    ->layer('CompilerLibrary', 'src/Compiler/Library')

    ->layer('Type', 'src/Type', 'src/Type/Dumper')
    ->layer('TypeDumper', 'src/Type/Dumper')

    ->layer('Definition', 'src/Definition', [
        'src/Definition/Repository/Cache',
        'src/Definition/Attributes.php',
        'src/Definition/AttributeDefinition.php',
    ])
    ->layer('DefinitionCache', 'src/Definition/Repository/Cache')
    // the only part of `Definition` that `Type` may see
    ->layer('Attributes', [
        'src/Definition/Attributes.php',
        'src/Definition/AttributeDefinition.php',
    ])

    ->layer('Cache', 'src/Cache', 'src/Cache/Warmup')
    ->layer('CacheWarmup', 'src/Cache/Warmup')

    ->layer('Mapper', 'src/Mapper', [
        'src/Mapper/Configurator',
        'src/Mapper/Http',
        'src/Mapper/Tree/Message',
        'src/Mapper/AsConverter.php',
        'src/Mapper/Object/Constructor.php',
        'src/Mapper/Object/DynamicConstructor.php',
    ])
    ->layer('MapperConfigurator', 'src/Mapper/Configurator')
    // the only part of `Mapper` that `Utility` may see
    ->layer('MapperHttp', 'src/Mapper/Http')
    // the only part of `Mapper` that `Type` may see
    ->layer('Message', 'src/Mapper/Tree/Message')

    ->layer('Normalizer', 'src/Normalizer', [
        'src/Normalizer/Configurator',
        'src/Normalizer/AsTransformer.php',
    ])
    ->layer('NormalizerConfigurator', 'src/Normalizer/Configurator')

    // the only part of `Mapper` and `Normalizer` that `Definition` may see
    ->layer('FeatureAttribute', [
        'src/Mapper/AsConverter.php',
        'src/Mapper/Object/Constructor.php',
        'src/Mapper/Object/DynamicConstructor.php',
        'src/Normalizer/AsTransformer.php',
    ])

    ->layer('Settings', 'src/Library/Settings.php')
    ->layer('Container', 'src/Library/Container.php')
    ->layer('Builder', [
        'src/MapperBuilder.php',
        'src/NormalizerBuilder.php',
    ])
    ->layer('QA', [
        'qa/Benchmark',
        'qa/PHPStan',
        'qa/Psalm',
    ])

    // Allowed dependencies of each layer, kept to what is used today.
    // `+Layer` means: that layer and everything it may depend on.
    ->ruleset([
        'Utility'                => ['MapperHttp'],
        'UtilityType'            => ['Utility', 'Type'],

        'Compiler'               => [],
        'CompilerLibrary'        => ['Compiler', 'Type', 'Attributes'],

        'Type'                   => ['Utility', 'UtilityType', 'Compiler', 'Message', 'Attributes'],
        'TypeDumper'             => ['+Type', 'Definition', 'Mapper'],

        'Definition'             => ['Utility', 'UtilityType', 'Type', 'Attributes', 'FeatureAttribute'],
        'DefinitionCache'        => ['+Definition', 'Cache'],
        'Attributes'             => ['Definition'],

        'Cache'                  => ['Utility', 'UtilityType', 'Type', 'Definition', 'Attributes', 'Settings'],
        'CacheWarmup'            => ['+Cache', 'Mapper'],

        // `Mapper` and `Normalizer` must stay independent from each other
        'Mapper'                 => [
            'Utility', 'UtilityType', 'Type', 'TypeDumper', 'Definition', 'Attributes', 'Settings',
            'MapperConfigurator', 'MapperHttp', 'Message', 'FeatureAttribute',
        ],
        'MapperConfigurator'     => ['+Mapper', 'Builder'],
        'MapperHttp'             => ['Mapper'],
        'Message'                => ['Utility'],

        'Normalizer'             => [
            'Utility', 'Compiler', 'CompilerLibrary', 'Type', 'Definition', 'Attributes', 'Cache',
            'NormalizerConfigurator',
        ],
        'NormalizerConfigurator' => ['+Normalizer', 'FeatureAttribute', 'Builder'],

        // `DynamicConstructor` imports the builder for its docblock only
        'FeatureAttribute'       => ['Builder'],

        'Settings'               => ['Cache', 'MapperHttp', 'Message', 'FeatureAttribute'],
        // composition root: only `Builder` may depend on it
        'Container'              => [
            'Settings', 'Type', 'TypeDumper', 'Definition', 'DefinitionCache', 'Cache', 'CacheWarmup',
            'Mapper', 'Normalizer',
        ],
        'Builder'                => [
            'Settings', 'Container', 'Cache',
            'Mapper', 'MapperConfigurator', 'Message', 'Normalizer', 'NormalizerConfigurator',
        ],
        'QA'                     => ['Utility', 'Mapper', 'Builder'],
    ])

    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY());
