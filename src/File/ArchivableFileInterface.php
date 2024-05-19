<?php

declare(strict_types=1);

namespace GlucNAc\ZipArchiveManager\File;

interface ArchivableFileInterface
{
    public function getFullPath(): string;

    public function getFileName(): string;

    public function getExtension(): string;

    /**
     * This method is used to get the name of the file inside the archive. It can be useful to rename the file
     * on the fly, or to put it in a subdirectory by returning a relative path.
     */
    public function getEntryName(): string;

    public function setEntryName(string|null $entryName): static;
}
