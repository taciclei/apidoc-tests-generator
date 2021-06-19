<?php

namespace PhpJit\ApidocTestsGenerator;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApidocTestsGeneratorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

    }
}
