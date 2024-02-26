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

namespace TYPO3\CMS\Core\Tests\Functional\Resource\Security;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Resource\Security\SvgSanitizer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class SvgSanitizerTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    /**
     * @return array<string, string[]>
     */
    public static function svgContentIsSanitizedDataProvider(): array
    {
        $basePath = dirname(__FILE__, 2) . '/Fixtures/';
        $finder = new Finder();
        $finder
            ->files()
            ->in($basePath . 'DirtySVG/')
            ->name('*.svg');
        $data = [];
        foreach ($finder as $file) {
            $fileName = $file->getFilename();
            $data[$fileName] = [
                $basePath . 'DirtySVG/' . $fileName,
                $basePath . 'CleanSVG/' . $fileName,
            ];
        }
        return $data;
    }

    #[DataProvider('svgContentIsSanitizedDataProvider')]
    #[Test]
    public function svgContentIsSanitized(string $filePath, string $sanitizedFilePath): void
    {
        $sanitizer = new SvgSanitizer();
        self::assertStringEqualsFile(
            $sanitizedFilePath,
            $sanitizer->sanitizeContent(file_get_contents($filePath))
        );
    }
}
