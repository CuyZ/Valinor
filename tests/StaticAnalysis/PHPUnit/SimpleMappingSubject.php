<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\StaticAnalysis\PHPUnit;

final readonly class SimpleMappingSubject
{
    public function __construct(
        public string $foo,
        public bool $bar,
    ) {
    }
}