<?php

namespace AppVentus\DataMigrationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 */
class AppVentusDataMigrationExtension extends Extension
{
    /**
     * Load the services and configuration
     *
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        //add the config to the container
        foreach ($config as $key => $value) {
            $container->setParameter('appventus_data_migration.'.$key, $value);
        }

        $key = 'dumpable_entities';
        $value = $config[$key];
        $container->setParameter('appventus_data_migration.'.$key, $value);

        $key = 'dumpable_instance_entities';
        $value = $config[$key];
        $container->setParameter('appventus_data_migration.'.$key, $value);

        $key = 'migration_file_path';
        $value = $config[$key];

        //test that file exists
        if (!file_exists($value)) {
            throw new \Exception('The file '.$value.' does not exists, please create this file.');
        }

        $container->setParameter('appventus_data_migration.'.$key, $value);

        //load services
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('helpers.yml');
    }
}
