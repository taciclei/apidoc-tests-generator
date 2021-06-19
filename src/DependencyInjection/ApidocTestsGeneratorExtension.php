<?php

namespace PhpJit\ApidocTestsGenerator\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;


class ApidocTestsGeneratorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/Config'));
        $loader->load('services.php');
        //dd($loader);

/*        $loader = new XmlFileLoader(
            $container,
            new FileLocator(
                __DIR__.'/../Resources/Config')
        );
        $loader->load('services.xml');*/
    }
}
