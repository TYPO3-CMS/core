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

namespace TYPO3\CMS\Core\SystemResource\Publishing;

use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\Resource\Definition\PublicResourceDefinition;
use TYPO3\CMS\Core\SystemResource\Type\PublicPackageFile;

/**
 * @internal Only to be used in TYPO3\CMS\Core\SystemResource namespace
 */
final readonly class ResourceUriBuildingContext
{
    public string $absoluteResourcePath;
    public string $uriPath;
    public bool $isSourcePublic;

    /**
     * Variable names are explicitly public API
     * for named variable access
     */
    public function __construct(
        public PublicPackageFile $resource,
        public PackageInterface $package,
        public PublicResourceDefinition $definition,
    ) {
        $this->absoluteResourcePath = $this->package->getPackagePath() . $this->resource->getRelativePath();
        $publishingContext = new ResourcePublishingContext(
            package: $package,
            definition: $definition
        );
        $this->isSourcePublic = $publishingContext->isSourcePublic;
        $this->uriPath = $publishingContext->prefix . substr($this->resource->getRelativePath(), strlen($this->definition->getRelativePath()));
    }
}
