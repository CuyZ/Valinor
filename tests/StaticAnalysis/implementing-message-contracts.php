<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\StaticAnalysis;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use Override;
use RuntimeException;

use function count;
use function get_debug_type;

final readonly class MessageHoldingItsValues implements ErrorMessage, HasCode, HasParameters
{
    public function __construct(
        private string $body,
        private string $code,
        private string $value,
    ) {}

    #[Override]
    public function body(): string
    {
        return $this->body;
    }

    #[Override]
    public function code(): string
    {
        return $this->code;
    }

    /**
     * @psalm-mutation-free
     * @return array<string, string>
     */
    #[Override]
    public function parameters(): array
    {
        return ['value' => $this->value];
    }
}

final class MappingErrorHoldingItsValues extends RuntimeException implements MappingError
{
    public function __construct(
        private readonly Messages $messages,
        private readonly string $type,
        private readonly mixed $source,
    ) {
        parent::__construct('Could not map the source.');
    }

    #[Override]
    public function messages(): Messages
    {
        return $this->messages;
    }

    #[Override]
    public function type(): string
    {
        return $this->type;
    }

    #[Override]
    public function source(): mixed
    {
        return $this->source;
    }
}

function implementations_of_message_contracts_may_read_their_own_state(Messages $messages): string
{
    $message = new MessageHoldingItsValues('Invalid value {value}.', 'invalid_value', 'foo');

    $error = new MappingErrorHoldingItsValues($messages, 'string', 'foo');

    return $message->body() . $message->code() . $message->parameters()['value']
        . $error->type() . count($error->messages()) . get_debug_type($error->source());
}
