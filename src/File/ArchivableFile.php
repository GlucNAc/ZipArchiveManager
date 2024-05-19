<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\File;

class ArchivableFile implements ArchivableFileInterface
{
    private string|null $entryName = null;

    public function __construct(private readonly string $fullPath)
    {
    }

    public function getFileName(): string
    {
        return \pathinfo($this->getFullPath(), PATHINFO_FILENAME);
    }

    public function getExtension(): string
    {
        return \pathinfo($this->getFullPath(), PATHINFO_EXTENSION);
    }

    public function getFullPath(): string
    {
        return $this->fullPath;
    }

    /**
     * This method is used to get the name of the file inside the archive. It can be useful to rename the file
     * on the fly, or to put it in a subdirectory by returning a relative path.
     *
     * When equals to null, the entry name will be the same as the file name.
     */
    public function getEntryName(): string
    {
        return $this->entryName ?? \pathinfo($this->getFullPath(), PATHINFO_BASENAME);
    }

    public function setEntryName(string|null $entryName): static
    {
        $this->entryName = $entryName;

        return $this;
    }
}
