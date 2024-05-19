<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\Test\ZipArchive;

use GlucNAc\ZipArchiveManager\ZipArchive\ZipArchiveException;
use GlucNAc\ZipArchiveManager\ZipArchive\ZipArchiveFactory;
use PHPUnit\Framework\TestCase;

class ZipArchiveFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        try {
            $directory = __DIR__ . '/../../var/test/archives/archive_test';

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            $archivePath = "$directory/archive.zip";

            $zip = ZipArchiveFactory::new($archivePath);
            $zip->addFromString('test.txt', 'test');
            $zip->close();

            self::assertFileExists($archivePath);

            unlink($archivePath);
            rmdir($directory);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCreateInNonWritableDirectory(): void
    {
        $this->expectException(ZipArchiveException::class);
        $this->expectExceptionMessageMatches('/Directory ".+" is not writable/');

        $directory = '/non_writable_directory';
        $archivePath = "$directory/archive.zip";

        ZipArchiveFactory::new($archivePath);
    }

    public function testOpenNonReadableArchive(): void
    {
        $this->expectException(ZipArchiveException::class);
        $this->expectExceptionMessageMatches('/Archive ".+" is not readable/');

        $archivePath = '/non_readable_archive.zip';

        ZipArchiveFactory::open($archivePath);
    }

    public function testOpenWithInvalidMode(): void
    {
        $this->expectException(ZipArchiveException::class);
        $this->expectExceptionMessageMatches('/Invalid mode ".+"/');

        $archivePath = '/valid_archive.zip';

        ZipArchiveFactory::open($archivePath, 9999); // 9999 is an invalid mode
    }
}
