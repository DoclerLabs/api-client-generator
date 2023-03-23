<?php

namespace DoclerLabs\ApiClientGenerator\Test\Acceptance;

use Composer\Console\Application as ComposerApplication;
use DoclerLabs\ApiClientGenerator\Application;
use DoclerLabs\ApiClientGenerator\Input\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;

class PetStore3Test extends TestCase
{
    private const BASE_NAMESPACE = 'OpenApi\\PetStoreClient';
    private const PACKAGE = 'openapi/pet-store-client';
    private const EXAMPLE_DIR = __DIR__ . '/../../../example';
    private const OUTPUT_DIR = self::EXAMPLE_DIR . '/PetStoreClient';

    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->filesystem->remove(self::OUTPUT_DIR);
    }

    public function testPetStore3Example(): void
    {
        $this->filesystem->mkdir(self::OUTPUT_DIR, 0755);

        $filePath = sprintf('%s/petstore3.json', self::EXAMPLE_DIR);

        putenv(sprintf('OPENAPI=%s', realpath($filePath)));
        putenv(sprintf('NAMESPACE=%s', self::BASE_NAMESPACE));
        putenv(sprintf('PACKAGE=%s', self::PACKAGE));
        putenv(sprintf('OUTPUT_DIR=%s', self::OUTPUT_DIR));

        $input = new ArrayInput(['command' => 'generate', '--quiet' => true]);
        $sut   = new Application();
        $sut->setAutoExit(false);
        $sut->run($input);

        self::assertDirectoryExists(sprintf('%s/%s', self::OUTPUT_DIR, Configuration::DEFAULT_SOURCE_DIRECTORY));

        $this->installDependencies();
        $this->verifyClient();
    }

    private function installDependencies(): void
    {
        $composer = new ComposerApplication();
        $composer->setAutoExit(false);
        $exitCode = $composer->run(
            new ArrayInput(
                [
                    'command'       => 'install',
                    '--working-dir' => self::EXAMPLE_DIR,
                    '--no-cache'    => true
                ]
            ),
            new BufferedOutput()
        );
        self::assertEquals(0, $exitCode);
    }

    private function verifyClient(): void
    {
        $output   = '';
        $exitCode = 0;
        exec('cd example && php test-example.php', $output, $exitCode);

        self::assertEmpty($output);
        self::assertEquals(0, $exitCode);
    }
}
