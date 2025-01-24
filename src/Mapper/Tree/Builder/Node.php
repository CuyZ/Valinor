<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\UnexpectedKeysInSource;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Utility\ValueDumper;
use Throwable;

use function array_diff;
use function array_keys;
use function array_merge;
use function assert;
use function is_array;

/** @internal */
final class Node
{
    private function __construct(
        private mixed $value,
        /** @var list<NodeMessage> */
        private array $messages,
        /** @var non-negative-int */
        private int $childrenCount = 0,
    ) {}

    public static function leaf(mixed $value): self
    {
        return new self(value: $value, messages: []);
    }

    /**
     * @param iterable<mixed>|object $value
     * @param non-negative-int $childrenCount
     */
    public static function branch(iterable|object $value, int $childrenCount = 0): self
    {
        return new self(value: $value, messages: [], childrenCount: $childrenCount);
    }

    public static function leafWithError(Shell $shell, Throwable&Message $error): self
    {
        $nodeMessage = new NodeMessage(
            $error,
            $error->body(),
            $shell->name(),
            $shell->path(),
            "`{$shell->type()->toString()}`",
            $shell->hasValue() ? ValueDumper::dump($shell->value()) : '*missing*',
        );

        return new self(value: null, messages: [$nodeMessage]);
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

        return new self(value: null, messages: $messages);
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

    public function flatten(): self
    {
        $messages = [];

        foreach ($this->messages as $message) {
            $path = $message->path();

            if (strpos($path, '.')) {
                $path = explode('.', $path, 2)[1];
            } else {
                $path = '*root*';
            }

            $messages[] = new NodeMessage(
                $message->originalMessage(),
                $message->body(),
                $message->name(),
                $path,
                $message->type(),
                $message->sourceValue(),
            );
        }

        return new self(
            $this->value,
            $messages,
            $this->childrenCount,
        );
    }

    /**
     * @param list<int|string> $children
     */
    public function checkUnexpectedKeys(Shell $shell, array $children): self
    {
        $value = $shell->value();

        if ($shell->allowSuperfluousKeys() || ! is_array($value)) {
            return $this;
        }

        $diff = array_diff(array_keys($value), $children, $shell->allowedSuperfluousKeys());

        if ($diff !== []) {
            /** @var non-empty-list<int|string> $children */
            $error = new UnexpectedKeysInSource($value, $children);

            $nodeMessage = new NodeMessage(
                $error,
                $error->body(),
                $shell->name(),
                $shell->path(),
                "`{$shell->type()->toString()}`",
                ValueDumper::dump($shell->value()),
            );

            return new self(
                value: null,
                messages: array_merge($this->messages, [$nodeMessage])
            );
        }

        return $this;
    }
}
