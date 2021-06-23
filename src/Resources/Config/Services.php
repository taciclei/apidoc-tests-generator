<?php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use ApiPlatform\Core\Serializer\ItemNormalizer;
use PhpParser\ParserFactory;

return function(ContainerConfigurator $configurator) {

    $services = $configurator->services()
        ->defaults()
        ->autowire()      // Automatically injects dependencies in your services.
        ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc
    ;
    $services->load('PhpJit\\ApidocTestsGenerator\\', '../../../src/*')
        ->exclude('../../../src/{Resources,DependencyInjection,Entity,Tests}');

    $services->set(ItemNormalizer::class)->autowire()->autoconfigure()->public();
    $services->set(ParserFactory::class)->autowire()->autoconfigure()->public();

};
