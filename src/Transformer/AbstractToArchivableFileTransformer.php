<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\Transformer;

use Doctrine\Common\Collections\Collection;
use GlucNAc\ZipArchiveManager\AssertCollectionTrait;
use GlucNAc\ZipArchiveManager\File\ArchivableFile;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractToArchivableFileTransformer
{
    use AssertCollectionTrait;

    /**
     * @return class-string
     */
    abstract public static function getTransformedObjectClass(): string;

    /**
     * @param array<string, mixed> $options
     */
    abstract public static function getArchivableFile(object $object, array $options = []): ArchivableFile;

    /**
     * Converts a collection of {@see object} into an {@see ArchivableFile} model collection.
     *
     * @param Collection<int, object> $collection
     * @param array<string, mixed> $options
     *
     * @return Collection<int, ArchivableFile>
     */
    public static function getArchivableFiles(Collection $collection, array $options = []): Collection
    {
        static::assertIsCollectionOf($collection, static::getTransformedObjectClass());

        /** @var Collection<int, ArchivableFile> $archivableFiles */ // For PHPStan
        $archivableFiles = $collection->map(static fn($element): ArchivableFile => static::getArchivableFile(
            $element,
            $options,
        ));

        static::assertIsCollectionOf($archivableFiles, ArchivableFile::class);

        return $archivableFiles;
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected static function resolveOptions(array $options): array
    {
        $optionResolver = new OptionsResolver();

        // Allow others entries in $context
        $optionResolver->setDefined(array_keys($options));

        $optionResolver->setRequired('entry_name_prefix');
        $optionResolver->setAllowedTypes('entry_name_prefix', 'string');
        $optionResolver->setDefaults([
            'entry_name_prefix' => '',
        ]);
        $optionResolver->setNormalizer(
            'entry_name_prefix',
            static fn(Options $options, string $value): string => $value ? trim($value, '/') . '/' : ''
        );

        return $optionResolver->resolve($options);
    }
}
