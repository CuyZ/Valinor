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

/** @internal */
final class HttpRequestNodeBuilder implements NodeBuilder
{
    public function __construct(
        private NodeBuilder $delegate,
        private KeyConversionPipeline $keyConverterContainer,
    ) {}

    public function build(Shell $shell): Node
    {
        $request = $shell->value();
        $type = $shell->type;

        if (! $type instanceof ShapedArrayType) {
            return $this->delegate->build($shell);
        }

        if (! $request instanceof HttpRequest) {
            return $this->delegate->build($shell);
        }

        if ($type->isUnsealed) {
            throw new CannotMapHttpRequestToUnsealedShapedArray();
        }

        // We always allow superfluous keys: HTTP request are coming from the
        // outside of the application, meaning extra parameters can be added
        // anytime. This could lead to server issues like DDoS log spam.
        $shell = $shell->allowSuperfluousKeys();

        $route = $request->routeParameters;
        $query = $request->queryParameters;
        $body = $request->bodyValues;
        $errors = [];

        if ($this->keyConverterContainer->hasConverters()) {
            // Key converters (e.g. camelCase to snake_case) are applied to all
            // three sources independently. Each conversion returns the renamed
            // values, a name map for error reporting, and any conversion errors.
            [$route, $routeNameMap, $routeErrors] = $this->keyConverterContainer->convert($request->routeParameters);
            [$query, $queryNameMap, $queryErrors] = $this->keyConverterContainer->convert($request->queryParameters);
            [$body, $bodyNameMap, $bodyErrors] = $this->keyConverterContainer->convert($request->bodyValues);

            foreach ([...$routeErrors, ...$queryErrors, ...$bodyErrors] as $key => $error) {
                $errors[] = $shell->child($key, UnresolvableType::forInvalidKey())->error($error);
            }

            $shell = $shell->withNameMap([...$routeNameMap, ...$queryNameMap, ...$bodyNameMap]);
        }

        if ($errors !== []) {
            return $shell->errors($errors);
        }

        $elements = [];
        $checkCollision = [];
        $result = [];

        $queryAttributes = 0;
        $bodyAttributes = 0;
        $queryasRoot = false;
        $bodyasRoot = false;

        foreach ($type->elements as $key => $element) {
            $attributes = $element->attributes();

            if ($attributes->has(FromRoute::class)) {
                // The value must *NEVER* come from query or body.
                unset($query[$key], $body[$key]);

                $elements[$key] = $element;
            } elseif ($attributes->has(FromQuery::class)) {
                /** @var FromQuery $attribute */
                $attribute = $attributes->firstOf(FromQuery::class)->instantiate();

                // This element must be resolved exclusively from query
                // parameters. When `asRoot` is true, the entire query array is
                // mapped to this single element.
                if ($attribute->asRoot) {
                    $queryasRoot = true;

                    $node = $shell->withType($element->type())->withValue($query)->build();
                    $query = [];

                    if ($node->isValid()) {
                        $result[$element->key()->value()] = $node->value();
                    } else {
                        $errors[] = $node;
                    }
                } else {
                    // The value must *NEVER* come from route or body.
                    unset($route[$key], $body[$key]);

                    $elements[$key] = $element;
                }

                $queryAttributes++;

                // No other `#[FromQuery]` element is allowed alongside.
                if ($queryasRoot && $queryAttributes > 1) {
                    throw new CannotUseBothFromQueryAttributes();
                }
            } elseif ($attributes->has(FromBody::class)) {
                /** @var FromBody $attribute */
                $attribute = $attributes->firstOf(FromBody::class)->instantiate();

                // This element must be resolved exclusively from body values.
                // When `asRoot` is true, the entire body array is mapped to
                // this single element.
                if ($attribute->asRoot) {
                    $bodyasRoot = true;

                    $node = $shell->withType($element->type())->withValue($body)->build();
                    $body = [];

                    if ($node->isValid()) {
                        $result[$element->key()->value()] = $node->value();
                    } else {
                        $errors[] = $node;
                    }
                } else {
                    // The value must *NEVER* come from route or query.
                    unset($route[$key], $query[$key]);

                    $elements[$key] = $element;
                }

                $bodyAttributes++;

                // No other `#[FromBody]` element is allowed alongside.
                if ($bodyasRoot && $bodyAttributes > 1) {
                    throw new CannotUseBothFromBodyAttributes();
                }
            } elseif ($request->requestObject && $element->type()->accepts($request->requestObject)) {
                $result[$key] = $request->requestObject;
            } else {
                $checkCollision[] = $key;
                $elements[$key] = $element;
            }
        }

        if ($checkCollision !== []) {
            $collisionErrors = [];
            $collisions = array_intersect_key($route, $query) + array_intersect_key($route, $body) + array_intersect_key($query, $body);

            foreach ($checkCollision as $key) {
                if (array_key_exists($key, $collisions)) {
                    $collisionErrors[] = $shell->child($key, UnresolvableType::forInvalidKey())->error(new HttpRequestKeyCollision($key));
                }
            }

            if ($collisionErrors !== []) {
                return $shell->errors($collisionErrors);
            }
        }

        if (! $shell->allowScalarValueCasting) {
            // Route and query values are all string values, so we enable scalar
            // value casting for them.
            $shell = $shell->allowScalarValueCastingForChildren(array_keys($route + $query));
        }

        // Build the remaining elements (those not handled by `asRoot` or
        // request object injection) using the merged values from all sources.
        $node = $shell
            ->withType(new ShapedArrayType($elements))
            ->withValue($route + $query + $body)
            ->build();

        if (! $node->isValid()) {
            $errors[] = $node;
        }

        if ($errors !== []) {
            return $shell->errors($errors);
        }

        return $shell->node($result + $node->value()); // @phpstan-ignore binaryOp.invalid (we know the node value is an array)
    }
}
