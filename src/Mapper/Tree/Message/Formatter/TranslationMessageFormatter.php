<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\DefaultMessage;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;

use function array_replace_recursive;

/** @api */
final class TranslationMessageFormatter implements MessageFormatter
{
    /** @var array<string, array<string, string>> */
    private array $translations = [];

    /**
     * Returns an instance of the class with the default translations provided
     * by the library.
     */
    public static function default(): self
    {
        $instance = new self();
        $instance->translations = DefaultMessage::TRANSLATIONS;

        return $instance;
    }

    /**
     * Creates or overrides a single translation.
     *
     * ```
     * (TranslationMessageFormatter::default())->withTranslation(
     *     'fr',
     *     'Invalid value {source_value}.',
     *     'Valeur invalide {source_value}.',
     * );
     * ```
     *
     * @pure
     */
    public function withTranslation(string $locale, string $original, string $translation): self
    {
        $clone = clone $this;
        $clone->translations[$original][$locale] = $translation;

        return $clone;
    }

    /**
     * Creates or overrides a list of translations.
     *
     * The given array consists of messages to be translated and for each one a
     * list of locales with their associated translations.
     *
     * ```
     * $formatter = (TranslationMessageFormatter::default())->withTranslations([
     *     'Invalid value {source_value}.' => [
     *         'fr' => 'Valeur invalide {source_value}.',
     *         'es' => 'Valor inválido {source_value}.',
     *     ],
     *     'Some custom message' => [
     *         // …
     *     ],
     * ]);
     *
     * $message = $formatter->format($message);
     * ```
     *
     * @pure
     * @param array<string, array<string, string>> $translations
     */
    public function withTranslations(array $translations): self
    {
        $clone = clone $this;
        // @phpstan-ignore assign.propertyType (PHPStan does not properly infer the return type of the function)
        $clone->translations = array_replace_recursive($this->translations, $translations);

        return $clone;
    }

    /** @pure */
    public function format(NodeMessage $message): NodeMessage
    {
        $body = $this->translations[$message->body()][$message->locale()] ?? null;

        if ($body) {
            return $message->withBody($body);
        }

        return $message;
    }
}
