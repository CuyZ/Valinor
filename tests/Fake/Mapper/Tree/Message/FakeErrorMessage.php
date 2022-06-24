<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use Exception;

final class FakeErrorMessage extends Exception implements ErrorMessage
{
    public function __construct(string $message = 'some error message', int $code = 1652883436)
    {
        parent::__construct($message, $code);
    }
}
