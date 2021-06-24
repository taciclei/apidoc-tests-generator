<?php

declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator;

use ApiPlatform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Core\OpenApi\Model\Components;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use Faker\Generator as FakerGenerator;
use PhpJit\ApidocTestsGenerator\Builder\RequestBodyBuilder;
use PhpJit\ApidocTestsGenerator\Builder\RequestBodyBuilderInterface;
use PhpJit\ApidocTestsGenerator\Builder\ResponseBuilderInterface;
use PhpParser\ParserFactory;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use function str_replace;

class TestClassGenerator implements TestClassGeneratorInterface
{
    public const  IDENTIFIER = 'TemplateClass';
    public const PATH_TESTS = 'App\Test\Func';
    public string $testNamespace;
    public string $code;
    private ReflectionClass $reflectionClass;
    private ParserFactory $parserFactory;
    private ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory;
    private RequestBodyBuilderInterface $requestBodyBuilder;
    private ResponseBuilderInterface $responseBuilder;

    /**
     * TestClassGenerator constructor.
     * @param ParserFactory $parserFactory
     * @param ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory
     * @param RequestBodyBuilderInterface $requestBodyBuilder
     */
    public function __construct(ParserFactory $parserFactory, ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory, RequestBodyBuilderInterface $requestBodyBuilder, ResponseBuilderInterface $responseBuilder)
    {
        $this->parserFactory = $parserFactory;
        $this->resourceNameCollectionFactory = $resourceNameCollectionFactory;
        $this->requestBodyBuilder = $requestBodyBuilder;
        $this->responseBuilder = $responseBuilder;
    }


    public function generate(array $templateOperation, string $route, PathItem $resource, Components $components): GeneratedTestClass
    {
        $tag = current($templateOperation['operation']->getTags());

        $this->init($templateOperation['template'], $route, $tag);
            $codeResponse = Response::HTTP_OK;
            if ($templateOperation['method'] === 'post' || $templateOperation['method'] === 'put') {
                $body = $this->requestBodyBuilder->getRequestBody($templateOperation['operation']);
                $codeResponse = Response::HTTP_CREATED;
                if ($body !== null) {
                    $this->code = str_replace('{body}', json_encode($body), $this->code);
                }
            }elseif ($templateOperation['method'] === 'delete') {
                $codeResponse = Response::HTTP_NO_CONTENT;
            }

            if (preg_match('/class\s+(\w+)(.*\r*\n*)?\{/', $this->code, $matches)) {
                $class = $matches[1];
                $jsonSchema  = $this->responseBuilder->getJsonSchema($templateOperation['operation'], $codeResponse);
                $generated = new GeneratedTestClass(
                    $class,
                    $this->testNamespace . '\\' . $class,
                    $this->code,
                    $jsonSchema
                );

                return $generated;
            }

    }

    public function toSnakeCase(string $name): string
    {
        return (new CamelCaseToSnakeCaseNameConverter())->normalize($name);
    }

    public function toCamelCase(string $name, $separator = '\\'): string
    {
        $array = explode($separator, $name);
        $array2 = [];
        foreach ($array as $item) {
            $array2[] = (new CamelCaseToSnakeCaseNameConverter(null, false))->denormalize($item);
        }
        return implode($separator, $array2);
    }

    private function replaceIdentifiers(string $code, string $route): string
    {
        $arrayRoute = $this->toCamelCase($route, '/');
        $testNamespace = str_replace('/', '', $arrayRoute);
        $code = str_replace(self::IDENTIFIER, $this->toCamelCase($testNamespace), $code);

        return str_replace($this->toSnakeCase(self::IDENTIFIER), $testNamespace, $code);
    }

    private function replaceRoute(string $route, string $code): void
    {
        $this->code = str_replace('{route}', $route, $code);
    }

    private function replaceNamespace(string $route): void
    {
        $arrayRoute = str_replace('/', '\\', $route);

        $this->testNamespace = self::PATH_TESTS . $this->toCamelCase($arrayRoute);

        if ($this->checkNamespace($this->testNamespace)) {
            $this->code = str_replace($this->reflectionClass->getNamespaceName(), $this->testNamespace, $this->code);
        }

    }

    private function checkNamespace(string $testNamespace): bool
    {
        if (preg_match(
            '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\\\\]*[a-zA-Z0-9_\x7f-\xff]$/',
            $testNamespace
        )) {
            return true;
        }

        return false;
    }

    private function cleanRoute(string $route): string
    {
        return str_replace(['{', '}'], "", $route);
    }

    private function getEntity($tag): ?string
    {
        $resourceNameCollection = $this->resourceNameCollectionFactory->create();
        $entity = [];
        foreach ($resourceNameCollection as $item) {
            $tag = str_replace('/', "\\", $tag);
            if (str_contains($item, $tag)) {
                return $entity[$tag] = '\\' . $item . '::class';
            }
        }
        foreach ($resourceNameCollection as $item) {
            $tag = str_replace('/', "\\", $tag);
            $item = str_replace('\\Entity', "", $item);
            if (str_contains($item, $tag)) {
                return $entity[$tag] = '\\' . $item . '::class';
            }
        }
        return '\\' . $tag . '::class';
    }

    private function replaceEntity($tag, $code): void
    {
        $this->code = str_replace('Entity::class', $this->getEntity($tag), $code);
    }

    private function init(string $className, string $route, string $tag, int $preferPhp = ParserFactory::PREFER_PHP7): void
    {
        $this->reflectionClass = new ReflectionClass($className);
        $this->code = $this->getClassContents();

        if ($this->reflectionClass->implementsInterface(TptClassTestInterface::class)) {

            $this->replaceEntity($tag, $this->code);

            $this->replaceRoute($route, $this->code);

            $templateRoute = $this->cleanRoute($route);
            $this->replaceNamespace($templateRoute);

            $this->code = $this->replaceIdentifiers($this->code, $templateRoute);

            try {
                $parser = $this->parserFactory->create($preferPhp);
                $this->parser = $parser->parse($this->code);

            } catch (\ParseError $error) {
                echo "Parse error: {$error->getMessage()}\n";
                return;
            }
        }
    }

    private function getClassContents(): string
    {
        return file_get_contents($this->reflectionClass->getFileName());
    }
}
