<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Cache\Fixtures;

#[SomeAttributeForClassToTestTypeFilesWatcher]
final class SomeClassWithAttributeToTestTypeFilesWatcher
{
    #[SomeAttributeForPropertyToTestTypeFilesWatcher]
    public string $property;

    #[SomeAttributeForMethodToTestTypeFilesWatcher]
    public function map(#[SomeAttributeForParameterToTestTypeFilesWatcher] string $parameter): void {}
}
