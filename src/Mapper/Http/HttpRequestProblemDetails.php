<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Http;

use CuyZ\Valinor\Mapper\MappingError;
use JsonSerializable;

/**
 * This class represents an RFC-7807 compliant problem details object for a
 * mapping error that occurred when mapping an instance of {@see HttpRequest}.
 *
 * @link https://www.rfc-editor.org/rfc/rfc7807.html
 * @link https://www.rfc-editor.org/rfc/rfc9457.html
 *
 * @api
 */
final readonly class HttpRequestProblemDetails implements JsonSerializable
{
    private function __construct(
        /** @var non-empty-string */
        public string $title,
        /** @var non-empty-string */
        public string $type,
        /** @var 422 */
        public int $status,
        /** @var non-empty-string */
        public string $detail,
        /** @var array<string, non-empty-list<non-empty-string>> */
        public array $errors,
    ) {}

    public static function fromMappingError(MappingError $mappingError): self
    {
        $errors = [];

        foreach ($mappingError->messages()->errors() as $error) {
            $errors[$error->path()][] = $error->toString();
        }

        return new self(
            'HTTP request is invalid',
            'https://www.rfc-editor.org/rfc/rfc9110#section-15.5.21',
            422,
            'A total of ' . count($mappingError->messages()) . ' mapping error(s) were found.',
            $errors,
        );
    }

    /**
     * @return array{
     *     title: non-empty-string,
     *     type: non-empty-string,
     *     status: 422,
     *     detail: non-empty-string,
     *     errors: array<string, non-empty-list<non-empty-string>>,
     * }
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'type' => $this->type,
            'status' => $this->status,
            'detail' => $this->detail,
            'errors' => $this->errors,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
