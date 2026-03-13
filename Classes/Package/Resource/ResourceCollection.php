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

namespace TYPO3\CMS\Core\Package\Resource;

use TYPO3\CMS\Core\Package\PackageInterface;

/**
 * @internal This is subject to change during v14 development. Do not use.
 */
final readonly class ResourceCollection implements ResourceCollectionInterface
{
    private ?string $iconIdentifier;

    public function __construct(
        PackageInterface $package,
    ) {
        $relativeIconPath = $package->getPackageIcon();
        $this->iconIdentifier = $relativeIconPath !== null ? sprintf(
            'PKG:%s:%s',
            $package->getValueFromComposerManifest('name') ?? $package->getPackageKey(),
            $relativeIconPath,
        ) : null;
    }

    public function isPublicPath(string $relativePath): bool
    {
        return str_starts_with($relativePath, self::PACKAGE_DEFAULT_PUBLIC_DIR);
    }

    public function isValidPath(string $relativePath): bool
    {
        return true;
    }

    public function getPackageIcon(): ?string
    {
        return $this->iconIdentifier;
    }
}
