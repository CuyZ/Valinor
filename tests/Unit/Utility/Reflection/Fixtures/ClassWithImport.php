<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures {
    use Closure;

    final class ClassWithImport
    {
        public Closure $closure;

        public function __construct()
        {
            // @phpstan-ignore assign.propertyType
            $this->closure = require(__DIR__ . '/ClosureForImport.php');
        }
    }
}
