<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Http;

use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\Tests\Fake\Mapper\Source\FakePsrRequest;
use PHPUnit\Framework\TestCase;

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
}
