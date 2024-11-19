<?php

/*
 * CKFinder
 * ========
 * https://ckeditor.com/ckfinder/
 * Copyright (c) 2007-2023, CKSource Holding sp. z o.o. All rights reserved.
 *
 * The software, this file and its contents are subject to the CKFinder
 * License. Please read the license.txt file before using, installing, copying,
 * modifying or distribute this file or part of its contents. The contents of
 * this file is part of the Source Code of CKFinder.
 */

namespace CKSource\CKFinder\Backend;

use CKSource\CKFinder\{Acl\AclInterface,
    Acl\Permission,
    CKFinder,
    Config,
    Filesystem\Path,
    ResizedImage\ResizedImage,
    ResourceType\ResourceType,
    Utils};
use CKSource\CKFinder\Backend\Adapter\EmulateRenameDirectoryInterface;
use Exception;
use JetBrains\PhpStorm\Pure;
use League\Flysystem\{Adapter\Ftp,
    AdapterInterface,
    Cached\CachedAdapter,
    FileExistsException,
    FileNotFoundException,
    Filesystem,
    Plugin\GetWithMetadata};

/**
 * The Backend file system class.
 *
 * A wrapper class for League\Flysystem\Filesystem with
 * CKFinder customizations.
 */
class Backend extends Filesystem
{
    /**
     * Access Control Lists.
     */
    protected AclInterface $acl;

    /**
     * Configuration.
     */
    protected Config $ckConfig;

    /**
     * Constructor.
     *
     * @param array            $backendConfig    the backend configuration node
     * @param CKFinder         $app              the CKFinder app container
     * @param AdapterInterface $adapter          the adapter
     * @param array|null       $filesystemConfig the configuration
     */
    public function __construct(
        protected array $backendConfig,
        protected CKFinder $app,
        AdapterInterface $adapter,
        array $filesystemConfig = null
    ) {
        $this->acl = $app['acl'];
        $this->ckConfig = $app['config'];

        parent::__construct($adapter, $filesystemConfig);

        $this->addPlugin(new GetWithMetadata());
    }

    /**
     * Returns the name of the backend.
     *
     * @return string name of the backend
     */
    public function getName(): string
    {
        return $this->backendConfig['name'];
    }

    /**
     * Returns an array of commands that should use operation tracking.
     */
    public function getTrackedOperations(): array
    {
        return $this->backendConfig['trackedOperations'] ?? [];
    }

    /**
     * Returns a filtered list of directories for a given resource type and path.
     */
    public function directories(ResourceType $resourceType, string $path = '', bool $recursive = false): array
    {
        $directoryPath = $this->buildPath($resourceType, $path);
        $contents = $this->listContents($directoryPath, $recursive);

        foreach ($contents as &$entry) {
            $entry['acl'] = $this->acl->getComputedMask(
                $resourceType->getName(),
                Path::combine($path, $entry['basename'])
            );
        }

        return array_filter($contents, fn($v) => isset($v['type']) &&
            'dir' === $v['type'] &&
            !$this->isHiddenFolder($v['basename']) &&
            $v['acl'] & Permission::FOLDER_VIEW
        );
    }

    /**
     * Returns a path based on the resource type and the resource type relative path.
     *
     * @param ResourceType $resourceType the resource type
     * @param string $path the resource type relative path
     *
     * @return string path to be used with the backend adapter
     */
    public function buildPath(ResourceType $resourceType, string $path): string
    {
        return Path::combine($resourceType->getDirectory(), $path);
    }

    /**
     * Checks if the directory with a given name is hidden.
     *
     * @return bool `true` if the directory is hidden
     */
    public function isHiddenFolder(string $folderName): bool
    {
        $hideFoldersRegex = $this->ckConfig->getHideFoldersRegex();

        if ($hideFoldersRegex) {
            return (bool)preg_match($hideFoldersRegex, $folderName);
        }

        return false;
    }

    /**
     * Returns a filtered list of files for a given resource type and path.
     */
    public function files(ResourceType $resourceType, string $path = '', bool $recursive = false): array
    {
        $directoryPath = $this->buildPath($resourceType, $path);
        $contents = $this->listContents($directoryPath, $recursive);

        return array_filter(
            $contents,
            fn($v) =>
                isset($v['type']) &&
                'file' === $v['type'] &&
                !$this->isHiddenFile($v['basename']) &&
                $resourceType->isAllowedExtension($v['extension'] ?? '')
        );
    }

    /**
     * Checks if the file with a given name is hidden.
     *
     * @return bool `true` if the file is hidden
     */
    public function isHiddenFile(string $fileName): bool
    {
        $hideFilesRegex = $this->ckConfig->getHideFilesRegex();

        if ($hideFilesRegex) {
            return (bool)preg_match($hideFilesRegex, $fileName);
        }

        return false;
    }

    /**
     * Check if the directory for a given path contains subdirectories.
     *
     * @return bool `true` if the directory contains subdirectories
     */
    public function containsDirectories(ResourceType $resourceType, string $path = ''): bool
    {
        $baseAdapter = $this->getBaseAdapter();
        if (method_exists($baseAdapter, 'containsDirectories')) {
            return $baseAdapter->containsDirectories($this, $resourceType, $path, $this->acl);
        }

        $directoryPath = $this->buildPath($resourceType, $path);

        // It's possible that directory may not exist yet. This is the case when very first Init command
        // is received, and resource type directories were not created yet. Some adapters will throw in
        // this case, so handle this gracefully.
        try {
            $contents = $this->listContents($directoryPath);
        } catch (Exception) {
            return false;
        }

        foreach ($contents as $entry) {
            if ('dir' === $entry['type'] &&
                !$this->isHiddenFolder($entry['basename']) &&
                $this->acl->isAllowed(
                    $resourceType->getName(),
                    Path::combine($path, $entry['basename']),
                    Permission::FOLDER_VIEW
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a base adapter used by this backend.
     *
     * The used adapter might be decorated with CachedAdapter. In this
     * case the returned adapter is the internal one used by CachedAdapter.
     */
    #[Pure]
    public function getBaseAdapter(): AdapterInterface
    {
        if ($this->adapter instanceof CachedAdapter) {
            return $this->adapter->getAdapter();
        }

        return $this->adapter;
    }

    /**
     * Checks if the path is hidden.
     *
     * @return bool `true` if the path is hidden
     */
    public function isHiddenPath(string $path): bool
    {
        $pathParts = explode('/', trim($path, '/'));
        if ($pathParts) {
            foreach ($pathParts as $part) {
                if ($this->isHiddenFolder($part)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Deletes a directory.
     *
     * @throws FileNotFoundException
     */
    public function deleteDir($dirname): bool
    {
        $baseAdapter = $this->getBaseAdapter();

        // For FTP first remove recursively all directory contents
        if ($baseAdapter instanceof Ftp) {
            $this->deleteContents($dirname);
        }

        return parent::deleteDir($dirname);
    }

    /**
     * Delete all contents of the given directory.
     *
     * @throws FileNotFoundException
     */
    public function deleteContents(string $dirname): void
    {
        $contents = $this->listContents($dirname);

        foreach ($contents as $entry) {
            if ('dir' === $entry['type']) {
                $this->deleteContents($entry['path']);
                $this->deleteDir($entry['path']);
            } else {
                $this->delete($entry['path']);
            }
        }
    }

    /**
     * Returns a URL to a file.
     *
     * If the useProxyCommand option is set for a backend, the returned
     * URL will point to the CKFinder connector Proxy command.
     *
     * @param ResourceType $resourceType      the file resource type
     * @param string       $folderPath        the resource-type relative folder path
     * @param string       $fileName          the file name
     * @param string|null  $thumbnailFileName the thumbnail file name - if the file is a thumbnail
     *
     * @return null|string URL to a file or `null` if the backend does not support it
     */
    public function getFileUrl(
        ResourceType $resourceType,
        string $folderPath,
        string $fileName,
        string $thumbnailFileName = null
    ): ?string {
        if ($this->usesProxyCommand()) {
            $connectorUrl = $this->app->getConnectorUrl();

            $queryParameters = [
                'command' => 'Proxy',
                'type' => $resourceType->getName(),
                'currentFolder' => $folderPath,
                'fileName' => $fileName,
            ];

            if ($thumbnailFileName) {
                $queryParameters['thumbnail'] = $thumbnailFileName;
            }

            $proxyCacheLifetime = (int)$this->ckConfig->get('cache.proxyCommand');

            if ($proxyCacheLifetime > 0) {
                $queryParameters['cache'] = $proxyCacheLifetime;
            }

            return $connectorUrl . '?' . http_build_query($queryParameters, '', '&');
        }

        $path = $thumbnailFileName
            ? Path::combine(
                $resourceType->getDirectory(),
                $folderPath,
                ResizedImage::DIR,
                $fileName,
                $thumbnailFileName
            )
            : Path::combine($resourceType->getDirectory(), $folderPath, $fileName);

        if (isset($this->backendConfig['baseUrl'])) {
            return Path::combine($this->backendConfig['baseUrl'], Utils::encodeURLParts($path));
        }

        $baseAdapter = $this->getBaseAdapter();

        if (method_exists($baseAdapter, 'getFileUrl')) {
            return $baseAdapter->getFileUrl($path);
        }

        return null;
    }

    /**
     * Returns a Boolean value telling if the backend uses the Proxy command.
     */
    public function usesProxyCommand(): bool
    {
        return isset($this->backendConfig['useProxyCommand']) && $this->backendConfig['useProxyCommand'];
    }

    /**
     * Returns the base URL used to build the direct URL to files stored
     * in this backend.
     *
     * @return null|string base URL or `null` if the base URL for a backend
     *                     was not defined
     */
    public function getBaseUrl(): ?string
    {
        if (isset($this->backendConfig['baseUrl']) && !$this->usesProxyCommand()) {
            return $this->backendConfig['baseUrl'];
        }

        return null;
    }

    /**
     * Returns the root directory defined for the backend.
     *
     * @return null|string root directory or `null` if the root directory
     *                     was not defined
     */
    public function getRootDirectory(): ?string
    {
        return $this->backendConfig['root'] ?? null;
    }

    /**
     * Creates a stream for writing.
     *
     * @param string $path file path
     *
     * @return null|resource a stream to a file or `null` if the backend does not
     *                       support writing streams
     */
    public function createWriteStream(string $path)
    {
        $baseAdapter = $this->getBaseAdapter();

        if (method_exists($baseAdapter, 'createWriteStream')) {
            return $baseAdapter->createWriteStream($path);
        }

        return null;
    }

    /**
     * Renames the object for a given path.
     *
     * @return bool `true` on success, `false` on failure
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function rename($path, $newpath): bool
    {
        $baseAdapter = $this->getBaseAdapter();

        if (($baseAdapter instanceof EmulateRenameDirectoryInterface) && $this->hasDirectory($path)) {
            return $baseAdapter->renameDirectory($path, $newpath);
        }

        return parent::rename($path, $newpath);
    }

    /**
     * Checks if a backend contains a directory.
     *
     * The Backend::has() method is not always reliable and may
     * work differently for various adapters. Checking for directory
     * should be done with this method.
     */
    public function hasDirectory(string $directoryPath): bool
    {
        $pathParts = array_filter(explode('/', $directoryPath), 'strlen');
        $dirName = array_pop($pathParts);

        try {
            $contents = $this->listContents(implode('/', $pathParts));
        } catch (Exception) {
            return false;
        }

        foreach ($contents as $c) {
            if (isset($c['type'], $c['basename']) && 'dir' === $c['type'] && $c['basename'] === $dirName) {
                return true;
            }
        }
        return false;
    }
}
