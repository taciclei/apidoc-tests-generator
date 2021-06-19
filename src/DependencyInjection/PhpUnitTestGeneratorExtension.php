<?php

namespace JWage\PHPUnitTestGenerator\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class PhpUnitTestGeneratorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
//        $loader = new XmlFileLoader(
//            $container,
//            new FileLocator(
//                __DIR__.'/../Resources/config')
//        );
//        $loader->load('services.xml');
    }
}
