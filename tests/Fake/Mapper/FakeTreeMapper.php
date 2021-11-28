<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper;

use CuyZ\Valinor\Mapper\TreeMapper;
use stdClass;

final class FakeTreeMapper implements TreeMapper
{
    /** @var array<string, object> */
    private array $objects = [];

    /**
     * @param mixed $source
     * @phpstan-return object
     */
    public function map(string $signature, $source): object
    {
        return $this->objects[$signature] ?? new stdClass();
    }

    /**
     * @param class-string $signature
     */
    public function willReturn(string $signature, object $object): void
    {
        $this->objects[$signature] = $object;
    }
}
