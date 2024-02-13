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

namespace TYPO3\CMS\Core\Tests\Functional\DataScenarios\FAL\Modify;

use TYPO3\CMS\Core\Tests\Functional\DataScenarios\FAL\AbstractActionTestCase;
use TYPO3\TestingFramework\Core\Functional\Framework\Constraint\RequestSection\DoesNotHaveRecordConstraint;
use TYPO3\TestingFramework\Core\Functional\Framework\Constraint\RequestSection\HasRecordConstraint;
use TYPO3\TestingFramework\Core\Functional\Framework\Constraint\RequestSection\StructureDoesNotHaveRecordConstraint;
use TYPO3\TestingFramework\Core\Functional\Framework\Constraint\RequestSection\StructureHasRecordConstraint;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\ResponseContent;

final class ActionTest extends AbstractActionTestCase
{
    /**
     * @test
     */
    public function verifyCleanReferenceIndex(): void
    {
        // The test verifies the imported data set has a clean reference index by the check in tearDown()
        self::assertTrue(true);
    }

    /**
     * @test
     * See DataSet/modifyContent.csv
     */
    public function modifyContent(): void
    {
        parent::modifyContent();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyContent.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1'));
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentImage)
            ->setTable(self::TABLE_FileReference)->setField('title')->setValues('This is Kasper', 'Taken at T3BOARD'));
    }

    /**
     * @test
     * See DataSet/deleteContent.csv
     */
    public function deleteContent(): void
    {
        parent::deleteContent();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/deleteContent.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1'));
        self::assertThat($responseSections, (new DoesNotHaveRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #2'));
    }

    /**
     * @test
     * See DataSet/copyContent.csv
     */
    public function copyContent(): void
    {
        parent::copyContent();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/copyContent.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #2 (copy 1)'));
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['copiedContentId'])->setRecordField(self::FIELD_ContentImage)
            ->setTable(self::TABLE_FileReference)->setField('title')->setValues('This is Kasper', 'Taken at T3BOARD'));
    }

    /**
     * @test
     * See DataSet/copyContentToLanguage.csv
     */
    public function copyContentToLanguage(): void
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        parent::copyContentToLanguage();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/copyContentToLanguage.csv');

        // Set danish to "free" fallback mode
        $this->writeSiteConfiguration(
            'test',
            $this->buildSiteConfiguration(1, '/'),
            [
                $this->buildDefaultLanguageConfiguration('EN', '/'),
                $this->buildLanguageConfiguration('DA', '/da/', [], 'free'),
                $this->buildLanguageConfiguration('DE', '/de/', ['DA', 'EN']),
            ]
        );

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Dansk:] Regular Element #2'));

        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['localizedContentId'])->setRecordField(self::FIELD_ContentImage)
            ->setTable(self::TABLE_FileReference)->setField('title')->setValues('[Translate to Dansk:] This is Kasper', '[Translate to Dansk:] Taken at T3BOARD'));
    }

    /**
     * @test
     * See DataSet/localizeContent.csv
     */
    public function localizeContent(): void
    {
        parent::localizeContent();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/localizeContent.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1', '[Translate to Dansk:] Regular Element #2'));

        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentImage)
            ->setTable(self::TABLE_FileReference)->setField('title')->setValues('[Translate to Dansk:] This is Kasper', '[Translate to Dansk:] Taken at T3BOARD'));
    }

    /**
     * @test
     * See DataSet/changeContentSorting.csv
     */
    public function changeContentSorting(): void
    {
        parent::changeContentSorting();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/changeContentSorting.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1', 'Regular Element #2'));
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentImage)
            ->setTable(self::TABLE_FileReference)->setField('title')->setValues('Kasper', 'T3BOARD'));
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentImage)
            ->setTable(self::TABLE_FileReference)->setField('title')->setValues('This is Kasper', 'Taken at T3BOARD'));
    }

    /**
     * @test
     * See DataSet/moveContentToDifferentPage.csv
     */
    public function moveContentToDifferentPage(): void
    {
        parent::moveContentToDifferentPage();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/moveContentToDifferentPage.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSectionsSource = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSectionsSource, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1'));
        self::assertThat($responseSectionsSource, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentImage)
            ->setTable(self::TABLE_FileReference)->setField('title')->setValues('Kasper', 'T3BOARD'));
        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageIdTarget));
        $responseSectionsTarget = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSectionsTarget, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #2'));
        self::assertThat($responseSectionsTarget, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentImage)
            ->setTable(self::TABLE_FileReference)->setField('title')->setValues('This is Kasper', 'Taken at T3BOARD'));
    }

    /**
     * @test
     * See DataSet/moveContentToDifferentPageNChangeSorting.csv
     */
    public function moveContentToDifferentPageAndChangeSorting(): void
    {
        parent::moveContentToDifferentPageAndChangeSorting();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/moveContentToDifferentPageNChangeSorting.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageIdTarget));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1', 'Regular Element #2'));
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentImage)
            ->setTable(self::TABLE_FileReference)->setField('title')->setValues('Kasper', 'T3BOARD'));
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentImage)
            ->setTable(self::TABLE_FileReference)->setField('title')->setValues('This is Kasper', 'Taken at T3BOARD'));
    }

    /**
     * File references
     */

    /**
     * @test
     * See DataSets/createContentWFileReference.csv
     */
    public function createContentWithFileReference(): void
    {
        parent::createContentWithFileReference();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/createContentWFileReference.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1'));
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['newContentId'])->setRecordField(self::FIELD_ContentImage)
            ->setTable(self::TABLE_FileReference)->setField('title')->setValues('Image #1'));
    }

    /**
     * @test
     * See DataSets/modifyContentWFileReference.csv
     */
    public function modifyContentWithFileReference(): void
    {
        parent::modifyContentWithFileReference();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyContentWFileReference.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1'));
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentImage)
            ->setTable(self::TABLE_FileReference)->setField('title')->setValues('Taken at T3BOARD', 'Image #1'));
    }

    /**
     * @test
     * See DataSets/modifyContentNAddFileReference.csv
     */
    public function modifyContentAndAddFileReference(): void
    {
        parent::modifyContentAndAddFileReference();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyContentNAddFileReference.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentImage)
            ->setTable(self::TABLE_FileReference)->setField('title')->setValues('Taken at T3BOARD', 'This is Kasper', 'Image #3'));
    }

    /**
     * @test
     * See DataSets/modifyContentNDeleteFileReference.csv
     */
    public function modifyContentAndDeleteFileReference(): void
    {
        parent::modifyContentAndDeleteFileReference();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyContentNDeleteFileReference.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentImage)
            ->setTable(self::TABLE_FileReference)->setField('title')->setValues('This is Kasper'));
        self::assertThat($responseSections, (new StructureDoesNotHaveRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentImage)
            ->setTable(self::TABLE_FileReference)->setField('title')->setValues('Taken at T3BOARD'));
    }

    /**
     * @test
     * See DataSets/modifyContentNDeleteAllFileReference.csv
     */
    public function modifyContentAndDeleteAllFileReference(): void
    {
        parent::modifyContentAndDeleteAllFileReference();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyContentNDeleteAllFileReference.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureDoesNotHaveRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentImage)
            ->setTable(self::TABLE_FileReference)->setField('title')->setValues('Taken at T3BOARD', 'This is Kasper'));
    }

    /**
     * @test
     */
    public function createContentWithFileReferenceAndDeleteFileReference(): void
    {
        parent::createContentWithFileReferenceAndDeleteFileReference();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/createContentWFileReferenceNDeleteFileReference.csv');
        // No FE test: Create and delete scenarios have FE coverage, this test is only about DB state.
    }

    /**
     * @test
     */
    public function creatingFileIsDenied(): void
    {
        $this->expectedErrorLogEntries = 1;
        $this->actionService->createNewRecord('sys_file', 0, [
            'storage' => 1,
            'name' => 'any.file',
            'extension' => 'file',
            'identifer' => '/any.file',
            'mime_type' => 'text/plain',
            'sha1' => 'this-is-not-a-hash-value',
        ]);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/sysFileUnchanged.csv');
    }

    /**
     * @test
     */
    public function modifyingFileIsDenied(): void
    {
        $this->expectedErrorLogEntries = 1;
        $this->actionService->modifyRecord('sys_file', 21, [
            'storage' => 1,
            'name' => 'any.file',
            'extension' => 'file',
            'identifer' => '/any.file',
            'mime_type' => 'text/plain',
            'sha1' => 'this-is-not-a-hash-value',
        ]);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/sysFileUnchanged.csv');
    }

    /**
     * @test
     */
    public function usingLegacyStorageFileInFileReferenceIsDenied(): void
    {
        $this->expectedErrorLogEntries = 1;
        $this->actionService->modifyRecord('sys_file_reference', 127, [
            'uid_local' => 9,
        ]);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/sysFileUnchanged.csv');
    }

    /**
     * @test
     */
    public function usingLegacyStorageFileInFileMetadataIsDenied(): void
    {
        $this->expectedErrorLogEntries = 1;
        $this->actionService->modifyRecord('sys_file_metadata', 21, [
            'file' => 9,
        ]);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/sysFileUnchanged.csv');
    }
}
