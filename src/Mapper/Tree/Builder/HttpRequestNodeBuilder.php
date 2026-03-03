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

        $routeElements = [];
        $queryElements = [];
        $bodyElements = [];

        $mapAllQueryKey = null;
        $mapAllBodyKey = null;

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
                    $mapAllQueryKey = $key;
                }
            } elseif ($attributes->has(FromBody::class)) {
                $bodyElements[$key] = $element;

                /** @var FromBody $attribute */
                $attribute = $attributes->firstOf(FromBody::class)->instantiate();

                if ($attribute->mapAll) {
                    $mapAllBodyKey = $key;
                }
            } elseif ($request->requestObject && is_a($request->requestObject, $element->type()->toString(), true)) {
                $values[$key] = $request->requestObject;
            } else {
                throw new CannotMapHttpRequestElement($key);
            }
        }

        $errors = [];

        // -----------------//
        // Route parameters //
        // -----------------//
        $routeNode = $shell
            ->withType(new ShapedArrayType($routeElements))
            ->withValue($request->routeParameters)
            // Allows converting string values to integers, for example.
            ->allowScalarValueCasting()
            // Some given route parameters might be optional.
            ->allowSuperfluousKeys()
            ->build();

        if ($routeNode->isValid()) {
            $values += $routeNode->value(); // @phpstan-ignore assignOp.invalid (we know value is an array)
        } else {
            $errors[] = $routeNode;
        }

        // -----------------//
        // Query parameters //
        // -----------------//
        if ($mapAllQueryKey !== null) {
            if (count($queryElements) > 1) {
                throw new CannotUseBothFromQueryAttributes();
            }

            $queryType = $queryElements[$mapAllQueryKey]->type();
        } else {
            $queryType = new ShapedArrayType($queryElements);
        }

        $queryNode = $shell
            ->withType($queryType)
            ->withValue($request->queryParameters)
            // Allows converting string values to integers, for example.
            ->allowScalarValueCasting()
            ->build();

        if (! $queryNode->isValid()) {
            $errors[] = $queryNode;
        } elseif ($mapAllQueryKey !== null) {
            $values[$mapAllQueryKey] = $queryNode->value();
        } else {
            $values += $queryNode->value(); // @phpstan-ignore assignOp.invalid (we know value is an array)
        }

        // ------------//
        // Body values //
        // ------------//
        if ($mapAllBodyKey !== null) {
            $bodyType = $bodyElements[$mapAllBodyKey]->type();
        } else {
            $bodyType = new ShapedArrayType($bodyElements);
        }

        $bodyNode = $shell
            ->withType($bodyType)
            ->withValue($request->bodyValues)
            ->build();

        if (! $bodyNode->isValid()) {
            $errors[] = $bodyNode;
        } elseif ($mapAllBodyKey !== null) {
            if (count($bodyElements) > 1) {
                throw new CannotUseBothFromBodyAttributes();
            }

            $values[$mapAllBodyKey] = $bodyNode->value();
        } else {
            $values += $bodyNode->value(); // @phpstan-ignore assignOp.invalid (we know value is an array)
        }

        if ($errors !== []) {
            return $shell->errors($errors);
        }

        // Reorder values to match the original parameter order.
        $values = array_replace(array_intersect_key($shell->type->elements, $values), $values);

        return $shell->node($values);
    }
}
