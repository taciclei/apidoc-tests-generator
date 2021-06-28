<?php

declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator;

class GeneratedTestClassDto
{
    public ?string $route = null;
    public ?string $method = null;
    public ?string $className  = null;
    public ?string $testClassName  = null;
    public ?string $code = null;
    public ?string $jsonSchema  = null;
    public ?string $body = null;
    public ?string $bodInvalid = null;

    public function __construct(?string $route, ?string $methode)
    {
        $this->route = $route;
        $this->method = $methode;
    }

    /**
     * @return string|null
     */
    public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * @param string|null $route
     * @return GeneratedTestClassDto
     */
    public function setRoute(?string $route): GeneratedTestClassDto
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @param string|null $method
     * @return GeneratedTestClassDto
     */
    public function setMethod(?string $method): GeneratedTestClassDto
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClassName(): ?string
    {
        return $this->className;
    }

    /**
     * @param string|null $className
     * @return GeneratedTestClassDto
     */
    public function setClassName(?string $className): GeneratedTestClassDto
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTestClassName(): ?string
    {
        return $this->testClassName;
    }

    /**
     * @param string|null $testClassName
     * @return GeneratedTestClassDto
     */
    public function setTestClassName(?string $testClassName): GeneratedTestClassDto
    {
        $this->testClassName = $testClassName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     * @return GeneratedTestClassDto
     */
    public function setCode(?string $code): GeneratedTestClassDto
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getJsonSchema(): ?string
    {
        return $this->jsonSchema;
    }

    /**
     * @param string|null $jsonSchema
     * @return GeneratedTestClassDto
     */
    public function setJsonSchema(?string $jsonSchema): GeneratedTestClassDto
    {
        $this->jsonSchema = $jsonSchema;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param string|null $body
     * @return GeneratedTestClassDto
     */
    public function setBody(?string $body): GeneratedTestClassDto
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBodInvalid(): ?string
    {
        return $this->bodInvalid;
    }

    /**
     * @param string|null $bodInvalid
     * @return GeneratedTestClassDto
     */
    public function setBodInvalid(?string $bodInvalid): GeneratedTestClassDto
    {
        $this->bodInvalid = $bodInvalid;
        return $this;
    }
}
