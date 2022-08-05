<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree;

use CuyZ\Valinor\Mapper\Tree\Exception\InvalidNodeHasNoMappedValue;
use CuyZ\Valinor\Mapper\Tree\Exception\SourceValueWasNotFilled;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;

/** @api */
final class Node
{
    private bool $isRoot;

    private string $name;

    private string $path;

    private string $type;

    private bool $sourceFilled;

    /** @var mixed */
    private $sourceValue;

    private bool $isValid = true;

    /** @var mixed */
    private $mappedValue;

    /** @var array<NodeMessage> */
    private array $messages = [];

    /** @var array<self> */
    private array $children;

    /**
     * @param mixed $sourceValue
     * @param mixed $mappedValue
     * @param array<Message> $messages
     * @param array<self> $children
     */
    public function __construct(
        bool $isRoot,
        string $name,
        string $path,
        string $type,
        bool $sourceFilled,
        $sourceValue,
        $mappedValue,
        array $messages,
        array $children
    ) {
        $this->isRoot = $isRoot;
        $this->name = $name;
        $this->path = $path;
        $this->type = $type;
        $this->sourceFilled = $sourceFilled;
        $this->sourceValue = $sourceValue;
        $this->mappedValue = $mappedValue;
        $this->children = $children;

        foreach ($messages as $message) {
            $message = new NodeMessage($this, $message);

            $this->messages[] = $message;
            $this->isValid = $this->isValid && ! $message->isError();
        }

        foreach ($this->children as $child) {
            $this->isValid = $this->isValid && $child->isValid();
        }
    }

    public function isRoot(): bool
    {
        return $this->isRoot;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function sourceFilled(): bool
    {
        return $this->sourceFilled;
    }

    /**
     * @return mixed
     */
    public function sourceValue()
    {
        if (! $this->sourceFilled) {
            throw new SourceValueWasNotFilled($this->path);
        }

        return $this->sourceValue;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @return mixed
     */
    public function mappedValue()
    {
        if (! $this->isValid) {
            throw new InvalidNodeHasNoMappedValue($this->path);
        }

        return $this->mappedValue;
    }

    /**
     * @deprecated use `$node->mappedValue()` instead
     *
     * @return mixed
     */
    public function value()
    {
        return $this->mappedValue();
    }

    /**
     * @return array<NodeMessage>
     */
    public function messages(): array
    {
        return $this->messages;
    }

    /**
     * @return array<self>
     */
    public function children(): array
    {
        return $this->children;
    }
}
