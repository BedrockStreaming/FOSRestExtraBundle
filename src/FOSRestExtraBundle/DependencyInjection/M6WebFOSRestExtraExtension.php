<?php
namespace M6Web\Bundle\FOSRestExtraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\Config\FileLocator,
    Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\DependencyInjection\Loader;

/**
 * Class that loads and manages the bundle configuration
 */
class M6WebFOSRestExtraExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!empty($config['param_fetcher'])) {

            $paramFetcherListener = $container
                ->getDefinition('m6_web_fos_rest_extra.listener.param_fetcher.listener');

            $configMap = [
                'allow_extra'       => 'setAllowExtraParam',
                'strict'            => 'setStrict',
                'error_status_code' => 'setErrorCode',
            ];

            foreach ($configMap as $key => $method) {
                if (isset($config['param_fetcher'][$key])) {
                    $paramFetcherListener->addMethodCall($method, [$config['param_fetcher'][$key]]);
                }
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\DependencyInjection\Extension\Extension::getAlias()
     *
     * @return string
     */
    public function getAlias()
    {
        return 'm6_web_fos_rest_extra';
    }
}
