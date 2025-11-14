<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Http;

use CuyZ\Valinor\Mapper\Exception\TypeErrorDuringArgumentsMapping;
use CuyZ\Valinor\Mapper\Exception\TypeErrorDuringMapping;
use CuyZ\Valinor\Mapper\Http\FromBody;
use CuyZ\Valinor\Mapper\Http\FromQuery;
use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Fake\Mapper\Source\FakePsrRequest;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use Psr\Http\Message\ServerRequestInterface;

final class HttpRequestMappingTest extends IntegrationTestCase
{
    public function test_can_map_http_request_with_single_query_parameter(): void
    {
        $request = HttpRequest::new()
            ->withQueryParameters(['someQueryParameter' => 'foo']);

        $controller = fn (#[FromQuery] string $someQueryParameter) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame(['someQueryParameter' => 'foo'], $result);
    }

    public function test_can_map_http_request_with_several_query_parameters(): void
    {
        $request = HttpRequest::new()
            ->withQueryParameters([
                'someQueryParameter' => 'foo',
                'anotherQueryParameter' => 42,
            ]);

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
        $request = HttpRequest::new()
            ->withQueryParameters([
                'someQueryParameter' => 'foo',
                'anotherQueryParameter' => 42,
            ]);

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
        $request = HttpRequest::new()
            ->withRouteParameters([
                'someRouteParameter' => 'foo',
                'anotherRouteParameter' => 42,
            ])
            ->withQueryParameters([
                'someQueryParameter' => 'bar',
                'anotherQueryParameter' => 1337,
            ]);

        $controller = fn (
            string $someRouteParameter,
            int $anotherRouteParameter,
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
        $request = HttpRequest::new()
            ->withBodyValues(['someBodyValue' => 'foo']);

        $controller = fn (#[FromBody] string $someBodyValue) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame(['someBodyValue' => 'foo'], $result);
    }

    public function test_can_map_http_request_with_several_body_values(): void
    {
        $request = HttpRequest::new()
            ->withBodyValues([
                'someBodyValue' => 'foo',
                'anotherBodyValue' => 42,
            ]);

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
        $request = HttpRequest::new()
            ->withBodyValues([
                'someBodyValue' => 'foo',
                'anotherBodyValue' => 42,
            ]);

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
        $request = HttpRequest::new()
            ->withRouteParameters([
                'someRouteParameter' => 'foo',
                'anotherRouteParameter' => 42,
            ])
            ->withBodyValues([
                'someBodyValue' => 'bar',
                'anotherBodyValue' => 1337,
            ]);

        $controller = fn (
            string $someRouteParameter,
            int $anotherRouteParameter,
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
        $request = HttpRequest::new()
            ->withQueryParameters([
                'someQueryParameter' => 'foo',
                'anotherQueryParameter' => 42,
            ])
            ->withBodyValues([
                'someBodyValue' => 'bar',
                'anotherBodyValue' => 1337,
            ]);

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

    public function test_mapping_route_parameters_enables_scalar_value_casting(): void
    {
        $request = HttpRequest::new()
            ->withRouteParameters([
                'someRouteParameter' => '42',
            ]);

        $controller = fn (int $someRouteParameter) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame(['someRouteParameter' => 42], $result);
    }

    public function test_mapping_query_parameters_enables_scalar_value_casting(): void
    {
        $request = HttpRequest::new()
            ->withQueryParameters([
                'someQueryParameter' => '42',
            ]);

        $controller = fn (#[FromQuery] int $someQueryParameter) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame(['someQueryParameter' => 42], $result);
    }

    public function test_detects_colliding_route_parameters_and_query_parameters(): void
    {
        $request = HttpRequest::new()
            ->withRouteParameters(['someParameter' => 'foo'])
            ->withQueryParameters(['someParameter' => 'bar']);

        $controller = fn (string $someParameter) => [];

        try {
            $this->mapperBuilder()
                ->argumentsMapper()
                ->mapArguments($controller, $request);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'someParameter' => '[unexpected_http_request_query_parameter] Unexpected query parameter `someParameter`.',
            ]);
        }
    }

    public function test_detects_colliding_route_parameters_and_body_values(): void
    {
        $request = HttpRequest::new()
            ->withRouteParameters(['someParameter' => 'foo'])
            ->withBodyValues(['someParameter' => 'bar']);

        $controller = fn (string $someParameter) => [];

        try {
            $this->mapperBuilder()
                ->argumentsMapper()
                ->mapArguments($controller, $request);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'someParameter' => '[unexpected_http_request_body_value] Unexpected body value `someParameter`.',
            ]);
        }
    }

    public function test_can_map_request_object(): void
    {
        $originalRequest = new FakePsrRequest();

        $request = HttpRequest::new()
            ->withQueryParameters(['someQueryParameter' => 'foo'])
            ->withRequestObject($originalRequest);

        $controller = fn (ServerRequestInterface $request, #[FromQuery] string $someQueryParameter) => [];

        $result = $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);

        self::assertSame([
            'request' => $originalRequest,
            'someQueryParameter' => 'foo',
        ], $result);
    }

    public function test_from_query_map_all_attribute_alongside_other_from_query_attributes_throws_exception(): void
    {
        $request = HttpRequest::new()
            ->withQueryParameters([
                'someQueryParameter' => 'foo',
                'anotherQueryParameter' => 42,
            ]);

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
        $request = HttpRequest::new()
            ->withBodyValues([
                'someBodyValue' => 'foo',
                'anotherBodyValue' => 42,
            ]);

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
            ->map('array{foo: string, ...}', HttpRequest::new());
    }

    public function test_mapping_http_request_to_invalid_element_throws_exception(): void
    {
        $request = HttpRequest::new()
            ->withRouteParameters(['someRouteParameter' => 'foo'])
            ->withQueryParameters(['someQueryParameter' => 'foo']);

        $controller =
            fn (
                string $someRouteParameter,
                #[FromQuery] string $someQueryParameter,
                string $invalidParameter,
            ) => [];

        $this->expectException(TypeErrorDuringArgumentsMapping::class);
        $this->expectExceptionMessageMatches('/Could not map arguments of `.*`: element `invalidParameter` is not bound to a route parameter and is not tagged with a `#\[FromQuery\]` nor a `#\[FromBody\]` attribute./');

        $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);
    }
}
