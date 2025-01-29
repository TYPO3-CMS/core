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

namespace TYPO3\CMS\Core\Tests\Functional\DataScenarios\ManyToMany\WorkspacesPublish;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\Tests\Functional\DataScenarios\ManyToMany\AbstractActionWorkspacesTestCase;
use TYPO3\TestingFramework\Core\Functional\Framework\Constraint\RequestSection\DoesNotHaveRecordConstraint;
use TYPO3\TestingFramework\Core\Functional\Framework\Constraint\RequestSection\HasRecordConstraint;
use TYPO3\TestingFramework\Core\Functional\Framework\Constraint\RequestSection\StructureDoesNotHaveRecordConstraint;
use TYPO3\TestingFramework\Core\Functional\Framework\Constraint\RequestSection\StructureHasRecordConstraint;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\ResponseContent;

final class ActionTest extends AbstractActionWorkspacesTestCase
{
    #[Test]
    public function verifyCleanReferenceIndex(): void
    {
        // Fix refindex, then compare with import csv again to verify nothing changed.
        // This is to make sure the import csv is 'clean' - important for the other tests.
        $this->get(ReferenceIndex::class)->updateIndex(false);
        $this->assertCSVDataSet(self::SCENARIO_DataSet);
    }

    #[Test]
    public function addSurfRelation(): void
    {
        parent::addSurfRelation();
        $this->actionService->publishRecord(self::TABLE_Content, self::VALUE_ContentIdFirst);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/addSurfRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf A', 'Surf B', 'Surf A.A'));
    }

    #[Test]
    public function deleteSurfRelation(): void
    {
        parent::deleteSurfRelation();
        $this->actionService->publishRecord(self::TABLE_Content, self::VALUE_ContentIdFirst);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/deleteSurfRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf A'));
        self::assertThat($responseSections, (new StructureDoesNotHaveRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf B', 'Surf C', 'Surf A.A'));
    }

    #[Test]
    public function changeSurfRelationSorting(): void
    {
        parent::changeSurfRelationSorting();
        $this->actionService->publishRecord(self::TABLE_Content, self::VALUE_ContentIdFirst);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/changeSurfRelationSorting.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf A', 'Surf B'));
    }

    #[Test]
    public function createContentAndAddRelation(): void
    {
        parent::createContentAndAddRelation();
        $this->actionService->publishRecord(self::TABLE_Content, $this->recordIds['newContentId']);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/createContentNAddRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Surfing #1'));
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['newContentId'])->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf B'));
    }

    #[Test]
    public function createSurfAndAddRelation(): void
    {
        parent::createSurfAndAddRelation();
        $this->actionService->publishRecord(self::TABLE_Surf, $this->recordIds['newSurfId']);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/createSurfNAddRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surfing #1'));
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surfing #1'));
    }

    #[Test]
    public function createContentAndCreateRelation(): void
    {
        parent::createContentAndCreateRelation();
        $this->actionService->publishRecords([
            self::TABLE_Surf => [$this->recordIds['newSurfId']],
            self::TABLE_Content => [$this->recordIds['newContentId']],
        ]);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/createContentNCreateRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Surfing #1'));
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['newContentId'])->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surfing #1'));
    }

    #[Test]
    public function createSurfAndCreateRelation(): void
    {
        parent::createSurfAndCreateRelation();
        $this->actionService->publishRecords([
            self::TABLE_Content => [$this->recordIds['newContentId']],
            self::TABLE_Surf => [$this->recordIds['newSurfId']],
        ]);
        $this->actionService->publishWorkspace(self::VALUE_WorkspaceId);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/createSurfNCreateRelation.csv');
    }

    #[Test]
    public function createContentWithSurfAndAddRelation(): void
    {
        parent::createContentWithSurfAndAddRelation();
        $this->actionService->publishRecords([
            self::TABLE_Surf => [$this->recordIds['newSurfId']],
            self::TABLE_Content => [$this->recordIds['newContentId']],
        ]);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/createContentWSurfNAddRelation.csv');
    }

    #[Test]
    public function createSurfWithContentAndAddRelation(): void
    {
        parent::createSurfWithContentAndAddRelation();
        $this->actionService->publishRecords([
            self::TABLE_Content => [$this->recordIds['newContentId']],
            self::TABLE_Surf => [$this->recordIds['newSurfId']],
        ]);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/createSurfWContentNAddRelation.csv');
    }

    #[Test]
    public function modifySurfOfRelation(): void
    {
        parent::modifySurfOfRelation();
        $this->actionService->publishRecord(self::TABLE_Surf, self::VALUE_SurfIdFirst);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifySurfOfRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surfing #1', 'Surf B'));
    }

    #[Test]
    public function modifyContentOfRelation(): void
    {
        parent::modifyContentOfRelation();
        $this->actionService->publishRecord(self::TABLE_Content, self::VALUE_ContentIdFirst);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyContentOfRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Surfing #1'));
    }

    #[Test]
    public function modifyBothsOfRelation(): void
    {
        parent::modifyBothsOfRelation();
        $this->actionService->publishRecords([
            self::TABLE_Content => [self::VALUE_ContentIdFirst],
            self::TABLE_Surf => [self::VALUE_SurfIdFirst],
        ]);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyBothsOfRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surfing #1', 'Surf B'));
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Surfing #1'));
    }

    #[Test]
    public function deleteContentOfRelation(): void
    {
        parent::deleteContentOfRelation();
        $this->actionService->publishRecord(self::TABLE_Content, self::VALUE_ContentIdLast);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/deleteContentOfRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new DoesNotHaveRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Surfing #1'));
    }

    #[Test]
    public function deleteSurfOfRelation(): void
    {
        parent::deleteSurfOfRelation();
        $this->actionService->publishRecord(self::TABLE_Surf, self::VALUE_SurfIdFirst);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/deleteSurfOfRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureDoesNotHaveRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf A'));
    }

    #[Test]
    public function copyContentOfRelation(): void
    {
        parent::copyContentOfRelation();
        $this->actionService->publishRecord(self::TABLE_Content, $this->recordIds['newContentId']);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/copyContentOfRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['newContentId'])->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf B', 'Surf C'));
    }

    #[Test]
    public function copyContentToLanguageOfRelation(): void
    {
        parent::copyContentToLanguageOfRelation();
        $this->actionService->publishRecord(self::TABLE_Content, $this->recordIds['newContentId']);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/copyContentToLanguageOfRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf B', 'Surf C'));
    }

    #[Test]
    public function copySurfOfRelation(): void
    {
        parent::copySurfOfRelation();
        $this->actionService->publishRecord(self::TABLE_Surf, $this->recordIds['newSurfId']);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/copySurfOfRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf A', 'Surf A (copy 1)'));
    }

    #[Test]
    public function copySurfToLanguageOfRelation(): void
    {
        parent::copySurfToLanguageOfRelation();
        $this->actionService->publishRecord(self::TABLE_Surf, $this->recordIds['newSurfId']);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/copySurfToLanguageOfRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf A'));
        // [Translate to Dansk:] Surf A is not connected, thus it is not shown
        // ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf A', '[Translate to Dansk:] Surf A'));

    }

    #[Test]
    public function localizeContentOfRelation(): void
    {
        parent::localizeContentOfRelation();
        $this->actionService->publishRecord(self::TABLE_Content, $this->recordIds['localizedContentId']);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/localizeContentOfRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf B', 'Surf C'));
    }

    #[Test]
    public function localizeContentOfRelationWithLanguageSynchronization(): void
    {
        parent::localizeContentOfRelationWithLanguageSynchronization();
        $this->actionService->publishRecord(self::TABLE_Content, $this->recordIds['localizedContentId']);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/localizeContentOfRelationWSynchronization.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf B', 'Surf C'));
    }

    #[Test]
    public function localizeContentOfRelationWithLanguageExclude(): void
    {
        parent::localizeContentOfRelationWithLanguageExclude();
        $this->actionService->publishRecord(self::TABLE_Content, $this->recordIds['localizedContentId']);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/localizeContentOfRelationWExclude.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf B', 'Surf C'));
    }

    #[Test]
    public function localizeContentOfRelationAndAddSurfWithLanguageSynchronization(): void
    {
        parent::localizeContentOfRelationAndAddSurfWithLanguageSynchronization();
        $this->actionService->publishRecord(self::TABLE_Content, $this->recordIds['localizedContentId']);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/localizeContentOfRelationNAddSurfWSynchronization.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf B', 'Surf C'));
    }

    #[Test]
    public function localizeContentChainOfRelationAndAddSurfWithLanguageSynchronization(): void
    {
        parent::localizeContentChainOfRelationAndAddSurfWithLanguageSynchronization();
        $this->actionService->publishRecord(self::TABLE_Content, $this->recordIds['localizedContentId']);
        $this->actionService->publishRecord(self::TABLE_Content, $this->recordIds['localizedContentIdSecond']);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/localizeContentChainOfRelationNAddSurfWSynchronization.csv');

        // @todo: should we check for LanguageId_Second?
        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf B', 'Surf C'));
    }

    #[Test]
    public function localizeSurfOfRelation(): void
    {
        // Create and publish translated page first
        $translatedPageResult = $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->actionService->publishRecord(self::TABLE_Page, $translatedPageResult[self::TABLE_Page][self::VALUE_PageId]);
        parent::localizeSurfOfRelation();
        $this->actionService->publishRecord(self::TABLE_Surf, $this->recordIds['localizedSurfId']);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/localizeSurfOfRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('[Translate to Dansk:] Surf A', 'Surf B'));
    }

    #[Test]
    public function moveContentOfRelationToDifferentPage(): void
    {
        parent::moveContentOfRelationToDifferentPage();
        $this->actionService->publishRecord(self::TABLE_Content, self::VALUE_ContentIdLast);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/moveContentOfRelationToDifferentPage.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageIdTarget));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf B', 'Surf C'));
    }

    #[Test]
    public function copyPage(): void
    {
        parent::copyPage();
        $this->actionService->publishRecords([
            self::TABLE_Page => [$this->recordIds['newPageId']],
            self::TABLE_Content => [$this->recordIds['newContentIdFirst'], $this->recordIds['newContentIdLast']],
        ]);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/copyPage.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId($this->recordIds['newPageId']));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Page)->setField('title')->setValues('Relations'));
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1', 'Regular Element #2'));
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['newContentIdFirst'])->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf A', 'Surf B'));
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['newContentIdLast'])->setRecordField(self::FIELD_Surfing)
            ->setTable(self::TABLE_Surf)->setField('title')->setValues('Surf B', 'Surf C'));
    }
}
