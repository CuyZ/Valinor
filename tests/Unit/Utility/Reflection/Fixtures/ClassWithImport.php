<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures;

use Closure;
use RuntimeException;

final readonly class ClassWithImport
{
    public Closure $closure;
    public function __construct(

    ) {
        $imported = require(__DIR__ . '/ClosureForImport.php');
        if (!$imported instanceof Closure) {
            throw new RuntimeException('Invalid closure');
        }
        $this->closure = $imported;
    }
}
