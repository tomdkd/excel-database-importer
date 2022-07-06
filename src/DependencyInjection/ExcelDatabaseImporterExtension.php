<?php

namespace Tomdkd\ExcelDatabaseImporter\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ExcelDatabaseImporterExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $locator    = new FileLocator(sprintf('%s/%s', __DIR__, '../Resources/config'));
        $fileloader = new YamlFileLoader($container, $locator);

        $fileloader->load('command.yml');
    }
}