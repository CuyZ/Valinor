<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Http\FromBody;
use CuyZ\Valinor\Mapper\Http\FromQuery;
use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\Mapper\Tree\Exception\UnexpectedHttpRequestValue;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use RuntimeException;

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
            throw new RuntimeException('Cannot map to unsealed shaped array'); // @todo move to own exception class
        }

        $route = $request->routeParameters;
        $body = $request->bodyValues;
        $query = $request->queryParameters;

        $children = [];
        $errors = [];

        // First phase: we loop through all the shaped array elements and try
        // to find corresponding values in the request in the following order:
        // 1. The element is a request object, we assign the request directly.
        // 2. The element is a route parameter, we assign the route value.
        // 3. The element is a query parameter, we assign the query value.
        // 4. The element is a body value, we assign the body value.
        foreach ($shell->type->elements as $key => $element) {
            $child = $shell
                ->child((string)$key, $element->type())
                ->withAttributes($element->attributes());

            if ($request->requestObject && is_a($request->requestObject, $child->type->toString(), true)) {
                $child = $child->withValue($request->requestObject);
            } elseif (array_key_exists($key, $route)) {
                $child = $child
                    ->withValue($route[$key])
                    ->allowScalarValueCasting();
            } elseif (array_key_exists($key, $query) && $element->attributes()->has(FromQuery::class)) {
                $child = $child
                    ->withValue($query[$key])
                    ->allowScalarValueCasting();

                unset($query[$key]);
            } elseif (array_key_exists($key, $body) && $element->attributes()->has(FromBody::class)) {
                $child = $child->withValue($body[$key]);

                unset($body[$key]);
            } else {
                throw new RuntimeException("Element `$key` cannot be mapped"); // @todo move to own exception class
            }

            $child = $child->build();

            if ($child->isValid()) {
                $children[$key] = $child->value();
            } else {
                $errors[] = $child;
            }
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
