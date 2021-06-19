<?php

declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\Command;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PhpJit\ApidocTestsGenerator\Configuration\AutoloadingStrategy;
use PhpJit\ApidocTestsGenerator\Configuration\ComposerConfigurationReader;
use PhpJit\ApidocTestsGenerator\Configuration\ComposerConfigurationReaderInterface;
use PhpJit\ApidocTestsGenerator\Configuration\Configuration;
use PhpJit\ApidocTestsGenerator\TemplateClass\DeleteTemplateClassItemTest;
use PhpJit\ApidocTestsGenerator\TemplateClass\GetTemplateClassCollectionTest;
use PhpJit\ApidocTestsGenerator\TemplateClass\GetTemplateClassItemTest;
use PhpJit\ApidocTestsGenerator\TemplateClass\PostTemplateClassCollectionTest;
use PhpJit\ApidocTestsGenerator\TemplateClass\PutTemplateClassItemTest;
use PhpJit\ApidocTestsGenerator\TestClassGenerator;
use PhpJit\ApidocTestsGenerator\TestClassGeneratorInterface;
use PhpJit\ApidocTestsGenerator\Traits\SwaggerTrait;
use PhpJit\ApidocTestsGenerator\Writer\Psr4TestClassWriterInterface;
use PhpJit\ApidocTestsGenerator\Writer\TestClassWriterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function array_diff;
use function array_values;
use function assert;
use function file_exists;
use function get_declared_classes;
use function getcwd;
use function is_string;
use function sprintf;

class GenerateTestClassCommand extends Command implements GenerateTestClassCommandInterface
{
    use SwaggerTrait;
    protected static $defaultName = 'generate-test-class';

    private OpenApiFactoryInterface $openApiFactory;
    private NormalizerInterface $normalizer;
    private EntityManagerInterface $entityManager;
    private ComposerConfigurationReaderInterface $composerConfigurationReader;
    private TestClassGeneratorInterface $testClassGenerator;
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

    public function __construct(OpenApiFactoryInterface $openApiFactory,
                                NormalizerInterface $normalizer,
                                ComposerConfigurationReaderInterface $composerConfigurationReader,
                                TestClassGeneratorInterface $testClassGenerator)
    {
        parent::__construct();
        $this->openApiFactory = $openApiFactory;
        $this->normalizer = $normalizer;
        $this->composerConfigurationReader = $composerConfigurationReader;
        $this->testClassGenerator = $testClassGenerator;
    }

    protected function configure(): void
    {
        $this
            ->setName('generate-test-class')
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
        return Command::SUCCESS;
    }

    private function getTemplatesOperation(PathItem $resource): array
    {
        $key = 0;
        $temmplates = [];
        if ($this->isGetItem($resource)) {
            //$temmplates[] = GetTemplateClassItemTest::class;
        }
        if ($this->isGetCollection($resource)) {
            //$temmplates[] = GetTemplateClassCollectionTest::class;
        }
        if ($operation = $this->isPost($resource)) {
            $key++;
            $temmplates[$key]['template'] = PostTemplateClassCollectionTest::class;
            $temmplates[$key]['operation'] = $operation;
        }
        if ($this->isDelete($resource)) {
            //$temmplates[] = DeleteTemplateClassItemTest::class;
        }
        if ($this->isPut($resource)) {
            //$temmplates[] = PutTemplateClassItemTest::class;
        }
        if (isset($temmplates)) {
            return $temmplates;
        }
    }

    private function isPut(PathItem $operationId)
    {
        if (method_exists($operationId, 'getPut') && $operationId->getPut() !== null && method_exists($operationId->getPut(), 'getoperationId')) {
            return !empty(preg_match('/put(.*)Item/', $operationId->getPut()->getoperationId()));
        }
    }

    private function isDelete(PathItem $operationId)
    {
        if (method_exists($operationId, 'getDelete') && $operationId->getDelete() !== null && method_exists($operationId->getDelete(), 'getoperationId')) {
            return !empty(preg_match('/delete(.*)Item/', $operationId->getDelete()->getoperationId()));
        }
    }

    private function isPost(PathItem $operationId)
    {
        if (method_exists($operationId, 'getPost') && $operationId->getPost() !== null && method_exists($operationId->getPost(), 'getoperationId')) {
            if(!empty(preg_match('/post(.*)Collection/', $operationId->getPost()->getoperationId()))) {
                return $operationId->getPost();
            }
        }
    }

    private function isGetItem(PathItem $operationId)
    {
        if (method_exists($operationId, 'getGet') && $operationId->getGet() !== null && method_exists($operationId->getGet(), 'getoperationId')) {
            return !empty(preg_match('/get(.*)Item/', $operationId->getGet()->getoperationId()));
        }
    }

    private function isGetCollection(PathItem $operationId)
    {
        if (method_exists($operationId, 'getGet') && $operationId->getGet() !== null && method_exists($operationId->getGet(), 'getoperationId')) {
            return !empty(preg_match('/get(.*)Collection/', $operationId->getGet()->getoperationId()));
        }
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
