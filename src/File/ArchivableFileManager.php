<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\File;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use GlucNAc\ZipArchiveManager\AssertCollectionTrait;
use GlucNAc\ZipArchiveManager\Transformer\SplFileInfoToArchivableFileTransformer;
use GlucNAc\ZipArchiveManager\ZipArchive\ZipArchiveException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ArchivableFileManager
{
    use AssertCollectionTrait;

    /**
     * @param array<string, mixed> $data
     */
    public static function buildFromArray(array $data): ArchivableFile
    {
        $optionResolver = new OptionsResolver();

        $optionResolver->setRequired([
            'full_path',
            'entry_name',
        ]);

        $optionResolver->setAllowedTypes('full_path', 'string');
        $optionResolver->setAllowedTypes('entry_name', ['string', 'null']);

        $optionResolver->setDefault('entry_name', null);

        $data = $optionResolver->resolve($data);

        return (new ArchivableFile())
            ->setFullPath($data['full_path'])
            ->setEntryName($data['entry_name'])
        ;
    }

    /**
     * @return Collection<int, ArchivableFile>
     * @throws ZipArchiveException
     */
    public function getArchivableFilesFromPath(string $filesDirectoryAbsolutePath): Collection
    {
        if (!is_dir($filesDirectoryAbsolutePath) || !\is_readable($filesDirectoryAbsolutePath)) {
            throw new ZipArchiveException(
                sprintf('Directory "%s" does not exist or is not readable', $filesDirectoryAbsolutePath)
            );
        }

        $finder = new Finder();
        $storedFiles = $finder
            ->in($filesDirectoryAbsolutePath)
            ->files();

        $files = new ArrayCollection();
        foreach ($storedFiles as $storedFile) {
            $files->add(SplFileInfoToArchivableFileTransformer::getArchivableFile($storedFile, [
                'root_directory' => $filesDirectoryAbsolutePath,
            ]));
        }

        return $files;
    }
}
