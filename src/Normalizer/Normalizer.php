<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

/**
 * @api
 *
 * @template-covariant T
 */
interface Normalizer
{
    /**
     * A normalizer is a service that transforms a given input into scalar and
     * array values, while preserving the original structure.
     *
     * This feature can be used to share information with other systems that use
     * a data format (JSON, CSV, XML, etc.). The normalizer will take care of
     * recursively transforming the data into a format that can be serialized.
     *
     * @pure
     * @return T
     */
    public function normalize(mixed $value): mixed;
}
