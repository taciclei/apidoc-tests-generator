<?php

namespace PhpJit\ApidocTestsGenerator\Builder;

use PhpJit\ApidocTestsGenerator\GeneratedTestClassDto;

interface MarkSkippedBuilderInterface
{
    public function getApidocTestsGeneratorConfigMarkTestSkipped(): array;
    public function setApidocTestsGeneratorConfigMarkTestSkipped(array $apidocTestsGeneratorConfigMarkTestSkipped): void;
    public function write(GeneratedTestClassDto $generatedTestClassDto, string $message);
}