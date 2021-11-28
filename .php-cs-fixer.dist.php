<?php

$finder = PhpCsFixer\Finder::create()->in([
    './src',
    './tests'
]);

if (PHP_VERSION_ID < 8_00_00) {
    $finder = $finder
        ->notPath('Fixture/Attribute/AttributeWithArguments.php')
        ->notPath('Fixture/Object/ObjectWithAttributes.php')
        ->notPath('Fixture/Object/ObjectWithPropertyWithNativeUnionType.php')
        ->notPath('Integration/Mapping/Fixture/NativeUnionValues.php');
}

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
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'remove_inheritdoc' => true
        ],
    ]);
