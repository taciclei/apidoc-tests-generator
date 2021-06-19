<?php

declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\Writer;

use PhpJit\ApidocTestsGenerator\GeneratedTestClass;

interface TestClassWriterInterface
{
    public function write(GeneratedTestClass $generatedTestClass) : string;
}
