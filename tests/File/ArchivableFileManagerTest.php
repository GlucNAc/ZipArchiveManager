<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\Test\File;

use Generator;
use GlucNAc\ZipArchiveManager\File\ArchivableFile;
use GlucNAc\ZipArchiveManager\File\ArchivableFileManager;
use GlucNAc\ZipArchiveManager\Transformer\SplFileInfoToArchivableFileTransformer;
use PHPUnit\Framework\TestCase;

final class ArchivableFileManagerTest extends TestCase
{
    /**
     * @dataProvider filesProvider
     */
    public function testGetArchivableFileFromPath(ArchivableFile $expectedFile): void
    {
        $archivableFileManager = new ArchivableFileManager(new SplFileInfoToArchivableFileTransformer());

        $filePath = __DIR__ . '/archivable_file_manager_dir/tmp1/test0.txt';
        $file = $archivableFileManager->getArchivableFileFromPath($filePath);

        $expectedFile->setEntryName($filePath);
        self::assertEquals($expectedFile, $file);
    }

    /**
     * @dataProvider filesProvider
     */
    public function testGetArchivableFilesFromPath(ArchivableFile $expectedFile): void
    {
        $archivableFileManager = new ArchivableFileManager(new SplFileInfoToArchivableFileTransformer());

        $files = $archivableFileManager->getArchivableFilesFromPath(__DIR__ . '/archivable_file_manager_dir');

        self::assertCount(1, $files);
        self::assertInstanceOf(ArchivableFile::class, $files->first());

        $expectedFile->setEntryName('tmp1/test0.txt');
        self::assertEquals($expectedFile, $files->first());
    }

    public static function filesProvider(): Generator
    {
        yield 'text file' => [
            new ArchivableFile(__DIR__ . '/archivable_file_manager_dir/tmp1/test0.txt'),
        ];
    }
}
