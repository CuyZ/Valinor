<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Utility\ValueDumper;

/** @internal */
final class InvalidArrayKey implements ErrorMessage, HasCode, HasParameters
{
    private string $body = 'Key {key} does not match type {expected_type}.';

    private string $code = 'invalid_array_key';

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(string|int $key, ArrayKeyType $type)
    {
        $this->parameters = [
            'key' => ValueDumper::dump($key),
            'expected_type' => '`' . $type->toString() . '`',
        ];
    }

    public function body(): string
    {
        return $this->body;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }
}
