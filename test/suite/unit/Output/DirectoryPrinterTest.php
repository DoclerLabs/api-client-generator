<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output;

use DoclerLabs\ApiClientGenerator\Output\DirectoryPrinter;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Output\DirectoryPrinter
 */
class DirectoryPrinterTest extends TestCase
{
    private DirectoryPrinter   $sut;
    private vfsStreamDirectory $directory;

    protected function setUp(): void
    {
        $this->sut       = new DirectoryPrinter();
        $this->directory = vfsStream::setup();
    }

    public function testCreationDuringEnsureDirectoryExists(): void
    {
        $directory = 'my-dir';

        $this->sut->ensureDirectoryExists(
            $this->directory->url() . DIRECTORY_SEPARATOR . $directory
        );

        self::assertTrue($this->directory->hasChild($directory));
    }

    public function testExistingDuringEnsureDirectoryExists(): void
    {
        $directory = 'existing';

        vfsStream::newDirectory($directory)->at($this->directory);

        $this->sut->ensureDirectoryExists(
            $this->directory->url() . DIRECTORY_SEPARATOR . $directory
        );

        self::assertTrue($this->directory->hasChild($directory));
    }

    public function testMove(): void
    {
        $source      = 'old';
        $destination = 'new';

        vfsStream::newDirectory($source)->at($this->directory);

        $this->sut->move(
            $this->directory->url() . DIRECTORY_SEPARATOR . $destination,
            $this->directory->url() . DIRECTORY_SEPARATOR . $source
        );

        self::assertTrue($this->directory->hasChild($destination));
        self::assertFalse($this->directory->hasChild($source));
    }

    public function testDeleteFolder(): void
    {
        $pathToKeep = 'to-keep';

        $pathToRemove      = 'to-remove';
        $fileToRemove      = 'file-to-remove';
        $subFolderToRemove = 'sub-folder';
        $subFileToRemove   = 'subfile-to-remove';

        vfsStream::newDirectory($pathToKeep)->at($this->directory);

        $toRemove = vfsStream::newDirectory($pathToRemove)->at($this->directory);
        vfsStream::newFile($fileToRemove)->at($toRemove);
        $toRemoveSubFolder = vfsStream::newDirectory($subFolderToRemove)->at($toRemove);
        vfsStream::newFile($subFileToRemove)->at($toRemoveSubFolder);

        self::assertTrue(
            $this->sut->delete(
                $this->directory->url() . DIRECTORY_SEPARATOR . $pathToRemove,
            )
        );

        self::assertTrue($this->directory->hasChild($pathToKeep));
        self::assertFalse($this->directory->hasChild($pathToRemove));
    }
}
