<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\TypeHelper;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @internal */
final class InvalidTraversableKey extends RuntimeException implements ErrorMessage, HasParameters
{
    private string $body = 'Key {key} does not match type {expected_type}.';

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(string|int $key, ArrayKeyType $type)
    {
        $this->parameters = [
            'key' => ValueDumper::dump($key),
            'expected_type' => TypeHelper::dump($type),
        ];

        parent::__construct(StringFormatter::for($this), 1630946163);
    }

    public function body(): string
    {
        return $this->body;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }
}
