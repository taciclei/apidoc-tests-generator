<?php

namespace PhpJit\ApidocTestsGenerator\Configuration;

interface ComposerConfigurationReaderInterface
{
    public function createConfiguration(?string $path = null): Configuration;
}
