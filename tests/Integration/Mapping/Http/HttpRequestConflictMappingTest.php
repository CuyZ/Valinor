<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Http;

use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

/**
 * This testsuite handles potential security regression issues. We ensure that
 * there can be no collision between different HTTP sources.
 *
 * Note that these behaviors only apply when the colliding keys do not have a
 * matching attribute (e.g. `#[FromRoute]` or `#[FromBody]`).
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
            string $userId,
            bool $layOff,
        ) => [];

        try {
            $this->mapperBuilder()
                ->argumentsMapper()
                ->mapArguments($controller, $request);

            self::fail('Expected MappingError');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'userId' => "[key_collision] Key `userId` was found in several HTTP request sources. It must be sent in only one of route, query or body.",
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
            string $userId,
            bool $layOff,
        ) => [];

        try {
            $this->mapperBuilder()
                ->argumentsMapper()
                ->mapArguments($controller, $request);

            self::fail('Expected MappingError');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'userId' => "[key_collision] Key `userId` was found in several HTTP request sources. It must be sent in only one of route, query or body.",
            ]);
        }
    }

    public function test_body_value_cannot_override_a_query_parameter(): void
    {
        $request = new HttpRequest(
            queryParameters: ['userId' => 'some-user-id'],
            bodyValues: [
                'userId' => 'overridden-user-id', // Wrongly overrides the route parameters
                'layOff' => true,
            ],
        );

        $controller = fn (
            string $userId,
            bool $layOff,
        ) => [];

        try {
            $this->mapperBuilder()
                ->argumentsMapper()
                ->mapArguments($controller, $request);

            self::fail('Expected MappingError');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'userId' => "[key_collision] Key `userId` was found in several HTTP request sources. It must be sent in only one of route, query or body.",
            ]);
        }
    }
}
