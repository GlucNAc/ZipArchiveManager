<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\ZipArchive;

use ZipArchive;

class ZipArchiveFactory
{
    /**
     * @throws ZipArchiveException
     */
    public static function new(string $archiveAbsolutePath): ZipArchive
    {
        return self::open($archiveAbsolutePath, ZipArchive::CREATE);
    }

    /**
     * @throws ZipArchiveException
     */
    public static function open(string $archiveAbsolutePath, int $mode = ZipArchive::RDONLY): ZipArchive
    {
        self::assertPathIsExploitable($archiveAbsolutePath, $mode);

        $zip = new ZipArchive();

        if (true !== $zip->open($archiveAbsolutePath, $mode)) {
            // @codeCoverageIgnoreStart
            throw new ZipArchiveException(
                sprintf('Unable to open archive "%s" returned error code "%s"', $archiveAbsolutePath, $zip->status)
            );
            // @codeCoverageIgnoreEnd
        }

        return $zip;
    }

    /**
     * @throws ZipArchiveException
     */
    private static function assertPathIsExploitable(string $archiveAbsolutePath, int $mode): void
    {
        switch ($mode) {
            case ZipArchive::CREATE:
                $directory = \dirname($archiveAbsolutePath);
                if (!\is_writable($directory)) {
                    throw new ZipArchiveException(sprintf('Directory "%s" is not writable', $directory));
                }
                break;
            case ZipArchive::RDONLY:
                if (!\is_readable($archiveAbsolutePath)) {
                    throw new ZipArchiveException(sprintf('Archive "%s" is not readable', $archiveAbsolutePath));
                }
                break;
            default:
                throw new ZipArchiveException(sprintf('Invalid mode "%s"', $mode));
        }
    }
}
