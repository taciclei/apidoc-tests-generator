<?php

declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator;

class TestClassMetadata
{
    /** @var mixed[] */
    private $useStatements;

    /** @var mixed[] */
    private $properties;

    /** @var mixed[] */
    private $setUpDependencies;

    /** @var mixed[] */
    private $testMethods;

    /**
     * @param mixed[] $useStatements
     * @param mixed[] $properties
     * @param mixed[] $testMethods
     */
    public function __construct(
        array $useStatements,
        array $properties,
        array $testMethods
    ) {
        $this->useStatements     = $useStatements;
        $this->properties        = $properties;
        $this->testMethods       = $testMethods;
    }

    public function getUseStatements() : array
    {
        return $this->useStatements;
    }

    public function getProperties() : array
    {
        return $this->properties;
    }

    public function getTestMethods() : array
    {
        return $this->testMethods;
    }
}
