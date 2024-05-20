<h1 align="center">GlucNAc/ZipArchiveManager</h1>

<p align="center">
    <strong>A simple wrapper around PHP's native <a href="https://www.php.net/manual/en/class.ziparchive.php">ZipArchive</a>, to make it easier to work with.</strong>
</p>

<p align="center">
    <a href="https://github.com/GlucNAc/ZipArchiveManager"><img src="https://img.shields.io/badge/source-GlucNAc/ZipArchiveManager-blue.svg?style=flat-square" alt="Source Code"></a>
    <a href="https://packagist.org/packages/GlucNAc/ZipArchiveManager"><img src="https://img.shields.io/packagist/v/GlucNAc/ZipArchiveManager.svg?style=flat-square&label=release" alt="Download Package"></a>
    <a href="https://php.net"><img src="https://img.shields.io/packagist/php-v/GlucNAc/ZipArchiveManager.svg?style=flat-square&colorB=%238892BF" alt="PHP Programming Language"></a>
    <a href="https://github.com/GlucNAc/ZipArchiveManager/blob/master/LICENSE"><img src="https://img.shields.io/packagist/l/GlucNAc/ZipArchiveManager.svg?style=flat-square" alt="Read License"></a>
    <a href="https://github.com/GlucNAc/ZipArchiveManager/actions/workflows/continuous-integration.yml"><img src="https://img.shields.io/github/actions/workflow/status/GlucNAc/ZipArchiveManager/continuous-integration.yml?branch=master&style=flat-square&logo=github" alt="Build Status"></a>
    <a href="https://codecov.io/gh/GlucNAc/ZipArchiveManager" ><img src="https://codecov.io/gh/GlucNAc/ZipArchiveManager/graph/badge.svg?token=S3A0XJEVNM" alt="Code coverage badge"/></a>
</p>

## Installation

Install this package as a dependency using [Composer](https://getcomposer.org).

``` bash
composer require glucnac/ziparchivemanager
```

## Usage

The GlucNAc/ZipArchiveManager provides a more object-oriented interface to work with PHP's native <a href="https://www.php.net/manual/en/class.ziparchive.php">ZipArchive</a>, making it easier to create, extract, and modify zip archives.

### Creating a new ZipArchive

To create a new zip archive, you can use the `ZipArchiveBuilder` class. The `ZipArchiveBuilder` requires a `ZipArchiveManager` object to manage the storage of the archive. The `ZipArchiveBuilder` class provides a fluent interface to add files to the archive.

```php
use GlucNAc\ZipArchiveManager\ZipArchiveBuilder;
use GlucNAc\ZipArchiveManager\ZipArchiveManager;

$zipArchiveManager = new ZipArchiveManager('/path/to/storage/archive');
$zipArchiveBuilder = new ZipArchiveBuilder($zipArchiveManager);

$zipArchive = $zipArchiveBuilder
    ->new('test.zip')
    ->addFiles([
        '/path/to/file1.txt',
        '/path/to/dir/file2.txt',
    ])
    ->addFile('/path/to/file3.txt')
    ->build();
```

Now the archive exists at `/path/to/storage/archive/test.zip` and will have this structure:

```
test.zip
├── /path/to/file1.txt
├── /path/to/file3.txt
└── /path/to/dir
             └── file2.txt
```

This can also be done quickly with the `ZipArchiveBuilder::buildWithFiles` method:

```php
use GlucNAc\ZipArchiveManager\ZipArchiveManager;
use GlucNAc\ZipArchiveManager\ZipArchiveBuilder;

$zipArchiveManager = new ZipArchiveManager('/path/to/storage/archive');
$zipArchiveBuilder = new ZipArchiveBuilder($zipArchiveManager);

$zipArchive = $zipArchiveBuilder->buildWithFiles('test.zip', [
    '/path/to/file1.txt',
    '/path/to/dir/file2.txt',
    '/path/to/file3.txt',
]);
```

#### Customizing the file structure in the archive

By default, the archive structure will mirror the structure of the files. If you want to change the structure of the files in the archive, you can use an associative array where the keys are the paths to the files and the values are the paths to the files in the archive:

```php
use GlucNAc\ZipArchiveManager\ZipArchiveManager;
use GlucNAc\ZipArchiveManager\ZipArchiveBuilder;

$zipArchiveManager = new ZipArchiveManager('/path/to/storage/archive');
$zipArchiveBuilder = new ZipArchiveBuilder($zipArchiveManager);

$zipArchive = $zipArchiveBuilder->buildWithFiles('test.zip', [
    '/path/to/file1.txt' => 'file1.txt',
    '/path/to/dir/file2.txt' => 'dir/file2.txt',
    '/path/to/file3.txt' => 'file3.txt',
]);
```

Now the archive exists at `/path/to/storage/archive/test.zip`and will have this structure:

```
test.zip
├── file1.txt
├── file3.txt
└── dir
    └── file2.txt
```

#### Adding files from a directory

Given the following directory structure:

```
/path/to
├── file1.txt
├── file3.txt
└── dir
    └── file2.txt
```

If you want to create an archive with all the files in the `/path/to` directory, while keeping the structure of the files in the archive, you can use the `ZipArchiveBuilder::addFilesFromPath` method:

```php
use GlucNAc\ZipArchiveManager\ZipArchiveBuilder;
use GlucNAc\ZipArchiveManager\ZipArchiveManager;

$zipArchiveManager = new ZipArchiveManager('/path/to/storage/archive');
$zipArchiveBuilder = new ZipArchiveBuilder($zipArchiveManager);

$zipArchive = $zipArchiveBuilder->buildWithFiles(
    'test.zip',
    ArchivableFileManager::getArchivableFilesFromPath('/path/to'),
);
```

The `ArchivableFileManager::getArchivableFilesFromPath` method returns an array of `ArchivableFile` objects, which implement the `ArchivableFileInterface` interface, and where the path of the files in the archive will be relative to the path passed to the method (`/path/to` in this case).

This works because `ZipArchiveBuilder::buildWithFiles`, `ZipArchiveBuilder::addFiles` and `ZipArchiveBuilder::addFile` methods also accept an array of `ArchivableFileInterface` objects (in addition to file paths).

See the [ArchivableFile](#ArchivableFile) section for more information about `ArchivableFileInterface` and `ArchivableFile` objects.

#### Keeping the archive open

By default, `build` methods will close the archive after building it. If you want to keep the archive open, you can pass `false` as the first argument to the `build` method:

```php
use GlucNAc\ZipArchiveManager\ZipArchiveManager;
use GlucNAc\ZipArchiveManager\ZipArchiveBuilder;

$zipArchiveManager = new ZipArchiveManager('/path/to/storage/archive');
$zipArchiveBuilder = new ZipArchiveBuilder($zipArchiveManager);

$zipArchive = $zipArchiveBuilder
    ->new('test.zip')
    ->addFiles([
        '/path/to/file1.txt',
        '/path/to/dir/file2.txt',
    ])
    ->addFile('/path/to/file3.txt')
    ->build(false);

// Do something with the archive

// Close the archive: this will save the archive to the storage
$zipArchiveManager->close($zipArchive);
```

### Adding files to an existing zip archive

To add files to an existing zip archive, you just have to open the archive with the `ZipArchiveManager::open` method
and use methods described in the [Creating a new ZipArchive](#Creating-a-new-ZipArchive) section:

```php
use GlucNAc\ZipArchiveManager\ZipArchiveManager;

$zipArchiveManager = new ZipArchiveManager('/path/to/storage/archive');

// Assume the archive exists at /path/to/storage/archive/test.zip
$zipArchive = $zipArchiveManager->open('test.zip');
```

### Extracting files from a ZipArchive

To extract files from a zip archive, simply use the `ZipArchiveManager::extractFiles` method:

```php
use GlucNAc\ZipArchiveManager\ZipArchiveManager;

$zipArchiveManager = new ZipArchiveManager('/path/to/storage/archive');

// Assume the archive exists at /path/to/storage/archive/test.zip
$zipArchive = $zipArchiveManager->open('test.zip');

$zipArchiveManager->extractFiles($zipArchive, '/path/to/extracted/files/dir');
```

### ArchivableFile

For this section, let's consider the following directory structure:

```
/path/to
├── file1.txt
├── file3.txt
└── dir
    └── file2.txt
```

You may have noticed that `ZipArchiveManager` uses the `ArchivableFile` class to represent files that can be added to a zip archive. More precisely, `ZipArchiveManager` expects an object that implements the [ArchivableFileInterface](src/File/ArchivableFileInterface.php). This interface defines the methods that an object must implement to be considered an archivable file. The `ArchivableFile` class provided by this package implements this interface and provides a simple way to work with files that can be added to a zip archive.

```php
interface ArchivableFileInterface
{
    public function getFullPath(): string;
    public function getFileName(): string;
    public function getExtension(): string;

    /**
     * This method is used to get the name of the file inside the archive. It can be useful to rename
     * the file on the fly, or to put it in a subdirectory by returning a relative path.
     */
    public function getEntryName(): string;
    public function setEntryName(string|null $entryName): static;
}

$archivableFile = new ArchivableFile('/path/to/file1.txt');

$archivableFile->getFullPath();  // /path/to/file1.txt
$archivableFile->getFileName();  // file1.txt
$archivableFile->getExtension(); // txt
$archivableFile->getEntryName(); // /path/to/file1.txt (by default, the entry name equals the full path)
```

You can create your own class that implements this interface, or you can use the `ArchivableFile` class provided by this package. To do so, you can use the `ArchivableFileManager::getArchivableFileFromPath` method to get an `ArchivableFile` object from a file path:

```php
use GlucNAc\ZipArchiveManager\ArchivableFileManager;

$archivableFile = ArchivableFileManager::getArchivableFileFromPath('/path/to/file1.txt');
$archivableFile->setEntryName('file1.txt'); // This will be the path of the file in the archive
```

You can also use the `ArchivableFileManager::getArchivableFilesFromPath` method to get an array of `ArchivableFile` objects from a directory path:

```php
use GlucNAc\ZipArchiveManager\ArchivableFileManager;

[$file1, $file2, $file3] = ArchivableFileManager::getArchivableFilesFromPath('/path/to');

// By default, when using the ArchivableFileManager::getArchivableFilesFromPath method,
// the entry name of the files will be relative to the path passed to the method.
$file1->getEntryName(); // file1.txt
$file2->getEntryName(); // dir/file2.txt
$file3->getEntryName(); // file3.txt
```

Internally, the `ArchivableFile` class uses the `SplFileInfo` class to represent files. You can also use the `SplFileInfoToArchivableFileTransformer` class to transform an `SplFileInfo` object into an `ArchivableFile` object:

```php
use GlucNAc\ZipArchiveManager\ArchivableFile;

$splFileInfo = new SplFileInfo('/path/to/file.txt');

$archivableFile = SplFileInfoToArchivableFileTransformer::getArchivableFile($splFileInfo);
```

## Contributing

Contributions are welcome! To contribute, please familiarize yourself with
[CONTRIBUTING.md](CONTRIBUTING.md).

## Coordinated Disclosure

Keeping user information safe and secure is a top priority, and we welcome the
contribution of external security researchers. If you believe you've found a
security issue in software that is maintained in this repository, please read
[SECURITY.md](SECURITY.md) for instructions on submitting a vulnerability report.

## Copyright and License

GlucNAc/ZipArchiveManager is copyright © [GlucNAc](https://github.com/GlucNAc)
and licensed for use under the terms of the
MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
