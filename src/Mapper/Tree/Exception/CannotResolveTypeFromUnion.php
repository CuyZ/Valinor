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
final class CannotResolveTypeFromUnion implements ErrorMessage, HasCode, HasParameters
{
    private string $body;

    private string $code = 'cannot_resolve_type_from_union';

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(mixed $source, UnionType $unionType)
    {
        $this->parameters = [
            'allowed_types' => implode(
                ', ',
                array_map(TypeHelper::dump(...), $unionType->types())
            ),
        ];

        if ($source === null) {
            $this->body = TypeHelper::containsObject($unionType)
                ? 'Cannot be empty.'
                : 'Cannot be empty and must be filled with a value matching any of {allowed_types}.';
        } else {
            $this->body = TypeHelper::containsObject($unionType)
                ? 'Invalid value {source_value}.'
                : 'Value {source_value} does not match any of {allowed_types}.';
        }
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
