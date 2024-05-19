<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\ZipArchive;

use GlucNAc\ZipArchiveManager\AssertCollectionTrait;
use GlucNAc\ZipArchiveManager\File\ArchivableFileInterface;
use GlucNAc\ZipArchiveManager\File\ArchivableFileManager;
use ZipArchive;

class ZipArchiveBuilder
{
    use AssertCollectionTrait;

    private ZipArchive|null $zipArchive = null;

    public function __construct(
        private readonly ZipArchiveManager $zipArchiveManager,
        private readonly ArchivableFileManager $archivableFileManager,
    ) {
    }

    /**
     * @throws ZipArchiveException
     */
    public function new(string $relativeArchivePath): static
    {
        $this->zipArchive = $this->zipArchiveManager->new($relativeArchivePath);

        return $this;
    }

    /**
     * @throws ZipArchiveException
     */
    public function addFile(ArchivableFileInterface|string $file, string|null $entryName = null): static
    {
        if ($this->zipArchive === null) {
            throw new ZipArchiveException('No archive to add file to');
        }

        if (\is_string($file)) {
            $file = $this->archivableFileManager->getArchivableFileFromPath($file)->setEntryName($entryName);
        }

        $this->zipArchiveManager->addFileToArchive($this->zipArchive, $file);

        return $this;
    }

    /**
     * @param iterable<ArchivableFileInterface>|array<int|string, string> $files
     *
     * @throws ZipArchiveException
     */
    public function addFiles(iterable $files): static
    {
        foreach ($files as $file => $entryName) {
            if (\is_int($file) || $entryName instanceof ArchivableFileInterface) {
                $file = $entryName;
                $entryName = null;
            }

            if (!$file instanceof ArchivableFileInterface && !\is_string($file)) {
                throw new ZipArchiveException('Invalid file');
            }

            $this->addFile($file, $entryName);
        }

        return $this;
    }

    /**
     * @throws ZipArchiveException
     */
    public function build(bool $close = true): ZipArchive
    {
        if ($this->zipArchive === null) {
            throw new ZipArchiveException('No archive to build');
        }

        $zipArchive = $this->zipArchive;

        if ($close) {
            $this->zipArchiveManager->close($zipArchive);
        }

        $this->zipArchive = null;

        return $zipArchive;
    }

    /**
     * @param iterable<ArchivableFileInterface>|array<int|string, string> $files
     *
     * @throws ZipArchiveException
     */
    public function buildWithFiles(string $relativeArchivePath, iterable $files, bool $close = true): ZipArchive
    {
        $this->new($relativeArchivePath);

        $this->addFiles($files);

        return $this->build($close);
    }
}
