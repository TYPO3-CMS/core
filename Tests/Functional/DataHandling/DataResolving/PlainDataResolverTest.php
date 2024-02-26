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

namespace TYPO3\CMS\Core\Tests\Functional\DataHandling\DataResolving;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\DataHandling\PlainDataResolver;
use TYPO3\CMS\Core\Tests\Functional\DataHandling\AbstractDataHandlerActionTestCase;

final class PlainDataResolverTest extends AbstractDataHandlerActionTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3/sysext/core/Tests/Functional/Fixtures/Extensions/irre_tutorial',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/DataSet/Pages.csv');
    }

    /**
     * entries:
     *  'key' => [
     *      [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], // input
     *      [1, 2, 10, 3, 4, 5, 8, 7, 6, 9], // output (expected output)
     *      ['sorting']                      // sorting criteria
     *  ]
     */
    public static function sortingDataProvider(): array
    {
        return [
            'sorting' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [1, 2, 10, 3, 4, 5, 8, 7, 6, 9],
                ['sorting'],
            ],
            'sorting asc' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [1, 2, 10, 3, 4, 5, 8, 7, 6, 9],
                ['sorting asc'],
            ],
            'sorting desc' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [9, 6, 7, 8, 5, 4, 3, 10, 2, 1],
                ['sorting desc'],
            ],
            'sorting ASC' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [1, 2, 10, 3, 4, 5, 8, 7, 6, 9],
                ['sorting ASC'],
            ],
            'sorting DESC' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [9, 6, 7, 8, 5, 4, 3, 10, 2, 1],
                ['sorting DESC'],
            ],
            'sorting ASC title' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [1, 2, 10, 3, 4, 5, 8, 7, 6, 9],
                ['sorting ASC', 'title'],
            ],
            'sorting ASC title asc' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [1, 2, 10, 3, 4, 5, 8, 7, 6, 9],
                ['sorting ASC', 'title asc'],
            ],
            'sorting ASC title desc' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [1, 2, 10, 3, 4, 5, 8, 7, 6, 9],
                ['sorting ASC', 'title desc'],
            ],
            'sorting ASC title ASC' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [1, 2, 10, 3, 4, 5, 8, 7, 6, 9],
                ['sorting ASC', 'title ASC'],
            ],
            'sorting ASC title DESC' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [1, 2, 10, 3, 4, 5, 8, 7, 6, 9],
                ['sorting ASC', 'title DESC'],
            ],
            'title sorting ASC' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                ['title', 'sorting ASC'],
            ],
            'title asc sorting ASC' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                ['title asc', 'sorting ASC'],
            ],
            'title desc sorting ASC' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [10, 9, 8, 7, 6, 5, 4, 3, 2, 1],
                ['title desc', 'sorting ASC'],
            ],
            'title ASC sorting ASC' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                ['title ASC', 'sorting ASC'],
            ],
            'title DESC sorting ASC' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [10, 9, 8, 7, 6, 5, 4, 3, 2, 1],
                ['title DESC', 'sorting ASC'],
            ],
        ];
    }

    #[DataProvider('sortingDataProvider')]
    #[Test]
    public function processSortingReturnsExpectedSequenceOfUids(array $input, array $expected, array $sortings): void
    {
        $subject = new PlainDataResolver('pages', [], $sortings);
        self::assertSame($expected, $subject->processSorting($input));
    }
}
