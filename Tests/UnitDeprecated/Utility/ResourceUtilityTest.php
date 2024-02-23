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

namespace TYPO3\CMS\Core\Tests\UnitDeprecated\Utility;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\ResourceUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Testcase for class \TYPO3\CMS\Core\Utility\ResourceUtility
 */
final class ResourceUtilityTest extends UnitTestCase
{
    public static function recursiveFileListSortingHelperTestDataProvider(): array
    {
        return [
            'normal file list' => [
                ['fileB', 'fileA', 'someFile'],
                ['fileA', 'fileB', 'someFile'],
            ],
            'already in correct order' => [
                ['fileA', 'fileB', 'someFile'],
                ['fileA', 'fileB', 'someFile'],
            ],
            'hidden file' => [
                ['someFile', '.hiddenFile'],
                ['.hiddenFile', 'someFile'],
            ],
            'mixed capitalization' => [
                ['alllower', 'allCAPS', 'ALLcaps', 'mIxedinanotherway', 'ALLCAPS', 'MiXeDcApItAlIzAtIoN'],
                ['ALLCAPS', 'ALLcaps', 'allCAPS', 'alllower', 'MiXeDcApItAlIzAtIoN', 'mIxedinanotherway'],
            ],
            'mixed capitalization reversed' => [
                ['MiXeDcApItAlIzAtIoN', 'mIxedinanotherway', 'ALLcaps', 'allCAPS', 'ALLCAPS', 'alllower'],
                ['ALLCAPS', 'ALLcaps', 'allCAPS', 'alllower', 'MiXeDcApItAlIzAtIoN', 'mIxedinanotherway'],
            ],
            'recursive list with one sublevel' => [
                ['fileA', 'fileB', 'anotherDir/someFile', 'someDir/someFile', 'anotherDir/anotherFile'],
                ['anotherDir/anotherFile', 'anotherDir/someFile', 'someDir/someFile', 'fileA', 'fileB'],
            ],
            'recursive list with two sub-levels' => [
                ['file', 'someDir/someFile', 'someDir/subdir/file', 'someDir/subdir/somefile', 'someDir/anotherDir/somefile', 'anotherDir/someFile'],
                ['anotherDir/someFile', 'someDir/anotherDir/somefile', 'someDir/subdir/file', 'someDir/subdir/somefile', 'someDir/someFile', 'file'],
            ],
            'recursive list with three sub-levels' => [
                ['someDir/someSubdir/file', 'someDir/someSubdir/someSubsubdir/someFile', 'someDir/someSubdir/someSubsubdir/anotherFile'],
                ['someDir/someSubdir/someSubsubdir/anotherFile', 'someDir/someSubdir/someSubsubdir/someFile', 'someDir/someSubdir/file'],
            ],
        ];
    }

    #[DataProvider('recursiveFileListSortingHelperTestDataProvider')]
    #[Test]
    public function recursiveFileListSortingHelperCorrectlySorts(array $unsortedList, array $expectedList): void
    {
        $result = $unsortedList;
        usort(
            $result,
            [ResourceUtility::class, 'recursiveFileListSortingHelper']
        );
        self::assertSame($expectedList, $result);
    }
}
