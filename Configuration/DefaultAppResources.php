<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Package\Resource\Definition\PublicResourceDefinition;
use TYPO3\CMS\Core\Package\VirtualAppPackage;

return static function (VirtualAppPackage $package, string $relativePublicPath) {
    return [
        new PublicResourceDefinition(
            relativePath: $relativePublicPath . '_assets',
        ),
        new PublicResourceDefinition(
            relativePath: $relativePublicPath . 'uploads',
        ),
        new PublicResourceDefinition(
            relativePath: $relativePublicPath . 'typo3temp/assets',
        ),
    ];
};
