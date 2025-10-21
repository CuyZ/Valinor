<?php

$finder = PhpCsFixer\Finder::create()->in([
    './src',
    './tests',
    './qa',
])
    ->notPath('Integration/Normalizer/ExpectedCache/')
    ->notPath('Fixtures/FunctionWithGroupedImportStatements.php')
    ->notPath('Fixtures/FunctionWithSeveralImportStatementsInSameUseStatement.php')
    ->notPath('Fixtures/TwoClassesInDifferentNamespaces.php');

if (PHP_VERSION_ID < 8_02_00) {
    $finder = $finder->notPath('Fixture/Object/ObjectWithPropertyWithNativeDisjunctiveNormalFormType.php');
}

if (PHP_VERSION_ID < 8_05_00) {
    $finder = $finder->notPath('Integration/Normalizer/TemporaryPHP85/ClassWithPropertyTransformerWithCallable.php');
}

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setCacheFile('var/cache/.php_cs.cache')
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR1' => true,
        '@PSR12' => true,
        'no_unused_imports' => true,
        'no_extra_blank_lines' => true,
        'no_empty_phpdoc' => true,
        'single_line_empty_body' => true,
        'no_empty_statement' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'remove_inheritdoc' => true,
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'native_function_invocation' => [
            'include' => [
                '@internal',
            ],
        ],
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
    ]);
