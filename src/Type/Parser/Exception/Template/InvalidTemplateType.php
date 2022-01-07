<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Template;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use LogicException;

/** @internal */
final class InvalidTemplateType extends LogicException implements InvalidTemplate
{
    public function __construct(string $type, string $template, InvalidType $exception)
    {
        parent::__construct(
            "Invalid type `$type` for the template `$template`: {$exception->getMessage()}",
            1607445951,
            $exception
        );
    }
}
