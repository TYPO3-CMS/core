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

namespace TYPO3\CMS\Core\Tests\Unit\Site\Entity;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class SiteLanguageTest extends UnitTestCase
{
    public static function languageFallbackIdConversionDataProvider(): array
    {
        return [
            'no fallback set' => [
                null,
                [],
            ],
            'fallback given as empty string returns no fallback' => [
                '',
                [],
            ],
            'fallback to default language as string returns proper fallback' => [
                '0',
                [0],
            ],
            'fallback to multiple languages as string returns proper fallback' => [
                '3,0',
                [3, 0],
            ],
            'fallback to default language as array returns proper fallback' => [
                ['0'],
                [0],
            ],
            'fallback to multiple languages as array returns proper fallback' => [
                ['3', '0'],
                [3, 0],
            ],
            'fallback to multiple languages as array with integers returns proper fallback' => [
                [3, 0],
                [3, 0],
            ],

        ];
    }

    /**
     * @param string|array|null $input
     */
    #[DataProvider('languageFallbackIdConversionDataProvider')]
    #[Test]
    public function languageFallbackIdConversion($input, array $expected): void
    {
        $configuration = [
            'fallbacks' => $input,
            'locale' => 'fr',
        ];
        $site = $this->createSiteWithLanguage($configuration);
        $subject = $site->getLanguageById(1);
        self::assertSame($expected, $subject->getFallbackLanguageIds());
    }

    #[Test]
    public function toArrayReturnsProperOverlaidData(): void
    {
        $configuration = [
            'navigationTitle' => 'NavTitle',
            'customValue' => 'a custom value',
            'fallbacks' => '1,2',
            'locale' => 'de',
        ];
        $site = $this->createSiteWithLanguage($configuration);
        $subject = $site->getLanguageById(1);
        $expected = [
            'navigationTitle' => 'NavTitle',
            'customValue' => 'a custom value',
            'fallbacks' => '1,2',
            'locale' => 'de',
            'languageId' => 1,
            'base' => '/',
            'title' => 'Default',
            'websiteTitle' => '',
            'twoLetterIsoCode' => 'de',
            'hreflang' => 'de',
            'direction' => '',
            'typo3Language' => 'de',
            'flagIdentifier' => '',
            'fallbackType' => 'strict',
            'enabled' => true,
            'fallbackLanguageIds' => [
                1,
                2,
            ],
        ];
        self::assertSame($expected, $subject->toArray());
    }

    public static function typo3LanguageAndLocaleDataProvider(): \Generator
    {
        yield 'Defined "default" configuration' => [
            'en-US',
            [
                'typo3Language' => 'default',
            ],
            'default',
        ];
        yield 'Undefined "default" configuration' => [
            'en-US',
            [],
            'en_US',
        ];
        yield 'Undefined "default" configuration with POSIX' => [
            'C',
            [],
            'default',
        ];
        yield 'Defined language + country combination' => [
            'de-AT',
            [
                'typo3Language' => 'de_AT',
            ],
            'de_AT',
        ];
        yield 'Undefined language + country combination' => [
            'de-AT',
            [],
            'de_AT',
        ];
        yield 'Different language + country combination' => [
            'de-AT',
            [
                'typo3Language' => 'de',
            ],
            'de',
        ];
    }

    #[DataProvider('typo3LanguageAndLocaleDataProvider')]
    #[Test]
    public function typo3LanguageIsEitherSetOrProperlyDerivedFromLocale(string $locale, array $configuration, $expected): void
    {
        $language = new SiteLanguage(0, $locale, new Uri('/'), $configuration);
        self::assertSame($expected, $language->getTypo3Language());
    }

    private function createSiteWithLanguage(array $languageConfiguration): Site
    {
        return new Site('test', 1, [
            'identifier' => 'test',
            'rootPageId' => 1,
            'base' => '/',
            'languages' => [
                array_merge(
                    $languageConfiguration,
                    [
                        'languageId' => 1,
                        'base' => '/',
                    ]
                ),
            ],
        ]);
    }
}
