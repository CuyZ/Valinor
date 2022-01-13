<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

/** @api */
interface TreeMapper
{
    /**
     * @template T of object
     *
     * @param string|class-string<T> $signature
     * @param mixed $source
     * @return T|mixed
     *
     * @psalm-return (
     *     $signature is class-string<T>
     *         ? T
     *         : mixed
     * )
     *
     * @throws MappingError
     */
    public function map(string $signature, $source);
}
