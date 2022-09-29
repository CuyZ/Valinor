<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use RuntimeException;

/** @internal */
final class CannotParseToBackwardCompatibilityDateTime extends RuntimeException implements ErrorMessage
{
    private string $body = 'Value {source_value} does not match a valid date format.';

    public function __construct()
    {
        parent::__construct($this->body, 1659706547);
    }

    public function body(): string
    {
        return $this->body;
    }
}
