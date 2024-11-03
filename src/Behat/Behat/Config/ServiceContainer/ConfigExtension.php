<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Config\ServiceContainer;

use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\Tester\ServiceContainer\TesterExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Enables stop on failure controller for Behat.
 */
final class ConfigExtension implements Extension
{
    public const STOP_ON_FAILURE_ID = 'config.stop_on_failure';
    public const STRICT_ID = 'config.strict';

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'config';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('stop_on_failure')
                    ->defaultValue(null)
                ->end()
                ->booleanNode('strict')
                    ->defaultFalse()
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadStopOnFailureHandler($container, $config);
        $this->loadStrictHandler($container, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }

    
    /**
     * Loads stop on failure controller.
     */
    private function loadStopOnFailureHandler(ContainerBuilder $container, array $config)
    {
        $definition = new Definition('Behat\Behat\Config\Handler\StopOnFailureHandler', array(
            new Reference(EventDispatcherExtension::DISPATCHER_ID),
            $config['strict']
        ));
        if ($config['stop_on_failure'] === true) {
            $definition->addMethodCall('registerListeners');
        }
        $container->setDefinition(self::STOP_ON_FAILURE_ID, $definition);
    }

    /**
     * Loads strict handler.
     */
    private function loadStrictHandler(ContainerBuilder $container, array $config)
    {
        $definition = new Definition('Behat\Behat\Config\Handler\StrictHandler', array(
            new Reference(TesterExtension::RESULT_INTERPRETER_ID)
        ));
        if ($config['strict'] === true) {
            $definition->addMethodCall('registerStrictInterpretation');
        }

        $container->setDefinition(self::STRICT_ID, $definition);
    }
}
