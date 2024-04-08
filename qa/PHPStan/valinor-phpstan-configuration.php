<?php

use CuyZ\Valinor\QA\PHPStan\Extension\ArgumentsMapperPHPStanExtension;
use CuyZ\Valinor\QA\PHPStan\Extension\TreeMapperPHPStanExtension;
use CuyZ\Valinor\QA\PHPStan\Extension\TypeMappingHelper;

require_once 'Extension/ArgumentsMapperPHPStanExtension.php';
require_once 'Extension/TreeMapperPHPStanExtension.php';
require_once 'Extension/TypeMappingHelper.php';

return [
    'services' => [
        [
            'class' => TreeMapperPHPStanExtension::class,
            'tags' => ['phpstan.broker.dynamicMethodReturnTypeExtension']
        ], [
            'class' => ArgumentsMapperPHPStanExtension::class,
            'tags' => ['phpstan.broker.dynamicMethodReturnTypeExtension']
        ], [
            'class' => TypeMappingHelper::class,
        ]
    ],
];
