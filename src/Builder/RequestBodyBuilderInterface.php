<?php

namespace PhpJit\ApidocTestsGenerator\Builder;

use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\PathItem;

interface RequestBodyBuilderInterface
{
    public function getResources(): PathItem;
    public function setResources(PathItem $resources): RequestBodyBuilder;

    public function getRequestBody(Operation $operation): self;

    public function populateEnum(string $index, array $items, \ArrayObject $value): array;

    public function populateExample(string $index, array $items, \ArrayObject $value): array;

    public function getBody(): array;

    public function getBodyInvalid(): array;

    public function getFakerType(string $index, array $items, \ArrayObject $value): array;

    public function getValueByType(\ArrayObject $value, $index);

    public function getSchema(Operation $operation, $type = 'application/ld+json'): ?string;
}
