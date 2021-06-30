<?php

declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\DependencyInjection;

use PhpJit\ApidocTestsGenerator\TemplateClass\RemoveTemplateClassItemTest;
use PhpJit\ApidocTestsGenerator\TemplateClass\GetTemplateClassCollectionTest;
use PhpJit\ApidocTestsGenerator\TemplateClass\GetTemplateClassItemTest;
use PhpJit\ApidocTestsGenerator\TemplateClass\PatchTemplateClassItemTest;
use PhpJit\ApidocTestsGenerator\TemplateClass\PostTemplateClassCollectionTest;
use PhpJit\ApidocTestsGenerator\TemplateClass\PutTemplateClassItemTest;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('apidoc_tests_generator');

        $treeBuilder = $builder->getRootNode()
            ->children();

        $this->templatesNode($treeBuilder);
        $this->markTestSkippedNode($treeBuilder);
        $this->ignoreRoutesNode($treeBuilder);
        $this->whiteListNode($treeBuilder);
        $treeBuilder->end();

        return $builder;
    }

    private function whiteListNode(NodeBuilder $treeBuilder) : void {
        $treeBuilder
            ->arrayNode('whiteList')
            ->arrayPrototype()
            ->children()
            ->scalarNode('route')->end()
            ->scalarNode('method')->end()
            ->end()
            ->end()
        ;
    }

    private function ignoreRoutesNode(NodeBuilder $treeBuilder) : void {
        $treeBuilder
            ->arrayNode('ignoreRoutes')
            ->arrayPrototype()
            ->children()
            ->scalarNode('route')->end()
            ->scalarNode('method')->end()
            ->end()
            ->end()
        ;
    }
    private function markTestSkippedNode(NodeBuilder $treeBuilder) : void {
        $treeBuilder
            ->arrayNode('markTestSkipped')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('route')->end()
                        ->scalarNode('method')->end()
                    ->end()
            ->end()
        ;
    }

    private function templatesNode(NodeBuilder $treeBuilder) : void {
        $treeBuilder
            ->arrayNode('templates')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('post')->defaultValue(PostTemplateClassCollectionTest::class)->end()
                    ->scalarNode('get')->defaultValue(GetTemplateClassItemTest::class)->end()
                    ->scalarNode('get_collection')->defaultValue(GetTemplateClassCollectionTest::class)->end()
                    ->scalarNode('put')->defaultValue(PutTemplateClassItemTest::class)->end()
                    ->scalarNode('patch')->defaultValue(PatchTemplateClassItemTest::class)->end()
                    ->scalarNode('delete')->defaultValue(RemoveTemplateClassItemTest::class)->end()
                ->end()
            ->end()
        ;
    }
}
