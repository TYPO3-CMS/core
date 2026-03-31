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

namespace TYPO3\CMS\Core\Configuration\Extension;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Package\Cache\PackageDependentCacheIdentifier;
use TYPO3\CMS\Core\Package\PackageManager;

/**
 * @internal Bootstrap related ext_tables loading. Extensions must not use this.
 * @deprecated this file will vanish in TYPO3 v15.0
 */
#[Autoconfigure(public: true)]
final readonly class ExtTablesFactory
{
    public function __construct(
        private PackageManager $packageManager,
        #[Autowire(service: 'cache.core')]
        private PhpFrontend $codeCache,
    ) {}

    /**
     * Execute all extension "ext_tables.php" files of loaded extensions.
     * Cache to a single file and use if exists.
     */
    public function load(): void
    {
        $cacheIdentifier = $this->getExtTablesCacheIdentifier();
        $hasCache = $this->codeCache->require($cacheIdentifier) !== false;
        if (!$hasCache) {
            $this->loadSingleExtTablesFiles();
            $this->createCacheEntry();
        }
    }

    public function loadUncached(): void
    {
        $this->loadSingleExtTablesFiles();
    }

    /**
     * Create cache entry for concatenated ext_tables.php files
     */
    public function createCacheEntry(): void
    {
        $phpCodeToCache = [];
        $phpCodeToCache[] = '/**';
        $phpCodeToCache[] = ' * Compiled ext_tables.php cache file';
        $phpCodeToCache[] = ' */';
        // Iterate through loaded extensions and add ext_tables content
        foreach ($this->packageManager->getActivePackages() as $package) {
            $extensionKey = $package->getPackageKey();
            $extTablesPath = $package->getPackagePath() . 'ext_tables.php';
            if (@file_exists($extTablesPath)) {
                if (!$package->getPackageMetaData()->isFrameworkType()) {
                    trigger_error(
                        'Loading ext_tables.php of extension "' . $extensionKey . '" has been'
                        . ' deprecated in TYPO3 v14.3 and will be removed in TYPO3 v15.0.'
                        . ' Register backend modules, routes and other TCA-unrelated configurations'
                        . ' in Configuration/Services.yaml or appropriate Configuration/ files instead.',
                        E_USER_DEPRECATED
                    );
                }
                // Include a header per extension to make the cache file more readable
                $phpCodeToCache[] = '/**';
                $phpCodeToCache[] = ' * Extension: ' . $extensionKey;
                $phpCodeToCache[] = ' * File: ' . $extTablesPath;
                $phpCodeToCache[] = ' */';
                // Add ext_tables.php content of extension
                $phpCodeToCache[] = 'namespace {';
                $phpCodeToCache[] = trim((string)file_get_contents($extTablesPath));
                $phpCodeToCache[] = '}';
                $phpCodeToCache[] = '';
                $phpCodeToCache[] = '';
            }
        }
        $phpCodeToCache = implode(LF, $phpCodeToCache);
        // Remove all start and ending php tags from content, and remove strict_types=1 declaration.
        $phpCodeToCache = preg_replace('/<\\?php|\\?>/is', '', $phpCodeToCache);
        $phpCodeToCache = preg_replace('/declare\\s?+\\(\\s?+strict_types\\s?+=\\s?+1\\s?+\\);/is', '', (string)$phpCodeToCache);
        $this->codeCache->set($this->getExtTablesCacheIdentifier(), $phpCodeToCache);
    }

    /**
     * Require ext_tables.php files from extensions
     */
    private function loadSingleExtTablesFiles(): void
    {
        foreach ($this->packageManager->getActivePackages() as $package) {
            $extTablesPath = $package->getPackagePath() . 'ext_tables.php';
            if (file_exists($extTablesPath)) {
                if (!$package->getPackageMetaData()->isFrameworkType()) {
                    trigger_error(
                        'Loading ext_tables.php of extension "' . $package->getPackageKey() . '" has been'
                        . ' deprecated in TYPO3 v14.3 and will be removed in TYPO3 v15.0.'
                        . ' Register backend modules, routes and other TCA-unrelated configurations'
                        . ' in Configuration/Services.yaml or appropriate Configuration/ files instead.',
                        E_USER_DEPRECATED
                    );
                }
                require $extTablesPath;
            }
        }
    }

    /**
     * Cache identifier of concatenated ext_tables file
     */
    private function getExtTablesCacheIdentifier(): string
    {
        return (new PackageDependentCacheIdentifier($this->packageManager))->withPrefix('ext_tables')->toString();
    }
}
