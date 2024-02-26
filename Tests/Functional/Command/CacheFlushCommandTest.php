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

namespace TYPO3\CMS\Core\Tests\Functional\Command;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\DependencyInjection\ContainerBuilder;
use TYPO3\CMS\Core\Package\PackageManager;

final class CacheFlushCommandTest extends AbstractCommandTestCase
{
    protected array $configurationToUseInTestInstance = [
        'SYS' => [
            'caching' => [
                'cacheConfigurations' => [
                    // Set pages cache database backend, testing-framework sets this to NullBackend by default.
                    'pages' => [
                        'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                    ],
                ],
            ],
        ],
    ];

    #[Test]
    public function cachesCanBeFlushed(): void
    {
        $containerBuilder = $this->get(ContainerBuilder::class);
        $packageManager = $this->get(PackageManager::class);
        $diCacheIdentifier = $containerBuilder->getCacheIdentifier($packageManager);
        $diCacheFile = Environment::getVarPath() . '/cache/code/di/' . $diCacheIdentifier . '.php';

        $siteConfiguration = $this->get(SiteConfiguration::class);
        $pageCache = $this->get(CacheManager::class)->getCache('pages');

        // fill cache
        $siteConfiguration->getAllExistingSites();
        $pageCache->set('dummy-page-cache-hash', ['dummy'], [], 0);
        // Reset DI cache timestamp
        touch($diCacheFile, 0);

        self::assertFileExists(Environment::getVarPath() . '/cache/code/core/sites-configuration.php');

        $result = $this->executeConsoleCommand('cache:flush');

        self::assertEquals(0, $result['status']);
        self::assertFileDoesNotExist(Environment::getVarPath() . '/cache/code/core/sites-configuration.php');
        self::assertFalse($pageCache->has('dummy-page-cache-hash'));

        // It's fine that DI cache is available…
        self::assertFileExists($diCacheFile);
        // but it must have been renewed (seem behaviour as in installtool).
        self::assertGreaterThan(0, filemtime($diCacheFile));
    }

    #[Test]
    public function diCachesCanBeFlushed(): void
    {
        $containerBuilder = $this->get(ContainerBuilder::class);
        $packageManager = $this->get(PackageManager::class);
        $diCacheIdentifier = $containerBuilder->getCacheIdentifier($packageManager);
        $diCacheFile = Environment::getVarPath() . '/cache/code/di/' . $diCacheIdentifier . '.php';

        touch($diCacheFile);
        self::assertFileExists($diCacheFile);

        $result = $this->executeConsoleCommand('cache:flush -g di');

        self::assertEquals(0, $result['status']);
        self::assertFileDoesNotExist($diCacheFile);
    }

    #[Test]
    public function systemCachesCanBeFlushed(): void
    {
        $containerBuilder = $this->get(ContainerBuilder::class);
        $packageManager = $this->get(PackageManager::class);
        $diCacheIdentifier = $containerBuilder->getCacheIdentifier($packageManager);

        $siteConfiguration = $this->get(SiteConfiguration::class);
        $pageCache = $this->get(CacheManager::class)->getCache('pages');
        $diCacheFile = Environment::getVarPath() . '/cache/code/di/' . $diCacheIdentifier . '.php';

        // fill cache
        $siteConfiguration->getAllExistingSites();
        $pageCache->set('dummy-page-cache-hash', ['dummy'], [], 0);
        // Reset DI cache timestamp
        touch($diCacheFile, 0);

        self::assertFileExists(Environment::getVarPath() . '/cache/code/core/sites-configuration.php');

        $result = $this->executeConsoleCommand('cache:flush -g system');

        self::assertEquals(0, $result['status']);
        // site caches should have been flushed
        self::assertFileDoesNotExist(Environment::getVarPath() . '/cache/code/core/sites-configuration.php');
        // page caches should stay
        self::assertTrue($pageCache->has('dummy-page-cache-hash'));

        // It's fine that DI cache is available…
        self::assertFileExists($diCacheFile);
        // but it must have been renewed (seem behaviour as in installtool).
        self::assertGreaterThan(0, filemtime($diCacheFile));
    }

    #[Test]
    public function pageCachesCanBeFlushed(): void
    {
        $containerBuilder = $this->get(ContainerBuilder::class);
        $packageManager = $this->get(PackageManager::class);
        $diCacheIdentifier = $containerBuilder->getCacheIdentifier($packageManager);

        $siteConfiguration = $this->get(SiteConfiguration::class);
        $pageCache = $this->get(CacheManager::class)->getCache('pages');
        $diCacheFile = Environment::getVarPath() . '/cache/code/di/' . $diCacheIdentifier . '.php';

        // fill cache
        $siteConfiguration->getAllExistingSites();
        $pageCache->set('dummy-page-cache-hash', ['dummy'], [], 0);
        // Reset DI cache timestamp
        touch($diCacheFile, 0);

        self::assertFileExists(Environment::getVarPath() . '/cache/code/core/sites-configuration.php');

        $result = $this->executeConsoleCommand('cache:flush -g pages');

        self::assertEquals(0, $result['status']);
        // site cache should stay
        self::assertFileExists(Environment::getVarPath() . '/cache/code/core/sites-configuration.php');
        // page caches should have been flushed
        self::assertFalse($pageCache->has('dummy-page-cache-hash'));

        // DI cache must be available…
        self::assertFileExists($diCacheFile);
        // and must not have been renewed
        self::assertEquals(0, filemtime($diCacheFile));
    }
}
