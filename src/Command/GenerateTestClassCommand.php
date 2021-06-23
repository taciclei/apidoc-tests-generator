<?php

declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\Command;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PhpJit\ApidocTestsGenerator\Configuration\AutoloadingStrategy;
use PhpJit\ApidocTestsGenerator\Configuration\ComposerConfigurationReaderInterface;
use PhpJit\ApidocTestsGenerator\Configuration\Configuration;
use PhpJit\ApidocTestsGenerator\TestClassGeneratorInterface;
use PhpJit\ApidocTestsGenerator\Traits\SwaggerTrait;
use PhpJit\ApidocTestsGenerator\Writer\Psr4TestClassWriterInterface;
use PhpJit\ApidocTestsGenerator\Writer\TestClassWriterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function sprintf;

class GenerateTestClassCommand extends Command implements GenerateTestClassCommandInterface
{
    use SwaggerTrait;

    protected static $defaultName = 'apidoc_tests:generate-classes';

    private OpenApiFactoryInterface $openApiFactory;
    private NormalizerInterface $normalizer;
    private EntityManagerInterface $entityManager;
    private ComposerConfigurationReaderInterface $composerConfigurationReader;
    private TestClassGeneratorInterface $testClassGenerator;
    private array $apiDocTemplates;

    /**
     * @return OpenApiFactoryInterface
     */
    public function getOpenApiFactory(): OpenApiFactoryInterface
    {
        return $this->openApiFactory;
    }

    /**
     * @return NormalizerInterface
     */
    public function getNormalizer(): NormalizerInterface
    {
        return $this->normalizer;
    }

    public function __construct(
        OpenApiFactoryInterface $openApiFactory,
        NormalizerInterface $normalizer,
        ComposerConfigurationReaderInterface $composerConfigurationReader,
        TestClassGeneratorInterface $testClassGenerator,
        array $apiDocTemplates
    )
    {
        parent::__construct();
        $this->openApiFactory = $openApiFactory;
        $this->normalizer = $normalizer;
        $this->composerConfigurationReader = $composerConfigurationReader;
        $this->testClassGenerator = $testClassGenerator;
        $this->apiDocTemplates = $apiDocTemplates;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate a PHPUnit test class from a class.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /* @var $resources PathItem[] */
        $resources = $this->getOpenApiFactory()->__invoke()->getPaths()->getPaths();
        $components = $this->getOpenApiFactory()->__invoke()->getComponents();

        foreach ($resources as $route => $resource) {
            $templatesOperation = $this->getTemplatesOperation($resource);

            foreach ($templatesOperation as $templateOperation) {
                $configuration = $this->composerConfigurationReader->createConfiguration();
                $output->writeln(sprintf('Generating test class for <info>%s</info>', $templateOperation['template']));
                $output->writeln('');

                $generatedTestClass = $this->testClassGenerator->generate($templateOperation, $route, $resource, $components);

                $output->writeln($generatedTestClass->getCode());
                $testClassWriter = $this->createTestClassWriter($configuration);

                $writePath = $testClassWriter->write($generatedTestClass);

                $output->writeln(sprintf('Test class written to <info>%s</info>', $writePath));
            }

        }

        return 0;
    }

    private function getTemplatesOperation(PathItem $resource): array
    {
        $key = 0;
        $templates = [];

        if ($operation = $this->isGetItem($resource)) {
            $key++;
            $templates[$key]['template'] = $this->apiDocTemplates['get'];
        } elseif ($operation = $this->isGetCollection($resource)) {
            $key++;
            $templates[$key]['template'] = $this->apiDocTemplates['get_collection'];
        } elseif ($operation = $this->isPost($resource)) {
            $key++;
            $templates[$key]['template'] = $this->apiDocTemplates['post'];
        } elseif ($operation = $this->isDelete($resource)) {
            $key++;
            $templates[$key]['template'] = $this->apiDocTemplates['delete'];
        } elseif ($operation = $this->isPut($resource)) {
            $key++;
            $templates[$key]['template'] = $this->apiDocTemplates['put'];
        }

        if ($operation !== null && !empty($templates[$key]['template'])) {
            $templates[$key]['operation'] = $operation;
        }

        return $templates;
    }

    private function isMethodExists(PathItem $operationId, string $verb, string $type = 'Item'): ?Operation
    {
        $verb = ucfirst(strtolower($verb));
        $type = ucfirst(strtolower($type));

        $method = sprintf('get%s', $verb);

        if (method_exists($operationId, $method) === false) {
            return null;
        }

        if ($operationId->{$method}() === null) {
            return null;
        }

        if (method_exists($operationId->{$method}(), 'getoperationId') === false) {
            return null;
        }

        $regex = sprintf('/%s(.*)%s/', strtolower($verb), $type);

        if (empty(preg_match($regex, $operationId->{$method}()->getoperationId()))) {
            return null;
        }

        return $operationId->{$method}();
    }

    private function isPut(PathItem $operationId): ?Operation
    {
        return $this->isMethodExists($operationId, 'put');
    }

    private function isDelete(PathItem $operationId): ?Operation
    {
        return $this->isMethodExists($operationId, 'delete');
    }

    private function isPost(PathItem $operationId): ?Operation
    {
        return $this->isMethodExists($operationId, 'post', 'Collection');
    }

    private function isGetItem(PathItem $operationId): ?Operation
    {
        return $this->isMethodExists($operationId, 'get');
    }

    private function isGetCollection(PathItem $operationId): ?Operation
    {
        return $this->isMethodExists($operationId, 'get', 'Collection');
    }

    private function createTestClassWriter(Configuration $configuration): TestClassWriterInterface
    {
        $autoloadingStrategy = $configuration->getAutoloadingStrategy();

        if ($autoloadingStrategy === AutoloadingStrategy::PSR4) {
            return new Psr4TestClassWriterInterface($configuration);
        }

        throw new InvalidArgumentException(
            sprintf('Autoloading strategy not supported %s not supported', $autoloadingStrategy)
        );
    }
}
