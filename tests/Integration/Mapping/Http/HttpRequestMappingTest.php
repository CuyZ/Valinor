<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Http;

use CuyZ\Valinor\Mapper\Exception\TypeErrorDuringArgumentsMapping;
use CuyZ\Valinor\Mapper\Exception\TypeErrorDuringMapping;
use CuyZ\Valinor\Mapper\Http\FromBody;
use CuyZ\Valinor\Mapper\Http\FromQuery;
use CuyZ\Valinor\Mapper\Http\FromRoute;
use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\Mapper\MappingError;
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
                #[FromQuery(mapAll: true)] array $query,
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
                #[FromBody(mapAll: true)] array $body,
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

    public function test_mapping_route_parameters_allows_superflous_keys(): void
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
                #[FromQuery(mapAll: true)] array $query,
            ) => [];

        $this->expectException(TypeErrorDuringArgumentsMapping::class);
        $this->expectExceptionMessageMatches('/Could not map arguments of `.*`: cannot use `#\[FromQuery\(mapAll: true\)\]` alongside other `#\[FromQuery\]` attributes./');

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
                #[FromBody(mapAll: true)] array $body,
            ) => [];

        $this->expectException(TypeErrorDuringArgumentsMapping::class);
        $this->expectExceptionMessageMatches('/Could not map arguments of `.*`: cannot use `#\[FromBody\(mapAll: true\)\]` alongside other `#\[FromBody\]` attributes./');

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

    public function test_mapping_http_request_to_invalid_element_throws_exception(): void
    {
        $request = new HttpRequest(
            routeParameters: ['someRouteParameter' => 'foo'],
            queryParameters: ['someQueryParameter' => 'foo'],
        );

        $controller =
            fn (
                #[FromRoute] string $someRouteParameter,
                #[FromQuery] string $someQueryParameter,
                string $invalidParameter,
            ) => [];

        $this->expectException(TypeErrorDuringArgumentsMapping::class);
        $this->expectExceptionMessageMatches('/Could not map arguments of `.*`: element `invalidParameter` is not tagged with any of `#\[FromRoute\]`, `#\[FromQuery\]` or `#\[FromBody\]` attribute./');

        $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);
    }
}
