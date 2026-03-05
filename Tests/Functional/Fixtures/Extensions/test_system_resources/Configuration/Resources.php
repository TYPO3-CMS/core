<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\Resource\Definition\PublicFileDefinition;
use TYPO3\CMS\Core\Package\Resource\Definition\PublicResourceDefinition;

return static function (Package $package) {
    return [
        new PublicResourceDefinition(
            relativePath: 'Resources/Public2',
            publicPrefix: 'custom2',
        ),
        new PublicResourceDefinition(
            relativePath: 'Resources/Public3',
            publicPrefix: 'custom3',
        ),
        new PublicResourceDefinition(
            relativePath: 'Resources/Public4',
        ),
        new PublicFileDefinition(
            relativePath: 'Resources/PublicFiles/Html/ToBePublished1.html',
        ),
        new PublicFileDefinition(
            relativePath: 'Resources/PublicFiles/Html/ToBePublished2.html',
            publicPrefix: $package->getPackageKey() . '/custom/folder/published2.html',
        ),
    ];
};
