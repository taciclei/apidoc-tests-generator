<?php

declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator;

use ApiPlatform\Core\OpenApi\Model\Components;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use ApiPlatform\Core\OpenApi\Serializer\OpenApiNormalizer;
use ApiPlatform\Core\Serializer\ItemNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator as FakerGenerator;
use PhpJit\ApidocTestsGenerator\Configuration\ComposerConfigurationReaderInterface;
use PhpJit\ApidocTestsGenerator\TemplateClass\PostTemplateClassCollectionTest;
use PhpJit\ApidocTestsGenerator\Traits\SwaggerTrait;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use ReflectionClass;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function str_replace;

class TestClassGenerator implements TestClassGeneratorInterface
{
    public const  IDENTIFIER = 'TemplateClass';
    public const PATH_TESTS = 'App\Test\Func';
    public string $testNamespace;
    public string $code;
    private ReflectionClass $reflectionClass;
    private ParserFactory $parserFactory;
    private EntityManagerInterface $entityManager;
    private FakerGenerator $fakerGenerator;
    private ComposerConfigurationReaderInterface $composerConfigurationReader;
    private ItemNormalizer $itemNormalizer;
    private DenormalizerInterface $denormalizer;
    private array $parser;

    /**
     * TestClassGenerator constructor.
     * @param ParserFactory $parserFactory
     * @param EntityManagerInterface $entityManager
     * @param FakerGenerator $fakerGenerator
     * @param ComposerConfigurationReaderInterface $composerConfigurationReader
     * @param ItemNormalizer $itemNormalizer
     * @param DenormalizerInterface $denormalizer
     */
    public function __construct(ParserFactory $parserFactory, EntityManagerInterface $entityManager, FakerGenerator $fakerGenerator, ComposerConfigurationReaderInterface $composerConfigurationReader, ItemNormalizer $itemNormalizer, DenormalizerInterface $denormalizer)
    {
        $this->parserFactory = $parserFactory;
        $this->entityManager = $entityManager;
        $this->fakerGenerator = $fakerGenerator;
        $this->composerConfigurationReader = $composerConfigurationReader;
        $this->itemNormalizer = $itemNormalizer;
        $this->denormalizer = $denormalizer;
    }

    use SwaggerTrait;


    public function generate(array $templateOperation, string $route, PathItem $resource, Components $components): GeneratedTestClass
    {
        $this->init($templateOperation['template'], $route);
        if ($templateOperation['template'] == PostTemplateClassCollectionTest::class) {

            $body = $this->getRequestBody($templateOperation, $components);
            if ($body !== null) {
                $this->code = str_replace('{body}', json_encode($body), $this->code);
            }
        }

        if (preg_match('/class\s+(\w+)(.*)?\{/', $this->code, $matches)) {
            $class = $matches[1];

            $generated = new GeneratedTestClass(
                $class,
                $this->testNamespace . '\\' . $class,
                $this->code
            );

            return $generated;
        }
    }

    public function toSnakeCase(string $name): string
    {
        return (new CamelCaseToSnakeCaseNameConverter())->normalize($name);
    }

    public function toCamelCase(string $name, $separator= '\\'): string
    {
        $array = explode($separator, $name);
        $array2 = [];
        foreach ($array as $item) {
            $array2[] = (new CamelCaseToSnakeCaseNameConverter(null,false))->denormalize($item);
        }
        return implode($separator,$array2);
    }

    private function replaceIdentifiers(string $code, string $route): string
    {
        $arrayRoute = $this->toCamelCase($route, '/');
        $testNamespace = str_replace('/', '',$arrayRoute);
        $code = str_replace(self::IDENTIFIER, $this->toCamelCase($testNamespace), $code);

        return str_replace($this->toSnakeCase(self::IDENTIFIER), $testNamespace, $code);
    }

    private function replaceRoute(string $route, string $code): void
    {
        $this->code = str_replace('{route}', $route, $code);
    }

    private function replaceNamespace(string $route):void
    {
        $arrayRoute = str_replace('/','\\', $route);

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
        return str_replace(['{','}'], "", $route);
    }

    private function init(string $className, string $route, int $preferPhp = ParserFactory::PREFER_PHP7): void
    {
        $this->reflectionClass = new ReflectionClass($className);
        $this->code  = $this->getClassContets();

        if ($this->reflectionClass->implementsInterface(TptClassTestInterface::class)) {

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

    private function getClassContets(): string
    {
        return file_get_contents($this->reflectionClass->getFileName());
    }
}
