<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\File;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use GlucNAc\ZipArchiveManager\AssertCollectionTrait;
use GlucNAc\ZipArchiveManager\Transformer\AbstractToArchivableFileTransformer;
use GlucNAc\ZipArchiveManager\ZipArchive\ZipArchiveException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

final class ArchivableFileManager
{
    use AssertCollectionTrait;

    public function __construct(private readonly AbstractToArchivableFileTransformer $toArchivableFileTransformer)
    {
    }

    /**
     * @return Collection<int, ArchivableFile>
     * @throws ZipArchiveException
     */
    public function getArchivableFilesFromPath(string $filesDirectoryAbsolutePath): Collection
    {
        if (!is_dir($filesDirectoryAbsolutePath) || !\is_readable($filesDirectoryAbsolutePath)) {
            throw new ZipArchiveException(
                sprintf('Directory "%s" does not exist or is not readable', $filesDirectoryAbsolutePath)
            );
        }

        $finder = new Finder();
        $storedFiles = $finder->in($filesDirectoryAbsolutePath)->files();

        $files = new ArrayCollection();
        foreach ($storedFiles as $storedFile) {
            $files->add($this->toArchivableFileTransformer::getArchivableFile($storedFile, [
                'root_directory' => $filesDirectoryAbsolutePath,
            ]));
        }

        return $files;
    }

    /**
     * @throws ZipArchiveException
     */
    public function getArchivableFileFromPath(string $fileAbsolutePath): ArchivableFile
    {
        if (!is_file($fileAbsolutePath) || !\is_readable($fileAbsolutePath)) {
            throw new ZipArchiveException(
                sprintf('File "%s" does not exist or is not readable', $fileAbsolutePath)
            );
        }

        return $this->toArchivableFileTransformer::getArchivableFile(
            new SplFileInfo($fileAbsolutePath),
            ['root_directory' => '/']
        );
    }
}
