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

namespace TYPO3\CMS\Core\Tests\Functional\Database\Query\Restriction;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Context\VisibilityAspect;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

final class FrontendRestrictionContainerTest extends AbstractRestrictionTestCase
{
    protected bool $resetSingletonInstances = true;

    public static function frontendStatesDataProvider(): array
    {
        return [
            'Live, no preview' => [
                'tableName' => 'aTable',
                'tableAlias' => 'aTable',
                'workspaceId' => 0,
                'hiddenPagePreview' => false,
                'hiddenRecordPreview' => false,
                'frontendUserGroups' => [0, -1],
                'localContextFrontendUserGroups' => null,
                'expectedSQL' => '(("aTable"."deleted" = 0) AND ((("aTable"."t3ver_wsid" = 0) AND ((("aTable"."t3ver_oid" = 0) OR ("aTable"."t3ver_state" = 4))))) AND ("aTable"."myHiddenField" = 0) AND ("aTable"."myStartTimeField" <= 42) AND ((("aTable"."myEndTimeField" = 0) OR ("aTable"."myEndTimeField" > 42))) AND ((("aTable"."myGroupField" IS NULL) OR ("aTable"."myGroupField" = \'\') OR ("aTable"."myGroupField" = \'0\') OR (FIND_IN_SET(\'0\', "aTable"."myGroupField")) OR (FIND_IN_SET(\'-1\', "aTable"."myGroupField")))))',
            ],
            'Live, local context with differing user groups' => [
                'tableName' => 'aTable',
                'tableAlias' => 'aTable',
                'workspaceId' => 0,
                'hiddenPagePreview' => false,
                'hiddenRecordPreview' => false,
                'frontendUserGroups' => [0, -1],
                'localContextFrontendUserGroups' => [3],
                'expectedSQL' => '(("aTable"."deleted" = 0) AND ((("aTable"."t3ver_wsid" = 0) AND ((("aTable"."t3ver_oid" = 0) OR ("aTable"."t3ver_state" = 4))))) AND ("aTable"."myHiddenField" = 0) AND ("aTable"."myStartTimeField" <= 42) AND ((("aTable"."myEndTimeField" = 0) OR ("aTable"."myEndTimeField" > 42))) AND ((("aTable"."myGroupField" IS NULL) OR ("aTable"."myGroupField" = \'\') OR ("aTable"."myGroupField" = \'0\') OR (FIND_IN_SET(\'3\', "aTable"."myGroupField")))))',
            ],
            'Live, with hidden record preview' => [
                'tableName' => 'aTable',
                'tableAlias' => 'aTable',
                'workspaceId' => 0,
                'hiddenPagePreview' => true,
                'hiddenRecordPreview' => true,
                'frontendUserGroups' => [0, -1],
                'localContextFrontendUserGroups' => null,
                'expectedSQL' => '(("aTable"."deleted" = 0) AND ((("aTable"."t3ver_wsid" = 0) AND ((("aTable"."t3ver_oid" = 0) OR ("aTable"."t3ver_state" = 4))))) AND ("aTable"."myStartTimeField" <= 42) AND ((("aTable"."myEndTimeField" = 0) OR ("aTable"."myEndTimeField" > 42))) AND ((("aTable"."myGroupField" IS NULL) OR ("aTable"."myGroupField" = \'\') OR ("aTable"."myGroupField" = \'0\') OR (FIND_IN_SET(\'0\', "aTable"."myGroupField")) OR (FIND_IN_SET(\'-1\', "aTable"."myGroupField")))))',
            ],
            'Workspace, with WS preview' => [
                'tableName' => 'aTable',
                'tableAlias' => 'aTable',
                'workspaceId' => 1,
                'hiddenPagePreview' => false,
                'hiddenRecordPreview' => false,
                'frontendUserGroups' => [0, -1],
                'localContextFrontendUserGroups' => null,
                'expectedSQL' => '(("aTable"."deleted" = 0) AND ((("aTable"."t3ver_wsid" IN (0, 1)) AND ((("aTable"."t3ver_oid" = 0) OR ("aTable"."t3ver_state" = 4))))) AND ("aTable"."myHiddenField" = 0) AND ("aTable"."myStartTimeField" <= 42) AND ((("aTable"."myEndTimeField" = 0) OR ("aTable"."myEndTimeField" > 42))) AND ((("aTable"."myGroupField" IS NULL) OR ("aTable"."myGroupField" = \'\') OR ("aTable"."myGroupField" = \'0\') OR (FIND_IN_SET(\'0\', "aTable"."myGroupField")) OR (FIND_IN_SET(\'-1\', "aTable"."myGroupField")))))',
            ],
            'Workspace, with WS preview and hidden record preview' => [
                'tableName' => 'aTable',
                'tableAlias' => 'aTable',
                'workspaceId' => 1,
                'hiddenPagePreview' => true,
                'hiddenRecordPreview' => true,
                'frontendUserGroups' => [0, -1],
                'localContextFrontendUserGroups' => null,
                'expectedSQL' => '(("aTable"."deleted" = 0) AND ((("aTable"."t3ver_wsid" IN (0, 1)) AND ((("aTable"."t3ver_oid" = 0) OR ("aTable"."t3ver_state" = 4))))) AND ("aTable"."myStartTimeField" <= 42) AND ((("aTable"."myEndTimeField" = 0) OR ("aTable"."myEndTimeField" > 42))) AND ((("aTable"."myGroupField" IS NULL) OR ("aTable"."myGroupField" = \'\') OR ("aTable"."myGroupField" = \'0\') OR (FIND_IN_SET(\'0\', "aTable"."myGroupField")) OR (FIND_IN_SET(\'-1\', "aTable"."myGroupField")))))',
            ],
            'Live page, no preview' => [
                'tableName' => 'pages',
                'tableAlias' => 'pages',
                'workspaceId' => 0,
                'hiddenPagePreview' => false,
                'hiddenRecordPreview' => false,
                'frontendUserGroups' => [0, -1],
                'localContextFrontendUserGroups' => null,
                'expectedSQL' => '(("pages"."deleted" = 0) AND ((("pages"."t3ver_wsid" = 0) AND ((("pages"."t3ver_oid" = 0) OR ("pages"."t3ver_state" = 4))))) AND ("pages"."hidden" = 0) AND ("pages"."starttime" <= 42) AND ((("pages"."endtime" = 0) OR ("pages"."endtime" > 42))) AND ((("pages"."fe_group" IS NULL) OR ("pages"."fe_group" = \'\') OR ("pages"."fe_group" = \'0\') OR (FIND_IN_SET(\'0\', "pages"."fe_group")) OR (FIND_IN_SET(\'-1\', "pages"."fe_group")))))',
            ],
            'Live page, with hidden page preview' => [
                'tableName' => 'pages',
                'tableAlias' => 'pages',
                'workspaceId' => 0,
                'hiddenPagePreview' => true,
                'hiddenRecordPreview' => true,
                'frontendUserGroups' => [0, -1],
                'localContextFrontendUserGroups' => null,
                'expectedSQL' => '(("pages"."deleted" = 0) AND ((("pages"."t3ver_wsid" = 0) AND ((("pages"."t3ver_oid" = 0) OR ("pages"."t3ver_state" = 4))))) AND ("pages"."starttime" <= 42) AND ((("pages"."endtime" = 0) OR ("pages"."endtime" > 42))) AND ((("pages"."fe_group" IS NULL) OR ("pages"."fe_group" = \'\') OR ("pages"."fe_group" = \'0\') OR (FIND_IN_SET(\'0\', "pages"."fe_group")) OR (FIND_IN_SET(\'-1\', "pages"."fe_group")))))',
            ],
            'Workspace page, with WS preview' => [
                'tableName' => 'pages',
                'tableAlias' => 'pages',
                'workspaceId' => 1,
                'hiddenPagePreview' => false,
                'hiddenRecordPreview' => false,
                'frontendUserGroups' => [0, -1],
                'localContextFrontendUserGroups' => null,
                'expectedSQL' => '(("pages"."deleted" = 0) AND ((("pages"."t3ver_wsid" IN (0, 1)) AND ((("pages"."t3ver_oid" = 0) OR ("pages"."t3ver_state" = 4))))) AND ("pages"."hidden" = 0) AND ("pages"."starttime" <= 42) AND ((("pages"."endtime" = 0) OR ("pages"."endtime" > 42))) AND ((("pages"."fe_group" IS NULL) OR ("pages"."fe_group" = \'\') OR ("pages"."fe_group" = \'0\') OR (FIND_IN_SET(\'0\', "pages"."fe_group")) OR (FIND_IN_SET(\'-1\', "pages"."fe_group")))))',
            ],
            'Workspace page, with WS preview and hidden pages preview' => [
                'tableName' => 'pages',
                'tableAlias' => 'pages',
                'workspaceId' => 1,
                'hiddenPagePreview' => true,
                'hiddenRecordPreview' => true,
                'frontendUserGroups' => [0, -1],
                'localContextFrontendUserGroups' => null,
                'expectedSQL' => '(("pages"."deleted" = 0) AND ((("pages"."t3ver_wsid" IN (0, 1)) AND ((("pages"."t3ver_oid" = 0) OR ("pages"."t3ver_state" = 4))))) AND ("pages"."starttime" <= 42) AND ((("pages"."endtime" = 0) OR ("pages"."endtime" > 42))) AND ((("pages"."fe_group" IS NULL) OR ("pages"."fe_group" = \'\') OR ("pages"."fe_group" = \'0\') OR (FIND_IN_SET(\'0\', "pages"."fe_group")) OR (FIND_IN_SET(\'-1\', "pages"."fe_group")))))',
            ],
            'Live, no preview with alias' => [
                'tableName' => 'aTable',
                'tableAlias' => 'a',
                'workspaceId' => 0,
                'hiddenPagePreview' => false,
                'hiddenRecordPreview' => false,
                'frontendUserGroups' => [0, -1],
                'localContextFrontendUserGroups' => null,
                'expectedSQL' => '(("a"."deleted" = 0) AND ((("a"."t3ver_wsid" = 0) AND ((("a"."t3ver_oid" = 0) OR ("a"."t3ver_state" = 4))))) AND ("a"."myHiddenField" = 0) AND ("a"."myStartTimeField" <= 42) AND ((("a"."myEndTimeField" = 0) OR ("a"."myEndTimeField" > 42))) AND ((("a"."myGroupField" IS NULL) OR ("a"."myGroupField" = \'\') OR ("a"."myGroupField" = \'0\') OR (FIND_IN_SET(\'0\', "a"."myGroupField")) OR (FIND_IN_SET(\'-1\', "a"."myGroupField")))))',
            ],
        ];
    }

    #[DataProvider('frontendStatesDataProvider')]
    #[Test]
    public function buildExpressionAddsCorrectClause(
        string $tableName,
        string $tableAlias,
        int $workspaceId,
        bool $hiddenPagePreview,
        bool $hiddenRecordPreview,
        array $frontendUserGroups,
        ?array $localContextFrontendUserGroups,
        string $expectedSQL
    ): void {
        $this->get(TcaSchemaFactory::class)->rebuild(array_replace_recursive($GLOBALS['TCA'], [
            'aTable' => [
                'ctrl' => [
                    'versioningWS' => 2,
                    'delete' => 'deleted',
                    'enablecolumns' => [
                        'disabled' => 'myHiddenField',
                        'starttime' => 'myStartTimeField',
                        'endtime' => 'myEndTimeField',
                        'fe_group' => 'myGroupField',
                    ],
                ],
                'columns' => [
                    'myHiddenField' => ['config' => ['type' => 'check']],
                    'myStartTimeField' => ['config' => ['type' => 'datetime']],
                    'myEndTimeField' => ['config' => ['type' => 'datetime']],
                    'myGroupField' => ['config' => ['type' => 'select']],
                ],
            ],
            'pages' => [
                'ctrl' => [
                    'label' => 'title',
                    'tstamp' => 'tstamp',
                    'sortby' => 'sorting',
                    'type' => 'doktype',
                    'versioningWS' => true,
                    'delete' => 'deleted',
                    'enablecolumns' => [
                        'disabled' => 'hidden',
                        'starttime' => 'starttime',
                        'endtime' => 'endtime',
                        'fe_group' => 'fe_group',
                    ],
                ],
                'columns' => [],
            ],
        ]));

        $context = new Context();
        $context->setAspect('visibility', new VisibilityAspect($hiddenPagePreview, $hiddenRecordPreview));
        $context->setAspect('frontend.user', new UserAspect(new FrontendUserAuthentication(), $frontendUserGroups));
        $context->setAspect('workspace', new WorkspaceAspect($workspaceId));
        GeneralUtility::setSingletonInstance(Context::class, $context);

        $GLOBALS['SIM_ACCESS_TIME'] = 42;

        $localContext = clone $context;
        if ($localContextFrontendUserGroups !== null) {
            $localContext->setAspect(
                'frontend.user',
                new UserAspect(new FrontendUserAuthentication(), $localContextFrontendUserGroups)
            );
        }

        $subject = new FrontendRestrictionContainer($localContext);
        $expression = $subject->buildExpression([$tableAlias => $tableName], $this->expressionBuilder);
        self::assertSame($expectedSQL, (string)$expression);
    }
}
