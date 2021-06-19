<?php

declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model\Components;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator as FakerGenerator;
use Nelmio\Alice\Loader\NativeLoader;
use PhpJit\ApidocTestsGenerator\Configuration\ComposerConfigurationReaderInterface;
use PhpJit\ApidocTestsGenerator\TemplateClass\PostTemplateClassCollectionTest;
use PhpJit\ApidocTestsGenerator\Traits\SwaggerTrait;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use ReflectionClass;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function str_replace;

class TestClassGenerator implements TestClassGeneratorInterface
{
    public const  IDENTIFIER = 'TemplateClass';
    private ReflectionClass $reflectionClass;
    private string $testNamespace;
    private $parser;
    private string $code;
    public const PATH_TESTS = 'Test\Func\Auto\\';
    private $faker;
    private EntityManagerInterface $entityManager;
    private FakerGenerator $fakerGenerator;
    private ComposerConfigurationReaderInterface $composerConfigurationReader;
    use SwaggerTrait;

    public function __construct(ComposerConfigurationReaderInterface $composerConfigurationReader,
                                EntityManagerInterface $entityManager,
                                FakerGenerator $fakerGenerator = null)
    {   $this->composerConfigurationReader = $composerConfigurationReader;
        $this->entityManager = $entityManager;
        $this->fakerGenerator = $fakerGenerator;
    }

    public function generate(array $templateOperation, string $route, PathItem $resource, Components $components): GeneratedTestClass
    {
        $this->init($templateOperation['template'], $route);
        if($templateOperation['template'] == PostTemplateClassCollectionTest::class) {

            $body = $this->getRequestBody($templateOperation, $components);
            $this->code = str_replace('{body}', json_encode($body), $this->code);
        }

        if (preg_match('/class\s+(\w+)(.*)?\{/', $this->code, $matches)) {
            $class = $matches[1];

            $generated = new GeneratedTestClass(
                $class,
                $this->testNamespace.'\\'.$class,
                $this->code
            );

            return $generated;
        } else {
            echo 'arffff';
            dd($this->code);
        }

        return (new PrettyPrinter\Standard())
            ->prettyPrintFile($this->parser);
    }

    public function toSnakeCase(string $name): string
    {
        return (new CamelCaseToSnakeCaseNameConverter())->normalize($name);
    }

    public function toCamelCase(string $name): string
    {
        return ucfirst((new CamelCaseToSnakeCaseNameConverter())->normalize($name));
    }

    private function replaceIdentifiers(string $code, string $route): string
    {
        $arrayRoute = explode('/', $route);
        $testNamespace = end($arrayRoute);

        $code = str_replace(self::IDENTIFIER, $this->toCamelCase($testNamespace), $code);

        return str_replace($this->toSnakeCase(self::IDENTIFIER), $testNamespace, $code);
    }

    private function replaceNamespace(string $className, string $route): string
    {
        $code = $this->getClassContets();
        $arrayRoute = explode('/', $route);

        $this->testNamespace = self::PATH_TESTS.$this->toCamelCase(end($arrayRoute));

        $code = str_replace($this->reflectionClass->getNamespaceName(), $this->testNamespace, $code);

        return $code;
    }
    private function cleanRoute(string $route): string
    {
        preg_match('/(.*)\/{(.*)}/', $route, $outputArray);
        if(isset($outputArray[1])) {
            return $outputArray[1];
        }
        return $route;
    }
    private function init(string $className, string $route, int $preferPhp = ParserFactory::PREFER_PHP7): void
    {
        $route = $this->cleanRoute($route);
        $this->reflectionClass = new ReflectionClass($className);
        if($this->reflectionClass->implementsInterface(TptClassTestInterface::class)) {
            $this->code = $this->replaceNamespace($className, $route);
            $this->code = $this->replaceIdentifiers($this->code, $route);

            $parser = (new ParserFactory)->create($preferPhp);

            try {
                $this->parser = $parser->parse($this->code);
            } catch (\ParseError $error) {
                echo "Parse error: {$error->getMessage()}\n";
                return ;
            }
        } else {
            dd($className);
        }
    }

    private function getClassContets(): string
    {
        return file_get_contents($this->reflectionClass->getFileName());
    }
}
