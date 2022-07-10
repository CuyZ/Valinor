<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Tree\Message\TranslatableMessage;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\TypeHelper;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @internal */
final class SourceIsNotAnArray extends RuntimeException implements TranslatableMessage
{
    private string $body;

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param mixed $source
     */
    public function __construct($source, Arguments $arguments)
    {
        $this->parameters = [
            'value' => ValueDumper::dump($source),
            'expected_type' => TypeHelper::dumpArguments($arguments),
        ];

        $this->body = $source === null
            ? 'Cannot be empty and must be filled with a value matching type {expected_type}.'
            : 'Value {value} does not match type {expected_type}.';

        parent::__construct(StringFormatter::for($this), 1632903281);
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
