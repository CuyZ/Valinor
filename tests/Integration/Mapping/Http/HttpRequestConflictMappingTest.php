<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Http;

use CuyZ\Valinor\Mapper\Exception\TypeErrorDuringArgumentsMapping;
use CuyZ\Valinor\Mapper\Http\FromBody;
use CuyZ\Valinor\Mapper\Http\FromQuery;
use CuyZ\Valinor\Mapper\Http\FromRoute;
use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

/**
 * This testsuite handles potential security regression issues. We ensure that:
 *
 * - POST values cannot override route parameters.
 * - GET values cannot override route parameters.
 * - A value cannot be filled if the matching property has no flagged attribute.
 *
 * Note: these tests are put aside from {@see HttpRequestMappingTest}, to help
 * detecting changes to these specific scenarios and handle them accordingly.
 */
final class HttpRequestConflictMappingTest extends IntegrationTestCase
{
    public function test_query_value_cannot_override_a_route_parameter(): void
    {
        $request = new HttpRequest(
            routeParameters: ['userId' => 'some-user-id'],
            queryParameters: [
                'userId' => 'overridden-user-id', // Wrongly overrides the route parameters
                'layOff' => true,
            ],
        );

        $controller = fn (
            #[FromRoute] string $userId,
            #[FromQuery] bool $layOff,
        ) => [];

        try {
            $this->mapperBuilder()
                ->argumentsMapper()
                ->mapArguments($controller, $request);

            self::fail('Expected MappingError');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'userId' => "[unexpected_key] Unexpected key `userId`.",
            ]);
        }
    }

    public function test_body_value_cannot_override_a_route_parameter(): void
    {
        $request = new HttpRequest(
            routeParameters: ['userId' => 'some-user-id'],
            bodyValues: [
                'userId' => 'overridden-user-id', // Wrongly overrides the route parameters
                'layOff' => true,
            ],
        );

        $controller = fn (
            #[FromRoute] string $userId,
            #[FromBody] bool $layOff,
        ) => [];

        try {
            $this->mapperBuilder()
                ->argumentsMapper()
                ->mapArguments($controller, $request);

            self::fail('Expected MappingError');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'userId' => "[unexpected_key] Unexpected key `userId`.",
            ]);
        }
    }

    public function test_query_value_matching_unflagged_parameter_throws_exception(): void
    {
        $request = new HttpRequest(
            queryParameters: [
                'userId' => 'some-user-id',
                'layOff' => true,
            ],
        );

        $controller = fn (
            string $userId,
            #[FromQuery] bool $layOff,
        ) => [];

        $this->expectException(TypeErrorDuringArgumentsMapping::class);
        $this->expectExceptionMessageMatches('/Could not map arguments of `.*`: element `userId` is not tagged with any of `#\[FromRoute\]`, `#\[FromQuery\]` or `#\[FromBody\]` attribute./');

        $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);
    }

    public function test_body_value_matching_unflagged_parameter_throws_exception(): void
    {
        $request = new HttpRequest(
            bodyValues: [
                'userId' => 'some-user-id',
                'layOff' => true,
            ],
        );

        $controller = fn (
            string $userId,
            #[FromBody] bool $layOff,
        ) => [];

        $this->expectException(TypeErrorDuringArgumentsMapping::class);
        $this->expectExceptionMessageMatches('/Could not map arguments of `.*`: element `userId` is not tagged with any of `#\[FromRoute\]`, `#\[FromQuery\]` or `#\[FromBody\]` attribute./');

        $this->mapperBuilder()
            ->argumentsMapper()
            ->mapArguments($controller, $request);
    }
}
