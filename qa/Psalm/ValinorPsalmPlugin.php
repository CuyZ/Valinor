<?php

namespace CuyZ\Valinor\QA\Psalm;

use CuyZ\Valinor\QA\Psalm\Plugin\ArgumentsMapperPsalmPlugin;
use CuyZ\Valinor\QA\Psalm\Plugin\TreeMapperPsalmPlugin;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use SimpleXMLElement;

class ValinorPsalmPlugin implements PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $api, ?SimpleXMLElement $config = null): void
    {
        require_once __DIR__ . '/Plugin/TreeMapperPsalmPlugin.php';
        require_once __DIR__ . '/Plugin/ArgumentsMapperPsalmPlugin.php';

        $api->registerHooksFromClass(TreeMapperPsalmPlugin::class);
        $api->registerHooksFromClass(ArgumentsMapperPsalmPlugin::class);
    }
}
