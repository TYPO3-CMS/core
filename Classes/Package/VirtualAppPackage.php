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

namespace TYPO3\CMS\Core\Package;

use TYPO3\CMS\Core\SystemResource\Package\AppResourceCollection;

/**
 * This represents the app package (root package in Composer terms)
 *
 * @internal Only to be used in TYPO3\CMS\Core\Package and TYPO3\CMS\Core\SystemResource namespace
 */
final class VirtualAppPackage extends Package
{
    public const APP_PACKAGE_KEY = 'typo3/app';

    public function __construct(
        PackageManager $packageManager,
        string $packageKey,
        string $packagePath,
        private readonly string $relativePublicPath,
    ) {
        parent::__construct($packageManager, $packageKey, $packagePath, true);
        $this->packageMetaData = new MetaData($packageKey);
        $this->composerManifest = new \stdClass();
        $this->composerManifest->name = self::APP_PACKAGE_KEY;
    }

    protected function createResources(): void
    {
        $this->resources = new AppResourceCollection($this->relativePublicPath);
    }
}
