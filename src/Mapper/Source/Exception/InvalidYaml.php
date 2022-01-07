<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Exception;

use RuntimeException;

/** @internal */
final class InvalidYaml extends RuntimeException implements SourceException
{
    public function __construct()
    {
        parent::__construct(
            "The given value is not a valid YAML entry.",
            1629990223
        );
    }
}
