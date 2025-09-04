<?php

use CuyZ\Valinor\Tests\Integration\Normalizer\TemporaryPHP85\ClassWithPropertyTransformerWithCallable;
use CuyZ\Valinor\Tests\Integration\Normalizer\TemporaryPHP85\ClassWithTransformerWithCallable;

return [
    new ClassWithPropertyTransformerWithCallable('foo'),
    new ClassWithTransformerWithCallable('foo'),
];
