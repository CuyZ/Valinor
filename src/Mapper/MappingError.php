<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use Throwable;

/** @api */
interface MappingError extends Throwable
{
    /**
     * Container for all messages that were caught during the mapping process.
     *
     * @pure
     */
    public function messages(): Messages;

    /**
     * Returns the original type that the mapper was attempting to map to.
     *
     * @pure
     */
    public function type(): string;

    /**
     * Returns the original source value given to the mapper.
     *
     * @pure
     */
    public function source(): mixed;
}
