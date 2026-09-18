<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    // apply on qa and src directory for now
    // for ease gradual changes
    ->layer('Source', ['qa', 'src'])

    // layers definition for ruleset uses
    ->layer('Utility', 'src/Utility')
    ->layer('UtilityReflection', 'src/Utility/Reflection')
    ->layer('UtilityString', 'src/Utility/String')
    ->layer('Compiler', 'src/Compiler')
    ->layer('CompilerLibrary', 'src/Compiler/Library')
    ->layer('CompilerNative', 'src/Compiler/Native')
    ->layer('Type', 'src/Type')
    ->layer('TypeDumper', 'src/Type/Dumper')
    ->layer('TypeParser', 'src/Type/Parser')
    ->layer('TypeTypes', 'src/Type/Types')
    ->layer('Definition', 'src/Definition')
    ->layer('DefinitionRepository', 'src/Definition/Repository')
    ->layer('DefinitionRepositoryCache', 'src/Definition/Repository/Cache')
    ->layer('DefinitionRepositoryReflection', 'src/Definition/Repository/Reflection')
    ->layer('Cache', 'src/Cache')
    ->layer('CacheException', 'src/Cache/Exception')
    ->layer('CacheWarmup', 'src/Cache/Warmup')
    ->layer('Mapper', 'src/Mapper')
    ->layer('MapperConfigurator', 'src/Mapper/Configurator')
    ->layer('MapperException', 'src/Mapper/Exception')
    ->layer('MapperHttp', 'src/Mapper/Http')
    ->layer('MapperObject', 'src/Mapper/Object')
    ->layer('MapperSource', 'src/Mapper/Source')
    ->layer('MapperTree', 'src/Mapper/Tree')
    ->layer('MapperTreeBuilder', 'src/Mapper/Tree/Builder')
    ->layer('MapperTreeException', 'src/Mapper/Tree/Exception')
    ->layer('MapperTreeMessage', 'src/Mapper/Tree/Message')
    ->layer('Normalizer', 'src/Normalizer')
    ->layer('NormalizerConfigurator', 'src/Normalizer/Configurator')
    ->layer('NormalizerException', 'src/Normalizer/Exception')
    ->layer('NormalizerFormatter', 'src/Normalizer/Formatter')
    ->layer('NormalizerTransformer', 'src/Normalizer/Transformer')
    ->layer('Library', 'src/Library')
    ->layer('Builder', [
        'src/MapperBuilder.php',
        'src/NormalizerBuilder.php',
    ])
    ->layer('QA', [
        'qa/Benchmark',
        'qa/PHPStan',
        'qa/Psalm',
    ])
    ->ruleset([
        'UtilityString'                  => [],
        'UtilityReflection'              => ['TypeParser'],
        'Utility'                        => ['MapperHttp', 'Type'],
        'Compiler'                       => [],
        'CompilerNative'                 => ['Compiler'],
        'CompilerLibrary'                => ['Compiler', 'Definition', 'Type'],
        'Type'                           => ['Compiler', 'MapperTreeMessage'],
        'TypeParser'                     => ['Type', 'Utility'],
        'TypeTypes'                      => ['Compiler', '+Definition', 'MapperTreeMessage'],
        'TypeDumper'                     => ['+Definition', 'MapperObject', 'MapperTreeBuilder', 'MapperTreeException'],
        'Definition'                     => ['Type', 'Utility'],
        'DefinitionRepository'           => ['Definition', 'Type'],
        'DefinitionRepositoryCache'      => ['Cache', 'Definition', 'Type', 'UtilityReflection'],
        'DefinitionRepositoryReflection' => ['Definition', 'Mapper', 'Normalizer', 'Type', 'UtilityReflection'],
        'Cache'                          => ['+Definition', 'Library'],
        'CacheException'                 => ['TypeTypes'],
        'CacheWarmup'                    => ['CacheException', 'DefinitionRepository', 'MapperObject', 'MapperTreeBuilder', 'Type', 'Utility'],
        'MapperTreeMessage'              => ['UtilityString'],
        'MapperException'                => ['Definition', 'Type'],
        'MapperHttp'                     => ['MapperException'],
        'Mapper'                         => ['Definition', 'TypeParser', 'TypeTypes', 'Utility'],
        'MapperConfigurator'             => ['Builder', 'Mapper'],
        'MapperSource'                   => ['MapperConfigurator'],
        'MapperObject'                   => ['Builder', '+Definition', 'MapperTreeException', 'MapperTreeMessage'],
        'MapperTree'                     => ['+Definition', 'Library'],
        'MapperTreeException'            => ['+Definition', '+MapperException', 'MapperObject', 'MapperTree'],
        'MapperTreeBuilder'              => ['+Definition', 'MapperConfigurator', 'MapperHttp', 'MapperObject', 'MapperTree'],
        'Normalizer'                     => [],
        'NormalizerException'            => ['Definition', 'Type'],
        'NormalizerConfigurator'         => ['Builder', 'Normalizer'],
        'NormalizerTransformer'          => ['Cache', 'Compiler', '+Definition', '+NormalizerException'],
        'NormalizerFormatter'            => ['NormalizerConfigurator', 'NormalizerException', 'NormalizerTransformer'],
        'Library'                        => ['Cache', 'Definition', 'Mapper', 'Normalizer', 'TypeDumper', 'TypeParser'],
        'Builder'                        => ['Cache', 'Library', 'Mapper', 'Normalizer'],
        'QA'                             => ['Builder', 'Mapper', 'Utility'],
    ])

    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY());
