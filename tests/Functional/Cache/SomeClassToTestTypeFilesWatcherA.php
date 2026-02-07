<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Cache;

use DateTimeInterface;

final class SomeClassToTestTypeFilesWatcherA
{
    /** @var array<SomeClassToTestTypeFilesWatcherB|SomeClassToTestTypeFilesWatcherC> */
    public array $value1;

    public DateTimeInterface|SomeClassToTestTypeFilesWatcherD $value2;

    public function map(SomeClassToTestTypeFilesWatcherE $param): SomeClassToTestTypeFilesWatcherF
    {
        return new SomeClassToTestTypeFilesWatcherF();
    }
}
