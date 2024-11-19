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

namespace CKSource\CKFinder\Filesystem\Folder;

use CKSource\CKFinder\{Backend\Backend,
    CKFinder,
    Exception\AccessDeniedException,
    Exception\AlreadyExistsException,
    Exception\FileNotFoundException,
    Exception\FolderNotFoundException,
    Exception\InvalidExtensionException,
    Exception\InvalidNameException,
    Exception\InvalidRequestException,
    Operation\OperationManager,
    ResizedImage\ResizedImageRepository,
    ResourceType\ResourceType,
    Response\JsonResponse,
    Thumbnail\ThumbnailRepository,
    Utils};
use CKSource\CKFinder\Filesystem\{File\File, Path};
use League\Flysystem\FileExistsException;
use JetBrains\PhpStorm\{Pure};
use League\Flysystem\Util\MimeType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\{Event\ResponseEvent, KernelEvents};
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * The WorkingFolder class.
 *
 * Represents a working folder for the current request defined by
 * a resource type and a relative path.
 */
class WorkingFolder extends Folder implements EventSubscriberInterface
{
    protected Backend $backend;

    protected ThumbnailRepository $thumbnailRepository;

    protected ResourceType $resourceType;

    /**
     * Current folder path.
     */
    protected string $clientCurrentFolder;

    /**
     * Backend relative path (includes the backend directory prefix).
     */
    protected ?string $path;

    /**
     * Directory ACL mask computed for the current user.
     */
    protected ?int $aclMask;

    /**
     * Constructor.
     *
     * @param CKFinder $app The app instance.
     *
     * @throws AccessDeniedException
     * @throws FolderNotFoundException
     * @throws InvalidNameException
     * @throws InvalidRequestException
     */
    public function __construct(protected CKFinder $app)
    {
        /** @var Request $request */
        $request = $app['request_stack']->getCurrentRequest();

        $resourceType = $app['resource_type_factory']->getResourceType((string)$request->get('type'));

        $this->clientCurrentFolder = Path::normalize(trim((string)$request->get('currentFolder')));

        if (!Path::isValid($this->clientCurrentFolder)) {
            throw new InvalidNameException('Invalid path');
        }

        $resourceTypeDirectory = $resourceType->getDirectory();

        parent::__construct($resourceType, $this->clientCurrentFolder);

        $this->backend = $this->resourceType->getBackend();
        $this->thumbnailRepository = $app['thumbnail_repository'];

        $backend = $this->getBackend();

        // Check if folder path is not hidden
        if ($backend->isHiddenPath($this->getClientCurrentFolder())) {
            throw new InvalidRequestException('Hidden folder path used');
        }

        // Check if resource type folder exists - if not then create it
        $currentCommand = (string)$request->query->get('command');
        $omitForCommands = ['Thumbnail'];

        if (
            !empty($resourceTypeDirectory)
            && !in_array($currentCommand, $omitForCommands, true)
            && !$backend->hasDirectory($this->path)
        ) {
            if ('/' === $this->clientCurrentFolder) {
                @$backend->createDir($resourceTypeDirectory);

                if (!$backend->hasDirectory($resourceTypeDirectory)) {
                    throw new AccessDeniedException(
                        "Couldn't create resource type directory. Please check permissions."
                    );
                }
            } else {
                throw new FolderNotFoundException();
            }
        }
    }

    /**
     * Returns the backend assigned for the current resource type.
     */
    #[Pure]
    public function getBackend(): Backend
    {
        return $this->resourceType->getBackend();
    }

    /**
     * Returns the client current folder path.
     */
    public function getClientCurrentFolder(): string
    {
        return $this->clientCurrentFolder;
    }

    /**
     * Returns listeners for the event dispatcher.
     *
     * @return array subscribed events
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => ['addCurrentFolderInfo', 512]];
    }

    /**
     * Returns the thumbnails' repository object.
     */
    public function getThumbnailsRepository(): ThumbnailRepository
    {
        return $this->thumbnailRepository;
    }

    /**
     * Lists directories in the current working folder.
     *
     * @return array list of directories
     */
    public function listDirectories(): array
    {
        return $this->getBackend()->directories($this->getResourceType(), $this->getClientCurrentFolder());
    }

    /**
     * Returns the ResourceType object for the current working folder.
     */
    public function getResourceType(): ResourceType
    {
        return $this->resourceType;
    }

    /**
     * Lists files in the current working folder.
     *
     * @return array list of files
     */
    public function listFiles(): array
    {
        return $this->getBackend()->files($this->getResourceType(), $this->getClientCurrentFolder());
    }

    /**
     * Creates a directory with a given name in the working folder.
     *
     * @param string $dirname directory name
     *
     * @return array [string, bool] [0] Created folder name, [1] `true` if the folder was created successfully
     *
     * @throws AlreadyExistsException
     * @throws InvalidNameException
     * @throws AccessDeniedException
     */
    public function createDir(string $dirname): array
    {
        $config = $this->app['config'];

        $locBackend = $this->getBackend();

        if (
            !Folder::isValidName($dirname, $config->get('disallowUnsafeCharacters')) ||
            $locBackend->isHiddenFolder($dirname)
        ) {
            throw new InvalidNameException('Invalid folder name');
        }

        if ($config->get('forceAscii')) {
            $dirname = File::convertToAscii($dirname);
        }

        $dirPath = Path::combine($this->getPath(), $dirname);

        if ($locBackend->hasDirectory($dirPath)) {
            throw new AlreadyExistsException('Folder already exists');
        }

        $result = $locBackend->createDir($dirPath);

        if (!$result) {
            throw new AccessDeniedException("Couldn't create new folder. Please check permissions.");
        }

        return [$dirname, $result];
    }

    /**
     * Returns the backend relative path with the resource type directory prefix.
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Creates a file inside the current working folder.
     *
     * @param string $fileName file name
     * @param string $data     file data
     *
     * @return bool `true` if created successfully
     *
     * @throws FileExistsException
     */
    public function write(string $fileName, string $data): bool
    {
        $locBackend = $this->getBackend();
        $filePath = Path::combine($this->getPath(), $fileName);

        return $locBackend->write($filePath, $data);
    }

    /**
     * Creates a file inside the current working folder using the stream.
     *
     * @param string   $fileName file name
     * @param resource $resource file data stream
     *
     * @return bool `true` if created successfully
     *
     * @throws FileExistsException
     */
    public function writeStream(string $fileName, $resource): bool
    {
        $locBackend = $this->getBackend();
        $filePath = Path::combine($this->getPath(), $fileName);

        return $locBackend->writeStream($filePath, $resource);
    }

    /**
     * Creates or updates a file inside the current working folder using the stream.
     *
     * @param string      $fileName file name
     * @param resource    $resource file data stream
     * @param string|null $mimeType file MIME type
     *
     * @return bool `true` if updated successfully
     */
    public function putStream(string $fileName, $resource, string $mimeType = null): bool
    {
        $locBackend = $this->getBackend();
        $filePath = Path::combine($this->getPath(), $fileName);

        if (!$mimeType) {
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $mimeType = MimeType::detectByFileExtension($ext);
        }

        $options = $mimeType ? ['mimetype' => $mimeType] : [];

        return $locBackend->putStream($filePath, $resource, $options);
    }

    /**
     * Returns contents of the file with a given name.
     *
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function read(string $fileName): string
    {
        $locBackend = $this->getBackend();
        $filePath = Path::combine($this->getPath(), $fileName);

        return $locBackend->read($filePath);
    }

    /**
     * Returns contents stream of the file with a given name.
     *
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function readStream(string $fileName)
    {
        $locBackend = $this->getBackend();
        $filePath = Path::combine($this->getPath(), $fileName);

        return $locBackend->readStream($filePath);
    }

    /**
     * Deletes the current working folder.
     *
     * @return bool `true` if the deletion was successful
     *
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function delete(): bool
    {
        // Delete related thumbs path
        $this->thumbnailRepository->deleteThumbnails($this->resourceType, $this->getClientCurrentFolder());

        $this->app['cache']->deleteByPrefix(
            Path::combine($this->resourceType->getName(), $this->getClientCurrentFolder())
        );

        return $this->getBackend()->deleteDir($this->getPath());
    }

    /**
     * Renames the current working folder.
     *
     * @param string $newName new folder name
     *
     * @return array containing newName and newPath
     *
     * @throws AccessDeniedException
     * @throws AlreadyExistsException
     * @throws InvalidNameException
     * @throws FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function rename(string $newName): array
    {
        $config = $this->app['config'];
        $disallowUnsafeCharacters = $config->get('disallowUnsafeCharacters');
        $forceAscii = $config->get('forceAscii');

        if (!Folder::isValidName($newName, $disallowUnsafeCharacters) || $this->backend->isHiddenFolder($newName)) {
            throw new InvalidNameException('Invalid folder name');
        }

        if ($forceAscii) {
            $newName = File::convertToAscii($newName);
        }

        $newBackendPath = dirname($this->getPath()) . '/' . $newName;

        if ($this->backend->has($newBackendPath)) {
            throw new AlreadyExistsException('File already exists');
        }

        $newClientPath = Path::normalize(dirname($this->getClientCurrentFolder()) . '/' . $newName);

        if (!$this->getBackend()->rename($this->getPath(), $newBackendPath)) {
            throw new AccessDeniedException();
        }

        /** @var OperationManager $currentRequestOperation */
        $currentRequestOperation = $this->app['operation'];

        if ($currentRequestOperation->isAborted()) {
            // Don't continue in this case, no need to touch thumbs and cache entries
            return ['aborted' => true];
        }

        // Delete related thumbs path
        $this->thumbnailRepository->deleteThumbnails($this->resourceType, $this->getClientCurrentFolder());

        $this->app['cache']->changePrefix(
            Path::combine($this->resourceType->getName(), $this->getClientCurrentFolder()),
            Path::combine($this->resourceType->getName(), $newClientPath)
        );

        return [
            'newName' => $newName,
            'newPath' => $newClientPath,
            'renamed' => 1
        ];
    }

    /**
     * Returns the URL to a given file.
     *
     * @throws InvalidExtensionException
     * @throws InvalidRequestException
     * @throws FileNotFoundException
     */
    public function getFileUrl(string $fileName, string $thumbnailFileName = null): ?string
    {
        $config = $this->app['config'];

        if (!File::isValidName($fileName, $config->get('disallowUnsafeCharacters'))) {
            throw new InvalidRequestException('Invalid file name');
        }

        if ($thumbnailFileName) {
            if (!File::isValidName($thumbnailFileName, $config->get('disallowUnsafeCharacters'))) {
                throw new InvalidRequestException('Invalid thumbnail file name');
            }

            if (!$this->resourceType->isAllowedExtension(pathinfo($thumbnailFileName, PATHINFO_EXTENSION))) {
                throw new InvalidExtensionException('Invalid thumbnail file name');
            }
        }

        if (!$this->containsFile($fileName)) {
            throw new FileNotFoundException();
        }

        return $this->backend->getFileUrl(
            $this->resourceType,
            $this->getClientCurrentFolder(),
            $fileName,
            $thumbnailFileName
        );
    }

    /**
     * Checks if the current working folder contains a file with a given name.
     */
    public function containsFile(string $fileName): bool
    {
        $locBackend = $this->getBackend();

        if (!File::isValidName($fileName, $this->app['config']->get('disallowUnsafeCharacters')) ||
            $locBackend->isHiddenFolder($this->getClientCurrentFolder()) ||
            $locBackend->isHiddenFile($fileName) ||
            !$this->resourceType->isAllowedExtension(pathinfo($fileName, PATHINFO_EXTENSION))) {
            return false;
        }

        $filePath = Path::combine($this->getPath(), $fileName);

        return $locBackend->has($filePath);
    }

    /**
     * @return ResizedImageRepository
     */
    public function getResizedImageRepository(): ResizedImageRepository
    {
        return $this->app['resized_image_repository'];
    }

    /**
     * Tells the current WorkingFolder object to not add the current folder
     * to the response.
     *
     * By default, the WorkingFolder object acts as an event subscriber and
     * listens for the `KernelEvents::RESPONSE` event. The response given is
     * then modified by adding information about the current folder.
     *
     * @see WorkingFolder::addCurrentFolderInfo()
     */
    public function omitResponseInfo(): void
    {
        $this->app['dispatcher']->removeSubscriber($this);
    }

    /**
     * Adds the current folder information to the response.
     *
     * @throws Throwable
     */
    public function addCurrentFolderInfo(ResponseEvent $event): void
    {
        /** @var JsonResponse $response */
        $response = $event->getResponse();

        if ($response instanceof JsonResponse) {
            $responseData = (array)$response->getData();

            $responseData = [
                    'resourceType' => $this->getResourceTypeName(),
                    'currentFolder' => [
                        'path' => $this->getClientCurrentFolder(),
                        'acl' => $this->getAclMask()
                    ]
                ] + $responseData;

            $baseUrl = $this->backend->getBaseUrl();

            if (null !== $baseUrl) {
                $folderUrl = Path::combine(
                    $baseUrl,
                    Utils::encodeURLParts(
                        Path::combine(
                            $this->resourceType->getDirectory(),
                            $this->getClientCurrentFolder()
                        )
                    )
                );
                $responseData['currentFolder']['url'] = rtrim($folderUrl, '/') . '/';
            }

            $response->setData($responseData);
        }
    }

    /**
     * Returns the name of the current resource type.
     */
    #[Pure]
    public function getResourceTypeName(): string
    {
        return $this->resourceType->getName();
    }

    /**
     * Returns ACL mask computed for the current user and the current working folder.
     */
    public function getAclMask(): ?int
    {
        if (empty($this->aclMask)) {
            $this->aclMask = $this->app->getAcl()->getComputedMask(
                $this->getResourceTypeName(),
                $this->getClientCurrentFolder()
            );
        }

        return $this->aclMask;
    }
}
