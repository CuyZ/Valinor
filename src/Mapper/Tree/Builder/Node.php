<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Mapper\Tree\Shell;

use function array_merge;
use function assert;

/** @internal */
final class Node
{
    private function __construct(
        private mixed $value,
        /** @var list<NodeMessage> */
        private array $messages = [],
        /** @var non-negative-int */
        private int $childrenCount = 0,
    ) {}

    /**
     * @param non-negative-int $childrenCount
     */
    public static function new(mixed $value, int $childrenCount): self
    {
        return new self($value, childrenCount: $childrenCount);
    }

    public static function error(Shell $shell, Message $error): self
    {
        $nodeMessage = new NodeMessage(
            $error,
            $error->body(),
            $shell->name,
            $shell->path,
            "`{$shell->type->toString()}`",
            $shell->expectedSignature(),
            $shell->dumpValue(),
        );

        return new self(null, messages: [$nodeMessage]);
    }

    /**
     * @param array<self> $nodes
     */
    public static function branchWithErrors(array $nodes): self
    {
        $messages = [];

        foreach ($nodes as $node) {
            $messages = array_merge($messages, $node->messages);
        }

        return new self(null, messages: $messages);
    }

    /**
     * @phpstan-assert-if-true ! non-empty-list<NodeMessage> $this->messages()
     */
    public function isValid(): bool
    {
        return $this->messages === [];
    }

    public function value(): mixed
    {
        assert($this->messages === [], 'Trying to get value of an invalid node.');

        return $this->value;
    }

    /**
     * @return list<NodeMessage>
     */
    public function messages(): array
    {
        return $this->messages;
    }

    /**
     * @return non-negative-int
     */
    public function childrenCount(): int
    {
        return $this->childrenCount;
    }
}
