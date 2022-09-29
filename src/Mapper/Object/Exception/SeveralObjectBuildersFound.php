<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use RuntimeException;

/** @internal */
final class SeveralObjectBuildersFound extends RuntimeException implements ErrorMessage
{
    private string $body = 'Invalid value {source_value}.';

    public function __construct()
    {
        parent::__construct($this->body, 1642787246);
    }

    public function body(): string
    {
        return $this->body;
    }
}
