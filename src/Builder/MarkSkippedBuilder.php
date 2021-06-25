<?php
declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\Builder;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model\Components;
use ApiPlatform\Core\OpenApi\Model\MediaType;
use ApiPlatform\Core\OpenApi\Model\Operation;
use Faker\Generator as FakerGenerator;
use PhpJit\ApidocTestsGenerator\GeneratedTestClassDto;
use Symfony\Component\HttpFoundation\Response;
use ApiPlatform\Core\OpenApi\Model\Response as ModelResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class MarkSkippedBuilder implements MarkSkippedBuilderInterface
{
    private array $apidocTestsGeneratorConfigMarkTestSkipped;
    private ContainerInterface $container;

    public function __construct(array $apidocTestsGeneratorConfigMarkTestSkipped,ContainerInterface $container)
    {
        $this->apidocTestsGeneratorConfigMarkTestSkipped = $apidocTestsGeneratorConfigMarkTestSkipped;
        $this->container = $container;
    }

    public function write(GeneratedTestClassDto $generatedTestClassDto, string $message) {

        $code = str_replace('//$this->markTestSkipped();', '$this->markTestSkipped(\''.$message.'\');', $generatedTestClassDto->getCode());
        $generatedTestClassDto->setCode($code);

        $filePath = $this->container->get('kernel')->getRootDir() .'/../config/packages/dev/apidoc_tests_generator.yaml';
        $arrayConf = Yaml::parseFile($filePath);
        $param = ['route' => $generatedTestClassDto->getRoute(), 'method' => $generatedTestClassDto->getMethod()];
        $arrayConf['apidoc_tests_generator']['markTestSkipped'][] = $param;

        $yaml = Yaml::dump($arrayConf, 2, 4, Yaml::DUMP_OBJECT);

        file_put_contents($filePath, $yaml);
    }

    /**
     * @return array
     */
    public function getApidocTestsGeneratorConfigMarkTestSkipped(): array
    {
        return $this->apidocTestsGeneratorConfigMarkTestSkipped;
    }

    /**
     * @param array $apidocTestsGeneratorConfigMarkTestSkipped
     */
    public function setApidocTestsGeneratorConfigMarkTestSkipped(array $apidocTestsGeneratorConfigMarkTestSkipped): void
    {
        $this->apidocTestsGeneratorConfigMarkTestSkipped = $apidocTestsGeneratorConfigMarkTestSkipped;
    }



}