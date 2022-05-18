<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\TranslatableMessage;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @api */
final class CannotParseToDateTime extends RuntimeException implements TranslatableMessage
{
    private string $body = 'Value {value} does not match a valid date format.';

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param string|int $datetime
     */
    public function __construct($datetime)
    {
        $this->parameters = [
            'value' => ValueDumper::dump($datetime),
        ];

        parent::__construct(StringFormatter::for($this), 1630686564);
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
