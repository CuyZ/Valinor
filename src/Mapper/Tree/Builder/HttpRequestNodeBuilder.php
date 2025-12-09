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
use CuyZ\Valinor\Mapper\Tree\Exception\InappropriateRouteParameter;
use CuyZ\Valinor\Mapper\Tree\Exception\UnexpectedHttpRequestValue;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\UnresolvableType;

use function array_key_exists;
use function assert;
use function is_a;

/** @internal */
final class HttpRequestNodeBuilder implements NodeBuilder
{
    public function build(Shell $shell): Node
    {
        $request = $shell->value();

        assert($request instanceof HttpRequest);
        assert($shell->type instanceof ShapedArrayType);

        if ($shell->type->isUnsealed) {
            throw new CannotMapHttpRequestToUnsealedShapedArray();
        }

        $route = $request->routeParameters;
        $body = $request->bodyValues;
        $query = $request->queryParameters;

        $fromQueryMapAll = false;
        $fromQueryMapSingle = false;
        $fromBodyMapAll = false;
        $fromBodyMapSingle = false;

        $children = [];
        $errors = [];

        // First phase: we loop through all the shaped array elements and try
        // to find corresponding values in the request in the following order:
        // 1. The element is a route parameter, we assign the route value.
        // 2. The element is a query parameter, we assign the query value.
        // 3. The element is a body value, we assign the body value.
        // 4. The element is a request object, we assign the request directly.
        foreach ($shell->type->elements as $key => $element) {
            $attributes = $element->attributes();

            $child = $shell
                ->child((string)$key, $element->type())
                ->withAttributes($attributes);

            if ($attributes->has(FromRoute::class)) {
                if (! array_key_exists($key, $route)) {
                    throw new InappropriateRouteParameter($key);
                }

                $child = $child
                    ->withValue($route[$key])
                    ->allowScalarValueCasting();
            } elseif ($attributes->has(FromQuery::class)) {
                $child = $child->allowScalarValueCasting();
                /** @var FromQuery $attribute */
                $attribute = $attributes->firstOf(FromQuery::class)->instantiate();

                if ($attribute->mapAll) {
                    $fromQueryMapAll = true;

                    $child = $child->withValue($query);
                    $query = [];
                } elseif (array_key_exists($key, $query)) {
                    $fromQueryMapSingle = true;

                    $child = $child->withValue($query[$key]);
                    unset($query[$key]);
                }
            } elseif ($attributes->has(FromBody::class)) {
                /** @var FromBody $attribute */
                $attribute = $attributes->firstOf(FromBody::class)->instantiate();

                if ($attribute->mapAll) {
                    $fromBodyMapAll = true;

                    $child = $child->withValue($body);
                    $body = [];
                } elseif (array_key_exists($key, $body)) {
                    $fromBodyMapSingle = true;

                    $child = $child->withValue($body[$key]);
                    unset($body[$key]);
                }
            } elseif ($request->requestObject && is_a($request->requestObject, $child->type->toString(), true)) {
                $child = $child->withValue($request->requestObject);
            } else {
                throw new CannotMapHttpRequestElement($key);
            }

            $child = $child->build();

            if ($child->isValid()) {
                $children[$key] = $child->value();
            } else {
                $errors[] = $child;
            }
        }

        if ($fromQueryMapAll && $fromQueryMapSingle) {
            throw new CannotUseBothFromQueryAttributes();
        }

        if ($fromBodyMapAll && $fromBodyMapSingle) {
            throw new CannotUseBothFromBodyAttributes();
        }

        // Second phase: if the superfluous keys are not allowed, we add an
        // error for each remaining key in the query and body.
        if (! $shell->allowSuperfluousKeys) {
            foreach ($body + $query as $key => $value) {
                $error = isset($body[$key]) ? UnexpectedHttpRequestValue::forRequestBodyValue($key) : UnexpectedHttpRequestValue::forRequestQueryParameter($key);

                $errors[] = $shell
                    ->child((string)$key, UnresolvableType::forSuperfluousValue((string)$key))
                    ->withValue($value)
                    ->error($error);
            }
        }

        if ($errors === []) {
            return $shell->node($children);
        }

        return $shell->errors($errors);
    }
}
