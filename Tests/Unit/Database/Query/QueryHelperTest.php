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

namespace TYPO3\CMS\Core\Tests\Unit\Database\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class QueryHelperTest extends UnitTestCase
{
    /**
     * Test cases for stripping of leading logical operators in where constraints.
     */
    public static function stripLogicalOperatorPrefixDataProvider(): array
    {
        return [
            'unprefixed input' => ['1=1', '1=1'],
            'leading/trailing whitespace is removed' => [' 1=1 ', '1=1'],
            'AND' => ['AND 1=1', '1=1'],
            'AND with leading space' => ['	AND 1=1', '1=1'],
            'AND with mixed whitespace' => [' 	 AND 1<>1', '1<>1'],
            'AND with opening bracket' => ['AND (1=1)', '(1=1)'],
            'AND without whitespace before bracket' => ['AND(1=1)', '(1=1)'],
            'AND within input' => ['1=1 AND 2=2', '1=1 AND 2=2'],
            'OR' => ['OR 1=1', '1=1'],
            'OR with leading space' => ['	OR 1=1', '1=1'],
            'OR with mixed whitespace' => [' 	 OR 1<>1', '1<>1'],
            'OR with opening bracket' => ['OR (1=1)', '(1=1)'],
            'OR without whitespace before bracket' => ['OR(1=1)', '(1=1)'],
            'OR within input' => ['1=1 OR 2=2', '1=1 OR 2=2'],
        ];
    }

    #[DataProvider('stripLogicalOperatorPrefixDataProvider')]
    #[Test]
    public function stripLogicalOperatorPrefixRemovesConstraintPrefixes(string $input, string $expectedSql): void
    {
        self::assertSame($expectedSql, QueryHelper::stripLogicalOperatorPrefix($input));
    }

    /**
     * Test cases for parsing ORDER BY SQL fragments
     */
    public static function parseOrderByDataProvider(): array
    {
        return [
            'empty string' => [
                '',
                [],
            ],
            'single field' => [
                'aField',
                [
                    ['aField', null],
                ],
            ],
            'single field with leading whitespace' => [
                ' aField',
                [
                    ['aField', null],
                ],
            ],
            'prefixed single field' => [
                'ORDER BY aField',
                [
                    ['aField', null],
                ],
            ],
            'prefixed single field with leading whitespace' => [
                ' ORDER BY aField',
                [
                    ['aField', null],
                ],
            ],
            'single field with direction' => [
                'aField DESC',
                [
                    ['aField', 'DESC'],
                ],
            ],
            'multiple fields' => [
                'aField,anotherField, aThirdField',
                [
                    ['aField', null],
                    ['anotherField', null],
                    ['aThirdField', null],
                ],
            ],
            'multiple fields with direction' => [
                'aField ASC,anotherField, aThirdField DESC',
                [
                    ['aField', 'ASC'],
                    ['anotherField', null],
                    ['aThirdField', 'DESC'],
                ],
            ],
            'prefixed multiple fields with direction' => [
                'ORDER BY aField ASC,anotherField, aThirdField DESC',
                [
                    ['aField', 'ASC'],
                    ['anotherField', null],
                    ['aThirdField', 'DESC'],
                ],
            ],
            'with table prefix' => [
                'ORDER BY be_groups.title',
                [
                    ['be_groups.title', null],
                ],
            ],
        ];
    }

    #[DataProvider('parseOrderByDataProvider')]
    #[Test]
    public function parseOrderByTest(string $input, array $expectedResult): void
    {
        self::assertSame($expectedResult, QueryHelper::parseOrderBy($input));
    }

    /**
     * Test cases for parsing FROM tableList SQL fragments
     */
    public static function parseTableListDataProvider(): array
    {
        return [
            'single table' => [
                'aTable',
                [
                    ['aTable', null],
                ],
            ],
            'single table with leading whitespace' => [
                ' aTable',
                [
                    ['aTable', null],
                ],
            ],
            'prefixed single table' => [
                'FROM aTable',
                [
                    ['aTable', null],
                ],
            ],
            'prefixed single table with leading whitespace' => [
                ' FROM aTable',
                [
                    ['aTable', null],
                ],
            ],
            'single table with alias' => [
                'aTable a',
                [
                    ['aTable', 'a'],
                ],
            ],
            'multiple tables' => [
                'aTable,anotherTable, aThirdTable',
                [
                    ['aTable', null],
                    ['anotherTable', null],
                    ['aThirdTable', null],
                ],
            ],
            'multiple tables with aliases' => [
                'aTable a,anotherTable, aThirdTable AS c',
                [
                    ['aTable', 'a'],
                    ['anotherTable', null],
                    ['aThirdTable', 'c'],
                ],
            ],
            'prefixed multiple tables with aliases' => [
                'FROM aTable a,anotherTable, aThirdTable AS c',
                [
                    ['aTable', 'a'],
                    ['anotherTable', null],
                    ['aThirdTable', 'c'],
                ],
            ],
        ];
    }

    #[DataProvider('parseTableListDataProvider')]
    #[Test]
    public function parseTableListTest(string $input, array $expectedResult): void
    {
        self::assertSame($expectedResult, QueryHelper::parseTableList($input));
    }

    /**
     * Test cases for parsing ORDER BY SQL fragments
     */
    public static function parseGroupByDataProvider(): array
    {
        return [
            'single field' => [
                'aField',
                ['aField'],
            ],
            'single field with leading whitespace' => [
                ' aField',
                ['aField'],
            ],
            'prefixed single field' => [
                'GROUP BY aField',
                ['aField'],
            ],
            'prefixed single field with leading whitespace' => [
                ' GROUP BY aField',
                ['aField'],
            ],
            'multiple fields' => [
                'aField,anotherField, aThirdField',
                ['aField', 'anotherField', 'aThirdField'],
            ],
            'prefixed multiple fields' => [
                'GROUP BY aField,anotherField, aThirdField',
                ['aField', 'anotherField', 'aThirdField'],
            ],
            'with table prefix' => [
                'GROUP BY be_groups.title',
                ['be_groups.title'],
            ],
        ];
    }

    #[DataProvider('parseGroupByDataProvider')]
    #[Test]
    public function parseGroupByTest(string $input, array $expectedResult): void
    {
        self::assertSame($expectedResult, QueryHelper::parseGroupBy($input));
    }

    /**
     * Test cases for parsing JOIN fragments into table name, alias and conditions
     */
    public static function parseJoinDataProvider(): array
    {
        return [
            'unquoted tableName' => [
                'aTable ON aTable.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'aTable',
                    'joinCondition' => 'aTable.uid = anotherTable.uid_foreign',
                ],
            ],
            'backtick quoted tableName' => [
                '`aTable` ON aTable.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'aTable',
                    'joinCondition' => 'aTable.uid = anotherTable.uid_foreign',
                ],
            ],
            'backtick quoted tableName with alias' => [
                '`aTable` a ON a.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'a',
                    'joinCondition' => 'a.uid = anotherTable.uid_foreign',
                ],
            ],
            'backtick quoted tableName with backtick quoted alias' => [
                '`aTable` `a` ON a.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'a',
                    'joinCondition' => 'a.uid = anotherTable.uid_foreign',
                ],
            ],
            'backtick quoted tableName with AS alias' => [
                '`aTable` AS anAlias ON anAlias.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'anAlias',
                    'joinCondition' => 'anAlias.uid = anotherTable.uid_foreign',
                ],
            ],
            'backtick quoted tableName with AS backtick quoted alias' => [
                '`aTable` AS `anAlias` ON anAlias.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'anAlias',
                    'joinCondition' => 'anAlias.uid = anotherTable.uid_foreign',
                ],
            ],
            'backtick unquoted tableName with AS backtick quoted alias' => [
                'aTable AS `anAlias` ON anAlias.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'anAlias',
                    'joinCondition' => 'anAlias.uid = anotherTable.uid_foreign',
                ],
            ],
            'quoted tableName' => [
                '"aTable" ON aTable.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'aTable',
                    'joinCondition' => 'aTable.uid = anotherTable.uid_foreign',
                ],
            ],
            'quoted tableName with alias' => [
                '"aTable" a ON a.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'a',
                    'joinCondition' => 'a.uid = anotherTable.uid_foreign',
                ],
            ],
            'quoted tableName with quoted alias' => [
                '"aTable" "a" ON a.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'a',
                    'joinCondition' => 'a.uid = anotherTable.uid_foreign',
                ],
            ],
            'quoted tableName with AS alias' => [
                '"aTable" AS anAlias ON anAlias.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'anAlias',
                    'joinCondition' => 'anAlias.uid = anotherTable.uid_foreign',
                ],
            ],
            'quoted tableName with AS quoted alias' => [
                '"aTable" AS "anAlias" ON anAlias.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'anAlias',
                    'joinCondition' => 'anAlias.uid = anotherTable.uid_foreign',
                ],
            ],
            'unquoted tableName with AS quoted alias' => [
                'aTable AS "anAlias" ON anAlias.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'anAlias',
                    'joinCondition' => 'anAlias.uid = anotherTable.uid_foreign',
                ],
            ],
            'square-bracket quoted tableName' => [
                '[aTable] ON aTable.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'aTable',
                    'joinCondition' => 'aTable.uid = anotherTable.uid_foreign',
                ],
            ],
            'square-bracket quoted tableName with alias' => [
                '[aTable] a ON a.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'a',
                    'joinCondition' => 'a.uid = anotherTable.uid_foreign',
                ],
            ],
            'square-bracket quoted tableName with square-bracket quoted alias' => [
                '[aTable] [a] ON a.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'a',
                    'joinCondition' => 'a.uid = anotherTable.uid_foreign',
                ],
            ],
            'square-bracket quoted tableName with AS alias' => [
                '[aTable] AS anAlias ON anAlias.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'anAlias',
                    'joinCondition' => 'anAlias.uid = anotherTable.uid_foreign',
                ],
            ],
            'square-bracket quoted tableName with AS square-bracket quoted alias' => [
                '[aTable] AS [anAlias] ON anAlias.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'anAlias',
                    'joinCondition' => 'anAlias.uid = anotherTable.uid_foreign',
                ],
            ],
            'unquoted tableName with AS square-bracket quoted alias' => [
                'aTable AS [anAlias] ON anAlias.uid = anotherTable.uid_foreign',
                [
                    'tableName' => 'aTable',
                    'tableAlias' => 'anAlias',
                    'joinCondition' => 'anAlias.uid = anotherTable.uid_foreign',
                ],
            ],
        ];
    }

    #[DataProvider('parseJoinDataProvider')]
    #[Test]
    public function parseJoinSplitsStatement(string $input, array $expected): void
    {
        self::assertSame($expected, QueryHelper::parseJoin($input));
    }

    /**
     * Test cases for quoting column/table name identifiers in SQL fragments
     */
    public static function quoteDatabaseIdentifierDataProvider(): array
    {
        return [
            'no marked identifiers' => [
                'colPos=0',
                'colPos=0',
            ],
            'single fieldname' => [
                '{#colPos}=0',
                '"colPos"=0',
            ],
            'tablename and fieldname' => [
                '{#tt_content.colPos}=0',
                '"tt_content"."colPos"=0',
            ],
            'multiple fieldnames' => [
                '{#colPos}={#aField}',
                '"colPos"="aField"',
            ],
        ];
    }

    #[DataProvider('quoteDatabaseIdentifierDataProvider')]
    #[Test]
    public function quoteDatabaseIdentifiers(string $input, string $expected): void
    {
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->method('quoteIdentifier')->willReturnCallback(static function (string $identifier): string {
            $parts = array_map(
                static function (string $identifier): string {
                    return '"' . $identifier . '"';
                },
                explode('.', $identifier)
            );

            return implode('.', $parts);
        });

        self::assertSame($expected, QueryHelper::quoteDatabaseIdentifiers($connectionMock, $input));
    }
}
