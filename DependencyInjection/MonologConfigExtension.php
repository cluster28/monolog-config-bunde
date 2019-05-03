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

        foreach ($this->monologConfigs as $definition => $arrayValues) {
            $this->addDefinitionConfigs($definition, $container);
        }

        $this->addParameters($container->getParameter("kernel.environment"), $container);
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
     * @param $definition
     * @param ContainerBuilder $container
     */
    private function addDefinitionConfigs($definition, ContainerBuilder $container)
    {
        if ($container->hasExtension($definition) || $definition === 'app') {
            foreach ($this->monologConfigs[$definition] as $environment => $environmentConfig) {
                if (in_array($environment, ['config', $container->getParameter("kernel.environment")])) {
                    $this->addHandlers($environmentConfig, $container);
                }
            }
        }
    }

    /**
     * @param $environmentConfig
     * @param ContainerBuilder $container
     */
    private function addHandlers($environmentConfig, ContainerBuilder $container)
    {
        if (isset($environmentConfig['handlers'])) {
            foreach ($environmentConfig['handlers'] as $name => $values) {
                $container->prependExtensionConfig('monolog', [
                    'handlers' => [$name => $values]
                ]);
            }
        }
    }

    /**
     * @param $currentEnvironment
     * @param ContainerBuilder $container
     */
    private function addParameters($currentEnvironment, ContainerBuilder $container)
    {
        if (!$parameters = $this->getConfigParameters()) {
            return;
        }

        foreach ($parameters as $environment => $arrayParameters) {
            if (in_array($environment, ['config', $currentEnvironment])) {
                foreach ($arrayParameters as $name => $value) {
                    $container->setParameter($name, $value);
                }
            }
        }

    }

    /**
     * @return mixed|null
     */
    private function getConfigParameters()
    {
        return isset($this->monologConfigs['parameters']) ? $this->monologConfigs['parameters'] : null ;
    }
}