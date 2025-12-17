<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Http;

use CuyZ\Valinor\Mapper\Exception\PsrRequestParsedBodyIsObject;
use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\Tests\Fake\Mapper\Source\FakePsrRequest;
use PHPUnit\Framework\TestCase;
use stdClass;

final class HttpRequestTest extends TestCase
{
    public function test_can_create_http_request_from_psr_request(): void
    {
        $psrRequest = new FakePsrRequest(
            queryParams: [
                'someQuery' => 'someValue',
                'anotherQuery' => 'anotherValue',
            ],
            parsedBody: [
                'someBody' => 'someValue',
                'anotherBody' => 'anotherValue',
            ]
        );

        $httpRequest = HttpRequest::fromPsr($psrRequest);

        self::assertSame(['someQuery' => 'someValue', 'anotherQuery' => 'anotherValue'], $httpRequest->queryParameters);
        self::assertSame(['someBody' => 'someValue', 'anotherBody' => 'anotherValue'], $httpRequest->bodyValues);
    }

    public function test_create_http_request_from_psr_request_with_object_parsed_body_throws_exception(): void
    {
        $this->expectException(PsrRequestParsedBodyIsObject::class);
        $this->expectExceptionMessage("HTTP request's body must be an array, got an instance of `stdClass`.");

        $psrRequest = new FakePsrRequest(
            parsedBody: new stdClass()
        );

        HttpRequest::fromPsr($psrRequest);
    }
}
