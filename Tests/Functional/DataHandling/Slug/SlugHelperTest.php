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

namespace TYPO3\CMS\Core\Tests\Functional\DataHandling\Slug;

use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Tests\Functional\DataHandling\AbstractDataHandlerActionTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Functional test for the SlugHelper
 */
final class SlugHelperTest extends AbstractDataHandlerActionTestCase
{
    /**
     * Default Site Configuration
     * @var array
     */
    protected $siteLanguageConfiguration = [
        1 => [
            'title' => 'Dansk',
            'enabled' => true,
            'languageId' => 1,
            'base' => '/da/',
            'locale' => 'da_DK.UTF-8',
            'flag' => 'dk',
            'fallbackType' => 'fallback',
            'fallbacks' => '0',
        ],
        2 => [
            'title' => 'Deutsch',
            'enabled' => true,
            'languageId' => 2,
            'base' => '/de/',
            'locale' => 'de_DE.UTF-8',
            'flag' => 'de',
            'fallbackType' => 'fallback',
            'fallbacks' => '0',
        ],
        3 => [
            'title' => 'Schweizer Deutsch',
            'enabled' => true,
            'languageId' => 3,
            'base' => '/de-ch/',
            'locale' => 'de_CH.UTF-8',
            'flag' => 'ch',
            'fallbackType' => 'fallback',
            'fallbacks' => '2,0',
        ],
    ];

    protected array $testExtensionsToLoad = [
        'typo3/sysext/core/Tests/Functional/Fixtures/Extensions/irre_tutorial',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/DataSet/Pages.csv');
        $this->setUpFrontendSite(1, $this->siteLanguageConfiguration);
        $this->setUpFrontendRootPage(1, ['typo3/sysext/core/Tests/Functional/Fixtures/Frontend/JsonRenderer.typoscript']);
    }

    /**
     * @test
     */
    public function verifyCleanReferenceIndex(): void
    {
        // The test verifies the imported data set has a clean reference index by the check in tearDown()
        self::assertTrue(true);
    }

    /**
     * DataProvider for testing the language resolving of the parent page.
     * - If the language can be resolved, get the slug of the current language
     * - If not, consecutively try the fallback languages from the site config
     * - As a last resort, fall back to the default language.
     *
     * Example languages:
     * 0 = "Default"
     * 1 = "Dansk" - (Fallback to Default)
     * 2 = "German" - (Fallback to Default)
     * 3 = "Swiss German" - (Fallback to German)
     */
    public static function generateRespectsFallbackLanguageOfParentPageSlugDataProvider(): array
    {
        return [
            'default page / default parent' => [
                '/default-parent/default-page',
                [
                    'uid' => '13',
                    'title' => 'Default Page',
                    'sys_language_uid' => 0,
                ],
            ],
            'Dansk page / default parent' => [
                '/default-parent/dansk-page',
                [
                    'uid' => '13',
                    'title' => 'Dansk Page',
                    'sys_language_uid' => 1,
                ],
            ],
            'german page / german parent' => [
                '/german-parent/german-page',
                [
                    'uid' => '13',
                    'title' => 'German Page',
                    'sys_language_uid' => 2,
                ],
            ],
            'swiss page / german fallback parent' => [
                 '/german-parent/swiss-page',
                 [
                     'uid' => '13',
                     'title' => 'Swiss Page',
                     'sys_language_uid' => 3,
                 ],
             ],
        ];
    }

    /**
     * @dataProvider generateRespectsFallbackLanguageOfParentPageSlugDataProvider
     * @test
     */
    public function generateRespectsFallbackLanguageOfParentPageSlug(string $expected, array $page): void
    {
        $slugHelper = GeneralUtility::makeInstance(
            SlugHelper::class,
            'pages',
            'slug',
            [
                'generatorOptions' => [
                    'fields' => ['title'],
                    'prefixParentPageSlug' => true,
                ],
            ]
        );

        self::assertEquals(
            $expected,
            $slugHelper->generate(
                [
                    'title' => $page['title'],
                    'uid' => $page['uid'],
                    'sys_language_uid' => $page['sys_language_uid'],
                ],
                (int)$page['uid']
            )
        );
    }
}
