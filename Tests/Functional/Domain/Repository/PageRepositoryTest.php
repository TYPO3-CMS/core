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

namespace TYPO3\CMS\Core\Tests\Functional\Domain\Repository;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Page;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Domain\Repository\PageRepositoryGetPageHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Core\Versioning\VersionState;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class PageRepositoryTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
    }

    /**
     * @test
     */
    public function getMenuSingleUidRoot(): void
    {
        $subject = new PageRepository();
        $rows = $subject->getMenu(1);
        self::assertArrayHasKey(2, $rows);
        self::assertArrayHasKey(3, $rows);
        self::assertArrayHasKey(4, $rows);
        self::assertCount(3, $rows);
    }

    /**
     * @test
     */
    public function getMenuSingleUidSubpage(): void
    {
        $subject = new PageRepository();
        $rows = $subject->getMenu(2);
        self::assertArrayHasKey(5, $rows);
        self::assertArrayHasKey(7, $rows);
        self::assertCount(2, $rows);
    }

    /**
     * @test
     */
    public function getMenuMultipleUid(): void
    {
        $subject = new PageRepository();
        $rows = $subject->getMenu([2, 3]);
        self::assertArrayHasKey(5, $rows);
        self::assertArrayHasKey(7, $rows);
        self::assertArrayHasKey(8, $rows);
        self::assertArrayHasKey(9, $rows);
        self::assertCount(4, $rows);
    }

    /**
     * @test
     */
    public function getMenuPageOverlay(): void
    {
        $subject = new PageRepository(new Context([
            'language' => new LanguageAspect(1),
        ]));

        $rows = $subject->getMenu([2, 3]);
        self::assertEquals('Attrappe 1-2-5', $rows[5]['title']);
        self::assertEquals('Dummy 1-2-7', $rows[7]['title']);
        self::assertEquals('Dummy 1-3-8', $rows[8]['title']);
        self::assertEquals('Attrappe 1-3-9', $rows[9]['title']);
        self::assertCount(4, $rows);
    }

    /**
     * @test
     */
    public function getMenuWithMountPoint(): void
    {
        $subject = new PageRepository();
        $rows = $subject->getMenu([1000]);
        self::assertEquals('root default language', $rows[1003]['title']);
        self::assertEquals('1001', $rows[1003]['uid']);
        self::assertEquals('1001-1003', $rows[1003]['_MP_PARAM']);
        self::assertCount(2, $rows);
    }

    /**
     * @test
     */
    public function getMenuPageOverlayWithMountPoint(): void
    {
        $subject = new PageRepository(new Context([
            'language' => new LanguageAspect(1),
        ]));
        $rows = $subject->getMenu([1000]);
        self::assertEquals('root translation', $rows[1003]['title']);
        self::assertEquals('1001', $rows[1003]['uid']);
        self::assertEquals('1002', $rows[1003]['_PAGES_OVERLAY_UID']);
        self::assertEquals('1001-1003', $rows[1003]['_MP_PARAM']);
        self::assertCount(2, $rows);
    }

    /**
     * @test
     */
    public function getPageOverlayById(): void
    {
        $subject = new PageRepository();
        $row = $subject->getPageOverlay(1, 1);
        $this->assertOverlayRow($row);
        self::assertEquals('Wurzel 1', $row['title']);
        self::assertEquals('901', $row['_PAGES_OVERLAY_UID']);
        self::assertEquals(1, $row['_PAGES_OVERLAY_LANGUAGE']);
    }

    /**
     * @test
     */
    public function getPageOverlayByIdWithoutTranslation(): void
    {
        $subject = new PageRepository();
        $row = $subject->getPageOverlay(4, 1);
        self::assertIsArray($row);
        self::assertCount(0, $row);
    }

    /**
     * @test
     */
    public function getPageOverlayByRow(): void
    {
        $subject = new PageRepository();
        $orig = $subject->getPage(1);
        $row = $subject->getPageOverlay($orig, 1);
        $this->assertOverlayRow($row);
        self::assertEquals(1, $row['uid']);
        self::assertEquals('Wurzel 1', $row['title']);
        self::assertEquals('901', $row['_PAGES_OVERLAY_UID']);
        self::assertEquals(1, $row['_PAGES_OVERLAY_LANGUAGE']);
    }

    /**
     * @test
     */
    public function getPageOverlayByRowWithoutTranslation(): void
    {
        $subject = new PageRepository();
        $orig = $subject->getPage(4);
        $row = $subject->getPageOverlay($orig, 1);
        self::assertIsArray($row);
        self::assertEquals(4, $row['uid']);
        self::assertEquals('Dummy 1-4', $row['title']);//original title
    }

    /**
     * @test
     */
    public function getPagesOverlayByIdSingle(): void
    {
        $subject = new PageRepository(new Context([
            'language' => new LanguageAspect(1),
        ]));
        $rows = $subject->getPagesOverlay([1]);
        self::assertIsArray($rows);
        self::assertCount(1, $rows);
        self::assertArrayHasKey(0, $rows);

        $row = $rows[0];
        $this->assertOverlayRow($row);
        self::assertEquals('Wurzel 1', $row['title']);
        self::assertEquals('901', $row['_PAGES_OVERLAY_UID']);
        self::assertEquals(1, $row['_PAGES_OVERLAY_LANGUAGE']);
    }

    /**
     * @test
     */
    public function getPagesOverlayByIdMultiple(): void
    {
        $subject = new PageRepository(new Context([
            'language' => new LanguageAspect(1),
        ]));
        $rows = $subject->getPagesOverlay([1, 5, 15]);
        self::assertIsArray($rows);
        self::assertCount(2, $rows);
        self::assertArrayHasKey(0, $rows);
        self::assertArrayHasKey(1, $rows);

        $row = $rows[0];
        $this->assertOverlayRow($row);
        self::assertEquals('Wurzel 1', $row['title']);
        self::assertEquals('901', $row['_PAGES_OVERLAY_UID']);
        self::assertEquals(1, $row['_PAGES_OVERLAY_LANGUAGE']);

        $row = $rows[1];
        $this->assertOverlayRow($row);
        self::assertEquals('Attrappe 1-2-5', $row['title']);
        self::assertEquals('904', $row['_PAGES_OVERLAY_UID']);
        self::assertEquals(1, $row['_PAGES_OVERLAY_LANGUAGE']);
    }

    /**
     * @test
     */
    public function getPagesOverlayByIdMultipleSomeNotOverlaid(): void
    {
        $subject = new PageRepository(new Context([
            'language' => new LanguageAspect(1),
        ]));
        $rows = $subject->getPagesOverlay([1, 4, 5, 8]);
        self::assertIsArray($rows);
        self::assertCount(2, $rows);
        self::assertArrayHasKey(0, $rows);
        self::assertArrayHasKey(2, $rows);

        $row = $rows[0];
        $this->assertOverlayRow($row);
        self::assertEquals('Wurzel 1', $row['title']);

        $row = $rows[2];
        $this->assertOverlayRow($row);
        self::assertEquals('Attrappe 1-2-5', $row['title']);
    }

    /**
     * @test
     */
    public function getPagesOverlayByRowSingle(): void
    {
        $subject = new PageRepository();
        $origRow = $subject->getPage(1);

        $subject = new PageRepository(new Context([
            'language' => new LanguageAspect(1),
        ]));
        $rows = $subject->getPagesOverlay([$origRow]);
        self::assertIsArray($rows);
        self::assertCount(1, $rows);
        self::assertArrayHasKey(0, $rows);

        $row = $rows[0];
        $this->assertOverlayRow($row);
        self::assertEquals('Wurzel 1', $row['title']);
        self::assertEquals('901', $row['_PAGES_OVERLAY_UID']);
        self::assertEquals(1, $row['_PAGES_OVERLAY_LANGUAGE']);
        self::assertEquals(new Page($origRow), $row['_TRANSLATION_SOURCE']);
    }

    /**
     * @test
     */
    public function groupRestrictedPageCanBeOverlaid(): void
    {
        $subject = new PageRepository();
        $origRow = $subject->getPage(6, true);

        $subject = new PageRepository(new Context([
            'language' => new LanguageAspect(1),
        ]));
        $rows = $subject->getPagesOverlay([$origRow]);
        self::assertIsArray($rows);
        self::assertCount(1, $rows);
        self::assertArrayHasKey(0, $rows);

        $row = $rows[0];
        $this->assertOverlayRow($row);
        self::assertEquals('Attrappe 1-2-6', $row['title']);
        self::assertEquals('905', $row['_PAGES_OVERLAY_UID']);
        self::assertEquals(1, $row['_PAGES_OVERLAY_LANGUAGE']);
    }

    /**
     * @test
     */
    public function getPagesOverlayByRowMultiple(): void
    {
        $subject = new PageRepository();
        $orig1 = $subject->getPage(1);
        $orig2 = $subject->getPage(5);

        $subject = new PageRepository(new Context([
            'language' => new LanguageAspect(1),
        ]));
        $rows = $subject->getPagesOverlay([1 => $orig1, 5 => $orig2]);
        self::assertIsArray($rows);
        self::assertCount(2, $rows);
        self::assertArrayHasKey(1, $rows);
        self::assertArrayHasKey(5, $rows);

        $row = $rows[1];
        $this->assertOverlayRow($row);
        self::assertEquals('Wurzel 1', $row['title']);
        self::assertEquals('901', $row['_PAGES_OVERLAY_UID']);
        self::assertEquals(1, $row['_PAGES_OVERLAY_LANGUAGE']);
        self::assertEquals(new Page($orig1), $row['_TRANSLATION_SOURCE']);

        $row = $rows[5];
        $this->assertOverlayRow($row);
        self::assertEquals('Attrappe 1-2-5', $row['title']);
        self::assertEquals('904', $row['_PAGES_OVERLAY_UID']);
        self::assertEquals(1, $row['_PAGES_OVERLAY_LANGUAGE']);
        self::assertEquals(new Page($orig2), $row['_TRANSLATION_SOURCE']);
    }

    /**
     * @test
     */
    public function getPagesOverlayByRowMultipleSomeNotOverlaid(): void
    {
        $subject = new PageRepository();
        $orig1 = $subject->getPage(1);
        $orig2 = $subject->getPage(7);
        $orig3 = $subject->getPage(9);

        $subject = new PageRepository(new Context([
            'language' => new LanguageAspect(1),
        ]));
        $rows = $subject->getPagesOverlay([$orig1, $orig2, $orig3]);
        self::assertIsArray($rows);
        self::assertCount(3, $rows);
        self::assertArrayHasKey(0, $rows);
        self::assertArrayHasKey(1, $rows);
        self::assertArrayHasKey(2, $rows);

        $row = $rows[0];
        $this->assertOverlayRow($row);
        self::assertEquals('Wurzel 1', $row['title']);
        self::assertEquals(new Page($orig1), $row['_TRANSLATION_SOURCE']);

        $row = $rows[1];
        $this->assertNotOverlayRow($row);
        self::assertEquals('Dummy 1-2-7', $row['title']);
        self::assertFalse(isset($row['_TRANSLATION_SOURCE']));

        $row = $rows[2];
        $this->assertOverlayRow($row);
        self::assertEquals('Attrappe 1-3-9', $row['title']);
        self::assertEquals(new Page($orig3), $row['_TRANSLATION_SOURCE']);
    }

    /**
     * Tests whether the getPage Hook is called correctly.
     *
     * @test
     */
    public function isGetPageHookCalled(): void
    {
        // Create a hook mock object
        $getPageHookMock = $this->createMock(PageRepositoryGetPageHookInterface::class);
        $getPageHookMock->expects(self::atLeastOnce())->method('getPage_preProcess')
            ->with(42, false, new PageRepository());
        $className = get_class($getPageHookMock);

        // Register hook mock object
        GeneralUtility::addInstance($className, $getPageHookMock);
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPage'][] = $className;
        $subject = new PageRepository();
        $subject->getPage(42, false);
    }

    ////////////////////////////////
    // Tests concerning mountpoints
    ////////////////////////////////
    ///
    /**
     * @test
     */
    public function getMountPointInfoForDefaultLanguage(): void
    {
        $subject = new PageRepository();
        $mountPointInfo = $subject->getMountPointInfo(1003);
        self::assertEquals('1001-1003', $mountPointInfo['MPvar']);
    }

    /**
     * @test
     */
    public function getMountPointInfoForTranslation(): void
    {
        $mpVar = '1001-1003';
        $subject = new PageRepository(new Context([
            'language' => new LanguageAspect(1),
        ]));
        $mountPointInfo = $subject->getMountPointInfo(1003);
        self::assertEquals($mpVar, $mountPointInfo['MPvar']);

        $mountPointInfo = $subject->getMountPointInfo(1004);
        self::assertEquals($mpVar, $mountPointInfo['MPvar']);
    }

    ////////////////////////////////
    // Tests concerning workspaces
    ////////////////////////////////

    /**
     * @test
     */
    public function previewShowsPagesFromLiveAndCurrentWorkspace(): void
    {
        // initialization
        $wsid = 987654321;
        // simulate calls from \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController->determineId()
        $subject = new PageRepository(new Context([
            'workspace' => new WorkspaceAspect($wsid),
        ]));

        $pageRec = $subject->getPage(11);

        self::assertEquals(11, $pageRec['uid']);
        self::assertEquals(0, $pageRec['t3ver_oid']);
        self::assertEquals(987654321, $pageRec['t3ver_wsid']);
        self::assertEquals(VersionState::NEW_PLACEHOLDER, $pageRec['t3ver_state']);
    }

    /**
     * @test
     */
    public function getWorkspaceVersionReturnsTheCorrectMethod(): void
    {
        // initialization
        $wsid = 987654321;

        // simulate calls from \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController->determineId()
        $subject = new PageRepository(new Context([
            'workspace' => new WorkspaceAspect($wsid),
        ]));

        $pageRec = $subject->getWorkspaceVersionOfRecord($wsid, 'pages', 11);

        self::assertEquals(11, $pageRec['uid']);
        self::assertEquals(0, $pageRec['t3ver_oid']);
        self::assertEquals(987654321, $pageRec['t3ver_wsid']);
        self::assertEquals(VersionState::NEW_PLACEHOLDER, $pageRec['t3ver_state']);
    }

    ////////////////////////////////
    // Tests concerning versioning
    ////////////////////////////////

    /**
     * @test
     */
    public function enableFieldsHidesVersionedRecordsAndPlaceholders(): void
    {
        $table = StringUtility::getUniqueId('aTable');
        $GLOBALS['TCA'][$table] = [
            'ctrl' => [
                'versioningWS' => true,
            ],
        ];

        $subject = new PageRepository(new Context());

        $conditions = $subject->enableFields($table);
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);

        self::assertThat(
            $conditions,
            self::stringContains(' AND ((' . $connection->quoteIdentifier($table . '.t3ver_state') . ' <= 0) '),
            'Versioning placeholders'
        );
        self::assertThat(
            $conditions,
            self::stringContains(' AND (((' . $connection->quoteIdentifier($table . '.t3ver_oid') . ' = 0) OR (' . $connection->quoteIdentifier($table . '.t3ver_state') . ' = 4)))'),
            'Records with online version'
        );
    }

    /**
     * @test
     */
    public function enableFieldsDoesNotHidePlaceholdersInPreview(): void
    {
        $table = StringUtility::getUniqueId('aTable');
        $GLOBALS['TCA'][$table] = [
            'ctrl' => [
                'versioningWS' => true,
            ],
        ];

        $subject = new PageRepository(new Context([
            'workspace' => new WorkspaceAspect(13),
        ]));

        $conditions = $subject->enableFields($table);
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);

        self::assertThat(
            $conditions,
            self::logicalNot(self::stringContains(' AND (' . $connection->quoteIdentifier($table . '.t3ver_state') . ' <= 0)')),
            'No versioning placeholders'
        );
        self::assertThat(
            $conditions,
            self::stringContains(' AND (((' . $connection->quoteIdentifier($table . '.t3ver_oid') . ' = 0) OR (' . $connection->quoteIdentifier($table . '.t3ver_state') . ' = 4)))'),
            'Records from online versions'
        );
    }

    /**
     * @test
     */
    public function enableFieldsDoesFilterToCurrentAndLiveWorkspaceForRecordsInPreview(): void
    {
        $table = StringUtility::getUniqueId('aTable');
        $GLOBALS['TCA'][$table] = [
            'ctrl' => [
                'versioningWS' => true,
            ],
        ];

        $subject = new PageRepository(new Context([
            'workspace' => new WorkspaceAspect(2),
        ]));

        $conditions = $subject->enableFields($table);
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);

        self::assertThat(
            $conditions,
            self::stringContains(' AND ((((' . $connection->quoteIdentifier($table . '.t3ver_wsid') . ' = 0) OR (' . $connection->quoteIdentifier($table . '.t3ver_wsid') . ' = 2)))'),
            'No versioning placeholders'
        );
    }

    protected function assertOverlayRow($row): void
    {
        self::assertIsArray($row);

        self::assertArrayHasKey('_PAGES_OVERLAY', $row);
        self::assertArrayHasKey('_PAGES_OVERLAY_UID', $row);
        self::assertArrayHasKey('_PAGES_OVERLAY_LANGUAGE', $row);

        self::assertTrue($row['_PAGES_OVERLAY']);
    }

    protected function assertNotOverlayRow($row): void
    {
        self::assertIsArray($row);

        self::assertFalse(isset($row['_PAGES_OVERLAY']));
        self::assertFalse(isset($row['_PAGES_OVERLAY_UID']));
        self::assertFalse(isset($row['_PAGES_OVERLAY_LANGUAGE']));
    }

    /**
     * @test
     */
    public function getPageIdsRecursiveTest(): void
    {
        // do not use cache_treelist
        $user = new BackendUserAuthentication();
        $user->user = ['uid' => PHP_INT_MAX];
        $subject = new PageRepository(
            new Context([
                'backend.user' => new UserAspect($user),
            ])
        );
        // empty array does not do anything
        $result = $subject->getPageIdsRecursive([], 1);
        self::assertEquals([], $result);
        // pid=0 does not do anything
        $result = $subject->getPageIdsRecursive([0], 1);
        self::assertEquals([0], $result);
        // depth=0 does return given ids int-casted
        $result = $subject->getPageIdsRecursive(['1'], 0);
        self::assertEquals([1], $result);
        $result = $subject->getPageIdsRecursive([1], 1);
        self::assertEquals([1, 2, 3, 4], $result);
        $result = $subject->getPageIdsRecursive([1], 2);
        self::assertEquals([1, 2, 5, 7, 3, 8, 9, 4, 10], $result);
        $result = $subject->getPageIdsRecursive([1000], 99);
        self::assertEquals([1000, 1001], $result);
    }

    /**
     * @test
     */
    public function getDescendantPageIdsRecursiveTest(): void
    {
        // do not use cache_treelist
        $user = new BackendUserAuthentication();
        $user->user = ['uid' => PHP_INT_MAX];
        $subject = new PageRepository(
            new Context([
                'backend.user' => new UserAspect($user),
            ])
        );
        // Negative numbers or "0" do not return anything
        $result = $subject->getDescendantPageIdsRecursive(-1, 1);
        self::assertEquals([], $result);
        $result = $subject->getDescendantPageIdsRecursive(0, 1);
        self::assertEquals([], $result);
        $result = $subject->getDescendantPageIdsRecursive(1, 1);
        self::assertEquals([2, 3, 4], $result);
        $result = $subject->getDescendantPageIdsRecursive(1, 2);
        self::assertEquals([2, 5, 7, 3, 8, 9, 4, 10], $result);
        // "Begin" leaves out a level
        $result = $subject->getDescendantPageIdsRecursive(1, 2, 1);
        self::assertEquals([5, 7, 8, 9, 10], $result);
        // Exclude a branch (3)
        $result = $subject->getDescendantPageIdsRecursive(1, 2, excludePageIds: [3]);
        self::assertEquals([2, 5, 7, 4, 10], $result);
        // Include Page ID 6
        $result = $subject->getDescendantPageIdsRecursive(1, 2, bypassEnableFieldsCheck: true);
        self::assertEquals([2, 5, 6, 7, 3, 8, 9, 4, 10], $result);
    }

    /**
     * @test
     */
    public function getLanguageOverlayResolvesContentWithNullInValues(): void
    {
        $context = new Context();
        $context->setAspect('language', new LanguageAspect(1, 1, LanguageAspect::OVERLAYS_ON_WITH_FLOATING, [0]));
        $subject = new PageRepository($context);
        $record = $subject->getRawRecord('tt_content', 1);
        self::assertSame('Default Content #1', $record['header']);
        $overlaidRecord = $subject->getLanguageOverlay('tt_content', $record);
        self::assertSame(2, $overlaidRecord['_LOCALIZED_UID']);
        self::assertSame('Translated Content #1', $overlaidRecord['header']);

        // Check if "bodytext" is actually overlaid with a NULL value
        $record = $subject->getRawRecord('tt_content', 3);
        $overlaidRecord = $subject->getLanguageOverlay('tt_content', $record);
        self::assertSame('Translated #2', $overlaidRecord['header']);
        self::assertNull($overlaidRecord['bodytext']);
    }

    /**
     * @return array<string, array{0: array<string, int>}>
     */
    public static function invalidRowForVersionOLDataProvider(): array
    {
        return [
            'no uid and no t3ver_oid' => [[]],
            'zero uid and no t3ver_oid' => [['uid' => 0]],
            'positive uid and no t3ver_oid' => [['uid' => 1]],
            'no uid but t3ver_oid' => [['t3ver_oid' => 1]],
        ];
    }

    /**
     * @test
     * @param array<string, int> $input
     * @dataProvider invalidRowForVersionOLDataProvider
     */
    public function versionOLForAnInvalidRowUnchangedRowData(array $input): void
    {
        $context = new Context();
        $context->setAspect('workspace', new WorkspaceAspect(4));
        $subject = new PageRepository($context);
        $originalInput = $input;

        $subject->versionOL('pages', $input);

        self::assertSame($originalInput, $input);
    }
}
