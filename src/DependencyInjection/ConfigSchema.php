<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\SymfonyBridge\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigSchema implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('retry');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->fixXmlConfig('config')
            ->children()
                ->arrayNode('handlerExceptionDeclarator')
                    ->children()
                        ->scalarNode('class')->end()
                        ->arrayNode('arguments')
                            ->variablePrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('items')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('exception')->end()
                            ->integerNode('maxRetries')->end()
                            ->arrayNode('executor')
                                ->children()
                                    ->scalarNode('class')->end()
                                    ->arrayNode('arguments')
                                        ->variablePrototype()->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('formula')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('operator')->end()
                                        ->scalarNode('argument')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('transport')
                                ->children()
                                    ->scalarNode('class')->end()
                                    ->arrayNode('arguments')
                                        ->variablePrototype()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
