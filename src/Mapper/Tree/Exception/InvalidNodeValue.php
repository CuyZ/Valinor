<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\TranslatableMessage;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\TypeHelper;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @api */
final class InvalidNodeValue extends RuntimeException implements TranslatableMessage
{
    private string $body;

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param mixed $value
     */
    public function __construct($value, Type $type)
    {
        $this->parameters = [
            'value' => ValueDumper::dump($value),
            'expected_type' => TypeHelper::dump($type),
        ];

        $this->body = TypeHelper::containsObject($type)
            ? 'Invalid value {value}.'
            : 'Value {value} does not match type {expected_type}.';

        parent::__construct(StringFormatter::for($this), 1630678334);
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
