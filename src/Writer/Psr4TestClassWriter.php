<?php

declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\Writer;

use PhpJit\ApidocTestsGenerator\Configuration\Configuration;
use PhpJit\ApidocTestsGenerator\GeneratedTestClassDto;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use const DIRECTORY_SEPARATOR;
use function dirname;
use function sprintf;
use function str_replace;

class Psr4TestClassWriter implements Psr4TestClassWriterInterface
{
    /** @var Configuration */
    private $configuration;

    /** @var Filesystem */
    private $filesystem;
    public const TYPE_WRITE_PHP = 'php';
    public const TYPE_WRITE_JSON = 'schemajson';

    public function __construct(Configuration $configuration, ?Filesystem $filesystem = null)
    {
        $this->configuration = $configuration;
        $this->filesystem    = $filesystem ?? new Filesystem();
    }

    public function write(GeneratedTestClassDto $generatedTestClass) : string
    {
        if(null !== $generatedTestClass->getJsonSchema()) {
            $this->writeSchemaJson($generatedTestClass);
        }

        if(null !== $generatedTestClass->getBody()) {
            $this->writeBodyJson($generatedTestClass);
        }

        $writePath = $this->generatePsr4TestWritePath($generatedTestClass);

        $writeDirectory = dirname($writePath);

        if (! $this->filesystem->exists($writeDirectory)) {
            $this->filesystem->mkdir($writeDirectory, 0777);
        }

        $this->filesystem->dumpFile(
            $writePath,
            $generatedTestClass->getJsonSchema()
        );

        $this->filesystem->dumpFile(
            $writePath,
            $generatedTestClass->getCode()
        );

        return $writePath;
    }


    public function writeBodyJson(GeneratedTestClassDto $generatedTestClass) : string
    {
        $writePath = $this->generatePsr4TestWritePath($generatedTestClass, self::TYPE_WRITE_JSON);

        $writeDirectory = dirname($writePath);

        if (! $this->filesystem->exists($writeDirectory)) {
            $this->filesystem->mkdir($writeDirectory, 0777);
        }

        $this->filesystem->dumpFile(
            $writePath,
            $generatedTestClass->getBody()
        );

        return $writePath;
    }

    public function writeSchemaJson(GeneratedTestClassDto $generatedTestClass) : string
    {
        $writePath = $this->generatePsr4TestWritePath($generatedTestClass, self::TYPE_WRITE_JSON);

        $writeDirectory = dirname($writePath);

        if (! $this->filesystem->exists($writeDirectory)) {
            $this->filesystem->mkdir($writeDirectory, 0777);
        }

        $this->filesystem->dumpFile(
            $writePath,
            $generatedTestClass->getJsonSchema()
        );

        return $writePath;
    }

    private function generatePsr4TestWritePath(GeneratedTestClassDto $generatedTestClass, $type = self::TYPE_WRITE_PHP) : string
    {

        $writePath = $this->configuration->getTestsDir();

        $testNamespace = explode('\\', $generatedTestClass->getTestClassName());
        array_shift($testNamespace);
        array_shift($testNamespace);

        if($type === self::TYPE_WRITE_JSON) {
            $writePath .= '/' . str_replace(
                    $this->configuration->getTestsNamespace() . '\\',
                    'Schema/',
                    implode('/',$testNamespace)
                ) . '.json';

            $writePath = str_replace('\\', DIRECTORY_SEPARATOR, $writePath);

            return $writePath;
        }


        $writePath .= '/' . str_replace(
            $this->configuration->getTestsNamespace() . '\\',
            '',
            implode('/',$testNamespace)
        ) . '.php';
        $writePath = str_replace('\\', DIRECTORY_SEPARATOR, $writePath);

        return $writePath;
    }
}
