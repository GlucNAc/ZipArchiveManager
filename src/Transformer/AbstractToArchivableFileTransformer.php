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
     * To be defined in child class.
     *
     * @var class-string|null
     */
    protected const TRANSFORMED_OBJECT_CLASS = null;

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
        if (null === static::TRANSFORMED_OBJECT_CLASS) {
            throw new \LogicException(
                sprintf(
                    'The constant "%s::TRANSFORMED_OBJECT_CLASS" must be defined in the child class.',
                    static::class,
                )
            );
        }

        static::assertIsCollectionOf($collection, static::TRANSFORMED_OBJECT_CLASS);

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
