<?php

namespace Edan\DpoEdanBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class DpoEdanExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $definition = $container->getDefinition('dpo_edan.edan');
        $definition->setArgument(0, $config['edan_url']);
        $definition->setArgument(1, $config['edan_app_id']);
        $definition->setArgument(2, $config['edan_auth_token']);
        $definition->setArgument(3, $config['edan_version']);
        $definition->setArgument(4, $config['edan_search_endpoint']);
        $definition->setArgument(5, $config['edan_content_endpoint']);
    }
}
