<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use RuntimeException;

/** @internal */
final class SourceIsNotNull extends RuntimeException implements ErrorMessage
{
    private string $body;

    public function __construct()
    {
        $this->body = 'Value {source_value} is not null.';

        parent::__construct($this->body, 1710263908);
    }

    public function body(): string
    {
        return $this->body;
    }
}
