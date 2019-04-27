<?php

namespace MonologConfigBundle\DependencyInjection;

use MonologConfigBundle\Importer\FileImporter;
use MonologConfigBundle\Importer\ImporterInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * Class MonologConfigExtension
 * @package MonologConfigBundle\DependencyInjection
 */
class MonologConfigExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @var array
     */
    protected $monologConfigs = [];

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
    }

    /**
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (isset($config['sources']) && is_array($config['sources']['files'])) {
            $this->monologConfigs = $this->mergeConfiguration($this->monologConfigs, new FileImporter($config['sources']['files']));
        }

        $this->addConfig('config', $container);
        $this->addConfig($container->getParameter("kernel.environment"), $container);
    }

    /**
     * @param $monologConfigs
     * @param ImporterInterface $importer
     * @return array
     */
    private function mergeConfiguration($monologConfigs, ImporterInterface $importer)
    {
        return array_merge($monologConfigs, $importer->getConfigs());
    }

    /**
     * @param $env
     * @param ContainerBuilder $container
     */
    private function addConfig($env, ContainerBuilder $container)
    {
        if (isset($this->monologConfigs['monolog']) && isset($this->monologConfigs['monolog'][$env])) {
            foreach ($this->monologConfigs['monolog'][$env]['handlers'] as $name => $values) {
                $container->prependExtensionConfig('monolog', [
                    'handlers' => [$name => $values]
                ]);
            }
        }

        if (isset($this->monologConfigs['parameters']) && isset($this->monologConfigs['parameters'][$env])) {
            foreach ($this->monologConfigs['parameters'][$env] as $name => $value) {
                $container->setParameter($name, $value);
            }
        }
    }
}