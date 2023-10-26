<?php

declare(strict_types=1);

namespace CuyZValinorTestsIntegrationMappingFixture;

final class Foo
{
    public function __construct(
        /** @var list<string> */
        public array $value,
    ) {}
}
