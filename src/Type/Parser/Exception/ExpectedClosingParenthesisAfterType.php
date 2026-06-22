<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception;

use RuntimeException;

/** @internal */
final class ExpectedClosingParenthesisAfterType extends RuntimeException implements InvalidType
{
    public function __construct(string $value)
    {
        parent::__construct("Expected closing parenthesis after `$value`.");
    }
}
