<?php
namespace M6Web\Bundle\FOSRestExtraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\Config\FileLocator,
    Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\DependencyInjection\Loader;

/**
 * Class that loads and manages the bundle configuration
 */
class M6ControllerExtraExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\DependencyInjection\Extension\Extension::getAlias()
     *
     * @return string
     */
    public function getAlias()
    {
        return 'm6_controller_extra';
    }
}
