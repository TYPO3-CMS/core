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

namespace TYPO3\CMS\Core\Tests\Functional\Html;

use TYPO3\CMS\Core\Html\DefaultSanitizerBuilder;
use TYPO3\CMS\Core\Html\SanitizerBuilderFactory;
use TYPO3\CMS\Core\Tests\Functional\Html\Fixtures\ExtendedSanitizerBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\HtmlSanitizer\Behavior;
use TYPO3\HtmlSanitizer\Sanitizer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class DefaultSanitizerBuilderTest extends FunctionalTestCase
{
    /**
     * @var bool Speed up this test case, it needs no database
     */
    protected $initializeDatabase = false;

    public static function isSanitizedDataProvider(): array
    {
        return [
            '#010' => [
                '<unknown unknown="unknown">value</unknown>',
                '&lt;unknown unknown="unknown"&gt;value&lt;/unknown&gt;',
            ],
            '#011' => [
                '<div class="nested"><unknown unknown="unknown">value</unknown></div>',
                '<div class="nested">&lt;unknown unknown="unknown"&gt;value&lt;/unknown&gt;</div>',
            ],
            '#012' => [
                '&lt;script&gt;alert(1)&lt;/script&gt;',
                '&lt;script&gt;alert(1)&lt;/script&gt;',
            ],
            // @todo bug in https://github.com/Masterminds/html5-php/issues
            // '#013' => [
            //    '<strong>Given that x < y and y > z...</strong>',
            //    '<strong>Given that x &lt; y and y &gt; z...</strong>',
            // ],
            '#020' => [
                '<div unknown="unknown">value</div>',
                '<div>value</div>',
            ],
            '#030' => [
                '<div class="class">value</div>',
                '<div class="class">value</div>',
            ],
            '#031' => [
                '<div data-value="value">value</div>',
                '<div data-value="value">value</div>',
            ],
            '#032' => [
                '<div data-bool>value</div>',
                '<div data-bool>value</div>',
            ],
            '#040' => [
                '<img src="mailto:noreply@typo3.org" onerror="alert(1)">',
                '',
            ],
            '#041' => [
                '<img src="https://typo3.org/logo.svg" onerror="alert(1)">',
                '<img src="https://typo3.org/logo.svg">',
            ],
            '#042' => [
                '<img src="http://typo3.org/logo.svg" onerror="alert(1)">',
                '<img src="http://typo3.org/logo.svg">',
            ],
            '#043' => [
                '<img src="/typo3.org/logo.svg" onerror="alert(1)">',
                '<img src="/typo3.org/logo.svg">',
            ],
            '#044' => [
                '<img src="typo3.org/logo.svg" onerror="alert(1)">',
                '<img src="typo3.org/logo.svg">',
            ],
            '#045' => [
                '<img src="//typo3.org/logo.svg" onerror="alert(1)">',
                '',
            ],
            '#050' => [
                '<a href="https://typo3.org/" role="button">value</a>',
                '<a href="https://typo3.org/" role="button">value</a>',
            ],
            '#051' => [
                '<a href="ssh://example.org/" role="button">value</a>',
                '<a role="button">value</a>',
            ],
            '#052' => [
                '<a href="javascript:alert(1)" role="button">value</a>',
                '<a role="button">value</a>',
            ],
            '#053' => [
                '<a href="data:text/html;..." role="button">value</a>',
                '<a role="button">value</a>',
            ],
            '#054' => [
                '<a href="t3://page?uid=1" role="button">value</a>',
                '<a href="t3://page?uid=1" role="button">value</a>',
            ],
            '#055' => [
                '<a href="tel:123456789" role="button">value</a>',
                '<a href="tel:123456789" role="button">value</a>',
            ],
            '#056' => [
                // config.spamProtectEmailAddresses = [n]
                '<a href="javascript:linkTo_UnCryptMailto(%27ocknvq%2CkphqBrtczku%5C%2Fmkghgt0fg%27);">email(at)domain.tld</a>',
                '<a href="javascript:linkTo_UnCryptMailto(%27ocknvq%2CkphqBrtczku%5C%2Fmkghgt0fg%27);">email(at)domain.tld</a>',
            ],
            '#057' => [
                // config.spamProtectEmailAddresses = ascii
                '<a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;&#115;&#111;&#109;&#101;&#46;&#98;&#111;&#100;&#121;&#64;&#116;&#101;&#115;&#116;&#46;&#116;&#121;&#112;&#111;&#51;&#46;&#111;&#114;&#103;">some.body(at)test.typo3(dot)org</a>',
                // HTML entity encoding is not really a "protection", `Masterminds/html5-php` per default
                // decodes those entities, which is good to have normalized attr values
                '<a href="mailto:some.body@test.typo3.org">some.body(at)test.typo3(dot)org</a>',
            ],
            '#058' => [
                // `... onclick="openPic(...)"` used in ContentObjectRenderer and AbstractMenuContentObject
                '<a href="/" target="FEopenLink" onclick="openPic(\'\/\',\'FEopenLink\',\'width=200,height=300\');return false;">Link</a>',
                '<a href="/" target="FEopenLink" onclick="openPic(\'\/\',\'FEopenLink\',\'width=200,height=300\');return false;">Link</a>'
            ],
            '#059' => [
                // `... onclick="openPic(...)"` used in ContentObjectRenderer and AbstractMenuContentObject
                '<a href="/index.php?eID=tx_cms_showpic" onclick="openPic(\'\/index.php?eID=tx_cms_showpic\u0026file=77\u0026md5=45a4b6287f68a61cf617a470e853d857461bc1d2\u0026parameters%5B0%5D=W10%3D\',\'thePicture\',\'width=1200,height=1799,status=0,menubar=0,=\'); return false;" target="thePicture"><img src="/logo.png"></a>',
                '<a href="/index.php?eID=tx_cms_showpic" onclick="openPic(\'\/index.php?eID=tx_cms_showpic\u0026file=77\u0026md5=45a4b6287f68a61cf617a470e853d857461bc1d2\u0026parameters%5B0%5D=W10%3D\',\'thePicture\',\'width=1200,height=1799,status=0,menubar=0,=\'); return false;" target="thePicture"><img src="/logo.png"></a>'
            ],
            '#090' => [
                '<p data-bool><span data-bool><strong data-bool>value</strong></span></p>',
                '<p data-bool><span data-bool><strong data-bool>value</strong></span></p>'
            ],
            // @todo `style` used in Introduction Package, inline CSS should be removed
            '#810' => [
                '<span style="color: orange">value</span>',
                '<span style="color: orange">value</span>',
            ],
        ];
    }

    /**
     * @param string $payload
     * @param string $expectation
     * @test
     * @dataProvider isSanitizedDataProvider
     */
    public function isSanitized(string $payload, string $expectation): void
    {
        $factory = new SanitizerBuilderFactory();
        $builder = $factory->build('default');
        $sanitizer = $builder->build();
        self::assertSame($expectation, $sanitizer->sanitize($payload));
    }

    /**
     * @test
     */
    public function behaviorIsCachedInMemory(): void
    {
        $default = GeneralUtility::makeInstance(DefaultSanitizerBuilder::class);
        $defaultSanitizer = $default->build();
        $defaultBehavior = $this->resolveBehaviorFromSanitizer($defaultSanitizer);

        self::assertSame(
            $defaultBehavior,
            $this->resolveBehaviorFromSanitizer($default->build()),
            'in-memory caching failed for same scope DefaultSanitizerBuilder'
        );

        $extended = GeneralUtility::makeInstance(ExtendedSanitizerBuilder::class);
        $extendedSanitizer = $extended->build();
        $extendedBehavior = $this->resolveBehaviorFromSanitizer($extendedSanitizer);

        self::assertSame(
            $extendedBehavior,
            $this->resolveBehaviorFromSanitizer($extended->build()),
            'in-memory caching failed for same scope ExtendedSanitizerBuilder'
        );

        self::assertNotSame(
            $defaultBehavior,
            $extendedBehavior,
            'in-memory cache violation for different scopes'
        );
    }

    private function resolveBehaviorFromSanitizer(Sanitizer $sanitizer): Behavior
    {
        $visitorsProp = (new \ReflectionObject($sanitizer))->getProperty('visitors');
        $visitorsProp->setAccessible(true);
        $visitor = $visitorsProp->getValue($sanitizer)[0];

        $behaviorProp = (new \ReflectionObject($visitor))->getProperty('behavior');
        $behaviorProp->setAccessible(true);
        return $behaviorProp->getValue($visitor);
    }
}
