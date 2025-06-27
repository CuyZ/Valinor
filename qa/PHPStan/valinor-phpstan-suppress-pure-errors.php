<?php

use CuyZ\Valinor\QA\PHPStan\Extension\SuppressPureErrors;

require_once 'Extension/SuppressPureErrors.php';

return [
    'services' => [
        [
            'class' => SuppressPureErrors::class,
            'tags' => ['phpstan.ignoreErrorExtension'],
        ],
    ],
];
