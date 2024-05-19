<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\Test\File;

use GlucNAc\ZipArchiveManager\File\ArchivableFileFactory;
use PHPUnit\Framework\TestCase;

class ArchivableFileFactoryTest extends TestCase
{
    public function testNew(): void
    {
        $archivableFile = ArchivableFileFactory::new([
            'full_path' => '/test_dir/test_path/123456.png',
            'entry_name' => 'toto',
        ]);

        self::assertSame('/test_dir/test_path/123456.png', $archivableFile->getFullPath());
        self::assertSame('toto', $archivableFile->getEntryName());
        self::assertSame('png', $archivableFile->getExtension());
        self::assertSame('123456', $archivableFile->getFileName());
    }
}
