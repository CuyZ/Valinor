<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\Mapper\Tree\Exception\UnexpectedHttpRequestValue;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use RuntimeException;

use function array_key_exists;
use function assert;
use function in_array;
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

        // Here is the most opinionated part of the algorithm: we chose what
        // part of the request is used to map to the desired shaped array.
        // If the request is supposed to have a body, we use the body values,
        // otherwise we use the query parameters.
        $requestHasBody = in_array($request->method, ['POST', 'PUT', 'PATCH'], true);
        $requestValues = $requestHasBody ? $request->bodyValues : $request->queryParameters;

        $children = [];
        $errors = [];

        // First phase: we loop through all the shaped array elements and try
        // to find corresponding values in the request in the following order:
        // 1. The element is a request object, we assign the request directly.
        // 2. The element is a route parameter, we assign the parameter value.
        // 3. The element is a value from the request's query parameters *or*
        //    the request's body values, we assign it.
        foreach ($shell->type->elements as $key => $element) {
            $child = $shell
                ->child((string)$key, $element->type())
                ->withAttributes($element->attributes());

            if ($request->requestObject && is_a($request->requestObject, $child->type->toString(), true)) {
                $child = $child->withValue($request->requestObject);
            } elseif (array_key_exists($key, $request->routeParameters)) {
                $child = $child
                    ->withValue($request->routeParameters[$key])
                    ->allowScalarValueCasting();
            } elseif (isset($requestValues[$key])) {
                // If the request does not have a body, we work with the query
                // parameters that contain only string values, thus we need to
                // make sure these values can be cast properly when needed.
                if (! $requestHasBody) {
                    $child = $child->allowScalarValueCasting();
                }

                $child = $child->withValue($requestValues[$key]);

                unset($requestValues[$key]);
            }

            $child = $child->build();

            if ($child->isValid()) {
                $children[$key] = $child->value();
            } else {
                $errors[] = $child;
            }
        }

        // Second phase: if the superfluous keys are not allowed, we add an
        // error for each remaining key in the request's values.
        if (! $shell->allowSuperfluousKeys) {
            foreach ($requestValues as $key => $value) {
                $error = $requestHasBody ? UnexpectedHttpRequestValue::forRequestBodyValue($key) : UnexpectedHttpRequestValue::forRequestQueryParameter($key);

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
