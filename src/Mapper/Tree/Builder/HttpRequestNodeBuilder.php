<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Http\FromBody;
use CuyZ\Valinor\Mapper\Http\FromQuery;
use CuyZ\Valinor\Mapper\Http\FromRoute;
use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotMapHttpRequestElement;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotMapHttpRequestToUnsealedShapedArray;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotUseBothFromBodyAttributes;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotUseBothFromQueryAttributes;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ShapedArrayType;

use function array_intersect_key;
use function array_replace;
use function count;
use function is_a;

/** @internal */
final class HttpRequestNodeBuilder implements NodeBuilder
{
    public function __construct(
        private NodeBuilder $delegate,
    ) {}

    public function build(Shell $shell): Node
    {
        $request = $shell->value();

        if (! $shell->type instanceof ShapedArrayType) {
            return $this->delegate->build($shell);
        }

        if (! $request instanceof HttpRequest) {
            return $this->delegate->build($shell);
        }

        if ($shell->type->isUnsealed) {
            throw new CannotMapHttpRequestToUnsealedShapedArray();
        }

        $routeParameters = $request->routeParameters;
        $queryParameters = $request->queryParameters;
        $bodyValues = $request->bodyValues;

        $routeElements = [];
        $queryElements = [];
        $bodyElements = [];

        $values = [];

        foreach ($shell->type->elements as $key => $element) {
            $attributes = $element->attributes();

            if ($attributes->has(FromRoute::class)) {
                $routeElements[$key] = $element;
            } elseif ($attributes->has(FromQuery::class)) {
                $queryElements[$key] = $element;

                /** @var FromQuery $attribute */
                $attribute = $attributes->firstOf(FromQuery::class)->instantiate();

                if ($attribute->mapAll) {
                    if (count($queryElements) > 1) {
                        throw new CannotUseBothFromQueryAttributes();
                    }

                    $queryParameters = [$key => $request->queryParameters];
                }
            } elseif ($attributes->has(FromBody::class)) {
                $bodyElements[$key] = $element;

                /** @var FromBody $attribute */
                $attribute = $attributes->firstOf(FromBody::class)->instantiate();

                if ($attribute->mapAll) {
                    if (count($bodyElements) > 1) {
                        throw new CannotUseBothFromBodyAttributes();
                    }

                    $bodyValues = [$key => $request->bodyValues];
                }
            } elseif ($request->requestObject && is_a($request->requestObject, $element->type()->toString(), true)) {
                $values[$key] = $request->requestObject;
            } else {
                throw new CannotMapHttpRequestElement($key);
            }
        }

        $errors = [];

        $items = [
            // Route parameters
            $shell
                ->withType(new ShapedArrayType($routeElements))
                ->withValue($routeParameters)
                // Allows converting string values to integers, for example.
                ->allowScalarValueCasting()
                // Some given route parameters might be optional.
                ->allowSuperfluousKeys(),

            // Query parameters
            $shell
                ->withType(new ShapedArrayType($queryElements))
                ->withValue($queryParameters)
                // Allows converting string values to integers, for example.
                ->allowScalarValueCasting(),

            // Body values
            $shell
                ->withType(new ShapedArrayType($bodyElements))
                ->withValue($bodyValues),
        ];

        foreach ($items as $item) {
            $childNode = $item->build();

            if ($childNode->isValid()) {
                $values += $childNode->value(); // @phpstan-ignore assignOp.invalid (we know value is an array)
            } else {
                $errors[] = $childNode;
            }
        }

        if ($errors !== []) {
            return $shell->errors($errors);
        }

        // Reorder values to match the original parameter order.
        $values = array_replace(array_intersect_key($shell->type->elements, $values), $values);

        return $shell->node($values);
    }
}
