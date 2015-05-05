<?php
/*
* This file is part of the Sulu CMS.
*
* (c) MASSIVE ART WebServices GmbH
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace Sulu\Bundle\ResourceBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SuluResourceExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $this->setDefaultForFilterConditionsConjunction($config);
        $container->setParameter(
            'sulu_resource.filters.conjunction',
            $config['filters']['conjunctions']
        );

        $container->setparameter('sulu_resource.filters.aliases', $config['aliases']);
    }

    /**
     * Sets default values for filter condition conjunction
     * @param $config
     */
    private function setDefaultForFilterConditionsConjunction(&$config)
    {
        if (!array_key_exists('filters', $config) ||
            !array_key_exists('conjunctions', $config['filters']) ||
            count($config['filters']['conjunctions']) === 0
        ) {
            $config['filters'] = array();
            $config['filters']['conjunctions'] = array(
                array(
                    'id' => 'and',
                    'translation' => 'resource.filter.conjunction.and',
                ),
                array(
                    'id' => 'or',
                    'translation' => 'resource.filter.conjunction.or',
                )
            );
        }
    }
}