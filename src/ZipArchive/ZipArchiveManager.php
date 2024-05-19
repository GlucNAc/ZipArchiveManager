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
    public function addFilesToArchive(ZipArchive $zip, iterable $files, bool $closeWhenFinished = false): void
    {
        self::assertIsIterableOf($files, ArchivableFileInterface::class);

        foreach ($files as $file) {
            $this->addFileToArchive($zip, $file);
        }

        if ($closeWhenFinished) {
            $this->close($zip);
        }
    }

    /**
     * @throws ZipArchiveException
     */
    public function addFileToArchive(ZipArchive $zip, ArchivableFileInterface $file, bool $closeWhenFinished = false): void
    {
        self::assertFileExist($file);

        if (true !== $zip->addFile($file->getFullPath(), trim($file->getEntryName()))) {
            throw new ZipArchiveException(
                sprintf('Unable to add file "%s" to archive, error code "%s"', $file->getFullPath(), $zip->status)
            );
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

        $ok = false;
        $error = null;

        try {
            $ok = $zip->extractTo($destinationPath);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        if (!$ok) {
            $error = $error ?? (string) $zip->status;
        }

        if ($error !== null) {
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
        $ok = false;
        $error = null;

        try {
            $ok = $zip->close();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        if (!$ok) {
            $error = $error ?? (string) $zip->status;
        }

        if ($error !== null) {
            throw new ZipArchiveException(
                sprintf('Unable to close archive "%s", error "%s"', $zip->filename, $error)
            );
        }
    }

    /**
     * @throws ZipArchiveException
     */
    private function createDirectory(string $directory): void
    {
        if (!mkdir($directory) && !is_dir($directory)) {
            // @codeCoverageIgnoreStart
            throw new ZipArchiveException(sprintf('Directory "%s" was not created', $directory));
            // @codeCoverageIgnoreEnd
        }
    }
}
