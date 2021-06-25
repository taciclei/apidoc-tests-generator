<?php

namespace PhpJit\ApidocTestsGenerator\Builder;

use ApiPlatform\Core\OpenApi\Model\Operation;

interface RequestBodyBuilderInterface
{
    public function getResources(string $className): string;

    public function getRequestBody(Operation  $properties): ?array;

    public function populateEnum(string $index, array $items, \ArrayObject $value): array;

    public function populateExample(string $index, array $items, \ArrayObject $value): array;

    public function getBody(array $values, array $required = []): array;

    public function getFakerType(string $index, array $items, \ArrayObject $value): array;

    public function getValueByType(\ArrayObject $value, $index);

    public function getSchema(Operation $operation, $type = 'application/ld+json'): ?string;
}