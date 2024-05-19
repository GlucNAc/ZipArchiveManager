<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager;

use Doctrine\Common\Collections\Collection;
use GlucNAc\ZipArchiveManager\File\ArchivableFileInterface;
use GlucNAc\ZipArchiveManager\ZipArchive\ZipArchiveException;

trait AssertCollectionTrait
{
    /**
     * @param iterable<ArchivableFileInterface> $files
     *
     * @throws ZipArchiveException
     */
    protected static function assertFilesExist(iterable $files): void
    {
        self::assertIsIterableOf($files, ArchivableFileInterface::class);

        foreach ($files as $file) {
            self::assertFileExist($file);
        }
    }

    /**
     * @throws ZipArchiveException
     */
    protected static function assertFileExist(ArchivableFileInterface $file): void
    {
        if (false === file_exists($file->getFullPath())) {
            throw new ZipArchiveException(
                sprintf('File "%s" does not exist', $file->getFullPath())
            );
        }
    }

    /**
     * @template T of object
     *
     * @param Collection<int, T> $collection
     * @param class-string<T> $expectedClass
     */
    protected static function assertIsCollectionOf(Collection $collection, string $expectedClass): void
    {
        self::assertClassExists($expectedClass);

        foreach ($collection as $key => $entity) {
            if (!$entity instanceof $expectedClass) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Argument 1 passed to "%s()" must be collection of "%s", but element "%s" is of "%s".',
                        __METHOD__,
                        $expectedClass,
                        $key,
                        \get_class($entity),
                    )
                );
            }
        }
    }

    /**
     * @template T of object
     *
     * @param iterable<T> $iterable
     * @param class-string<T> $expectedClass
     */
    protected static function assertIsIterableOf(iterable $iterable, string $expectedClass): void
    {
        self::assertClassExists($expectedClass);

        foreach ($iterable as $entity) {
            if (!$entity instanceof $expectedClass) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Argument 1 passed to "%s()" must be an iterable of "%s", "%s" given.',
                        __METHOD__,
                        $expectedClass,
                        \get_class($entity),
                    )
                );
            }
        }
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $expectedClass
     */
    protected static function assertClassExists(string $expectedClass): void
    {
        if (!class_exists($expectedClass) && !interface_exists($expectedClass)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Argument 2 passed to "%s()" must be a valid FQCN. "%s" given.',
                    __METHOD__,
                    $expectedClass,
                )
            );
        }
    }
}
