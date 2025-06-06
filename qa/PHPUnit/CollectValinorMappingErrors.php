<?php

declare(strict_types=1);

namespace CuyZ\Valinor\QA\PHPUnit;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use Throwable;

/**
 * @require-extends \PHPUnit\Framework\TestCase
 */
trait CollectValinorMappingErrors
{
    protected function transformException(Throwable $t): Throwable
    {
        $originalThrowable = $t;

        while ($t !== null && !$t instanceof MappingError) {
            $t = $t->getPrevious();
        }

        if ($t instanceof MappingError) {
            MappingErrorsCollector::getInstance()
                ->publish(static::class, $this->name(), Messages::flattenFromNode($t->node()));
        }

        return $originalThrowable;
    }
}
