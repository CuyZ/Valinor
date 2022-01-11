<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

/** @api */
interface TreeMapper
{
    /**
     * @psalm-template TObject of object
     * @psalm-template TypeDefinition of string|class-string<TObject>
     *
     * @param TypeDefinition $signature
     * @param mixed $source
     * @return TObject|mixed
     *
     * @psalm-return (
     *     $signature is class-string<TObject>
     *         ? TObject
     *         : mixed
     * )
     *
     * @throws MappingError
     */
    public function map(string $signature, $source);
}
