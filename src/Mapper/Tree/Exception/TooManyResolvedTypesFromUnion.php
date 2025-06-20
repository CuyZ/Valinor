<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Utility\TypeHelper;

use function array_map;
use function implode;

/** @internal */
final class TooManyResolvedTypesFromUnion implements ErrorMessage, HasCode, HasParameters
{
    private string $body;

    private string $code = 'too_many_resolved_types_from_union';

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(UnionType $unionType)
    {
        $this->parameters = [
            'allowed_types' => implode(
                ', ',
                array_map(TypeHelper::dump(...), $unionType->types()),
            ),
        ];

        $this->body = TypeHelper::containsObject($unionType)
            ? 'Invalid value {source_value}, it matches two or more types from union: cannot take a decision.'
            : 'Invalid value {source_value}, it matches two or more types from {allowed_types}: cannot take a decision.';
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
