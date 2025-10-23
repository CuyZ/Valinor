<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Source;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class FakePsrRequest implements ServerRequestInterface
{
    public function __construct(
        private string $method = 'GET',
        /** @var array<mixed> */
        private array $queryParams = [],
        /** @var array<mixed> */
        private array $parsedBody = [],
    ) {}

    public function getProtocolVersion(): string
    {
        return '1.1';
    }

    public function withProtocolVersion(string $version): self
    {
        return $this;
    }

    public function getHeaders(): array
    {
        return [];
    }

    public function hasHeader(string $name): bool
    {
        return false;
    }

    public function getHeader(string $name): array
    {
        return [];
    }

    public function getHeaderLine(string $name): string
    {
        return '';
    }

    public function withHeader(string $name, $value): self
    {
        return $this;
    }

    public function withAddedHeader(string $name, $value): self
    {
        return $this;
    }

    public function withoutHeader(string $name): self
    {
        return $this;
    }

    public function getBody(): StreamInterface
    {
        return new class () implements StreamInterface {
            public function __toString(): string
            {
                return '';
            }

            public function close(): void {}

            public function detach()
            {
                return null;
            }

            public function getSize(): int
            {
                return 0;
            }

            public function tell(): int
            {
                return 0;
            }

            public function eof(): bool
            {
                return true;
            }

            public function isSeekable(): bool
            {
                return false;
            }

            public function seek(int $offset, int $whence = SEEK_SET): void {}

            public function rewind(): void {}

            public function isWritable(): bool
            {
                return false;
            }

            public function write(string $string): int
            {
                return 0;
            }

            public function isReadable(): bool
            {
                return false;
            }

            public function read(int $length): string
            {
                return '';
            }

            public function getContents(): string
            {
                return '';
            }

            public function getMetadata(?string $key = null)
            {
                return null;
            }
        };
    }

    public function withBody(StreamInterface $body): self
    {
        return $this;
    }

    public function getRequestTarget(): string
    {
        return '/';
    }

    public function withRequestTarget(string $requestTarget): self
    {
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): self
    {
        return $this;
    }

    public function getUri(): UriInterface
    {
        return new class () implements UriInterface {
            public function getScheme(): string
            {
                return 'http';
            }

            public function getAuthority(): string
            {
                return '';
            }

            public function getUserInfo(): string
            {
                return '';
            }

            public function getHost(): string
            {
                return 'localhost';
            }

            public function getPort(): ?int
            {
                return null;
            }

            public function getPath(): string
            {
                return '/';
            }

            public function getQuery(): string
            {
                return '';
            }

            public function getFragment(): string
            {
                return '';
            }

            public function withScheme(string $scheme): UriInterface
            {
                return $this;
            }

            public function withUserInfo(string $user, ?string $password = null): UriInterface
            {
                return $this;
            }

            public function withHost(string $host): UriInterface
            {
                return $this;
            }

            public function withPort(?int $port): UriInterface
            {
                return $this;
            }

            public function withPath(string $path): UriInterface
            {
                return $this;
            }

            public function withQuery(string $query): UriInterface
            {
                return $this;
            }

            public function withFragment(string $fragment): UriInterface
            {
                return $this;
            }

            public function __toString(): string
            {
                return 'http://localhost/';
            }
        };
    }

    public function withUri(UriInterface $uri, bool $preserveHost = true): self
    {
        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getServerParams(): array
    {
        return [];
    }

    /**
     * @return array<mixed>
     */
    public function getCookieParams(): array
    {
        return [];
    }

    /**
     * @param array<mixed> $cookies
     */
    public function withCookieParams(array $cookies): self
    {
        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @param array<mixed> $query
     */
    public function withQueryParams(array $query): self
    {
        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getUploadedFiles(): array
    {
        return [];
    }

    /**
     * @param array<mixed> $uploadedFiles
     */
    public function withUploadedFiles(array $uploadedFiles): self
    {
        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getParsedBody(): array
    {
        return $this->parsedBody;
    }

    /**
     * @param array<mixed>|object|null $data
     */
    public function withParsedBody(mixed $data): self
    {
        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getAttributes(): array
    {
        return [];
    }

    public function getAttribute(string $name, $default = null)
    {
        return $default;
    }

    public function withAttribute(string $name, $value): self
    {
        return $this;
    }

    public function withoutAttribute(string $name): self
    {
        return $this;
    }
}
