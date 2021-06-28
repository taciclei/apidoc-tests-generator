<?php

declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\Writer;

use PhpJit\ApidocTestsGenerator\GeneratedTestClassDto;

interface Psr4TestClassWriterInterface
{
    public function write(GeneratedTestClassDto $generatedTestClass) : string;
}
