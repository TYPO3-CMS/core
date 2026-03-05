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

namespace TYPO3\CMS\Core\Package\Resource\Definition;

/**
 * This class is meant to be used for resource definition in Configuration/Resources.php
 */
final readonly class PublicFileDefinition extends PublicResourceDefinition
{
    public function __construct(
        string $relativePath,
        string|DynamicPublicPrefixInterface $publicPrefix = new DefaultPublicFilePrefix(),
        ?string $identifier = null,
    ) {
        parent::__construct($relativePath, $publicPrefix, $identifier);
    }

    public function matches(string $relativePath): bool
    {
        return str_starts_with($relativePath, $this->relativePath);
    }
}
