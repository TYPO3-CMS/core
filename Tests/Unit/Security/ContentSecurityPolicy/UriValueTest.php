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

namespace TYPO3\CMS\Core\Tests\Unit\Security\ContentSecurityPolicy;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\UriValue;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class UriValueTest extends UnitTestCase
{
    public static function uriIsParsedAndSerializedDataProvider(): \Generator
    {
        yield ['https://www.typo3.org/uri/path.html?key=value#fragment', 'https://www.typo3.org/uri/path.html?key=value'];
        yield ['//www.typo3.org/uri/path.html?key=value#fragment', '//www.typo3.org/uri/path.html?key=value'];
        yield ['https://www.typo3.org#fragment', 'https://www.typo3.org'];
        yield ['//www.typo3.org#fragment', '//www.typo3.org'];
        yield ['https://*.typo3.org#fragment', 'https://*.typo3.org'];
        yield ['//*.typo3.org#fragment', '//*.typo3.org'];
        yield ['www.typo3.org#fragment', 'www.typo3.org'];
        yield ['*.typo3.org#fragment', '*.typo3.org'];

        yield ['https://www.typo3.org/uri/path.html?key=value'];
        yield ['https://www.typo3.org'];
        yield ['https://*.typo3.org'];
        yield ['//www.typo3.org/uri/path.html?key=value'];
        yield ['//www.typo3.org'];
        yield ['www.typo3.org'];
        yield ['*.typo3.org'];
        yield ['*'];

        // expected behavior, falls back to upstream parser´
        // (since e.g. query-param is given, which is not expected here in the scope of CSP with `UriValue`)
        yield ['www.typo3.org?key=value', '/www.typo3.org?key=value'];
        yield ['*.typo3.org?key=value', '/%2A.typo3.org?key=value'];
    }

    #[DataProvider('uriIsParsedAndSerializedDataProvider')]
    #[Test]
    public function uriIsParsedAndSerialized(string $value, ?string $expectation = null): void
    {
        $uri = new UriValue($value);
        self::assertSame($expectation ?? $value, (string)$uri);
    }

    public static function urisAreEqualDataProvider(): \Generator
    {
        yield ['https://example.com/path/file.js', 'https://example.com/path/file.js', true];
        yield ['https://example.com/path/file.js', 'https://example.com/path/file.css', false];
        yield ['https://*.example.com', 'https://*.example.com', true];
        yield ['example.com/path', 'example.com/path', true];
        yield ['example.com/path', 'example.com/other', false];
        yield ['*.example.com', '*.example.com', true];
    }

    #[DataProvider('urisAreEqualDataProvider')]
    #[Test]
    public function urisAreEqual(string $a, string $b, bool $expectation): void
    {
        self::assertSame($expectation, (new UriValue($a))->equals(new UriValue($b)));
    }

    public static function uriIsCoveredDataProvider(): \Generator
    {
        yield ['https://example.com/path/file.js', 'https://example.com/path/file.js', true];
        yield ['https://example.com/path/file.js', 'https://example.com/path/file.css', false];
        yield ['example.com/path', 'example.com/path', true];
        yield ['example.com/path', 'example.com/other', false];
        yield ['*.example.com', '*.example.com', true];
        yield ['*', '*.example.com', true];

        yield ['https://example.com/', 'https://example.com/path/file.css', true];
        yield ['example.com', 'https://example.com/path/file.css', true];
        yield ['https://*.example.com', 'https://sub.example.com/path/file.css', true];
        yield ['*.example.com', 'https://sub.example.com/path/file.css', true];
        yield ['*.example.com', 'sub.example.com', true];
        yield ['*.example.com', '*.sub.example.com', true];
        yield ['sub.example.com', 'example.com', false];
        yield ['*.sub.example.com', 'example.com', false];
        yield ['sub.example.com', '*.example.com', false];
        yield ['*.sub.example.com', '*.example.com', false];
        yield ['*.example.com', '*', false];
    }

    #[DataProvider('uriIsCoveredDataProvider')]
    #[Test]
    public function uriIsCovered(string $a, string $b, bool $expectation): void
    {
        self::assertSame($expectation, (new UriValue($a))->covers(new UriValue($b)));
    }
}
