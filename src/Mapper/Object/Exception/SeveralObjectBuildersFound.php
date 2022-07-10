<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\TranslatableMessage;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @internal */
final class SeveralObjectBuildersFound extends RuntimeException implements TranslatableMessage
{
    private string $body = 'Invalid value {value}.';

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param mixed $source
     */
    public function __construct($source)
    {
        $this->parameters = [
            'value' => ValueDumper::dump($source),
        ];

        parent::__construct(StringFormatter::for($this), 1642787246);
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
