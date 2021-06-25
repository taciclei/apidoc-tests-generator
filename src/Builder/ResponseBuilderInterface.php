<?php

namespace PhpJit\ApidocTestsGenerator\Builder;

use ApiPlatform\Core\OpenApi\Model\Operation;

interface ResponseBuilderInterface
{
    public function getJsonSchema(Operation $operation, int $codeResponse): ?string;
}