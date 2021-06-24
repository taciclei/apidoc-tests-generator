<?php

declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator;

class GeneratedTestClass
{
    /** @var string */
    private $className;

    /** @var string */
    private $testClassName;

    /** @var string */
    private string $code;
    private ?string $jsonSchema;

    public function __construct(string $className, string $testClassName, string $code, ?string $jsonSchema)
    {
        $this->className = $className;
        $this->testClassName = $testClassName;
        $this->code = $code;
        $this->jsonSchema = $jsonSchema;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getTestClassName(): string
    {
        return $this->testClassName;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getJsonSchema(): ?string
    {
        return $this->jsonSchema;
    }


}
