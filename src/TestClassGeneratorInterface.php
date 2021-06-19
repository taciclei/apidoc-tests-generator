<?php

namespace PhpJit\ApidocTestsGenerator;

use ApiPlatform\Core\OpenApi\Model\Components;
use ApiPlatform\Core\OpenApi\Model\PathItem;

interface TestClassGeneratorInterface
{
    public function generate(array $templateOperation, string $route, PathItem $resource, Components $components): GeneratedTestClass;

    public function toSnakeCase(string $name): string;

    public function toCamelCase(string $name): string;
}
