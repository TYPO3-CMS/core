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

namespace TYPO3\CMS\Core\Tests\Functional\TypoScript;

use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use TYPO3\CMS\Core\TypoScript\PageTsConfigFactory;
use TYPO3\CMS\Core\TypoScript\UserTsConfigFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Tests PageTsConfigFactory and indirectly IncludeTree/TsConfigTreeBuilder
 */
final class PageTsConfigFactoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3/sysext/core/Tests/Functional/Fixtures/Extensions/test_typoscript_pagetsconfigfactory',
    ];

    /**
     * @test
     */
    public function pageTsConfigLoadsDefaultsFromGlobals(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'] = 'loadedFromGlobals = loadedFromGlobals';
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create([], new NullSite());
        self::assertSame('loadedFromGlobals', $pageTsConfig->getPageTsConfigArray()['loadedFromGlobals']);
    }

    /**
     * @test
     */
    public function pageTsConfigLoadsSingleFileWithOldImportSyntaxFromGlobals(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'] = '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:test_typoscript_pagetsconfigfactory/Configuration/TsConfig/tsconfig-includes.tsconfig">';
        /** @var PageTsConfigFactory $subject */
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create([], new NullSite());
        self::assertSame('loadedFromTsconfigIncludesWithTsconfigSuffix', $pageTsConfig->getPageTsConfigArray()['loadedFromTsconfigIncludesWithTsconfigSuffix']);
    }

    /**
     * @test
     */
    public function pageTsConfigLoadsFromPagesTsconfigTestExtensionConfigurationFile(): void
    {
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create([], new NullSite());
        self::assertSame('loadedFromTestExtensionConfigurationPageTsConfig', $pageTsConfig->getPageTsConfigArray()['loadedFromTestExtensionConfigurationPageTsConfig']);
    }

    /**
     * @test
     */
    public function pageTsConfigLoadsFromPageRecordTsconfigField(): void
    {
        $rootLine = [
            [
                'uid' => 1,
                'TSconfig' => 'loadedFromTsConfigField = loadedFromTsConfigField',
            ],
        ];
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create($rootLine, new NullSite());
        self::assertSame('loadedFromTsConfigField', $pageTsConfig->getPageTsConfigArray()['loadedFromTsConfigField']);
    }

    /**
     * @test
     */
    public function pageTsConfigLoadsFromWildcardAtImportWithTsconfigSuffix(): void
    {
        $rootLine = [
            [
                'uid' => 1,
                'TSconfig' => '@import \'EXT:test_typoscript_pagetsconfigfactory/Configuration/TsConfig/*.tsconfig\'',
            ],
        ];
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create($rootLine, new NullSite());
        self::assertSame('loadedFromTsconfigIncludesWithTsconfigSuffix', $pageTsConfig->getPageTsConfigArray()['loadedFromTsconfigIncludesWithTsconfigSuffix']);
    }

    /**
     * @test
     */
    public function pageTsConfigLoadsFromWildcardAtImportWithTypoScriptSuffix(): void
    {
        $rootLine = [
            [
                'uid' => 1,
                'TSconfig' => '@import \'EXT:test_typoscript_pagetsconfigfactory/Configuration/TsConfig/*.typoscript\'',
            ],
        ];
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create($rootLine, new NullSite());
        self::assertSame('loadedFromTsconfigIncludesWithTyposcriptSuffix', $pageTsConfig->getPageTsConfigArray()['loadedFromTsconfigIncludesWithTyposcriptSuffix']);
    }

    /**
     * @test
     */
    public function pageTsConfigLoadsSingleFileWithOldImportSyntax(): void
    {
        $rootLine = [
            [
                'uid' => 1,
                'TSconfig' => '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:test_typoscript_pagetsconfigfactory/Configuration/TsConfig/tsconfig-includes.tsconfig">',
            ],
        ];
        /** @var PageTsConfigFactory $subject */
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create($rootLine, new NullSite());
        self::assertSame('loadedFromTsconfigIncludesWithTsconfigSuffix', $pageTsConfig->getPageTsConfigArray()['loadedFromTsconfigIncludesWithTsconfigSuffix']);
    }

    /**
     * @test
     */
    public function pageTsConfigLoadsFromPageRecordTsconfigFieldOverridesByLowerLevel(): void
    {
        $rootLine = [
            [
                'uid' => 1,
                'TSconfig' => 'loadedFromTsConfigField1 = loadedFromTsConfigField1'
                    . chr(10) . 'loadedFromTsConfigField2 = loadedFromTsConfigField2',
            ],
            [
                'uid' => 2,
                'TSconfig' => 'loadedFromTsConfigField1 = loadedFromTsConfigField1'
                    . chr(10) . 'loadedFromTsConfigField2 = loadedFromTsConfigField2Override',
            ],
        ];
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create($rootLine, new NullSite());
        self::assertSame('loadedFromTsConfigField1', $pageTsConfig->getPageTsConfigArray()['loadedFromTsConfigField1']);
        self::assertSame('loadedFromTsConfigField2Override', $pageTsConfig->getPageTsConfigArray()['loadedFromTsConfigField2']);
    }

    /**
     * @test
     */
    public function pageTsConfigSubstitutesSettingsFromSite(): void
    {
        $rootLine = [
            [
                'uid' => 1,
                'TSconfig' => 'siteSetting = {$aSiteSetting}',
            ],
        ];
        $subject = $this->get(PageTsConfigFactory::class);
        $siteSettings = new SiteSettings(['aSiteSetting' => 'aSiteSettingValue']);
        $site = new Site('siteIdentifier', 1, [], $siteSettings);
        $pageTsConfig = $subject->create($rootLine, $site);
        self::assertSame('aSiteSettingValue', $pageTsConfig->getPageTsConfigArray()['siteSetting']);
    }

    /**
     * @test
     */
    public function pageTsConfigLoadsFromEvent(): void
    {
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create([], new NullSite());
        self::assertSame('loadedFromEvent', $pageTsConfig->getPageTsConfigArray()['loadedFromEvent']);
    }

    /**
     * @test
     */
    public function pageTsConfigLoadsFromLegacyEvent(): void
    {
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create([], new NullSite());
        self::assertSame('loadedFromLegacyEvent', $pageTsConfig->getPageTsConfigArray()['loadedFromLegacyEvent']);
    }

    /**
     * @test
     */
    public function pageTsConfigCanBeOverloadedWithUserTsConfig(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/pageTsConfigTestFixture.csv');
        $backendUser = $this->setUpBackendUser(1);
        $userTsConfigFactory = $this->get(UserTsConfigFactory::class);
        $userTsConfig = $userTsConfigFactory->create($backendUser);
        $rootLine = [
            [
                'uid' => 1,
                'TSconfig' => 'valueOverriddenByUserTsConfig = base',
            ],
        ];
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create($rootLine, new NullSite(), $userTsConfig);
        self::assertSame('overridden', $pageTsConfig->getPageTsConfigArray()['valueOverriddenByUserTsConfig']);
    }
}
