<?php

$finder = PhpCsFixer\Finder::create()->in([
    './src',
    './tests',
    './qa',
])
    ->notPath('Fixtures/FunctionWithGroupedImportStatements.php')
    ->notPath('Fixtures/FunctionWithSeveralImportStatementsInSameUseStatement.php')
    ->notPath('Fixtures/TwoClassesInDifferentNamespaces.php');

if (PHP_VERSION_ID < 8_01_00) {
    $finder = $finder->notPath('Fixture/Enum/PureEnum.php');
    $finder = $finder->notPath('Fixture/Enum/BackedStringEnum.php');
    $finder = $finder->notPath('Fixture/Enum/BackedIntegerEnum.php');
    $finder = $finder->notPath('Fixture/Object/ObjectWithPropertyWithNativeIntersectionType.php');
    $finder = $finder->notPath('Integration/Mapping/Fixture/ReadonlyValues.php');
}

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setCacheFile('var/cache/.php_cs.cache')
    ->setRules([
        '@PSR1' => true,
        '@PSR12' => true,
        'no_unused_imports' => true,
        'no_extra_blank_lines' => true,
        'no_empty_phpdoc' => true,
        'single_line_empty_body' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'remove_inheritdoc' => true,
        ],
    ]);
