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

namespace TYPO3\CMS\Core\Tests\Functional\DataScenarios\FAL\WorkspacesDiscard;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Tests\Functional\DataScenarios\FAL\AbstractActionWorkspacesTestCase;

/**
 * Functional test for the DataHandler
 */
final class ActionTest extends AbstractActionWorkspacesTestCase
{
    #[Test]
    public function verifyCleanReferenceIndex(): void
    {
        // The test verifies the imported data set has a clean reference index by the check in tearDown()
        self::assertTrue(true);
    }

    #[Test]
    public function modifyContent(): void
    {
        parent::modifyContent();
        $this->actionService->clearWorkspaceRecord(self::TABLE_Content, self::VALUE_ContentIdLast);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyContent.csv');
    }

    #[Test]
    public function deleteContent(): void
    {
        parent::deleteContent();
        $this->actionService->clearWorkspaceRecord(self::TABLE_Content, self::VALUE_ContentIdLast);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/deleteContent.csv');
    }

    #[Test]
    public function copyContent(): void
    {
        parent::copyContent();
        $this->actionService->clearWorkspaceRecord(self::TABLE_Content, $this->recordIds['copiedContentId']);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/copyContent.csv');
    }

    #[Test]
    public function localizeContent(): void
    {
        parent::localizeContent();
        $this->actionService->clearWorkspaceRecord(self::TABLE_Content, $this->recordIds['localizedContentId']);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/localizeContent.csv');
    }

    #[Test]
    public function changeContentSorting(): void
    {
        parent::changeContentSorting();
        $this->actionService->clearWorkspaceRecord(self::TABLE_Content, self::VALUE_ContentIdFirst);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/changeContentSorting.csv');
    }

    #[Test]
    public function moveContentToDifferentPage(): void
    {
        $newRecordIds = parent::moveContentToDifferentPage();
        $this->actionService->clearWorkspaceRecord(self::TABLE_Content, $newRecordIds[self::TABLE_Content][self::VALUE_ContentIdLast]);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/moveContentToDifferentPage.csv');
    }

    #[Test]
    public function moveContentToDifferentPageAndChangeSorting(): void
    {
        parent::moveContentToDifferentPageAndChangeSorting();
        $this->actionService->clearWorkspaceRecords([
            self::TABLE_Content => [self::VALUE_ContentIdFirst, self::VALUE_ContentIdLast],
        ]);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/moveContentToDifferentPageNChangeSorting.csv');
    }

    #[Test]
    public function createContentWithFileReference(): void
    {
        parent::createContentWithFileReference();
        $this->actionService->clearWorkspaceRecord(self::TABLE_Content, $this->recordIds['newContentId']);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/createContentWFileReference.csv');
    }

    #[Test]
    public function modifyContentWithFileReference(): void
    {
        parent::modifyContentWithFileReference();
        $this->actionService->clearWorkspaceRecord(self::TABLE_Content, self::VALUE_ContentIdLast);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyContentWFileReference.csv');
    }

    #[Test]
    public function modifyContentAndAddFileReference(): void
    {
        parent::modifyContentAndAddFileReference();
        $this->actionService->clearWorkspaceRecord(self::TABLE_Content, self::VALUE_ContentIdLast);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyContentNAddFileReference.csv');
    }

    #[Test]
    public function modifyContentAndDeleteFileReference(): void
    {
        parent::modifyContentAndDeleteFileReference();
        $this->actionService->clearWorkspaceRecord(self::TABLE_Content, self::VALUE_ContentIdLast);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyContentNDeleteFileReference.csv');
    }

    #[Test]
    public function modifyContentAndDeleteAllFileReference(): void
    {
        parent::modifyContentAndDeleteAllFileReference();
        $this->actionService->clearWorkspaceRecord(self::TABLE_Content, self::VALUE_ContentIdLast);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyContentNDeleteAllFileReference.csv');
    }

    #[Test]
    public function createContentWithFileReferenceAndDeleteFileReference(): void
    {
        parent::createContentWithFileReferenceAndDeleteFileReference();
        $this->actionService->clearWorkspaceRecord(self::TABLE_Content, $this->recordIds['newContentId']);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/createContentWFileReferenceNDeleteFileReference.csv');
        // No FE test: Create and delete scenarios have FE coverage, this test is only about DB state.
    }
}
