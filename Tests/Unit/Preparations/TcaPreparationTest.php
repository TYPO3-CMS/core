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

namespace TYPO3\CMS\Core\Tests\Unit\Preparations;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Preparations\TcaPreparation;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class TcaPreparationTest extends UnitTestCase
{
    #[DataProvider('configureCategoryRelationsDataProvider')]
    #[Test]
    public function configureCategoryRelations(array $input, array $expected): void
    {
        self::assertEquals($expected, (new TcaPreparation())->prepare($input));
    }

    public static function configureCategoryRelationsDataProvider(): \Generator
    {
        yield 'No category field' => [
            [
                'aTable' => [
                    'columns' => [
                        'foo' => [
                            'config' => [
                                'type' => 'select',
                                'foreign_table' => 'sys_category',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'aTable' => [
                    'columns' => [
                        'foo' => [
                            'config' => [
                                'type' => 'select',
                                'foreign_table' => 'sys_category',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        yield 'category field without relationship given (falls back to manyToMany)' => [
            [
                'aTable' => [
                    'columns' => [
                        'aField' => [
                            'config' => [
                                'type' => 'category',
                                'minitems' => 1,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'aTable' => [
                    'columns' => [
                        'aField' => [
                            'config' => [
                                'type' => 'category',
                                'minitems' => 1,
                                'size' => 20,
                                'default' => 0,
                                'foreign_table' => 'sys_category',
                                'foreign_table_where' => ' AND {#sys_category}.{#sys_language_uid} IN (-1, 0)',
                                'relationship' => 'manyToMany',
                                'maxitems' => 99999,
                                'MM' => 'sys_category_record_mm',
                                'MM_opposite_field' => 'items',
                                'MM_match_fields' => [
                                    'tablenames' => 'aTable',
                                    'fieldname' => 'aField',
                                ],
                            ],
                            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.categories',
                            'exclude' => true,
                        ],
                    ],
                ],
                'sys_category' => [
                    'columns' => [
                        'items' => [
                            'config' => [
                                'MM_oppositeUsage' => [
                                    'aTable' => [
                                        'aField',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        yield 'category field with oneToOne relationship and custom foreign_table_* options' => [
            [
                'aTable' => [
                    'columns' => [
                        'aField' => [
                            'config' => [
                                'type' => 'category',
                                'foreign_table' => 'some_table',
                                'foreign_table_where' => ' AND sys_category.pid IN (###PAGE_TSCONFIG_IDLIST###)',
                                'relationship' => 'oneToOne',
                                'minitems' => 1,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'aTable' => [
                    'columns' => [
                        'aField' => [
                            'config' => [
                                'type' => 'category',
                                'relationship' => 'oneToOne',
                                'minitems' => 1,
                                'size' => 20,
                                'default' => 0,
                                'foreign_table' => 'sys_category',
                                'foreign_table_where' => ' AND sys_category.pid IN (###PAGE_TSCONFIG_IDLIST###)',
                                'maxitems' => 1,
                            ],
                            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.categories',
                        ],
                    ],
                ],
            ],
        ];
        yield 'categoryField with oneToMany relationship' => [
            [
                'aTable' => [
                    'columns' => [
                        'aField' => [
                            'config' => [
                                'type' => 'category',
                                'relationship' => 'oneToMany',
                                'size' => 123,
                                'maxitems' => 0,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'aTable' => [
                    'columns' => [
                        'aField' => [
                            'config' => [
                                'type' => 'category',
                                'relationship' => 'oneToMany',
                                'size' => 123,
                                'foreign_table' => 'sys_category',
                                'foreign_table_where' => ' AND {#sys_category}.{#sys_language_uid} IN (-1, 0)',
                                'maxitems' => 99999,
                            ],
                            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.categories',
                        ],
                    ],
                ],
            ],
        ];
        yield 'categoryField with manyToMany relationship' => [
            [
                'aTable' => [
                    'columns' => [
                        'aField' => [
                            'exclude' => false,
                            'config' => [
                                'type' => 'category',
                                'relationship' => 'manyToMany',
                                'default' => 123,
                                'maxitems' => 123,
                                'foreign_table' => 'will_be_overwritten',
                                'MM' => 'will_be_overwritten',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'aTable' => [
                    'columns' => [
                        'aField' => [
                            'config' => [
                                'type' => 'category',
                                'relationship' => 'manyToMany',
                                'size' => 20,
                                'default' => 123,
                                'foreign_table' => 'sys_category',
                                'foreign_table_where' => ' AND {#sys_category}.{#sys_language_uid} IN (-1, 0)',
                                'maxitems' => 123,
                                'MM' => 'sys_category_record_mm',
                                'MM_opposite_field' => 'items',
                                'MM_match_fields' => [
                                    'tablenames' => 'aTable',
                                    'fieldname' => 'aField',
                                ],
                            ],
                            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.categories',
                            'exclude' => false,
                        ],
                    ],
                ],
                'sys_category' => [
                    'columns' => [
                        'items' => [
                            'config' => [
                                'MM_oppositeUsage' => [
                                    'aTable' => [
                                        'aField',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('configureCategoryRelationsThrowsExceptionOnInvalidMaxitemsDataProvider')]
    #[Test]
    public function configureCategoryRelationsThrowsExceptionOnInvalidMaxitems(array $input, int $exceptionCode): void
    {
        $this->expectExceptionCode($exceptionCode);
        $this->expectException(\RuntimeException::class);
        (new TcaPreparation())->prepare($input);
    }

    public static function configureCategoryRelationsThrowsExceptionOnInvalidMaxitemsDataProvider(): \Generator
    {
        yield 'No relationship with maxitems=1 (falls back to manyToMany)' => [
            [
                'aTable' => [
                    'columns' => [
                        'foo' => [
                            'config' => [
                                'type' => 'category',
                                'maxitems' => 1,
                            ],
                        ],
                    ],
                ],
            ],
            1627335017,
        ];
        yield 'oneToOne relationship with maxitems=2' => [
            [
                'aTable' => [
                    'columns' => [
                        'foo' => [
                            'config' => [
                                'type' => 'category',
                                'relationship' => 'oneToOne',
                                'maxitems' => 2,
                            ],
                        ],
                    ],
                ],
            ],
            1627335016,
        ];
        yield 'oneToMany relationship with maxitems=1' => [
            [
                'aTable' => [
                    'columns' => [
                        'foo' => [
                            'config' => [
                                'type' => 'category',
                                'relationship' => 'oneToMany',
                                'maxitems' => 1,
                            ],
                        ],
                    ],
                ],
            ],
            1627335017,
        ];
        yield 'manyToMany relationship with maxitems=1' => [
            [
                'aTable' => [
                    'columns' => [
                        'foo' => [
                            'config' => [
                                'type' => 'category',
                                'relationship' => 'oneToMany',
                                'maxitems' => 1,
                            ],
                        ],
                    ],
                ],
            ],
            1627335017,
        ];
    }

    #[Test]
    public function configureCategoryRelationsThrowsExceptionOnInvalidRelationship(): void
    {
        $this->expectExceptionCode(1627898896);
        $this->expectException(\RuntimeException::class);
        (new TcaPreparation())->prepare([
            'aTable' => [
                'columns' => [
                    'foo' => [
                        'config' => [
                            'type' => 'category',
                            'relationship' => 'invalid',
                        ],
                    ],
                ],
            ],
        ]);
    }

    #[Test]
    public function prepareFileExtensionsReplacesPlaceholders(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] = 'jpg,png';

        self::assertEquals(
            'jpg,png,gif',
            TcaPreparation::prepareFileExtensions(['common-image-types', 'gif'])
        );
    }

    #[Test]
    public function prepareFileExtensionsRemovesDuplicates(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] = 'jpg,png';

        self::assertEquals(
            'jpg,png,gif',
            TcaPreparation::prepareFileExtensions('common-image-types,jpg,gif')
        );
    }
}
