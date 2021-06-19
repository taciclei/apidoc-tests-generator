<?php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function(ContainerConfigurator $configurator) {

    $services = $configurator->services()
        ->defaults()
        ->autowire()      // Automatically injects dependencies in your services.
        ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc
    ;
    $services->load('PhpJit\\ApidocTestsGenerator\\', '../../../src/*')
        ->exclude('../../../src/{DependencyInjection,Entity,Tests}');
};
