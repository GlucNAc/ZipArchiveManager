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

        $openResult = $zip->open($archiveAbsolutePath, $mode);

        if ($openResult !== true) {
            $errorMessage = match ($openResult) {
                ZipArchive::ER_NOZIP => 'Not a zip archive',
                ZipArchive::ER_INCONS => 'Zip archive inconsistent',
                ZipArchive::ER_CRC => 'CRC error',
                ZipArchive::ER_MEMORY => 'Memory allocation failure',
                ZipArchive::ER_NOENT => 'No such file',
                ZipArchive::ER_OPEN => 'Can\'t open file',
                ZipArchive::ER_READ => 'Read error',
                ZipArchive::ER_SEEK => 'Seek error',
                default => 'Unknown error',
            };

            throw new ZipArchiveException(
                \sprintf(
                    'Failed to open the ZIP file "%s" (ZipArchive error code: %d, message: %s)',
                    $archiveAbsolutePath,
                    $openResult,
                    $errorMessage
                )
            );
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
