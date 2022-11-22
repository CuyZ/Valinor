<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

/** @api */
interface ArgumentsMapper
{
    /**
     * @pure
     *
     * @param mixed $source
     * @return array<string, mixed>
     *
     * @throws MappingError
     */
    public function mapArguments(callable $callable, $source): array;
}
