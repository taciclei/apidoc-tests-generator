<?php

declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\DependencyInjection;

use PhpJit\ApidocTestsGenerator\TemplateClass\DeleteTemplateClassItemTest;
use PhpJit\ApidocTestsGenerator\TemplateClass\GetTemplateClassCollectionTest;
use PhpJit\ApidocTestsGenerator\TemplateClass\GetTemplateClassItemTest;
use PhpJit\ApidocTestsGenerator\TemplateClass\PathTemplateClassItemTest;
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

        $treeBuilder->end();

        return $builder;
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
                    ->scalarNode('patch')->defaultValue(PathTemplateClassItemTest::class)->end()
                    ->scalarNode('delete')->defaultValue(DeleteTemplateClassItemTest::class)->end()
                ->end()
            ->end()
        ;
    }
}
