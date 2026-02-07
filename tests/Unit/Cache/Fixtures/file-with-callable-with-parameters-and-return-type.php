<?php

use CuyZ\Valinor\Tests\Unit\Cache\Fixtures\SomeAttributeForMethodToTestTypeFilesWatcher;
use CuyZ\Valinor\Tests\Unit\Cache\Fixtures\SomeAttributeForParameterToTestTypeFilesWatcher;
use CuyZ\Valinor\Tests\Unit\Cache\Fixtures\SomeClassToTestTypeFilesWatcherE;
use CuyZ\Valinor\Tests\Unit\Cache\Fixtures\SomeClassToTestTypeFilesWatcherF;

#[SomeAttributeForMethodToTestTypeFilesWatcher]
function some_function_with_parameter_and_return_type_and_attribute_to_test_type_files_watcher(
    #[SomeAttributeForParameterToTestTypeFilesWatcher]
    SomeClassToTestTypeFilesWatcherE $parameter,
): SomeClassToTestTypeFilesWatcherF {
    return new SomeClassToTestTypeFilesWatcherF();
}
