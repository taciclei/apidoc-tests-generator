<?php

declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\Writer;

use PhpJit\ApidocTestsGenerator\Configuration\Configuration;
use PhpJit\ApidocTestsGenerator\GeneratedTestClass;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use const DIRECTORY_SEPARATOR;
use function dirname;
use function sprintf;
use function str_replace;

class Psr4TestClassWriterInterface implements TestClassWriterInterface
{
    /** @var Configuration */
    private $configuration;

    /** @var Filesystem */
    private $filesystem;

    public function __construct(Configuration $configuration, ?Filesystem $filesystem = null)
    {
        $this->configuration = $configuration;
        $this->filesystem    = $filesystem ?? new Filesystem();
    }

    public function write(GeneratedTestClass $generatedTestClass) : string
    {

        $writePath = $this->generatePsr4TestWritePath($generatedTestClass);

        $writeDirectory = dirname($writePath);

        if (! $this->filesystem->exists($writeDirectory)) {
            $this->filesystem->mkdir($writeDirectory, 0777);
        }

        if ($this->filesystem->exists($writePath)) {
            throw new RuntimeException(sprintf('Test class already exists at %s', $writePath));
        }

        $this->filesystem->dumpFile(
            $writePath,
            $generatedTestClass->getCode()
        );

        return $writePath;
    }

    private function generatePsr4TestWritePath(GeneratedTestClass $generatedTestClass) : string
    {

        $writePath = $this->configuration->getTestsDir();

        $testNamespace = explode('\\', $generatedTestClass->getTestClassName());
        array_shift($testNamespace);

        $writePath .= '/' . str_replace(
            $this->configuration->getTestsNamespace() . '\\',
            '',
            implode('/',$testNamespace)
        ) . '.php';
        $writePath = str_replace('\\', DIRECTORY_SEPARATOR, $writePath);

        return $writePath;
    }
}
