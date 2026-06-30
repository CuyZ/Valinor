<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Exception;

use RuntimeException;

/** @internal */
final class IgnoreOnNormalizationIsNotRegistered extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'The `IgnoreOnNormalization` configurator must be registered on the normalizer builder with ' .
            '`configureWith(new IgnoreOnNormalization())` for the attribute to take effect.',
        );
    }
}
