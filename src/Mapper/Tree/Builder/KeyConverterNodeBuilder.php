<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\UnresolvableType;

use function is_array;
use function is_iterable;
use function iterator_to_array;

/** @internal */
final class KeyConverterNodeBuilder implements NodeBuilder
{
    public function __construct(
        private NodeBuilder $delegate,
        private KeyConversionPipeline $keyConverterContainer,
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

        [$newValue, $nameMap, $keyErrors] = $this->keyConverterContainer->convert($value);

        $errors = [];

        foreach ($keyErrors as $key => $error) {
            $errors[] = $shell
                ->child($key, UnresolvableType::forInvalidKey())
                ->error($error);
        }

        if ($errors !== []) {
            return $shell->errors($errors);
        }

        return $this->delegate->build(
            $shell->withValue($newValue)->withNameMap($nameMap),
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
