<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\Core\Resource;

use TYPO3\CMS\Core\Resource\Search\FileSearchDemand;
use TYPO3\CMS\Core\Resource\Search\Result\FileSearchResultInterface;

/**
 * Interface for folders
 */
interface FolderInterface extends ResourceInterface
{
    /**
     * Roles for folders
     */
    public const ROLE_DEFAULT = 'default';
    public const ROLE_RECYCLER = 'recycler';
    public const ROLE_PROCESSING = 'processing';
    public const ROLE_TEMPORARY = 'temporary';
    public const ROLE_USERUPLOAD = 'userupload';
    public const ROLE_MOUNT = 'mount';
    public const ROLE_READONLY_MOUNT = 'readonly-mount';
    public const ROLE_USER_MOUNT = 'user-mount';

    /**
     * @phpstan-return array<array-key, FolderInterface>
     */
    public function getSubfolders(): array;

    /**
     * Returns the object for a subfolder of the current folder, if it exists.
     */
    public function getSubfolder(string $name): FolderInterface;

    /**
     * Checks if a folder exists in this folder.
     */
    public function hasFolder(string $name): bool;

    /**
     * Checks if a file exists in this folder
     */
    public function hasFile(string $name): bool;

    /**
     * Fetches a file from a folder, must be a direct descendant of a folder.
     */
    public function getFile(string $fileName): ?FileInterface;

    /**
     * Renames this folder.
     */
    public function rename(string $newName): self;

    /**
     * Deletes this folder from its storage. This also means that this object becomes useless.
     */
    public function delete(): bool;

    /**
     * Returns the modification time of the folder as Unix timestamp
     */
    public function getModificationTime(): int;

    /**
     * Returns the creation time of the folder as Unix timestamp
     */
    public function getCreationTime(): int;

    /**
     * Returns a string of the path to this folder, relative to the root of the storage
     */
    public function getReadablePath(?string $rootId = null): string;

    /**
     * Returns a list of files in this folder, based on filters and includes pagination.
     */
    public function getFiles(int $start = 0, int $numberOfItems = 0, int $filterMode = Folder::FILTER_MODE_USE_OWN_AND_STORAGE_FILTERS, bool $recursive = false, string $sort = '', bool $sortRev = false);

    /**
     * Returns a list of files in this folder, based on SearchDemand.
     */
    public function searchFiles(FileSearchDemand $searchDemand, int $filterMode = Folder::FILTER_MODE_USE_OWN_AND_STORAGE_FILTERS): FileSearchResultInterface;

    /**
     * Some folders have special roles in TYPO3, see the constants of this interface.
     */
    public function getRole(): string;
}
