<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Http;

use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

use function is_object;

/**
 * @todo update examples with attributes
 *
 * This class represents an HTTP request that can be given to the mapper to have
 * custom mapping rules applied, based on the request's method and data.
 *
 * The following rules apply:
 *
 * - Route parameters will be mapped to the corresponding target's parameters.
 * - If the request is supposed to contain a body (`POST`, `PUT` or `PATCH`),
 *   query parameters are ignored and only the body values will be mapped to the
 *   corresponding target's parameters.
 * - If the request is *not* supposed to contain a body, body values are ignored
 *   and only the query parameters will be mapped to the corresponding target's
 *   parameters.
 *
 * Example of a GET request:
 *
 * ```php
 * // Controller to list articles of an author
 * final class ListArticle
 * {
 *     #[Route('GET', '/api/authors/{authorId}/articles')]
 *     public function __invoke(
 *         // Comes from the route
 *         \My\App\AuthorId $authorId,
 *
 *         // Both come from query parameters
 *         \My\App\Status $status,
 *         \My\App\Sort $sort,
 *     ): ResponseInterface { … }
 * }
 *
 * // GET /api/authors/42/articles?status=published&sort=date-desc
 * $request = \CuyZ\Valinor\Mapper\Source\HttpRequest::new('GET')
 *     ->withRouteParameters(['authorId' => 42])
 *     ->withQueryParameters([
 *         'status' => 'published',
 *         'sort' => 'date-desc',
 *     ]);
 *
 * $arguments = (new \CuyZ\Valinor\MapperBuilder())
 *     ->argumentsMapper()
 *     ->mapArguments(new ListArticle(), $request);
 * ```
 *
 * Example of a POST request:
 *
 * ```php
 * // Controller to post a comment on an article
 * final class PostComment
 * {
 *     #[Route('POST', '/api/posts/{postId}/comments')]
 *     public function __invoke(
 *         // Comes from the route
 *         \My\App\PostId $postId,
 *
 *         // Both come from body payload
 *         \My\App\Email $author,
 *         \My\App\Comment $content,
 *     ): ResponseInterface { … }
 * }
 *
 * // POST /api/posts/1337/comments
 * $request = \CuyZ\Valinor\Mapper\Source\HttpRequest::new('POST')
 *     ->withRouteParameters(['postId' => 1337])
 *     ->withBodyValues([
 *         'author' => 'jane.doe@example.com',
 *         'content' => 'Great article, thanks for sharing!',
 *     ]);
 *
 * $arguments = (new \CuyZ\Valinor\MapperBuilder())
 *     ->argumentsMapper()
 *     ->mapArguments(new PostComment(), $request);
 * ```
 *
 * @api
 */
final class HttpRequest
{
    /**
     * @todo because we don't need the "method" (`GET`, …), we could simplify
     *       this class by removing all withers?
     */
    private function __construct(
        /**
         * Route parameters that were extracted by the router.
         *
         * @var array<mixed>
         */
        public readonly array $routeParameters = [],

        /**
         * Query parameters that were extracted from the request URI.
         *
         * @var array<mixed>
         */
        public readonly array $queryParameters = [],

        /**
         * Body values that were extracted from the request content.
         *
         * @var array<mixed>
         */
        public readonly array $bodyValues = [],

        /**
         * Original request object coming, for instance, from a library or a
         * framework. If it is given, then this object will automatically be
         * mapped to any target parameter matching its type.
         */
        public readonly ?object $requestObject = null,
    ) {}

    public static function new(): self
    {
        return new self();
    }

    public static function fromPsr(ServerRequestInterface $request): self
    {
        if (is_object($request->getParsedBody())) {
            throw new RuntimeException("Request's parsed body must be an array."); // @todo move to own exception class
        }

        return new self([], $request->getQueryParams(), $request->getParsedBody() ?? [], $request);
    }

    /**
     * @param array<mixed> $routeParameters
     */
    public function withRouteParameters(array $routeParameters): self
    {
        return new self($routeParameters, $this->queryParameters, $this->bodyValues, $this->requestObject);
    }

    /**
     * @param array<mixed> $queryParameters
     */
    public function withQueryParameters(array $queryParameters): self
    {
        return new self($this->routeParameters, $queryParameters, $this->bodyValues, $this->requestObject);
    }

    /**
     * @param array<mixed> $bodyValues
     */
    public function withBodyValues(array $bodyValues): self
    {
        return new self($this->routeParameters, $this->queryParameters, $bodyValues, $this->requestObject);
    }

    public function withRequestObject(object $request): self
    {
        return new self($this->routeParameters, $this->queryParameters, $this->bodyValues, $request);
    }
}
