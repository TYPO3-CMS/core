<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\Resource\Definition\PublicResourceDefinition;
use TYPO3\CMS\Core\Package\Resource\Definition\ResourceDefinition;

return static function (Package $package) {
    $resourceDefinitions = [
        new ResourceDefinition('Resources/Private'),
    ];
    if (is_dir($package->getPackagePath() . 'Resources/Public')) {
        $resourceDefinitions[] = new PublicResourceDefinition('Resources/Public');
    }
    return $resourceDefinitions;
};
