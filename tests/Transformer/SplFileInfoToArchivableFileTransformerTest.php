<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\Test\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Generator;
use GlucNAc\ZipArchiveManager\File\ArchivableFile;
use GlucNAc\ZipArchiveManager\Transformer\SplFileInfoToArchivableFileTransformer;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

final class SplFileInfoToArchivableFileTransformerTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Argument 1 passed to "GlucNAc\ZipArchiveManager\Transformer\SplFileInfoToArchivableFileTransformer::getArchivableFile()" must be an instance of "SplFileInfo", "stdClass" given.'
        );

        SplFileInfoToArchivableFileTransformer::getArchivableFile(new \stdClass());
    }

    /**
     * @param Collection<int, SplFileInfo> $collection
     * @param array<string, mixed> $options
     *
     * @dataProvider getArchivableFilesProvider
     */
    public function testGetArchivableFiles(Collection $collection, array $options, string $expectedEntryName): void
    {
        try {
            $files = SplFileInfoToArchivableFileTransformer::getArchivableFiles($collection, $options);
        } catch (\Throwable $e) {
            $this->fail($e->getMessage());
        }

        self::assertContainsOnlyInstancesOf(ArchivableFile::class, $files);
        self::assertNotFalse($files->first());
        self::assertSame($expectedEntryName, $files->first()->getEntryName());
        self::assertSame('/azerty/tmp1/test1_0123546.txt', $files->first()->getFullPath());

        /**
         * There is no need to test other values here, {@see ArchivableFileFactoryTest::testNew()} for
         * values tests.
         */
    }

    public static function getArchivableFilesProvider(): Generator
    {
        $splFile = new SplFileInfo('/azerty/tmp1/test1_0123546.txt');

        $collection = new ArrayCollection([$splFile]);

        $options = ['root_directory' => '/azerty'];
        yield 'without entry name prefix' => [$collection, $options, 'tmp1/test1_0123546.txt'];

        $options['entry_name_prefix'] = 'prefix';
        yield 'with entry name prefix' => [$collection, $options, 'prefix/tmp1/test1_0123546.txt'];
    }
}
