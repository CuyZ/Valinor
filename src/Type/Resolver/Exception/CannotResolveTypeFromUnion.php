<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Resolver\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\TypeHelper;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

use function array_map;
use function implode;

/** @internal */
final class CannotResolveTypeFromUnion extends RuntimeException implements ErrorMessage, HasParameters
{
    private string $body;

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param mixed $value
     */
    public function __construct(UnionType $unionType, $value)
    {
        $this->parameters = [
            'value' => ValueDumper::dump($value),
            'allowed_types' => implode(
                ', ',
                // @PHP8.1 First-class callable syntax
                array_map([TypeHelper::class, 'dump'], $unionType->types())
            ),
        ];

        $this->body = TypeHelper::containsObject($unionType)
            ? 'Invalid value {value}.'
            : 'Value {value} does not match any of {allowed_types}.';

        parent::__construct(StringFormatter::for($this), 1607027306);
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
