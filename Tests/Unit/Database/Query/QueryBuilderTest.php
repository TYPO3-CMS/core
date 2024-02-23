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

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform as PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Query\QueryBuilder as DoctrineQueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\AbstractRestrictionContainer;
use TYPO3\CMS\Core\Database\Query\Restriction\DefaultRestrictionContainer;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\QueryRestrictionInterface;
use TYPO3\CMS\Core\Tests\Unit\Database\Mocks\MockPlatform;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class QueryBuilderTest extends UnitTestCase
{
    private Connection&MockObject $connection;
    private ?QueryBuilder $subject;
    private DoctrineQueryBuilder&MockObject $concreteQueryBuilder;

    /**
     * Create a new database connection mock object for every test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->concreteQueryBuilder = $this->createMock(DoctrineQueryBuilder::class);
        $this->connection = $this->createMock(Connection::class);
        $this->subject = new QueryBuilder(
            $this->connection,
            null,
            $this->concreteQueryBuilder
        );
    }

    #[Test]
    public function exprReturnsExpressionBuilderForConnection(): void
    {
        $this->connection->expects(self::atLeastOnce())->method('getExpressionBuilder')
            ->willReturn(GeneralUtility::makeInstance(ExpressionBuilder::class, $this->connection));
        $this->subject->expr();
    }

    #[Test]
    public function getTypeDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('getType')
            ->willReturn(DoctrineQueryBuilder::INSERT);
        $this->subject->getType();
    }

    #[Test]
    public function getStateDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('getState')
            ->willReturn(DoctrineQueryBuilder::STATE_CLEAN);
        $this->subject->getState();
    }

    #[Test]
    public function getSQLDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('getSQL')
            ->willReturn('UPDATE aTable SET pid = 7');
        $this->concreteQueryBuilder->method('getType')
            ->willReturn(2); // Update Type
        $this->subject->getSQL();
    }

    #[Test]
    public function setParameterDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('setParameter')->with('aField', 5, self::anything())
            ->willReturn($this->subject);
        $this->subject->setParameter('aField', 5);
    }

    #[Test]
    public function setParametersDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('setParameters')->with(['aField' => 'aValue'], [])
            ->willReturn($this->subject);
        $this->subject->setParameters(['aField' => 'aValue']);
    }

    #[Test]
    public function getParametersDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('getParameters')
            ->willReturn(['aField' => 'aValue']);
        $this->subject->getParameters();
    }

    #[Test]
    public function getParameterDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('getParameter')->with('aField')
            ->willReturn('aValue');
        $this->subject->getParameter('aField');
    }

    #[Test]
    public function getParameterTypesDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('getParameterTypes')->willReturn([]);
        $this->subject->getParameterTypes();
    }

    #[Test]
    public function getParameterTypeDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('getParameterType')->with('aField')
            ->willReturn(Connection::PARAM_STR);
        $this->subject->getParameterType('aField');
    }

    #[Test]
    public function setFirstResultDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('setFirstResult')->with(self::anything())
            ->willReturn($this->subject);
        $this->subject->setFirstResult(1);
    }

    #[Test]
    public function getFirstResultDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('getFirstResult')->willReturn(1);
        $this->subject->getFirstResult();
    }

    #[Test]
    public function setMaxResultsDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('setMaxResults')->with(self::anything())
            ->willReturn($this->subject);
        $this->subject->setMaxResults(1);
    }

    #[Test]
    public function getMaxResultsDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('getMaxResults')->willReturn(1);
        $this->subject->getMaxResults();
    }

    #[Test]
    public function addDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('add')->with('select', 'aField', self::anything())
            ->willReturn($this->subject);
        $this->subject->add('select', 'aField');
    }

    #[Test]
    public function countBuildsExpressionAndCallsSelect(): void
    {
        $this->connection->method('getDatabasePlatform')->willReturn(new MockPlatform());
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('select')->with('COUNT(*)')
            ->willReturn($this->subject);
        $this->subject->count('*');
    }

    #[Test]
    public function selectQuotesIdentifiersAndDelegatesToConcreteQueryBuilder(): void
    {
        $series = [
            ['aField'],
            ['anotherField'],
        ];
        $this->connection->expects(self::exactly(2))->method('quoteIdentifier')
            ->willReturnCallback(function (string $field) use (&$series): string {
                $arguments = array_shift($series);
                self::assertSame($arguments[0], $field);
                return $field;
            });
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('select')->with('aField', 'anotherField')
            ->willReturn($this->subject);
        $this->subject->select('aField', 'anotherField');
    }

    public static function quoteIdentifiersForSelectDataProvider(): array
    {
        return [
            'fieldName' => [
                'fieldName',
                '"fieldName"',
            ],
            'tableName.fieldName' => [
                'tableName.fieldName',
                '"tableName"."fieldName"',
            ],
            'tableName.*' => [
                'tableName.*',
                '"tableName".*',
            ],
            '*' => [
                '*',
                '*',
            ],
            'fieldName AS anotherFieldName' => [
                'fieldName AS anotherFieldName',
                '"fieldName" AS "anotherFieldName"',
            ],
            'tableName.fieldName AS anotherFieldName' => [
                'tableName.fieldName AS anotherFieldName',
                '"tableName"."fieldName" AS "anotherFieldName"',
            ],
            'tableName.fieldName AS anotherTable.anotherFieldName' => [
                'tableName.fieldName AS anotherTable.anotherFieldName',
                '"tableName"."fieldName" AS "anotherTable"."anotherFieldName"',
            ],
            'fieldName as anotherFieldName' => [
                'fieldName as anotherFieldName',
                '"fieldName" AS "anotherFieldName"',
            ],
            'tableName.fieldName as anotherFieldName' => [
                'tableName.fieldName as anotherFieldName',
                '"tableName"."fieldName" AS "anotherFieldName"',
            ],
            'tableName.fieldName as anotherTable.anotherFieldName' => [
                'tableName.fieldName as anotherTable.anotherFieldName',
                '"tableName"."fieldName" AS "anotherTable"."anotherFieldName"',
            ],
            'fieldName aS anotherFieldName' => [
                'fieldName aS anotherFieldName',
                '"fieldName" AS "anotherFieldName"',
            ],
            'tableName.fieldName aS anotherFieldName' => [
                'tableName.fieldName aS anotherFieldName',
                '"tableName"."fieldName" AS "anotherFieldName"',
            ],
            'tableName.fieldName aS anotherTable.anotherFieldName' => [
                'tableName.fieldName aS anotherTable.anotherFieldName',
                '"tableName"."fieldName" AS "anotherTable"."anotherFieldName"',
            ],
        ];
    }

    #[DataProvider('quoteIdentifiersForSelectDataProvider')]
    #[Test]
    public function quoteIdentifiersForSelect(string $identifier, string $expectedResult): void
    {
        $this->connection->method('quoteIdentifier')->willReturnCallback(
            static function (string $identifier): string {
                return (new MockPlatform())->quoteIdentifier($identifier);
            }
        );
        self::assertSame([$expectedResult], $this->subject->quoteIdentifiersForSelect([$identifier]));
    }

    #[Test]
    public function quoteIdentifiersForSelectWithInvalidAlias(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1461170686);
        $this->connection->method('quoteIdentifier')->willReturnCallback(
            static function (string $identifier): string {
                return (new MockPlatform())->quoteIdentifier($identifier);
            }
        );
        $this->subject->quoteIdentifiersForSelect(['aField AS anotherField,someField AS someThing']);
    }

    #[Test]
    public function selectDoesNotQuoteStarPlaceholder(): void
    {
        $this->connection->expects(self::never())->method('quoteIdentifier')->with('*');
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('select')->with('*')
            ->willReturn($this->subject);
        $this->subject->select('*');
    }

    #[Test]
    public function addSelectQuotesIdentifiersAndDelegatesToConcreteQueryBuilder(): void
    {
        $series = [
            ['aField'],
            ['anotherField'],
        ];
        $this->connection->expects(self::exactly(2))->method('quoteIdentifier')
            ->willReturnCallback(function (string $field) use (&$series): string {
                $arguments = array_shift($series);
                self::assertSame($arguments[0], $field);
                return $field;
            });
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('addSelect')->with('aField', 'anotherField')
            ->willReturn($this->subject);
        $this->subject->addSelect('aField', 'anotherField');
    }

    #[Test]
    public function addSelectDoesNotQuoteStarPlaceholder(): void
    {
        $this->connection->expects(self::never())->method('quoteIdentifier')->with('*');
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('addSelect')->with('*')
            ->willReturn($this->subject);
        $this->subject->addSelect('*');
    }

    #[Test]
    public function selectLiteralDirectlyDelegatesToConcreteQueryBuilder(): void
    {
        $this->connection->expects(self::never())->method('quoteIdentifier')->with(self::anything());
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('select')->with('MAX(aField) AS anAlias')
            ->willReturn($this->subject);
        $this->subject->selectLiteral('MAX(aField) AS anAlias');
    }

    #[Test]
    public function addSelectLiteralDirectlyDelegatesToConcreteQueryBuilder(): void
    {
        $this->connection->expects(self::never())->method('quoteIdentifier')->with(self::anything());
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('addSelect')->with('MAX(aField) AS anAlias')
            ->willReturn($this->subject);
        $this->subject->addSelectLiteral('MAX(aField) AS anAlias');
    }

    /**
     * @todo: Test with alias
     */
    #[Test]
    public function deleteQuotesIdentifierAndDelegatesToConcreteQueryBuilder(): void
    {
        $this->connection->expects(self::atLeastOnce())->method('quoteIdentifier')->with('aTable')
            ->willReturnArgument(0);
        $this->concreteQueryBuilder->method('delete')->with('aTable', self::anything())->willReturn($this->subject);
        $this->subject->delete('aTable');
    }

    /**
     * @todo: Test with alias
     */
    #[Test]
    public function updateQuotesIdentifierAndDelegatesToConcreteQueryBuilder(): void
    {
        $this->connection->expects(self::atLeastOnce())->method('quoteIdentifier')->with('aTable')
            ->willReturnArgument(0);
        $this->concreteQueryBuilder->method('update')->with('aTable', self::anything())->willReturn($this->subject);
        $this->subject->update('aTable');
    }

    #[Test]
    public function insertQuotesIdentifierAndDelegatesToConcreteQueryBuilder(): void
    {
        $this->connection->expects(self::atLeastOnce())->method('quoteIdentifier')->with('aTable')
            ->willReturnArgument(0);
        $this->concreteQueryBuilder->method('insert')->with('aTable')->willReturn($this->subject);
        $this->subject->insert('aTable');
    }

    /**
     * @todo: Test with alias
     */
    #[Test]
    public function fromQuotesIdentifierAndDelegatesToConcreteQueryBuilder(): void
    {
        $this->connection->expects(self::atLeastOnce())->method('quoteIdentifier')->with('aTable')
            ->willReturnArgument(0);
        $this->concreteQueryBuilder->method('from')->with('aTable', self::anything())->willReturn($this->subject);
        $this->subject->from('aTable');
    }

    #[Test]
    public function joinQuotesIdentifiersAndDelegatesToConcreteQueryBuilder(): void
    {
        $series = [
            ['fromAlias'],
            ['join'],
            ['alias'],
        ];
        $this->connection->expects(self::exactly(3))->method('quoteIdentifier')
            ->willReturnCallback(function (string $field) use (&$series): string {
                $arguments = array_shift($series);
                self::assertSame($arguments[0], $field);
                return $field;
            });
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('innerJoin')
            ->with('fromAlias', 'join', 'alias', null)->willReturn($this->subject);
        $this->subject->join('fromAlias', 'join', 'alias');
    }

    #[Test]
    public function innerJoinQuotesIdentifiersAndDelegatesToConcreteQueryBuilder(): void
    {
        $series = [
            ['fromAlias'],
            ['join'],
            ['alias'],
        ];
        $this->connection->expects(self::exactly(3))->method('quoteIdentifier')
            ->willReturnCallback(function (string $field) use (&$series): string {
                $arguments = array_shift($series);
                self::assertSame($arguments[0], $field);
                return $field;
            });
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('innerJoin')
            ->with('fromAlias', 'join', 'alias', null)->willReturn($this->subject);
        $this->subject->innerJoin('fromAlias', 'join', 'alias');
    }

    #[Test]
    public function leftJoinQuotesIdentifiersAndDelegatesToConcreteQueryBuilder(): void
    {
        $series = [
            ['fromAlias'],
            ['join'],
            ['alias'],
        ];
        $this->connection->expects(self::exactly(3))->method('quoteIdentifier')
            ->willReturnCallback(function (string $field) use (&$series): string {
                $arguments = array_shift($series);
                self::assertSame($arguments[0], $field);
                return $field;
            });
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('leftJoin')
            ->with('fromAlias', 'join', 'alias', self::anything())->willReturn($this->subject);
        $expressionBuilder = GeneralUtility::makeInstance(ExpressionBuilder::class, $this->connection);
        $this->connection->method('getExpressionBuilder')->willReturn($expressionBuilder);
        $this->subject->leftJoin('fromAlias', 'join', 'alias');
    }

    #[Test]
    public function rightJoinQuotesIdentifiersAndDelegatesToConcreteQueryBuilder(): void
    {
        $series = [
            ['fromAlias'],
            ['join'],
            ['alias'],
        ];
        $this->connection->expects(self::exactly(3))->method('quoteIdentifier')
            ->willReturnCallback(function (string $field) use (&$series): string {
                $arguments = array_shift($series);
                self::assertSame($arguments[0], $field);
                return $field;
            });
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('rightJoin')
            ->with('fromAlias', 'join', 'alias', self::anything())->willReturn($this->subject);
        $this->concreteQueryBuilder->method('getQueryPart')->with('from')->willReturn([]);
        $expressionBuilder = GeneralUtility::makeInstance(ExpressionBuilder::class, $this->connection);
        $this->connection->method('getExpressionBuilder')->willReturn($expressionBuilder);
        $this->subject->rightJoin('fromAlias', 'join', 'alias');
    }

    #[Test]
    public function setQuotesIdentifierAndDelegatesToConcreteQueryBuilder(): void
    {
        $this->connection->expects(self::atLeastOnce())->method('quoteIdentifier')->with('aField')
            ->willReturnArgument(0);
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('createNamedParameter')
            ->with('aValue', self::anything())->willReturn(':dcValue1');
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('set')->with('aField', ':dcValue1')
            ->willReturn($this->subject);
        $this->subject->set('aField', 'aValue');
    }

    #[Test]
    public function setWithoutNamedParameterQuotesIdentifierAndDelegatesToConcreteQueryBuilder(): void
    {
        $this->connection->expects(self::atLeastOnce())->method('quoteIdentifier')->with('aField')
            ->willReturnArgument(0);
        $this->concreteQueryBuilder->expects(self::never())->method('createNamedParameter')->with(self::anything());
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('set')->with('aField', 'aValue')
            ->willReturn($this->subject);
        $this->subject->set('aField', 'aValue', false);
    }

    #[Test]
    public function whereDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('where')->with('uid=1', 'type=9')
            ->willReturn($this->subject);
        $this->subject->where('uid=1', 'type=9');
    }

    #[Test]
    public function andWhereDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('andWhere')->with('uid=1', 'type=9')
            ->willReturn($this->subject);
        $this->subject->andWhere('uid=1', 'type=9');
    }

    #[Test]
    public function orWhereDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('orWhere')->with('uid=1', 'type=9')
            ->willReturn($this->subject);
        $this->subject->orWhere('uid=1', 'type=9');
    }

    #[Test]
    public function groupByQuotesIdentifierAndDelegatesToConcreteQueryBuilder(): void
    {
        $this->connection->expects(self::atLeastOnce())->method('quoteIdentifiers')->with(['aField', 'anotherField'])
            ->willReturnArgument(0);
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('groupBy')->with('aField', 'anotherField')
            ->willReturn($this->subject);
        $this->subject->groupBy('aField', 'anotherField');
    }

    #[Test]
    public function addGroupByQuotesIdentifierAndDelegatesToConcreteQueryBuilder(): void
    {
        $this->connection->expects(self::atLeastOnce())->method('quoteIdentifiers')->with(['aField', 'anotherField'])
            ->willReturnArgument(0);
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('addGroupBy')->with('aField', 'anotherField')
            ->willReturn($this->subject);
        $this->subject->addGroupBy('aField', 'anotherField');
    }

    #[Test]
    public function setValueQuotesIdentifierAndDelegatesToConcreteQueryBuilder(): void
    {
        $this->connection->expects(self::atLeastOnce())->method('quoteIdentifier')->with('aField')
            ->willReturnArgument(0);
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('createNamedParameter')
            ->with('aValue', self::anything())->willReturn(':dcValue1');
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('setValue')->with('aField', ':dcValue1')
            ->willReturn($this->subject);
        $this->subject->setValue('aField', 'aValue');
    }

    #[Test]
    public function setValueWithoutNamedParameterQuotesIdentifierAndDelegatesToConcreteQueryBuilder(): void
    {
        $this->connection->expects(self::atLeastOnce())->method('quoteIdentifier')->with('aField')
            ->willReturnArgument(0);
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('setValue')->with('aField', 'aValue')
            ->willReturn($this->subject);
        $this->subject->setValue('aField', 'aValue', false);
    }

    #[Test]
    public function valuesQuotesIdentifiersAndDelegatesToConcreteQueryBuilder(): void
    {
        $this->connection->expects(self::atLeastOnce())->method('quoteColumnValuePairs')
            ->with(['aField' => ':dcValue1', 'aValue' => ':dcValue2'])->willReturnArgument(0);
        $series = [
            [1, ':dcValue1'],
            [2, ':dcValue2'],
        ];
        $this->concreteQueryBuilder->expects(self::exactly(2))->method('createNamedParameter')
            ->willReturnCallback(function (int $value) use (&$series): string {
                $arguments = array_shift($series);
                self::assertSame($arguments[0], $value);
                return $arguments[1];
            });
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('values')
            ->with(['aField' => ':dcValue1', 'aValue' => ':dcValue2'])->willReturn($this->subject);
        $this->subject->values(['aField' => 1, 'aValue' => 2]);
    }

    #[Test]
    public function valuesWithoutNamedParametersQuotesIdentifiersAndDelegatesToConcreteQueryBuilder(): void
    {
        $this->connection->expects(self::atLeastOnce())->method('quoteColumnValuePairs')
            ->with(['aField' => 1, 'aValue' => 2])->willReturnArgument(0);
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('values')
            ->with(['aField' => 1, 'aValue' => 2])->willReturn($this->subject);
        $this->subject->values(['aField' => 1, 'aValue' => 2], false);
    }

    #[Test]
    public function havingDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('having')->with('uid=1', 'type=9')
            ->willReturn($this->subject);
        $this->subject->having('uid=1', 'type=9');
    }

    #[Test]
    public function andHavingDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('andHaving')->with('uid=1', 'type=9')
            ->willReturn($this->subject);
        $this->subject->andHaving('uid=1', 'type=9');
    }

    #[Test]
    public function orHavingDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('orHaving')->with('uid=1', 'type=9')
            ->willReturn($this->subject);
        $this->subject->orHaving('uid=1', 'type=9');
    }

    #[Test]
    public function orderByQuotesIdentifierAndDelegatesToConcreteQueryBuilder(): void
    {
        $this->connection->expects(self::atLeastOnce())->method('quoteIdentifier')->with('aField')
            ->willReturnArgument(0);
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('orderBy')->with('aField', null)
            ->willReturn($this->subject);
        $this->subject->orderBy('aField');
    }

    #[Test]
    public function addOrderByQuotesIdentifierAndDelegatesToConcreteQueryBuilder(): void
    {
        $this->connection->expects(self::atLeastOnce())->method('quoteIdentifier')->with('aField')
            ->willReturnArgument(0);
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('addOrderBy')->with('aField', 'DESC')
            ->willReturn($this->subject);
        $this->subject->addOrderBy('aField', 'DESC');
    }

    #[Test]
    public function getQueryPartDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('getQueryPart')->with('from')
            ->willReturn('aTable');
        $this->subject->getQueryPart('from');
    }

    #[Test]
    public function getQueryPartsDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('getQueryParts')
            ->willReturn([]);
        $this->subject->getQueryParts();
    }

    #[Test]
    public function resetQueryPartsDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('resetQueryParts')->with(['select', 'from'])
            ->willReturn($this->subject);
        $this->subject->resetQueryParts(['select', 'from']);
    }

    #[Test]
    public function resetQueryPartDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('resetQueryPart')->with('select')
            ->willReturn($this->subject);
        $this->subject->resetQueryPart('select');
    }

    #[Test]
    public function createNamedParameterDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('createNamedParameter')
            ->with(5, self::anything())->willReturn(':dcValue1');
        $this->subject->createNamedParameter(5);
    }

    #[Test]
    public function createPositionalParameterDelegatesToConcreteQueryBuilder(): void
    {
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('createPositionalParameter')
            ->with(5, self::anything())->willReturn('?');
        $this->subject->createPositionalParameter(5);
    }

    #[Test]
    public function queryRestrictionsAreAddedForSelectOnExecuteQuery(): void
    {
        $GLOBALS['TCA']['pages']['ctrl'] = [
            'tstamp' => 'tstamp',
            'versioningWS' => true,
            'delete' => 'deleted',
            'crdate' => 'crdate',
            'enablecolumns' => [
                'disabled' => 'hidden',
            ],
        ];

        $this->connection->method('getDatabasePlatform')->willReturn(new MockPlatform());
        $this->connection->method('quoteIdentifier')->with(self::anything())->willReturnArgument(0);
        $this->connection->method('quoteIdentifiers')->with(self::anything())->willReturnArgument(0);

        $connectionBuilder = GeneralUtility::makeInstance(
            DoctrineQueryBuilder::class,
            $this->connection
        );

        $expressionBuilder = GeneralUtility::makeInstance(ExpressionBuilder::class, $this->connection);
        $this->connection->method('getExpressionBuilder')->willReturn($expressionBuilder);

        $subject = GeneralUtility::makeInstance(
            QueryBuilder::class,
            $this->connection,
            null,
            $connectionBuilder
        );

        $subject->select('*')
            ->from('pages')
            ->where('uid=1');

        $expectedSQL = 'SELECT * FROM pages WHERE (uid=1) AND (((pages.deleted = 0) AND (pages.hidden = 0)))';
        $this->connection->method('executeQuery')->with($expectedSQL, self::anything())
            ->willReturn($this->createMock(Result::class));

        $subject->executeQuery();
    }

    #[Test]
    public function queryRestrictionsAreAddedForCountOnExecuteQuery(): void
    {
        $GLOBALS['TCA']['pages']['ctrl'] = [
            'tstamp' => 'tstamp',
            'versioningWS' => true,
            'delete' => 'deleted',
            'crdate' => 'crdate',
            'enablecolumns' => [
                'disabled' => 'hidden',
            ],
        ];

        $this->connection->method('getDatabasePlatform')->willReturn(new MockPlatform());
        $this->connection->method('quoteIdentifier')->with(self::anything())->willReturnArgument(0);
        $this->connection->method('quoteIdentifiers')->with(self::anything())->willReturnArgument(0);

        $connectionBuilder = GeneralUtility::makeInstance(
            DoctrineQueryBuilder::class,
            $this->connection
        );

        $expressionBuilder = GeneralUtility::makeInstance(ExpressionBuilder::class, $this->connection);
        $this->connection->method('getExpressionBuilder')->willReturn($expressionBuilder);

        $subject = GeneralUtility::makeInstance(
            QueryBuilder::class,
            $this->connection,
            null,
            $connectionBuilder
        );

        $subject->count('uid')
            ->from('pages')
            ->where('uid=1');

        $expectedSQL = 'SELECT COUNT(uid) FROM pages WHERE (uid=1) AND (((pages.deleted = 0) AND (pages.hidden = 0)))';
        $this->connection->method('executeQuery')->with($expectedSQL, self::anything())
            ->willReturn($this->createMock(Result::class));

        $subject->executeQuery();
    }

    #[Test]
    public function queryRestrictionsAreReevaluatedOnSettingsChangeForGetSQL(): void
    {
        $GLOBALS['TCA']['pages']['ctrl'] = [
            'tstamp' => 'tstamp',
            'versioningWS' => true,
            'delete' => 'deleted',
            'crdate' => 'crdate',
            'enablecolumns' => [
                'disabled' => 'hidden',
            ],
        ];

        $this->connection->method('getDatabasePlatform')->willReturn(new MockPlatform());
        $this->connection->method('quoteIdentifier')->with(self::anything())->willReturnArgument(0);
        $this->connection->method('quoteIdentifiers')->with(self::anything())->willReturnArgument(0);
        $this->connection->method('getExpressionBuilder')
            ->willReturn(GeneralUtility::makeInstance(ExpressionBuilder::class, $this->connection));

        $concreteQueryBuilder = GeneralUtility::makeInstance(
            DoctrineQueryBuilder::class,
            $this->connection
        );

        $subject = GeneralUtility::makeInstance(
            QueryBuilder::class,
            $this->connection,
            null,
            $concreteQueryBuilder
        );

        $subject->select('*')
            ->from('pages')
            ->where('uid=1');

        $expectedSQL = 'SELECT * FROM pages WHERE (uid=1) AND (((pages.deleted = 0) AND (pages.hidden = 0)))';
        self::assertSame($expectedSQL, $subject->getSQL());

        $subject->getRestrictions()->removeAll()->add(new DeletedRestriction());

        $expectedSQL = 'SELECT * FROM pages WHERE (uid=1) AND (pages.deleted = 0)';
        self::assertSame($expectedSQL, $subject->getSQL());
    }

    #[Test]
    public function queryRestrictionsAreReevaluatedOnSettingsChangeForExecuteQuery(): void
    {
        $GLOBALS['TCA']['pages']['ctrl'] = [
            'tstamp' => 'tstamp',
            'versioningWS' => true,
            'delete' => 'deleted',
            'crdate' => 'crdate',
            'enablecolumns' => [
                'disabled' => 'hidden',
            ],
        ];

        $this->connection->method('getDatabasePlatform')->willReturn(new MockPlatform());
        $this->connection->method('quoteIdentifier')->with(self::anything())->willReturnArgument(0);
        $this->connection->method('quoteIdentifiers')->with(self::anything())->willReturnArgument(0);
        $this->connection->method('getExpressionBuilder')
            ->willReturn(GeneralUtility::makeInstance(ExpressionBuilder::class, $this->connection));

        $concreteQueryBuilder = GeneralUtility::makeInstance(
            DoctrineQueryBuilder::class,
            $this->connection
        );

        $subject = GeneralUtility::makeInstance(
            QueryBuilder::class,
            $this->connection,
            null,
            $concreteQueryBuilder
        );

        $subject->select('*')
            ->from('pages')
            ->where('uid=1');

        $subject->getRestrictions()->removeAll()->add(new DeletedRestriction());

        $expectedSQLForQuery = 'SELECT * FROM pages WHERE (uid=1) AND (pages.deleted = 0)';
        $expectedSQLForResetRestrictions = 'SELECT * FROM pages WHERE (uid=1) AND (((pages.deleted = 0) AND (pages.hidden = 0)))';

        $series = [
            [$expectedSQLForQuery, $this->createMock(Result::class)],
            [$expectedSQLForResetRestrictions, $this->createMock(Result::class)],
        ];
        $this->connection->expects(self::exactly(2))->method('executeQuery')
            ->willReturnCallback(function (string $sql) use (&$series): Result&MockObject {
                $arguments = array_shift($series);
                self::assertSame($arguments[0], $sql);
                return $arguments[1];
            });

        $subject->executeQuery();
        $subject->resetRestrictions();

        $subject->executeQuery();
    }

    #[Test]
    public function getQueriedTablesReturnsSameTableTwiceForInnerJoin(): void
    {
        $this->connection->method('getDatabasePlatform')->willReturn(new MockPlatform());
        $series = [
            [
                'from',
                [
                    [
                        'table' => 'aTable',
                    ],
                ],
            ],
            [
                'join',
                [
                    'aTable' => [
                        [
                            'joinType' => 'inner',
                            'joinTable' => 'aTable',
                            'joinAlias' => 'aTable_alias',
                        ],
                    ],
                ],
            ],
        ];
        $this->concreteQueryBuilder->expects(self::atLeastOnce())->method('getQueryPart')
            ->willReturnCallback(function (string $sql) use (&$series): array {
                $arguments = array_shift($series);
                self::assertSame($arguments[0], $sql);
                return $arguments[1];
            });
        // Call a protected method
        $result = \Closure::bind(function () {
            return $this->getQueriedTables();
        }, $this->subject, QueryBuilder::class)();

        $expected = [
            'aTable' => 'aTable',
            'aTable_alias' => 'aTable',
        ];
        self::assertEquals($expected, $result);
    }

    public static function unquoteSingleIdentifierUnquotesCorrectlyOnDifferentPlatformsDataProvider(): array
    {
        return [
            'mysql' => [
                'platform' => MySQLPlatform::class,
                'quoteChar' => '`',
                'input' => '`anIdentifier`',
                'expected' => 'anIdentifier',
            ],
            'mysql with spaces' => [
                'platform' => MySQLPlatform::class,
                'quoteChar' => '`',
                'input' => ' `anIdentifier` ',
                'expected' => 'anIdentifier',
            ],
            'postgres' => [
                'platform' => PostgreSQLPlatform::class,
                'quoteChar' => '"',
                'input' => '"anIdentifier"',
                'expected' => 'anIdentifier',
            ],
        ];
    }

    #[DataProvider('unquoteSingleIdentifierUnquotesCorrectlyOnDifferentPlatformsDataProvider')]
    #[Test]
    public function unquoteSingleIdentifierUnquotesCorrectlyOnDifferentPlatforms(string $platform, string $quoteChar, string $input, string $expected): void
    {
        $connectionMock = $this->createMock(Connection::class);
        $databasePlatformMock = $this->createMock($platform);
        $databasePlatformMock->method('getIdentifierQuoteCharacter')->willReturn($quoteChar);
        $connectionMock->method('getDatabasePlatform')->willReturn($databasePlatformMock);
        $subject = $this->getAccessibleMock(QueryBuilder::class, null, [$connectionMock]);
        $result = $subject->_call('unquoteSingleIdentifier', $input);
        self::assertEquals($expected, $result);
    }

    #[Test]
    public function cloningQueryBuilderClonesConcreteQueryBuilder(): void
    {
        $clonedQueryBuilder = clone $this->subject;
        self::assertNotSame($this->subject->getConcreteQueryBuilder(), $clonedQueryBuilder->getConcreteQueryBuilder());
    }

    #[Test]
    public function changingClonedQueryBuilderDoesNotInfluenceSourceOne(): void
    {
        $GLOBALS['TCA']['pages']['ctrl'] = [
            'tstamp' => 'tstamp',
            'versioningWS' => true,
            'delete' => 'deleted',
            'crdate' => 'crdate',
            'enablecolumns' => [
                'disabled' => 'hidden',
            ],
        ];

        $this->connection->method('getDatabasePlatform')->willReturn(new MockPlatform());
        $this->connection->method('quoteIdentifier')->with(self::anything())->willReturnArgument(0);
        $this->connection->method('quoteIdentifiers')->with(self::anything())->willReturnArgument(0);
        $this->connection->method('getExpressionBuilder')
            ->willReturn(GeneralUtility::makeInstance(ExpressionBuilder::class, $this->connection));

        $concreteQueryBuilder = GeneralUtility::makeInstance(
            DoctrineQueryBuilder::class,
            $this->connection
        );

        $subject = GeneralUtility::makeInstance(
            QueryBuilder::class,
            $this->connection,
            null,
            $concreteQueryBuilder
        );

        $subject->select('*')
            ->from('pages')
            ->where('uid=1');

        $expectedSQL = 'SELECT * FROM pages WHERE (uid=1) AND (((pages.deleted = 0) AND (pages.hidden = 0)))';
        self::assertSame($expectedSQL, $subject->getSQL());

        $clonedQueryBuilder = clone $subject;
        //just after cloning both query builders should return the same sql
        self::assertSame($expectedSQL, $clonedQueryBuilder->getSQL());

        //change cloned QueryBuilder
        $clonedQueryBuilder->count('*');
        $expectedCountSQL = 'SELECT COUNT(*) FROM pages WHERE (uid=1) AND (((pages.deleted = 0) AND (pages.hidden = 0)))';
        self::assertSame($expectedCountSQL, $clonedQueryBuilder->getSQL());

        //check if the original QueryBuilder has not changed
        self::assertSame($expectedSQL, $subject->getSQL());

        //change restrictions in the original QueryBuilder and check if cloned has changed
        $subject->getRestrictions()->removeAll()->add(new DeletedRestriction());
        $expectedSQL = 'SELECT * FROM pages WHERE (uid=1) AND (pages.deleted = 0)';
        self::assertSame($expectedSQL, $subject->getSQL());

        self::assertSame($expectedCountSQL, $clonedQueryBuilder->getSQL());
    }

    #[Test]
    public function settingRestrictionContainerWillAddAdditionalRestrictionsFromConstructor(): void
    {
        $restrictionClass = get_class($this->createMock(QueryRestrictionInterface::class));
        $queryBuilder = new QueryBuilder(
            $this->connection,
            null,
            $this->concreteQueryBuilder,
            [
                $restrictionClass => [],
            ]
        );

        $container = $this->createMock(AbstractRestrictionContainer::class);
        $container->expects(self::atLeastOnce())->method('add')->with(new $restrictionClass());

        $queryBuilder->setRestrictions($container);
    }

    #[Test]
    public function settingRestrictionContainerWillAddAdditionalRestrictionsFromConfiguration(): void
    {
        $restrictionClass = get_class($this->createMock(QueryRestrictionInterface::class));
        $GLOBALS['TYPO3_CONF_VARS']['DB']['additionalQueryRestrictions'][$restrictionClass] = [];
        $queryBuilder = new QueryBuilder(
            $this->connection,
            null,
            $this->concreteQueryBuilder
        );

        $container = $this->createMock(AbstractRestrictionContainer::class);
        $container->expects(self::atLeastOnce())->method('add')->with(new $restrictionClass());

        $queryBuilder->setRestrictions($container);
    }

    #[Test]
    public function settingRestrictionContainerWillNotAddAdditionalRestrictionsFromConfigurationIfNotDisabled(): void
    {
        $restrictionClass = get_class($this->createMock(QueryRestrictionInterface::class));
        $GLOBALS['TYPO3_CONF_VARS']['DB']['additionalQueryRestrictions'][$restrictionClass] = ['disabled' => true];
        $queryBuilder = new QueryBuilder(
            $this->connection,
            null,
            $this->concreteQueryBuilder
        );

        $container = $this->createMock(AbstractRestrictionContainer::class);
        $container->expects(self::never())->method('add')->with(new $restrictionClass());

        $queryBuilder->setRestrictions($container);
    }

    #[Test]
    public function resettingToDefaultRestrictionContainerWillAddAdditionalRestrictionsFromConfiguration(): void
    {
        $restrictionClass = get_class($this->createMock(QueryRestrictionInterface::class));
        $queryBuilder = new QueryBuilder(
            $this->connection,
            null,
            $this->concreteQueryBuilder,
            [
                $restrictionClass => [],
            ]
        );

        $container = $this->createMock(DefaultRestrictionContainer::class);
        $container->expects(self::atLeastOnce())->method('add')->with(new $restrictionClass());
        GeneralUtility::addInstance(DefaultRestrictionContainer::class, $container);

        $queryBuilder->resetRestrictions();
    }

    /**
     * @param mixed $input
     */
    #[DataProvider('createNamedParameterInput')]
    #[Test]
    public function setWithNamedParameterPassesGivenTypeToCreateNamedParameter($input, int $type): void
    {
        $this->connection->method('quoteIdentifier')->with('aField')
            ->willReturnArgument(0);
        $concreteQueryBuilder = new DoctrineQueryBuilder($this->connection);

        $subject = new QueryBuilder($this->connection, null, $concreteQueryBuilder);
        $subject->set('aField', $input, true, $type);
        self::assertSame($type, $concreteQueryBuilder->getParameterType('dcValue1'));
    }

    public static function createNamedParameterInput(): array
    {
        return [
            'string input and output' => [
                'aValue',
                Connection::PARAM_STR,
            ],
            'int input and string output' => [
                17,
                Connection::PARAM_STR,
            ],
            'int input and int output' => [
                17,
                Connection::PARAM_INT,
            ],
            'string input and array output' => [
                'aValue',
                Connection::PARAM_STR_ARRAY,
            ],
        ];
    }

    public static function castFieldToTextTypeDataProvider(): array
    {
        return [
            'Test cast for MySQLPlatform' => [
                new MySQLPlatform(),
                'CONVERT(aField, CHAR)',
            ],
            'Test cast for PostgreSqlPlatform' => [
                new PostgreSQLPlatform(),
                'aField::text',
            ],
            'Test cast for SqlitePlatform' => [
                new SqlitePlatform(),
                'CAST(aField as TEXT)',
            ],
            'Test cast for OraclePlatform' => [
                new OraclePlatform(),
                'CAST(aField as VARCHAR)',
            ],
        ];
    }

    #[DataProvider('castFieldToTextTypeDataProvider')]
    #[Test]
    public function castFieldToTextType(AbstractPlatform $platform, string $expectation): void
    {
        $this->connection->expects(self::atLeastOnce())->method('quoteIdentifier')->with('aField')
            ->willReturnArgument(0);

        $this->connection->method('getDatabasePlatform')->willReturn($platform);

        $concreteQueryBuilder = new DoctrineQueryBuilder($this->connection);

        $subject = new QueryBuilder($this->connection, null, $concreteQueryBuilder);
        $result = $subject->castFieldToTextType('aField');

        self::assertSame($expectation, $result);
    }

    #[Test]
    public function limitRestrictionsToTablesLimitsRestrictionsInTheContainerToTheGivenTables(): void
    {
        $GLOBALS['TCA']['tt_content']['ctrl'] = $GLOBALS['TCA']['pages']['ctrl'] = [
            'delete' => 'deleted',
            'enablecolumns' => [
                'disabled' => 'hidden',
            ],
        ];

        $this->connection->method('getDatabasePlatform')->willReturn(new MockPlatform());
        $this->connection->method('quoteIdentifier')->with(self::anything())->willReturnArgument(0);
        $this->connection->method('quoteIdentifiers')->with(self::anything())->willReturnArgument(0);

        $connectionBuilder = GeneralUtility::makeInstance(
            DoctrineQueryBuilder::class,
            $this->connection
        );

        $expressionBuilder = GeneralUtility::makeInstance(ExpressionBuilder::class, $this->connection);
        $this->connection->method('getExpressionBuilder')->willReturn($expressionBuilder);

        $subject = new QueryBuilder(
            $this->connection,
            null,
            $connectionBuilder
        );
        $subject->limitRestrictionsToTables(['pages']);

        $subject->select('*')
            ->from('pages')
            ->leftJoin(
                'pages',
                'tt_content',
                'content',
                'pages.uid = content.pid'
            )
            ->where($expressionBuilder->eq('uid', 1));

        $this->connection->method('executeQuery')->with(
            'SELECT * FROM pages LEFT JOIN tt_content content ON pages.uid = content.pid WHERE (uid = 1) AND (((pages.deleted = 0) AND (pages.hidden = 0)))',
            self::anything()
        )->willReturn($this->createMock(Result::class));

        $subject->executeQuery();
    }

    #[Test]
    public function restrictionsCanStillBeRemovedAfterTheyHaveBeenLimitedToTables(): void
    {
        $GLOBALS['TCA']['tt_content']['ctrl'] = $GLOBALS['TCA']['pages']['ctrl'] = [
            'delete' => 'deleted',
            'enablecolumns' => [
                'disabled' => 'hidden',
            ],
        ];

        $this->connection->method('getDatabasePlatform')->willReturn(new MockPlatform());
        $this->connection->method('quoteIdentifier')->with(self::anything())->willReturnArgument(0);
        $this->connection->method('quoteIdentifiers')->with(self::anything())->willReturnArgument(0);

        $connectionBuilder = GeneralUtility::makeInstance(
            DoctrineQueryBuilder::class,
            $this->connection
        );

        $expressionBuilder = GeneralUtility::makeInstance(ExpressionBuilder::class, $this->connection);
        $this->connection->method('getExpressionBuilder')->willReturn($expressionBuilder);

        $subject = new QueryBuilder(
            $this->connection,
            null,
            $connectionBuilder
        );
        $subject->limitRestrictionsToTables(['pages']);
        $subject->getRestrictions()->removeByType(DeletedRestriction::class);

        $subject->select('*')
            ->from('pages')
            ->leftJoin(
                'pages',
                'tt_content',
                'content',
                'pages.uid = content.pid'
            )
            ->where($expressionBuilder->eq('uid', 1));

        $this->connection->method('executeQuery')->with(
            'SELECT * FROM pages LEFT JOIN tt_content content ON pages.uid = content.pid WHERE (uid = 1) AND (pages.hidden = 0)',
            self::anything()
        )->willReturn($this->createMock(Result::class));

        $subject->executeQuery();
    }

    #[Test]
    public function restrictionsAreAppliedInJoinConditionForLeftJoins(): void
    {
        $GLOBALS['TCA']['tt_content']['ctrl'] = $GLOBALS['TCA']['pages']['ctrl'] = [
            'delete' => 'deleted',
            'enablecolumns' => [
                'disabled' => 'hidden',
            ],
        ];

        $this->connection->method('getDatabasePlatform')->willReturn(new MockPlatform());
        $this->connection->method('quoteIdentifier')->with(self::anything())->willReturnArgument(0);
        $this->connection->method('quoteIdentifiers')->with(self::anything())->willReturnArgument(0);

        $connectionBuilder = GeneralUtility::makeInstance(
            DoctrineQueryBuilder::class,
            $this->connection
        );

        $expressionBuilder = GeneralUtility::makeInstance(ExpressionBuilder::class, $this->connection);
        $this->connection->method('getExpressionBuilder')->willReturn($expressionBuilder);

        $subject = new QueryBuilder(
            $this->connection,
            null,
            $connectionBuilder
        );

        $subject->select('*')
                ->from('pages')
                ->leftJoin(
                    'pages',
                    'tt_content',
                    'content',
                    'pages.uid = content.pid'
                )
                ->where($expressionBuilder->eq('uid', 1));

        $this->connection->method('executeQuery')->with(
            'SELECT * FROM pages LEFT JOIN tt_content content ON ((pages.uid = content.pid) AND (((content.deleted = 0) AND (content.hidden = 0)))) WHERE (uid = 1) AND (((pages.deleted = 0) AND (pages.hidden = 0)))',
            self::anything()
        )->willReturn($this->createMock(Result::class));

        $subject->executeQuery();
    }

    #[Test]
    public function restrictionsAreAppliedInJoinConditionForRightJoins(): void
    {
        $GLOBALS['TCA']['tt_content']['ctrl'] = $GLOBALS['TCA']['pages']['ctrl'] = [
            'delete' => 'deleted',
            'enablecolumns' => [
                'disabled' => 'hidden',
            ],
        ];

        $this->connection->method('getDatabasePlatform')->willReturn(new MockPlatform());
        $this->connection->method('quoteIdentifier')->with(self::anything())->willReturnArgument(0);
        $this->connection->method('quoteIdentifiers')->with(self::anything())->willReturnArgument(0);

        $connectionBuilder = GeneralUtility::makeInstance(
            DoctrineQueryBuilder::class,
            $this->connection
        );

        $expressionBuilder = GeneralUtility::makeInstance(ExpressionBuilder::class, $this->connection);
        $this->connection->method('getExpressionBuilder')->willReturn($expressionBuilder);

        $subject = new QueryBuilder(
            $this->connection,
            null,
            $connectionBuilder
        );

        $subject->select('*')
                ->from('tt_content')
                ->rightJoin(
                    'tt_content',
                    'pages',
                    'pages',
                    'pages.uid = tt_content.pid'
                )
                ->where($expressionBuilder->eq('uid', 1));

        $this->connection->method('executeQuery')->with(
            'SELECT * FROM tt_content RIGHT JOIN pages pages ON ((pages.uid = tt_content.pid) AND (((tt_content.deleted = 0) AND (tt_content.hidden = 0)))) WHERE (uid = 1) AND (((pages.deleted = 0) AND (pages.hidden = 0)))',
            self::anything()
        )->willReturn($this->createMock(Result::class));

        $subject->executeQuery();
    }
}
