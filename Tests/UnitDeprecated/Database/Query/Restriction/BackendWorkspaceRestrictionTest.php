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

namespace TYPO3\CMS\Core\Tests\UnitDeprecated\Database\Query\Restriction;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Database\Query\Restriction\BackendWorkspaceRestriction;
use TYPO3\CMS\Core\Tests\Unit\Database\Query\Restriction\AbstractRestrictionTestCase;

final class BackendWorkspaceRestrictionTest extends AbstractRestrictionTestCase
{
    #[Test]
    public function buildExpressionAddsLiveWorkspaceWhereClause(): void
    {
        $GLOBALS['TCA']['aTable']['ctrl'] = [
            'versioningWS' => 2,
        ];
        $subject = new BackendWorkspaceRestriction(0);
        $expression = $subject->buildExpression(['aTable' => 'aTable'], $this->expressionBuilder);
        self::assertSame('(("aTable"."t3ver_wsid" = 0) OR ("aTable"."t3ver_state" <= 0))', (string)$expression);
    }

    #[Test]
    public function buildExpressionAddsNonLiveWorkspaceWhereClause(): void
    {
        $GLOBALS['TCA']['aTable']['ctrl'] = [
            'versioningWS' => 2,
        ];
        $subject = new BackendWorkspaceRestriction(42);
        $expression = $subject->buildExpression(['aTable' => 'aTable'], $this->expressionBuilder);
        self::assertSame('(("aTable"."t3ver_wsid" = 42) OR ("aTable"."t3ver_state" <= 0))', (string)$expression);
    }

    #[Test]
    public function buildExpressionAddsLiveWorkspaceLimitedWhereClause(): void
    {
        $GLOBALS['TCA']['aTable']['ctrl'] = [
            'versioningWS' => 2,
        ];
        $subject = new BackendWorkspaceRestriction(0, false);
        $expression = $subject->buildExpression(['aTable' => 'aTable'], $this->expressionBuilder);
        self::assertSame('(("aTable"."t3ver_wsid" = 0) AND ("aTable"."t3ver_oid" = 0))', (string)$expression);
    }

    #[Test]
    public function buildExpressionAddsNonLiveWorkspaceLimitedWhereClause(): void
    {
        $GLOBALS['TCA']['aTable']['ctrl'] = [
            'versioningWS' => 2,
        ];
        $subject = new BackendWorkspaceRestriction(42, false);
        $expression = $subject->buildExpression(['aTable' => 'aTable'], $this->expressionBuilder);
        self::assertSame('(("aTable"."t3ver_wsid" = 42) AND ("aTable"."t3ver_oid" > 0))', (string)$expression);
    }
}
