<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\Test\ZipArchive;

use GlucNAc\ZipArchiveManager\File\ArchivableFile;
use GlucNAc\ZipArchiveManager\Transformer\SplFileInfoToArchivableFileTransformer;
use GlucNAc\ZipArchiveManager\ZipArchive\ZipArchiveException;
use GlucNAc\ZipArchiveManager\ZipArchive\ZipArchiveManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class ZipArchiveManagerTest extends TestCase
{
    private const ARCHIVE_STORAGE_PATH = __DIR__ . '/../../var/test/archives';

    private ZipArchiveManager $zipArchiveManager;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->zipArchiveManager = new ZipArchiveManager(self::ARCHIVE_STORAGE_PATH);
        $this->filesystem = new Filesystem();
        $this->filesystem->remove(self::ARCHIVE_STORAGE_PATH);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove(self::ARCHIVE_STORAGE_PATH);

        parent::tearDown();
    }

    public function testNewArchiveWithNonExistentDirectory(): void
    {
        $this->expectException(ZipArchiveException::class);
        $this->expectExceptionMessageMatches('/Directory ".+" was not created/');

        $relativeArchivePath = 'non_existent_directory/archive.zip';

        $this->zipArchiveManager->new($relativeArchivePath);
    }

    /**
     * @dataProvider filesProvider
     *
     * @param array<ArchivableFile> $files
     */
    public function testArchiveFiles(array $files): void
    {
        $this->filesystem->mkdir(self::ARCHIVE_STORAGE_PATH . '/archive_manager_test');
        $relativeArchivePath = 'archive_manager_test/zip_archive_manager_test.zip';

        $zipArchive = $this->zipArchiveManager->new($relativeArchivePath);
        $this->zipArchiveManager->addFilesToArchive($zipArchive, $files);

        self::assertSame(\count($files), $zipArchive->numFiles);

        $archiveFullPath = self::ARCHIVE_STORAGE_PATH . DIRECTORY_SEPARATOR . $relativeArchivePath;

        $currentDirectory = \getcwd();
        self::assertNotFalse($currentDirectory);
        $archiveRelativePath = $this->filesystem->makePathRelative($archiveFullPath, $currentDirectory);
        $zipArchiveFilename = $this->filesystem->makePathRelative($zipArchive->filename, $currentDirectory);
        self::assertSame($archiveRelativePath, $zipArchiveFilename);

        $this->zipArchiveManager->close($zipArchive);

        // File exist only after closing the archive
        self::assertFileExists($archiveFullPath);
        self::assertFileIsReadable($archiveFullPath);
        self::assertFileIsWritable($archiveFullPath);
    }

    /**
     * @dataProvider filesProvider
     *
     * @param array<ArchivableFile> $expectedFiles
     */
    public function testExtractFiles(array $expectedFiles): void
    {
        // Create the directory where the archive will be stored
        $rootArchiveDirectory = 'archive_manager_test';
        $this->filesystem->mkdir(self::ARCHIVE_STORAGE_PATH . DIRECTORY_SEPARATOR . $rootArchiveDirectory);
        $archiveRelativePath = "$rootArchiveDirectory/test_archive.zip";

        // Create the archive
        $zipArchive = $this->zipArchiveManager->new($archiveRelativePath);
        $this->zipArchiveManager->addFilesToArchive($zipArchive, $expectedFiles, true);

        // Create the directory where the files will be extracted
        $extractedDirectory = "$rootArchiveDirectory/extracted";
        $extractedDirectoryPath = self::ARCHIVE_STORAGE_PATH . DIRECTORY_SEPARATOR . $extractedDirectory;
        $this->filesystem->mkdir($extractedDirectoryPath);

        // Extract the files
        $zipArchive = $this->zipArchiveManager->open($archiveRelativePath);
        $this->zipArchiveManager->extractFiles($zipArchive, $extractedDirectoryPath);

        // Transform the expected files into a collection of ArchivableFile to facilitate the comparison
        /** @var array<int, ArchivableFile> $extractedFiles */
        $extractedFiles = [];

        $finder = new Finder();
        $storedFiles = $finder
            ->in($extractedDirectoryPath)
            ->files();

        foreach ($storedFiles as $storedFile) {
            $extractedFiles[] = SplFileInfoToArchivableFileTransformer::getArchivableFile($storedFile, [
                'root_directory' => $extractedDirectoryPath,
            ]);
        }
        \usort(
            $extractedFiles,
            static fn(ArchivableFile $a, ArchivableFile $b): int => $a->getFileName() <=> $b->getFileName()
        );

        // Ensure extraction is successful
        self::assertCount($zipArchive->count(), $extractedFiles);
        $this->zipArchiveManager->close($zipArchive);

        // Compare extracted files with the original ones
        self::assertCount(\count($expectedFiles), $extractedFiles);
        self::assertContainsOnlyInstancesOf(ArchivableFile::class, $extractedFiles);
        foreach ($expectedFiles as $key => $expectedFile) {
            self::assertSame($expectedFile->getFileName(), $extractedFiles[$key]->getFileName());
            self::assertSame($expectedFile->getExtension(), $extractedFiles[$key]->getExtension());
            self::assertSame($expectedFile->getEntryName(), $extractedFiles[$key]->getEntryName());
        }
    }

    public static function filesProvider(): \Generator
    {
        $filesDirectory = __DIR__ . '/zip_archive_manager_dir';

        yield 'files stored in structure tree with relative path' => [
            [
                new ArchivableFile($filesDirectory . '/test0.txt'),
                new ArchivableFile($filesDirectory . '/tmp1/test1.txt'),
                new ArchivableFile($filesDirectory . '/tmp1/tmp2/test2.txt'),
            ],
        ];
    }

    public function testNewInNonWritableDirectory(): void
    {
        $this->expectException(ZipArchiveException::class);
        $this->expectExceptionMessageMatches('/Directory ".+" was not created/');

        $relativeArchivePath = '/non_writable_directory/archive.zip';

        $this->zipArchiveManager->new($relativeArchivePath);
    }

    public function testOpenNonReadableArchive(): void
    {
        $this->expectException(ZipArchiveException::class);
        $this->expectExceptionMessageMatches('/Archive ".+" does not exist or is not readable/');

        $relativeArchivePath = '/non_readable_archive.zip';

        $this->zipArchiveManager->open($relativeArchivePath);
    }

    public function testAddNonExistentFileToArchive(): void
    {
        $this->expectException(ZipArchiveException::class);
        $this->expectExceptionMessageMatches('/File ".+" does not exist/');
        $relativeArchivePath = '/valid_archive.zip';
        $zipArchive = $this->zipArchiveManager->new($relativeArchivePath);

        $nonExistentFile = new ArchivableFile('/non_existent_file.txt');

        $this->zipArchiveManager->addFileToArchive($zipArchive, $nonExistentFile);
    }

    public function testExtractToNonWritableDirectory(): void
    {
        $this->expectException(ZipArchiveException::class);
        $this->expectExceptionMessageMatches('/Directory ".+" was not created/');

        $relativeArchivePath = '/valid_archive.zip';
        $zipArchive = $this->zipArchiveManager->new($relativeArchivePath);

        $nonWritableDirectory = '/non_writable_directory';

        $this->zipArchiveManager->extractFiles($zipArchive, $nonWritableDirectory);
    }

    public function testCloseNotOpenArchive(): void
    {
        $this->expectException(ZipArchiveException::class);
        $this->expectExceptionMessageMatches('/Unable to close archive ".*", error ".+"/');

        $relativeArchivePath = '/valid_archive.zip';
        $zipArchive = $this->zipArchiveManager->new($relativeArchivePath);

        $this->zipArchiveManager->close($zipArchive);
        $this->zipArchiveManager->close($zipArchive); // Attempt to close the same archive again
    }

    public function testAddFileInInvalidZipArchive(): void
    {
        $this->expectException(ZipArchiveException::class);
        $this->expectExceptionMessageMatches('/Unable to add file ".+" to archive, error code ".+"/');

        $zipArchive = new \ZipArchive();
        $archivableFile = new ArchivableFile('tests/ZipArchive/zip_archive_manager_dir/test0.txt');

        $this->zipArchiveManager->addFileToArchive($zipArchive, $archivableFile);
    }

    public function testExtractFilesFromInvalideZipArchive(): void
    {
        $this->expectException(ZipArchiveException::class);
        $this->expectExceptionMessageMatches('/Unable to extract archive to ".+", error code ".+"/');

        $zipArchive = new \ZipArchive();
        $destinationPath = 'tests/ZipArchive/zip_archive_manager_dir/extracted';

        $this->zipArchiveManager->extractFiles($zipArchive, $destinationPath);
    }

    public function testExtractFilesWithCloseWhenFinished(): void
    {
        $this->filesystem->mkdir(self::ARCHIVE_STORAGE_PATH);
        $archiveRelativePath = 'zip_archive_manager_test.zip';
        $this->filesystem->copy(
            __DIR__ . '/' . $archiveRelativePath,
            self::ARCHIVE_STORAGE_PATH . '/' . $archiveRelativePath,
        );

        $zipArchive = $this->zipArchiveManager->open($archiveRelativePath);

        $extractedDirectoryPath = self::ARCHIVE_STORAGE_PATH . '/archive_manager_test/extracted';
        $this->filesystem->mkdir($extractedDirectoryPath);

        $this->zipArchiveManager->extractFiles($zipArchive, $extractedDirectoryPath, true);

        // Assert that the archive is closed
        $this->expectException(ZipArchiveException::class);
        $this->expectExceptionMessageMatches('/Unable to close archive ".*", error ".+"/');
        $this->zipArchiveManager->close($zipArchive); // Attempt to close the same archive again
    }

    public function testAddFileToArchiveWithCloseWhenFinished(): void
    {
        $zipArchive = $this->zipArchiveManager->new('/zip_archive_manager_test.zip');

        $archivableFile = new ArchivableFile('tests/ZipArchive/zip_archive_manager_dir/test0.txt');

        $this->zipArchiveManager->addFileToArchive($zipArchive, $archivableFile, true);

        // Assert that the archive is closed
        $this->expectException(ZipArchiveException::class);
        $this->expectExceptionMessageMatches('/Unable to close archive ".*", error ".+"/');
        $this->zipArchiveManager->close($zipArchive); // Attempt to close the same archive again
    }
}
