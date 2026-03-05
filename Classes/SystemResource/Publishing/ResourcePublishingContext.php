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

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\Resource\Definition\DynamicPublicPrefixInterface;
use TYPO3\CMS\Core\Package\Resource\Definition\PublicResourceDefinition;

/**
 * @internal Only to be used in TYPO3\CMS\Core\SystemResource namespace
 */
final readonly class ResourcePublishingContext
{
    public string $prefix;
    public bool $isSourcePublic;
    public string $filesystemPath;

    /**
     * Variable names are explicitly public API
     * for named variable access
     */
    public function __construct(
        private PackageInterface $package,
        private PublicResourceDefinition $definition,
    ) {
        $this->prefix = $definition->getPublicPrefix() instanceof DynamicPublicPrefixInterface
            ? $definition->getPublicPrefix()->calculatePrefix($package, $definition)
            : $definition->getPublicPrefix();
        $this->isSourcePublic = str_starts_with($this->package->getPackagePath() . $this->definition->getRelativePath(), Environment::getPublicPath());
        $this->filesystemPath = $package->getPackagePath() . $definition->getRelativePath();
    }
}
