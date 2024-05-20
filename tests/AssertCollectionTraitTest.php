<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\Test;

use Doctrine\Common\Collections\ArrayCollection;
use GlucNAc\ZipArchiveManager\AssertCollectionTrait;
use GlucNAc\ZipArchiveManager\File\ArchivableFile;
use GlucNAc\ZipArchiveManager\ZipArchive\ZipArchiveException;
use PHPUnit\Framework\TestCase;
use stdClass;

class AssertCollectionTraitTest extends TestCase
{
    use AssertCollectionTrait;

    public function testAssertFilesExistWithNonExistingFiles(): void
    {
        $this->expectException(ZipArchiveException::class);
        $this->expectExceptionMessageMatches('/File ".+" does not exist/');

        $files = [
            new ArchivableFile(__DIR__ . '/test0.txt'),
            new ArchivableFile(__DIR__ . '/non_existent_file.txt'),
        ];

        self::assertFilesExist($files);
    }

    public function testAssertFileExistWithNonExistingFile(): void
    {
        $this->expectException(ZipArchiveException::class);
        $this->expectExceptionMessageMatches('/File ".+" does not exist/');

        $file = new ArchivableFile(__DIR__ . '/non_existent_file.txt');

        self::assertFileExist($file);
    }

    public function testAssertIsIterableOfWithInvalidIterable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Argument 1 passed to ".+" must be an iterable of ".+", ".+" given./');

        $files = [
            new ArchivableFile(__DIR__ . '/test0.txt'),
            new StdClass(),
        ];

        self::assertIsIterableOf($files, ArchivableFile::class);
    }

    public function testAssertClassExistsWithNonExistingClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Argument 2 passed to ".+" must be a valid FQCN. ".+" given./');

        // @phpstan-ignore-next-line
        self::assertClassExists('NonExistentClass');
    }

    public function testAssertIsCollectionOfWithInvalidCollection(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Argument 1 passed to ".+" must be collection of ".+", but element ".+" is of ".+"./');

        $collection = new ArrayCollection([
            new ArchivableFile(__DIR__ . '/test0.txt'),
            new StdClass(),
        ]);

        self::assertIsCollectionOf($collection, ArchivableFile::class);
    }
}
