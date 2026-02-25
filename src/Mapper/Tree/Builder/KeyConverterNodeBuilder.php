<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use Exception;
use Throwable;

use function is_array;
use function is_iterable;
use function iterator_to_array;

/** @internal */
final class KeyConverterNodeBuilder implements NodeBuilder
{
    public function __construct(
        private NodeBuilder $delegate,
        private KeyConverterContainer $keyConverterContainer,
        /** @var callable(Throwable): ErrorMessage */
        private mixed $exceptionFilter,
    ) {}

    public function build(Shell $shell): Node
    {
        if (! $this->shouldConvertKeys($shell)) {
            return $this->delegate->build($shell);
        }

        $value = $shell->value();

        if (! is_iterable($value)) {
            return $this->delegate->build($shell);
        }

        if (! is_array($value)) {
            $value = iterator_to_array($value);
        }

        $newValue = [];
        $keyMap = [];
        $errors = [];

        foreach ($value as $key => $val) {
            $convertedKey = (string)$key;

            try {
                foreach ($this->keyConverterContainer->converters() as $converter) {
                    $convertedKey = $converter($convertedKey);
                }
            } catch (Exception $exception) {
                if (! $exception instanceof Message) {
                    $exception = ($this->exceptionFilter)($exception);
                }

                $errors[$key] = $shell
                    ->child((string)$key, UnresolvableType::forInvalidKey())
                    ->withValue($val)
                    ->error($exception);
            }

            $newValue[$convertedKey] = $val;
            $keyMap[$convertedKey] = (string)$key;
        }

        if ($errors !== []) {
            return Node::branchWithErrors($errors);
        }

        return $this->delegate->build(
            $shell->withValue($newValue)->withNameMap($keyMap),
        );
    }

    private function shouldConvertKeys(Shell $shell): bool
    {
        // Keys were already converted by a previous pass through this builder,
        // so we skip to avoid double-transformation.
        if ($shell->hasNameMap()) {
            return false;
        }

        return $shell->type instanceof ShapedArrayType
            || $shell->type instanceof ObjectType;
    }
}
