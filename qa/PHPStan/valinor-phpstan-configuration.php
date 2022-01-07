<?php

use CuyZ\Valinor\QA\PHPStan\Extension\TreeMapperPHPStanExtension;

require_once 'Extension/TreeMapperPHPStanExtension.php';

return [
    'services' => [
        [
            'class' => TreeMapperPHPStanExtension::class,
            'tags' => ['phpstan.broker.dynamicMethodReturnTypeExtension']
        ]
    ],
];
