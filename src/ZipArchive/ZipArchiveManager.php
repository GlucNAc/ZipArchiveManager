<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\ZipArchive;

use GlucNAc\ZipArchiveManager\AssertCollectionTrait;
use GlucNAc\ZipArchiveManager\File\ArchivableFileInterface;
use ZipArchive;

class ZipArchiveManager
{
    use AssertCollectionTrait;

    public function __construct(
        private readonly string $archivesStoragePath,
    ) {
    }

    /**
     * @throws ZipArchiveException
     */
    public function new(string $relativeArchivePath): ZipArchive
    {
        $relativeArchivePath = trim($relativeArchivePath, DIRECTORY_SEPARATOR);

        $archiveFullPath = $this->archivesStoragePath . DIRECTORY_SEPARATOR . $relativeArchivePath;

        $directory = \dirname($archiveFullPath);
        if (!\is_dir($directory)) {
            $this->createDirectory($directory);
        }

        return ZipArchiveFactory::open($archiveFullPath, ZipArchive::CREATE);
    }

    /**
     * @throws ZipArchiveException
     */
    public function open(string $relativeArchivePath): ZipArchive
    {
        $relativeArchivePath = trim($relativeArchivePath, DIRECTORY_SEPARATOR);

        $archiveFullPath = $this->archivesStoragePath . DIRECTORY_SEPARATOR . $relativeArchivePath;

        if (!\is_file($archiveFullPath) || !\is_readable($archiveFullPath)) {
            throw new ZipArchiveException(sprintf('Archive "%s" does not exist or is not readable', $archiveFullPath));
        }

        return ZipArchiveFactory::open($archiveFullPath);
    }

    /**
     * @param iterable<ArchivableFileInterface> $files
     *
     * @throws ZipArchiveException
     */
    public function createArchiveWithFiles(
        string $relativeArchivePath,
        iterable $files,
        bool $closeWhenFinished = false
    ): ZipArchive {
        $zip = $this->new($relativeArchivePath);

        $this->addFilesToArchive($zip, $files, $closeWhenFinished);

        return $zip;
    }

    /**
     * @param iterable<ArchivableFileInterface> $files
     *
     * @throws ZipArchiveException
     */
    public function addFilesToArchive(ZipArchive $zip, iterable $files, bool $closeWhenFinished = false): void
    {
        self::assertIsIterableOf($files, ArchivableFileInterface::class);
        self::assertFilesExist($files);

        foreach ($files as $file) {
            if (true !== $zip->addFile($file->getFullPath(), trim($file->getEntryName()))) {
                throw new ZipArchiveException(
                    sprintf('Unable to add file "%s" to archive, error code "%s"', $file->getFullPath(), $zip->status)
                );
            }
        }

        if ($closeWhenFinished) {
            $this->close($zip);
        }
    }

    /**
     * @throws ZipArchiveException
     */
    public function extractFiles(ZipArchive $zip, string $destinationPath, bool $closeWhenFinished = false): void
    {
        if (!is_dir($destinationPath) || !is_writable($destinationPath)) {
            $this->createDirectory($destinationPath);
        }

        if (true !== $zip->extractTo($destinationPath)) {
            throw new ZipArchiveException(
                sprintf('Unable to extract archive to "%s", error code "%s"', $destinationPath, $zip->status)
            );
        }

        if ($closeWhenFinished) {
            $this->close($zip);
        }
    }

    /**
     * @throws ZipArchiveException
     */
    public function close(ZipArchive $zip): void
    {
        if (true !== $zip->close()) {
            throw new ZipArchiveException(
                sprintf('Unable to close archive "%s", error code "%s"', $zip->filename, $zip->status)
            );
        }
    }

    /**
     * @param iterable<ArchivableFileInterface> $files
     *
     * @throws ZipArchiveException
     */
    private static function assertFilesExist(iterable $files): void
    {
        foreach ($files as $file) {
            if (false === file_exists($file->getFullPath())) {
                throw new ZipArchiveException(
                    sprintf('File "%s" does not exist', $file->getFullPath())
                );
            }
        }
    }

    /**
     * @throws ZipArchiveException
     */
    private function createDirectory(string $directory): void
    {
        if (!mkdir($directory) && !is_dir($directory)) {
            throw new ZipArchiveException(sprintf('Directory "%s" was not created', $directory));
        }
    }
}
