<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Http\FromBody;
use CuyZ\Valinor\Mapper\Http\FromQuery;
use CuyZ\Valinor\Mapper\Http\FromRoute;
use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotMapHttpRequestToUnsealedShapedArray;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotUseBothFromBodyAttributes;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotUseBothFromQueryAttributes;
use CuyZ\Valinor\Mapper\Tree\Exception\HttpRequestKeyCollision;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\UnresolvableType;

use function array_intersect_key;
use function array_key_exists;
use function array_keys;
use function assert;

/** @internal */
final class HttpRequestNodeBuilder implements NodeBuilder
{
    public function build(Shell $shell): Node
    {
        $request = $shell->value();
        $type = $shell->type;

        assert($request instanceof HttpRequest);
        assert($type instanceof ShapedArrayType);

        if ($type->isUnsealed()) {
            throw new CannotMapHttpRequestToUnsealedShapedArray();
        }

        // We always allow superfluous keys: HTTP request are coming from the
        // outside of the application, meaning extra parameters can be added
        // anytime. This could lead to server issues like DDoS log spam.
        $shell = $shell->allowSuperfluousKeys();

        $queryAttributes = 0;
        $bodyAttributes = 0;
        $queryAsRoot = false;
        $bodyAsRoot = false;

        $requestObjects = [];
        $route = $request->routeParameters;
        $query = $request->queryParameters;
        $body = $request->bodyValues;

        $collisionErrors = [];
        $collisions = array_intersect_key($route, $query) + array_intersect_key($route, $body) + array_intersect_key($query, $body);

        foreach ($type->elements as $key => $element) {
            $attributes = $element->attributes();

            if ($attributes->has(FromRoute::class)) {
                // The value must *NEVER* come from query or body.
                unset($query[$key], $body[$key]);
            } elseif ($attributes->has(FromQuery::class)) {
                // The value must *NEVER* come from route or body.
                unset($route[$key], $body[$key]);

                $queryAttributes++;
                $queryAsRoot = $queryAsRoot || $attributes->firstOf(FromQuery::class)->instantiate()->asRoot; // @phpstan-ignore property.notFound

                if ($queryAsRoot) {
                    if ($queryAttributes > 1) {
                        throw new CannotUseBothFromQueryAttributes();
                    }

                    $query = [$key => $query];
                    $shell = $shell->withPathMap(["$shell->path.$key" => $shell->path]);
                }
            } elseif ($attributes->has(FromBody::class)) {
                // The value must *NEVER* come from route or query.
                unset($route[$key], $query[$key]);

                $bodyAttributes++;
                $bodyAsRoot = $bodyAsRoot || $attributes->firstOf(FromBody::class)->instantiate()->asRoot; // @phpstan-ignore property.notFound

                if ($bodyAsRoot) {
                    if ($bodyAttributes > 1) {
                        throw new CannotUseBothFromBodyAttributes();
                    }

                    $body = [$key => $body];
                    $shell = $shell->withPathMap(["$shell->path.$key" => $shell->path]);
                }
            } elseif ($request->requestObject && $element->type()->accepts($request->requestObject)) {
                $requestObjects[$key] = $request->requestObject;
            } elseif (array_key_exists($key, $collisions)) {
                $collisionErrors[] = $shell->child($key, UnresolvableType::forInvalidKey())->error(new HttpRequestKeyCollision($key));
            }
        }

        if ($collisionErrors !== []) {
            return $shell->errors($collisionErrors);
        }

        return $shell
            ->allowScalarValueCastingForChildren(array_keys($route + $query))
            ->withValue($requestObjects + $route + $query + $body)
            ->build();
    }
}
