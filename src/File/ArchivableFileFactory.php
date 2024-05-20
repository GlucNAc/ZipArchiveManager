<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\File;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ArchivableFileFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public static function new(array $data): ArchivableFile
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

        return (new ArchivableFile($data['full_path']))->setEntryName($data['entry_name']);
    }
}
