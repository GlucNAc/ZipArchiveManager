<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\Test\ZipArchive;

use Generator;
use GlucNAc\ZipArchiveManager\File\ArchivableFile;
use GlucNAc\ZipArchiveManager\File\ArchivableFileInterface;
use GlucNAc\ZipArchiveManager\File\ArchivableFileManager;
use GlucNAc\ZipArchiveManager\Transformer\SplFileInfoToArchivableFileTransformer;
use GlucNAc\ZipArchiveManager\ZipArchive\ZipArchiveBuilder;
use GlucNAc\ZipArchiveManager\ZipArchive\ZipArchiveManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class ZipArchiveBuilderTest extends TestCase
{
    private const ARCHIVE_STORAGE_PATH = __DIR__ . '/../../var/test/archives';

    private ZipArchiveBuilder $zipArchiveBuilder;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->zipArchiveBuilder = new ZipArchiveBuilder(
            new ZipArchiveManager(self::ARCHIVE_STORAGE_PATH),
            new ArchivableFileManager(new SplFileInfoToArchivableFileTransformer()),
        );

        $this->filesystem = new Filesystem();
        $this->filesystem->remove(self::ARCHIVE_STORAGE_PATH);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove(self::ARCHIVE_STORAGE_PATH);

        parent::tearDown();
    }

    public function testAddFile(): void
    {
        $relativeArchivePath = 'test.zip';
        $fileToAdd = __DIR__ . '/zip_archive_manager_dir/test0.txt';

        $this->zipArchiveBuilder
            ->new($relativeArchivePath)
            ->addFile($fileToAdd)
            ->build();

        // Assert that the archive file is created at the expected location
        self::assertFileExists(self::ARCHIVE_STORAGE_PATH . '/' . $relativeArchivePath);

        /**
         * As the ZipArchiveBuilder is a thin wrapper around the ZipArchiveManager,
         * we will not go further with the assertions. @see ZipArchiveManagerTest for more detailed tests.
         */
    }

    /**
     * @dataProvider filesProvider
     *
     * @param iterable<ArchivableFileInterface>|array<int|string, string> $files
     */
    public function testAddFiles(iterable $files): void
    {
        $relativeArchivePath = 'test.zip';

        $this->zipArchiveBuilder
            ->new($relativeArchivePath)
            ->addFiles($files)
            ->build();

        // Assert that the archive file is created at the expected location
        self::assertFileExists(self::ARCHIVE_STORAGE_PATH . '/' . $relativeArchivePath);

        /**
         * As the ZipArchiveBuilder is a thin wrapper around the ZipArchiveManager,
         * we will not go further with the assertions. @see ZipArchiveManagerTest for more detailed tests.
         */
    }

    /**
     * @dataProvider filesProvider
     *
     * @param iterable<ArchivableFileInterface>|array<int|string, string> $files
     */
    public function testBuildWithFiles(iterable $files): void
    {
        $relativeArchivePath = 'test.zip';

        $this->zipArchiveBuilder->buildWithFiles($relativeArchivePath, $files);

        // Assert that the archive file is created at the expected location
        self::assertFileExists(self::ARCHIVE_STORAGE_PATH . '/' . $relativeArchivePath);

        /**
         * As the ZipArchiveBuilder is a thin wrapper around the ZipArchiveManager,
         * we will not go further with the assertions. @see ZipArchiveManagerTest for more detailed tests.
         */
    }

    public static function filesProvider(): Generator
    {
        yield 'list of paths' => [
            [
                __DIR__ . '/zip_archive_manager_dir/test0.txt',
                __DIR__ . '/zip_archive_manager_dir/tmp1/test1.txt',
            ],
        ];

        yield 'list of paths with overridden entry name' => [
            [
                __DIR__ . '/zip_archive_manager_dir/test0.txt' => 'test0.txt',
                __DIR__ . '/zip_archive_manager_dir/tmp1/test1.txt' => 'tmp1/test1.txt',
            ],
        ];

        yield 'list of ArchivableFileInterface' => [
            [
                new ArchivableFile(__DIR__ . '/zip_archive_manager_dir/test0.txt'),
                new ArchivableFile(__DIR__ . '/zip_archive_manager_dir/tmp1/test1.txt'),
            ],
        ];
    }
}
