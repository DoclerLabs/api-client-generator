<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output;

use DoclerLabs\ApiClientGenerator\Output\Php\PhpFile;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\Parser\Php7;
use Symfony\Component\Finder\SplFileInfo;

class StaticPhpFileCopier
{
    public function __construct(
        private Php7 $parser,
        private PhpFilePrinter $printer,
        private NodeTraverser $traverser
    ) {
    }

    public function copy(string $destinationPath, SplFileInfo $originalFile): void
    {
        if ($originalFile->getExtension() !== 'php') {
            return;
        }

        /** @var Node[] $originalNodes */
        $originalNodes = $this->parser->parse($originalFile->getContents());
        $copiedFile    = new PhpFile(
            $originalFile->getFilename(),
            $originalFile->getFilenameWithoutExtension(),
            $this->traverser->traverse($originalNodes),
        );
        $this->printer->print($destinationPath, $copiedFile);
    }
}
