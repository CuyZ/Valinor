<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

/** @api */
interface ArgumentsMapper
{
    /**
     * @pure
     *
     * @return array<string, mixed>
     *
     * @throws MappingError
     */
    public function mapArguments(callable $callable, mixed $source): array;
}
