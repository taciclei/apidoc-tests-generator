<?php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use PhpParser\ParserFactory;

return function(ContainerConfigurator $configurator) {

    $services = $configurator->services()
        ->defaults()
        ->autowire()      // Automatically injects dependencies in your services.
        ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc
        ->bind('$apidocTestsGeneratorConfigTemplates', '%apidoc_tests_generator.templates%')
        ->bind('$apidocTestsGeneratorConfigMarkTestSkipped', '%apidoc_tests_generator.markTestSkipped%')
    ;
    $services->load('PhpJit\\ApidocTestsGenerator\\', '../../../src/*')
        ->exclude('../../../src/{DependencyInjection,Entity,Tests,Resources}');

    $services->set(ParserFactory::class)->autowire()->autoconfigure()->public();

};
