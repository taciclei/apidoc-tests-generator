<?php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use ApiPlatform\Core\Hydra\Serializer\DocumentationNormalizer;
use ApiPlatform\Core\JsonLd\Action\ContextAction;
use ApiPlatform\Core\JsonLd\Serializer\ItemNormalizer;
use ApiPlatform\Core\Metadata\Extractor\ExtractorInterface;
use ApiPlatform\Core\Serializer\JsonEncoder;
use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use PhpParser\ParserFactory;
use ApiPlatform\Core\Identifier\Normalizer\DateTimeIdentifierDenormalizer;
use ApiPlatform\Core\JsonLd\ContextBuilderInterface;

return function(ContainerConfigurator $configurator) {

    $services = $configurator->services()
        ->defaults()
        ->autowire()      // Automatically injects dependencies in your services.
        ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc
        ->bind('$apidocTestsGeneratorConfigTemplates', '%apidoc_tests_generator.templates%')
        ->bind('$apidocTestsGeneratorConfigWhiteList', '%apidoc_tests_generator.whiteList%')
        ->bind('$apidocTestsGeneratorConfigIgnoreRoutes', '%apidoc_tests_generator.ignoreRoutes%')
        ->bind('$apidocTestsGeneratorConfigMarkTestSkipped', '%apidoc_tests_generator.markTestSkipped%')
    ;
    $services->load('PhpJit\\ApidocTestsGenerator\\', '../../../src/*')
        ->exclude('../../../src/{DependencyInjection,Entity,Tests,Resources}');

    $services->set(JsonEncoder::class)
        ->arg('$format', 'json')
        ->public();

    $services->set(ParserFactory::class)->autowire()->autoconfigure()->public();
    $services->set(DateTimeIdentifierDenormalizer::class)->autowire()->autoconfigure()->public();
    $services->set(SerializerContextBuilderInterface::class)->autowire()->autoconfigure()->public();
    $services->set(ContextAction::class)->autowire()->autoconfigure()->public();
    $services->set(ContextBuilderInterface::class)->autowire()->autoconfigure()->public();
    $services->set(DocumentationNormalizer::class)->autowire()->autoconfigure()->public();
};
