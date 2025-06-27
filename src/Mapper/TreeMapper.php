<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

/** @api */
interface TreeMapper
{
    /**
     * @pure
     *
     * @template T of object
     *
     * @param string|class-string<T> $signature
     * @return T
     * @phpstan-return (
     *     $signature is class-string<T>
     *         ? T
     *         : ($signature is class-string ? object : mixed)
     * )
     *
     * @throws MappingError
     */
    public function map(string $signature, mixed $source): mixed;
}
