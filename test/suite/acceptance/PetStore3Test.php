<?php

namespace DoclerLabs\ApiClientGenerator\Test\Acceptance;

use DoclerLabs\ApiClientGenerator\Application;
use DoclerLabs\ApiClientGenerator\Input\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Filesystem\Filesystem;

class PetStore3Test extends TestCase
{
    private const SPECIFICATION_URL = 'https://petstore3.swagger.io/api/v3/openapi.json';
    private const BASE_NAMESPACE = 'OpenApi\\PetStoreClient';
    private const PACKAGE = 'openapi/pet-store-client';
    private const OUTPUT_DIR = __DIR__ . '/../../../example/gen';

    /** @var Filesystem */
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->filesystem->remove(self::OUTPUT_DIR);

        parent::setUp();
    }

    public function testPetStore3Example(): void
    {
        $this->filesystem->mkdir(self::OUTPUT_DIR, 0755);

        $filePath = sprintf('%s/../petstore3.json', self::OUTPUT_DIR);

        putenv(sprintf('OPENAPI=%s', $filePath));
        putenv(sprintf('NAMESPACE=%s', self::BASE_NAMESPACE));
        putenv(sprintf('PACKAGE=%s', self::PACKAGE));
        putenv(sprintf('OUTPUT_DIR=%s', self::OUTPUT_DIR));

        $input = new ArrayInput(['command' => 'generate', '--quiet' => true]);
        $sut   = new Application();
        $sut->setAutoExit(false);
        $sut->run($input);

        self::assertDirectoryExists(sprintf('%s/%s', self::OUTPUT_DIR, Configuration::DEFAULT_SOURCE_DIRECTORY));

        $output   = '';
        $exitCode = 0;
        exec('cd example && php test-example.php', $output, $exitCode);

        self::assertEmpty($output);
        self::assertEquals(0, $exitCode);
    }
}
