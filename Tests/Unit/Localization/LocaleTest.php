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

namespace TYPO3\CMS\Core\Tests\Unit\Localization;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Localization\Locale;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LocaleTest extends UnitTestCase
{
    #[Test]
    public function localeWithJustLanguageCodeSanitizesIncomingValuesProperly(): void
    {
        $subject = new Locale('en');
        self::assertNull($subject->getLanguageScriptCode());
        self::assertNull($subject->getCountryCode());
        self::assertEquals('en', $subject->getLanguageCode());
        self::assertEquals('en', (string)$subject);

        $subject = new Locale('C');
        self::assertNull($subject->getLanguageScriptCode());
        self::assertNull($subject->getCountryCode());
        self::assertEquals('en', $subject->getLanguageCode());
        self::assertEquals('C', $subject->getPosixCodeSet());
        self::assertEquals('C', $subject->posixFormatted());
        self::assertEquals('en', (string)$subject);

        $subject = new Locale('de_DE.UTF-8');
        self::assertNull($subject->getLanguageScriptCode());
        self::assertEquals('DE', $subject->getCountryCode());
        self::assertEquals('de', $subject->getLanguageCode());
        self::assertEquals('de-DE', (string)$subject);
        self::assertEquals('de_DE.UTF-8', $subject->posixFormatted());

        $subject = new Locale('de_DE@euro');
        self::assertNull($subject->getLanguageScriptCode());
        self::assertEquals('DE', $subject->getCountryCode());
        self::assertEquals('de', $subject->getLanguageCode());
        self::assertEquals('de-DE', (string)$subject);
        self::assertEquals('de_DE@euro', $subject->posixFormatted());

        // Also with mixed case
        $subject = new Locale('eN');
        self::assertNull($subject->getLanguageScriptCode());
        self::assertNull($subject->getCountryCode());
        self::assertEquals('en', $subject->getLanguageCode());
        self::assertEquals('en', (string)$subject);
    }

    #[Test]
    public function localeWithLanguageAndScriptCodeSanitizesIncomingValuesProperly(): void
    {
        $subject = new Locale('zh_HANS');
        self::assertEquals('Hans', $subject->getLanguageScriptCode());
        self::assertNull($subject->getCountryCode());
        self::assertEquals('zh', $subject->getLanguageCode());
        self::assertEquals('zh-Hans', (string)$subject);
    }

    #[Test]
    public function localeWithLanguageAndScriptCodeAndCountryCodeSanitizesIncomingValuesProperly(): void
    {
        $subject = new Locale('zh_HANS_CN');
        self::assertEquals('Hans', $subject->getLanguageScriptCode());
        self::assertEquals('CN', $subject->getCountryCode());
        self::assertEquals('zh', $subject->getLanguageCode());
        self::assertEquals('zh-Hans-CN', (string)$subject);
    }

    #[Test]
    public function variousCombinationsOfLanguageAndCountryCodeReturnsSanitizedValues(): void
    {
        $subject = new Locale('fr_CA');
        self::assertNull($subject->getLanguageScriptCode());
        self::assertEquals('CA', $subject->getCountryCode());
        self::assertEquals('fr', $subject->getLanguageCode());
        self::assertEquals('fr-CA', (string)$subject);
        $subject = new Locale('de-AT');
        self::assertNull($subject->getLanguageScriptCode());
        self::assertEquals('AT', $subject->getCountryCode());
        self::assertEquals('de', $subject->getLanguageCode());
        self::assertEquals('de-AT', (string)$subject);
    }

    #[Test]
    public function dependenciesAreSetAndRetrievedCorrectly(): void
    {
        $subject = new Locale('fr_CA', ['fr', 'en']);
        self::assertNull($subject->getLanguageScriptCode());
        self::assertEquals('CA', $subject->getCountryCode());
        self::assertEquals('fr', $subject->getLanguageCode());
        self::assertEquals(['fr', 'en'], $subject->getDependencies());
        self::assertEquals('fr-CA', (string)$subject);
        $subject = new Locale('en-US', ['en-UK', 'en']);
        self::assertNull($subject->getLanguageScriptCode());
        self::assertEquals('US', $subject->getCountryCode());
        self::assertEquals('en', $subject->getLanguageCode());
        self::assertEquals(['en-UK', 'en'], $subject->getDependencies());
        self::assertEquals('en-US', (string)$subject);
    }
}
