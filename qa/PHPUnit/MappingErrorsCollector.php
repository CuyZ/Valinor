<?php

declare(strict_types=1);

namespace CuyZ\Valinor\QA\PHPUnit;

use CuyZ\Valinor\Mapper\Tree\Message\Messages;

final class MappingErrorsCollector
{
    private static MappingErrorsCollector | null $instance = null;

    /**
     * @var array<class-string, array<string, Messages>>
     */
    private array $mappingErrorsPerTest = [];

    public static function getInstance(): MappingErrorsCollector
    {
        if (self::$instance === null) {
            self::$instance = new MappingErrorsCollector();
        }

        return self::$instance;
    }

    /**
     * @param class-string $testClass
     */
    public function publish(string $testClass, string $method, Messages $messages): void
    {
        if (!\array_key_exists($testClass, $this->mappingErrorsPerTest)) {
            $this->mappingErrorsPerTest[$testClass] = [];
        }

        $this->mappingErrorsPerTest[$testClass][$method] = $messages;
    }

    public function hasErrors(): bool
    {
        return count($this->mappingErrorsPerTest) > 0;
    }

    /**
     * @return array<class-string, array<string, Messages>>
     */
    public function getMappingErrorsPerClass(): array
    {
        return $this->mappingErrorsPerTest;
    }
}
