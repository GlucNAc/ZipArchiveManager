<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\Transformer;

use GlucNAc\ZipArchiveManager\File\ArchivableFile;
use GlucNAc\ZipArchiveManager\File\ArchivableFileFactory;
use SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-type ResolvedOptions = array{root_directory: string, entry_name_prefix: string}
 */
final class SplFileInfoToArchivableFileTransformer extends AbstractToArchivableFileTransformer
{
    protected const TRANSFORMED_OBJECT_CLASS = SplFileInfo::class;

    /**
     * @param array<string, mixed> $options
     */
    public static function getArchivableFile(object $object, array $options = []): ArchivableFile
    {
        if (!$object instanceof SplFileInfo) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Argument 1 passed to "%s()" must be an instance of "%s", "%s" given.',
                    __METHOD__,
                    SplFileInfo::class,
                    \get_class($object),
                )
            );
        }

        $options = self::resolveOptions($options);

        $rootDirectory = $options['root_directory'];

        return ArchivableFileFactory::new([
            'full_path' => $filePath = $object->getPath() . DIRECTORY_SEPARATOR . $object->getFilename(),
            'entry_name' => $options['entry_name_prefix'] . self::extractFileName($filePath, $rootDirectory),
        ]);
    }

    /**
     * @return ResolvedOptions
     */
    protected static function resolveOptions(array $options): array
    {
        $options = parent::resolveOptions($options);

        $optionResolver = new OptionsResolver();

        // Allow others entries in $context
        $optionResolver->setDefined(array_keys($options));

        $optionResolver->setRequired('root_directory');
        $optionResolver->setAllowedTypes('root_directory', 'string');

        /** @var ResolvedOptions $resolvedOptions */
        $resolvedOptions = $optionResolver->resolve($options);

        return $resolvedOptions;
    }

    private static function extractFileName(string $filePath, string $directory): string
    {
        return str_replace($directory . DIRECTORY_SEPARATOR, '', $filePath);
    }
}
