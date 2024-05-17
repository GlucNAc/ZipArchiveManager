<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\Test;

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
}
