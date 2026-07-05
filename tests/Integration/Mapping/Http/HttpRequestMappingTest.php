<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Http;

use CuyZ\Valinor\Mapper\Configurator\MapFromKey;
use CuyZ\Valinor\Mapper\Configurator\MapKeysToCamelCase;
use CuyZ\Valinor\Mapper\Configurator\RestrictKeysToSnakeCase;
use CuyZ\Valinor\Mapper\Exception\TypeErrorDuringArgumentsMapping;
use CuyZ\Valinor\Mapper\Exception\TypeErrorDuringMapping;
use CuyZ\Valinor\Mapper\Http\FromBody;
use CuyZ\Valinor\Mapper\Http\FromQuery;
use CuyZ\Valinor\Mapper\Http\FromRoute;
use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Exception\SeveralAttributesMapToSameKey;
use CuyZ\Valinor\Tests\Fake\Mapper\Source\FakePsrRequest;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use Psr\Http\Message\ServerRequestInterface;

final class HttpRequestMappingTest extends IntegrationTestCase
{
    public function test_can_map_http_request_with_single_query_parameter(): void
    {
        $request = new HttpRequest(
            queryParameters: ['someQueryParameter' => 'foo'],
        );

        $controller = fn (#[FromQuery] string $someQueryParameter) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame(['someQueryParameter' => 'foo'], $result);
    }

    public function test_can_map_http_request_with_several_query_parameters(): void
    {
        $request = new HttpRequest(
            queryParameters: [
                'someQueryParameter' => 'foo',
                'anotherQueryParameter' => 42,
            ],
        );

        $controller = fn (
            #[FromQuery] string $someQueryParameter,
            #[FromQuery] int $anotherQueryParameter,
        ) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame([
            'someQueryParameter' => 'foo',
            'anotherQueryParameter' => 42,
        ], $result);
    }

    public function test_can_map_all_query_parameters_to_single_property(): void
    {
        $request = new HttpRequest(
            queryParameters: [
                'someQueryParameter' => 'foo',
                'anotherQueryParameter' => 42,
            ],
        );

        $controller =
            /**
             * @param array{someQueryParameter: string, anotherQueryParameter: int} $query
             */
            fn (
                #[FromQuery(asRoot: true)] array $query,
            ) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame([
            'query' => [
                'someQueryParameter' => 'foo',
                'anotherQueryParameter' => 42,
            ],
        ], $result);
    }

    public function test_can_map_http_request_with_several_route_parameters_and_several_query_parameters(): void
    {
        $request = new HttpRequest(
            routeParameters: [
                'someRouteParameter' => 'foo',
                'anotherRouteParameter' => 42,
            ],
            queryParameters: [
                'someQueryParameter' => 'bar',
                'anotherQueryParameter' => 1337,
            ],
        );

        $controller = fn (
            #[FromRoute] string $someRouteParameter,
            #[FromRoute] int $anotherRouteParameter,
            #[FromQuery] string $someQueryParameter,
            #[FromQuery] int $anotherQueryParameter,
        ) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame([
            'someRouteParameter' => 'foo',
            'anotherRouteParameter' => 42,
            'someQueryParameter' => 'bar',
            'anotherQueryParameter' => 1337,
        ], $result);
    }

    public function test_can_map_http_request_with_single_body_value(): void
    {
        $request = new HttpRequest(
            bodyValues: ['someBodyValue' => 'foo'],
        );

        $controller = fn (#[FromBody] string $someBodyValue) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame(['someBodyValue' => 'foo'], $result);
    }

    public function test_can_map_http_request_with_several_body_values(): void
    {
        $request = new HttpRequest(
            bodyValues: [
                'someBodyValue' => 'foo',
                'anotherBodyValue' => 42,
            ],
        );

        $controller = fn (
            #[FromBody] string $someBodyValue,
            #[FromBody] int $anotherBodyValue,
        ) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame([
            'someBodyValue' => 'foo',
            'anotherBodyValue' => 42,
        ], $result);
    }

    public function test_can_map_all_body_parameters_to_single_property(): void
    {
        $request = new HttpRequest(
            bodyValues: [
                'someBodyValue' => 'foo',
                'anotherBodyValue' => 42,
            ],
        );

        $controller =
            /**
             * @param array{someBodyValue: string, anotherBodyValue: int} $body
             */
            fn (
                #[FromBody(asRoot: true)] array $body,
            ) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame([
            'body' => [
                'someBodyValue' => 'foo',
                'anotherBodyValue' => 42,
            ],
        ], $result);
    }

    public function test_can_map_http_request_with_several_route_parameters_and_several_body_values(): void
    {
        $request = new HttpRequest(
            routeParameters: [
                'someRouteParameter' => 'foo',
                'anotherRouteParameter' => 42,
            ],
            bodyValues: [
                'someBodyValue' => 'bar',
                'anotherBodyValue' => 1337,
            ],
        );

        $controller = fn (
            #[FromRoute] string $someRouteParameter,
            #[FromRoute] int $anotherRouteParameter,
            #[FromBody] string $someBodyValue,
            #[FromBody] int $anotherBodyValue,
        ) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame([
            'someRouteParameter' => 'foo',
            'anotherRouteParameter' => 42,
            'someBodyValue' => 'bar',
            'anotherBodyValue' => 1337,
        ], $result);
    }

    public function test_can_map_http_request_with_several_body_values_and_several_query_parameters(): void
    {
        $request = new HttpRequest(
            queryParameters: [
                'someQueryParameter' => 'foo',
                'anotherQueryParameter' => 42,
            ],
            bodyValues: [
                'someBodyValue' => 'bar',
                'anotherBodyValue' => 1337,
            ],
        );

        $controller = fn (
            #[FromQuery] string $someQueryParameter,
            #[FromQuery] int $anotherQueryParameter,
            #[FromBody] string $someBodyValue,
            #[FromBody] int $anotherBodyValue,
        ) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame([
            'someQueryParameter' => 'foo',
            'anotherQueryParameter' => 42,
            'someBodyValue' => 'bar',
            'anotherBodyValue' => 1337,
        ], $result);
    }

    public function test_can_map_http_request_with_default_route_values(): void
    {
        $request = new HttpRequest(
            routeParameters: [
                'someRouteParameter' => 'foo',
                'anotherRouteParameter' => 42,
            ],
        );

        $controller = fn (
            #[FromRoute] string $someRouteParameter,
            #[FromRoute] int $anotherRouteParameter = 999,
            #[FromRoute] string $yetAnotherRouteParameter = 'foo',
        ) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame([
            'someRouteParameter' => 'foo',
            'anotherRouteParameter' => 42,
        ], $result);
    }

    public function test_can_map_http_request_with_default_query_values(): void
    {
        $request = new HttpRequest(
            queryParameters: [
                'someQueryParameter' => 'bar',
                'anotherQueryParameter' => 404,
            ],
        );

        $controller = fn (
            #[FromQuery] string $someQueryParameter,
            #[FromQuery] int $anotherQueryParameter = 999,
            #[FromQuery] string $yetAnotherQueryParameter = 'bar',
        ) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame([
            'someQueryParameter' => 'bar',
            'anotherQueryParameter' => 404,
        ], $result);
    }

    public function test_can_map_http_request_with_default_body_values(): void
    {
        $request = new HttpRequest(
            bodyValues: [
                'someBodyValue' => 'baz',
                'anotherBodyValue' => 1337,
            ],
        );

        $controller = fn (
            #[FromBody] string $someBodyValue,
            #[FromBody] int $anotherBodyValue = 999,
            #[FromBody] string $yetAnotherBodyValue = 'fiz',
        ) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame([
            'someBodyValue' => 'baz',
            'anotherBodyValue' => 1337,
        ], $result);
    }

    public function test_map_http_request_respects_parameter_orders_even_when_attributes_are_disordered(): void
    {
        $request = new HttpRequest(
            routeParameters: [
                'someRouteParameter' => 'foo',
                'anotherRouteParameter' => 42,
            ],
            queryParameters: [
                'someQueryParameter' => 'bar',
                'anotherQueryParameter' => 404,
            ],
            bodyValues: [
                'someBodyValue' => 'baz',
                'anotherBodyValue' => 1337,
            ],
        );

        $controller = fn (
            #[FromRoute] string $someRouteParameter,
            #[FromQuery] string $someQueryParameter,
            #[FromBody] string $someBodyValue,
            #[FromRoute] int $anotherRouteParameter,
            #[FromQuery] int $anotherQueryParameter,
            #[FromBody] int $anotherBodyValue,
        ) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame([
            'someRouteParameter' => 'foo',
            'someQueryParameter' => 'bar',
            'someBodyValue' => 'baz',
            'anotherRouteParameter' => 42,
            'anotherQueryParameter' => 404,
            'anotherBodyValue' => 1337,
        ], $result);
    }

    public function test_mapping_route_parameters_enables_scalar_value_casting(): void
    {
        $request = new HttpRequest(
            routeParameters: [
                'someRouteParameter' => '42',
            ],
        );

        $controller = fn (#[FromRoute] int $someRouteParameter) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame(['someRouteParameter' => 42], $result);
    }

    public function test_mapping_route_parameters_allows_superfluous_keys(): void
    {
        $request = new HttpRequest(
            routeParameters: [
                'someRouteParameter' => '42',
                'extraParameter' => 'foo'
            ],
        );

        $controller = fn (#[FromRoute] int $someRouteParameter) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame(['someRouteParameter' => 42], $result);
    }

    public function test_mapping_query_parameters_enables_scalar_value_casting(): void
    {
        $request = new HttpRequest(
            queryParameters: [
                'someQueryParameter' => '42',
            ],
        );

        $controller = fn (#[FromQuery] int $someQueryParameter) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame(['someQueryParameter' => 42], $result);
    }

    public function test_detects_colliding_route_parameters_and_query_parameters(): void
    {
        $request = new HttpRequest(
            routeParameters: ['someParameter' => 'foo'],
            queryParameters: ['someParameter' => 'bar'],
        );

        $controller = fn (#[FromRoute] string $someParameter) => [];

        try {
            $this->mapperBuilder()
                ->argumentsMapper()
                ->mapArguments($controller, $request);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'someParameter' => '[unexpected_key] Unexpected key `someParameter`.',
            ]);
        }
    }

    public function test_detects_colliding_route_parameters_and_body_values(): void
    {
        $request = new HttpRequest(
            routeParameters: ['someParameter' => 'foo'],
            bodyValues: ['someParameter' => 'bar'],
        );

        $controller = fn (#[FromRoute] string $someParameter) => [];

        try {
            $this->mapperBuilder()
                ->argumentsMapper()
                ->mapArguments($controller, $request);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'someParameter' => '[unexpected_key] Unexpected key `someParameter`.',
            ]);
        }
    }

    public function test_detects_colliding_query_parameters_and_body_values(): void
    {
        $request = new HttpRequest(
            queryParameters: ['someParameter' => 'foo'],
            bodyValues: ['someParameter' => 'bar'],
        );

        $controller = fn (#[FromBody] string $someParameter) => [];

        try {
            $this->mapperBuilder()
                ->argumentsMapper()
                ->mapArguments($controller, $request);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'someParameter' => '[unexpected_key] Unexpected key `someParameter`.',
            ]);
        }
    }

    public function test_can_map_request_object(): void
    {
        $originalRequest = new FakePsrRequest();

        $request = new HttpRequest(
            queryParameters: ['someQueryParameter' => 'foo'],
            requestObject: $originalRequest,
        );

        $controller = fn (ServerRequestInterface $request, #[FromQuery] string $someQueryParameter) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame([
            'request' => $originalRequest,
            'someQueryParameter' => 'foo',
        ], $result);
    }

    public function test_can_map_http_request_to_object(): void
    {
        $class = (new class ('bar') {
            public function __construct(
                #[FromRoute] public string $someRouteParameter,
            ) {}
        })::class;

        $request = new HttpRequest(
            routeParameters: ['someRouteParameter' => 'foo'],
        );

        $result = $this->mapperBuilder()
            ->mapper()
            ->map($class, $request);

        self::assertSame('foo', $result->someRouteParameter);
    }

    public function test_mapping_http_request_to_shaped_list_returns_error(): void
    {
        try {
            $this->mapperBuilder()
                ->mapper()
                ->map('list{string}', new HttpRequest(bodyValues: ['a' => 'x']));

            self::fail('Expected MappingError');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[value_is_not_iterable] Value HttpRequest{body: array{a: 'x'}} does not match `list{string}`.",
            ]);
        }
    }

    public function test_can_map_http_request_to_union_of_shaped_array_and_shaped_list(): void
    {
        $result = $this->mapperBuilder()
            ->mapper()
            ->map('array{a: string}|list{string}', new HttpRequest(bodyValues: ['a' => 'x']));

        self::assertSame(['a' => 'x'], $result);
    }

    public function test_route_parameter_from_attribute_cannot_come_from_query(): void
    {
        $class = (new class () {
            public function __construct(
                #[FromRoute] public string $someParameter = 'default value',
            ) {}
        })::class;

        $request = new HttpRequest(
            queryParameters: ['someParameter' => 'foo'],
        );

        $result = $this->mapperBuilder()
            ->mapper()
            ->map($class, $request);

        self::assertSame('default value', $result->someParameter);
    }

    public function test_route_parameter_from_attribute_cannot_come_from_body(): void
    {
        $class = (new class () {
            public function __construct(
                #[FromRoute] public string $someParameter = 'default value',
            ) {}
        })::class;

        $request = new HttpRequest(
            bodyValues: ['someParameter' => 'foo'],
        );

        $result = $this->mapperBuilder()
            ->mapper()
            ->map($class, $request);

        self::assertSame('default value', $result->someParameter);
    }

    public function test_query_parameter_from_attribute_cannot_come_from_route(): void
    {
        $class = (new class () {
            public function __construct(
                #[FromQuery] public string $someParameter = 'default value',
            ) {}
        })::class;

        $request = new HttpRequest(
            routeParameters: ['someParameter' => 'foo'],
        );

        $result = $this->mapperBuilder()
            ->mapper()
            ->map($class, $request);

        self::assertSame('default value', $result->someParameter);
    }

    public function test_query_parameter_from_attribute_cannot_come_from_body(): void
    {
        $class = (new class () {
            public function __construct(
                #[FromQuery] public string $someParameter = 'default value',
            ) {}
        })::class;

        $request = new HttpRequest(
            bodyValues: ['someParameter' => 'foo'],
        );

        $result = $this->mapperBuilder()
            ->mapper()
            ->map($class, $request);

        self::assertSame('default value', $result->someParameter);
    }

    public function test_body_value_from_attribute_cannot_come_from_route(): void
    {
        $class = (new class () {
            public function __construct(
                #[FromBody] public string $someParameter = 'default value',
            ) {}
        })::class;

        $request = new HttpRequest(
            routeParameters: ['someParameter' => 'foo'],
        );

        $result = $this->mapperBuilder()
            ->mapper()
            ->map($class, $request);

        self::assertSame('default value', $result->someParameter);
    }

    public function test_body_value_from_attribute_cannot_come_from_query(): void
    {
        $class = (new class () {
            public function __construct(
                #[FromBody] public string $someParameter = 'default value',
            ) {}
        })::class;

        $request = new HttpRequest(
            queryParameters: ['someParameter' => 'foo'],
        );

        $result = $this->mapperBuilder()
            ->mapper()
            ->map($class, $request);

        self::assertSame('default value', $result->someParameter);
    }

    public function test_mapping_http_request_with_invalid_value_returns_errors(): void
    {
        $request = new HttpRequest(
            routeParameters: ['someRouteParameter' => 'not-an-int'],
            queryParameters: ['someQueryParameter' => 'not-an-int'],
            bodyValues: ['someBodyValue' => 'not-an-int'],
        );

        $controller = fn (
            #[FromRoute] int $someRouteParameter,
            #[FromQuery] int $someQueryParameter,
            #[FromBody] int $someBodyValue,
        ) => [];

        try {
            $this->mapperBuilder()
                ->argumentsMapper()
                ->mapArguments($controller, $request);

            self::fail('Expected MappingError');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'someRouteParameter' => "[invalid_integer] Value 'not-an-int' is not a valid integer.",
                'someQueryParameter' => "[invalid_integer] Value 'not-an-int' is not a valid integer.",
                'someBodyValue' => "[invalid_integer] Value 'not-an-int' is not a valid integer.",
            ]);
        }
    }

    public function test_mapping_http_request_with_map_all_with_invalid_value_returns_flattened_errors(): void
    {
        $request = new HttpRequest(
            routeParameters: ['route' => 'not-an-int'],
            queryParameters: [
                'someQueryValue' => 'not-an-int',
                'anotherQueryValue' => 'still-not-an-int',
            ],
            bodyValues: [
                'someBodyValue' => 'not-an-int',
                'anotherBodyValue' => 'still-not-an-int',
            ],
        );

        $controller =
            /**
             * @param array{someQueryValue: int, anotherQueryValue: int} $query
             * @param array{someBodyValue: int, anotherBodyValue: int} $body
             */
            fn (
                #[FromRoute] int $route,
                #[FromQuery(asRoot: true)] array $query,
                #[FromBody(asRoot: true)] array $body,
            ) => [];

        try {
            $this->mapperBuilder()
                ->argumentsMapper()
                ->mapArguments($controller, $request);

            self::fail('Expected MappingError');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'route' => "[invalid_integer] Value 'not-an-int' is not a valid integer.",
                'someQueryValue' => "[invalid_integer] Value 'not-an-int' is not a valid integer.",
                'anotherQueryValue' => "[invalid_integer] Value 'still-not-an-int' is not a valid integer.",
                'someBodyValue' => "[invalid_integer] Value 'not-an-int' is not a valid integer.",
                'anotherBodyValue' => "[invalid_integer] Value 'still-not-an-int' is not a valid integer.",
            ]);
        }
    }

    public function test_from_query_map_all_attribute_alongside_other_from_query_attributes_throws_exception(): void
    {
        $request = new HttpRequest(
            queryParameters: [
                'someQueryParameter' => 'foo',
                'anotherQueryParameter' => 42,
            ],
        );

        $controller =
            /**
             * @param array{someQueryParameter: string, anotherQueryParameter: int} $query
             */
            fn (
                #[FromQuery] string $someQueryParameter,
                #[FromQuery(asRoot: true)] array $query,
            ) => [];

        $this->expectException(TypeErrorDuringArgumentsMapping::class);
        $this->expectExceptionMessageMatches('/Could not map arguments of `.*`: cannot use `#\[FromQuery\(asRoot: true\)\]` alongside other `#\[FromQuery\]` attributes./');

        $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);
    }

    public function test_from_body_map_all_attribute_alongside_other_from_body_attributes_throws_exception(): void
    {
        $request = new HttpRequest(
            bodyValues: [
                'someBodyValue' => 'foo',
                'anotherBodyValue' => 42,
            ],
        );

        $controller =
            /**
             * @param array{someBodyValue: string, anotherBodyValue: int} $body
             */
            fn (
                #[FromBody] string $someBodyValue,
                #[FromBody(asRoot: true)] array $body,
            ) => [];

        $this->expectException(TypeErrorDuringArgumentsMapping::class);
        $this->expectExceptionMessageMatches('/Could not map arguments of `.*`: cannot use `#\[FromBody\(asRoot: true\)\]` alongside other `#\[FromBody\]` attributes./');

        $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);
    }

    public function test_mapping_http_request_to_unsealed_shaped_array_throws_exception(): void
    {
        $this->expectException(TypeErrorDuringMapping::class);
        $this->expectExceptionMessage('Error while trying to map to `array{foo: string, ...}`: mapping an HTTP request to an unsealed shaped array is not supported.');

        $this->mapperBuilder()
            ->mapper()
            ->map('array{foo: string, ...}', new HttpRequest());
    }

    public function test_can_map_request_object_alongside_parameters(): void
    {
        $originalRequest = new FakePsrRequest();

        $request = new HttpRequest(
            routeParameters: ['name' => 'John'],
            requestObject: $originalRequest,
        );

        $controller = fn (ServerRequestInterface $request, string $name) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame([
            'request' => $originalRequest,
            'name' => 'John',
        ], $result);
    }

    public function test_can_map_http_request_with_key_converter(): void
    {
        $request = new HttpRequest(
            routeParameters: ['route_param' => 'foo'],
            queryParameters: ['query_param' => 'bar'],
            bodyValues: ['body_param' => 'baz'],
        );

        $controller = fn (
            #[FromRoute] string $routeParam,
            #[FromQuery] string $queryParam,
            #[FromBody] string $bodyParam,
        ) => [];

        $result = $this->mapperBuilder()
            ->configureWith(new MapKeysToCamelCase())
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame([
            'routeParam' => 'foo',
            'queryParam' => 'bar',
            'bodyParam' => 'baz',
        ], $result);
    }

    public function test_key_converter_restriction_error_for_route_parameter_is_reported(): void
    {
        $request = new HttpRequest(
            routeParameters: ['invalidKey' => 'value'],
        );

        $controller = fn (#[FromRoute] string $invalidKey) => [];

        try {
            $this->mapperBuilder()
                ->configureWith(new RestrictKeysToSnakeCase())
                ->argumentsMapper()
                ->mapArguments($controller, $request);

            self::fail('Expected MappingError');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'invalidKey' => "[invalid_key_case] Key must follow the snake_case format.",
            ]);
        }
    }

    public function test_can_map_http_request_route_parameter_from_key(): void
    {
        $request = new HttpRequest(
            routeParameters: ['route_key' => 'foo'],
        );

        $controller = fn (#[FromRoute] #[MapFromKey('route_key')] string $someRouteParameter) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame(['someRouteParameter' => 'foo'], $result);
    }

    public function test_can_map_http_request_query_parameter_from_key(): void
    {
        $request = new HttpRequest(
            queryParameters: ['query_key' => 'foo'],
        );

        $controller = fn (#[FromQuery] #[MapFromKey('query_key')] string $someQueryParameter) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame(['someQueryParameter' => 'foo'], $result);
    }

    public function test_can_map_http_request_body_value_from_key(): void
    {
        $request = new HttpRequest(
            bodyValues: ['body_key' => 'foo'],
        );

        $controller = fn (#[FromBody] #[MapFromKey('body_key')] string $someBodyValue) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame(['someBodyValue' => 'foo'], $result);
    }

    public function test_literal_key_superfluous_to_remapped_body_value_is_tolerated(): void
    {
        // The argument is fed from `body_key`, so the literal argument-name key
        // `someBodyValue` is superfluous. Unlike an array source, an HTTP
        // request tolerates it rather than raising an unexpected-key error.
        $request = new HttpRequest(
            bodyValues: ['body_key' => 'kept', 'someBodyValue' => 'ignored'],
        );

        $controller = fn (#[FromBody] #[MapFromKey('body_key')] string $someBodyValue) => $someBodyValue;

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame(['someBodyValue' => 'kept'], $result);
    }

    public function test_can_map_http_request_from_keys_across_all_sources(): void
    {
        $request = new HttpRequest(
            routeParameters: ['route_key' => 'foo'],
            queryParameters: ['query_key' => 'bar'],
            bodyValues: ['body_key' => 'baz'],
        );

        $controller = fn (
            #[FromRoute] #[MapFromKey('route_key')] string $routeParam,
            #[FromQuery] #[MapFromKey('query_key')] string $queryParam,
            #[FromBody] #[MapFromKey('body_key')] string $bodyParam,
        ) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame([
            'routeParam' => 'foo',
            'queryParam' => 'bar',
            'bodyParam' => 'baz',
        ], $result);
    }

    public function test_map_from_key_reads_only_from_its_declared_source(): void
    {
        // The key is present in both route and query, but `#[FromQuery]`
        // restricts the parameter to the query source.
        $request = new HttpRequest(
            routeParameters: ['shared' => 'from-route'],
            queryParameters: ['shared' => 'from-query'],
        );

        $controller = fn (#[FromQuery] #[MapFromKey('shared')] string $someParameter) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame(['someParameter' => 'from-query'], $result);
    }

    public function test_literal_parameter_name_key_is_superfluous_when_remapped(): void
    {
        // An HTTP request always allows superfluous keys, so the literal
        // parameter-name key of a remapped parameter is silently dropped.
        $request = new HttpRequest(
            queryParameters: [
                'query_key' => 'foo',
                'someQueryParameter' => 'ignored',
            ],
        );

        $controller = fn (#[FromQuery] #[MapFromKey('query_key')] string $someQueryParameter) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame(['someQueryParameter' => 'foo'], $result);
    }

    public function test_map_from_key_error_path_uses_source_key(): void
    {
        $request = new HttpRequest(
            queryParameters: ['query_key' => 'not-an-int'],
        );

        $controller = fn (#[FromQuery] #[MapFromKey('query_key')] int $someQueryParameter) => [];

        try {
            $this->mapperBuilder()
                ->argumentsMapper()
                ->mapArguments($controller, $request);

            self::fail('Expected MappingError');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'query_key' => "[invalid_integer] Value 'not-an-int' is not a valid integer.",
            ]);
        }
    }

    public function test_map_from_key_bypasses_key_converter_in_http_request(): void
    {
        $request = new HttpRequest(
            queryParameters: [
                'my_key' => 'foo',
                'other_param' => 'bar',
            ],
        );

        $controller = fn (
            #[FromQuery] #[MapFromKey('my_key')] string $someParameter,
            #[FromQuery] string $otherParam,
        ) => [];

        $result = $this->mapperBuilder()
            ->configureWith(new MapKeysToCamelCase())
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        // `my_key` is read as-is (bypass), while `other_param` still goes
        // through the camelCase key converter.
        self::assertSame([
            'someParameter' => 'foo',
            'otherParam' => 'bar',
        ], $result);
    }

    public function test_two_parameters_mapped_from_same_key_throws_exception(): void
    {
        $this->expectException(SeveralAttributesMapToSameKey::class);
        $this->expectExceptionMessage('Attributes on `someParameter` and `anotherParameter` both map from the source key `shared`.');

        $request = new HttpRequest(
            queryParameters: ['shared' => 'foo'],
        );

        $controller = fn (
            #[FromQuery] #[MapFromKey('shared')] string $someParameter,
            #[FromQuery] #[MapFromKey('shared')] string $anotherParameter,
        ) => [];

        $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);
    }

    public function test_can_map_http_request_to_object_with_map_from_key(): void
    {
        $class = (new class ('') {
            public function __construct(
                #[FromRoute] #[MapFromKey('route_key')] public string $someRouteParameter,
            ) {}
        })::class;

        $request = new HttpRequest(
            routeParameters: ['route_key' => 'foo'],
        );

        $result = $this->mapperBuilder()
            ->mapper()
            ->map($class, $request);

        self::assertSame('foo', $result->someRouteParameter);
    }
}
