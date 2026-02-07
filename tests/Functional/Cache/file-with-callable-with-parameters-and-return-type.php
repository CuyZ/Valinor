<?php

use CuyZ\Valinor\Tests\Functional\Cache\SomeAttributeForMethodToTestTypeFilesWatcher;
use CuyZ\Valinor\Tests\Functional\Cache\SomeAttributeForParameterToTestTypeFilesWatcher;
use CuyZ\Valinor\Tests\Functional\Cache\SomeClassToTestTypeFilesWatcherE;
use CuyZ\Valinor\Tests\Functional\Cache\SomeClassToTestTypeFilesWatcherF;

#[SomeAttributeForMethodToTestTypeFilesWatcher]
function some_function_with_parameter_and_return_type_and_attribute_to_test_type_files_watcher(
    #[SomeAttributeForParameterToTestTypeFilesWatcher]
    SomeClassToTestTypeFilesWatcherE $parameter,
): SomeClassToTestTypeFilesWatcherF {
    return new SomeClassToTestTypeFilesWatcherF();
}
