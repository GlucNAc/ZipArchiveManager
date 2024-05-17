<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\Test\File;

use Generator;
use GlucNAc\ZipArchiveManager\File\ArchivableFile;
use GlucNAc\ZipArchiveManager\File\ArchivableFileManager;
use PHPUnit\Framework\TestCase;

final class ArchivableFileManagerTest extends TestCase
{
    public function testBuildFromArray(): void
    {
        $archivableFile = ArchivableFileManager::buildFromArray([
            'full_path' => '/test_dir/test_path/123456.png',
            'entry_name' => 'toto',
        ]);

        self::assertSame('/test_dir/test_path/123456.png', $archivableFile->getFullPath());
        self::assertSame('toto', $archivableFile->getEntryName());
        self::assertSame('png', $archivableFile->getExtension());
        self::assertSame('123456', $archivableFile->getFileName());
    }

    /**
     * @dataProvider filesProvider
     */
    public function testGetArchivableFilesFromPath(ArchivableFile $file): void
    {
        $archivableFileManager = new ArchivableFileManager();

        $files = $archivableFileManager->getArchivableFilesFromPath(__DIR__ . '/archivable_file_manager_dir');

        self::assertCount(1, $files);
        self::assertInstanceOf(ArchivableFile::class, $files->first());

        self::assertEquals($file, $files->first());
    }

    public function filesProvider(): Generator
    {
        yield 'text file' => [
            (new ArchivableFile())
                ->setFullPath(__DIR__ . '/archivable_file_manager_dir/tmp1/test0.txt')
                ->setEntryName('tmp1/test0.txt'),
        ];
    }
}
