<?php

declare(strict_types=1);

namespace CuyZ\Valinor\QA\PHPUnit;

use PHPUnit\Event\Application\Finished;
use PHPUnit\Event\Application\FinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class PrettyPrintMappingErrorsExtension implements Extension
{
    public function __construct()
    {
        if (!\class_exists(ConsoleOutput::class)) {
            throw new RuntimeException('In order to use the Valinor PHPUnit extension you should install the symfony/console package.');
        }
    }

    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(
            new class () implements FinishedSubscriber {
                private OutputInterface $output;

                public function __construct()
                {
                    $this->output = new ConsoleOutput();
                }

                public function notify(Finished $event): void
                {
                    if (!MappingErrorsCollector::getInstance()->hasErrors()) {
                        return;
                    }

                    $this->output->writeln('');
                    $this->output->writeln('<comment>The following Valinor mapping errors were thrown:</>');
                    $this->output->writeln('');

                    foreach (MappingErrorsCollector::getInstance()->getMappingErrorsPerClass() as $testClass => $mappingErrorsPerMethod) {
                        $table = (new Table($this->output))
                            ->setHeaderTitle($testClass)
                            ->setHeaders(['Method', 'Path', 'Error']);

                        foreach ($mappingErrorsPerMethod as $methodName => $messages) {
                            foreach ($messages->toArray() as $message) {
                                $table->addRow([
                                    $methodName,
                                    $message->node()->path(),
                                    $message->toString(),
                                ]);
                            }
                        }

                        $table->render();
                    }
                }
            }
        );
    }
}
