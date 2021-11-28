<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

interface TreeMapper
{
    /**
     * @template T of object
     *
     * @param class-string<T> $signature
     * @param mixed $source
     * @return T
     *
     * @throws MappingError
     */
    public function map(string $signature, $source): object;
}
