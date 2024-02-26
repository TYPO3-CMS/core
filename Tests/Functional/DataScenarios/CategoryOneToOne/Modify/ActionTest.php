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

namespace TYPO3\CMS\Core\Tests\Functional\DataScenarios\CategoryOneToOne\Modify;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Tests\Functional\DataScenarios\CategoryOneToOne\AbstractActionTestCase;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\ResponseContent;

/**
 * Functional test for the DataHandler
 */
final class ActionTest extends AbstractActionTestCase
{
    #[Test]
    public function verifyCleanReferenceIndex(): void
    {
        // The test verifies the imported data set has a clean reference index by the check in tearDown()
        self::assertTrue(true);
    }

    #[Test]
    public function addCategoryRelation(): void
    {
        parent::addCategoryRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/addCategoryRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();

        self::assertThat(
            $responseSections,
            $this->getRequestSectionStructureHasRecordConstraint()
                ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_Category)
                ->setTable(self::TABLE_Category)->setField('title')->setValues('Category A.A')
        );
    }

    #[Test]
    public function createAndAddCategoryRelation(): void
    {
        parent::createAndAddCategoryRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/createAndAddCategoryRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();

        self::assertThat(
            $responseSections,
            $this->getRequestSectionHasRecordConstraint()
                ->setTable(self::TABLE_Category)->setField('title')->setValues('Category B.A')
        );

        self::assertThat(
            $responseSections,
            $this->getRequestSectionStructureHasRecordConstraint()
                ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_Category)
                ->setTable(self::TABLE_Category)->setField('title')->setValues('Category B.A')
        );
    }

    #[Test]
    public function createAndReplaceCategoryRelation(): void
    {
        parent::createAndReplaceCategoryRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/createAndReplaceCategoryRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();

        self::assertThat(
            $responseSections,
            $this->getRequestSectionHasRecordConstraint()
                 ->setTable(self::TABLE_Category)->setField('title')->setValues('Category B.A')
        );

        self::assertThat(
            $responseSections,
            $this->getRequestSectionStructureHasRecordConstraint()
                ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Category)
                ->setTable(self::TABLE_Category)->setField('title')->setValues('Category B.A')
        );
    }

    #[Test]
    public function changeExistingCategoryRelation(): void
    {
        parent::changeExistingCategoryRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/changeExistingCategoryRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();

        self::assertThat(
            $responseSections,
            $this->getRequestSectionStructureHasRecordConstraint()
                ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Category)
                ->setTable(self::TABLE_Category)->setField('title')->setValues('Category B')
        );
    }

    #[Test]
    public function modifyReferencingContentElement(): void
    {
        parent::modifyReferencingContentElement();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyReferencingContentElement.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();

        self::assertThat(
            $responseSections,
            $this->getRequestSectionHasRecordConstraint()
                 ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1')
        );
    }

    #[Test]
    public function modifyContentOfRelatedCategory(): void
    {
        parent::modifyContentOfRelatedCategory();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyContentOfRelatedCategory.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();

        self::assertThat(
            $responseSections,
            $this->getRequestSectionStructureHasRecordConstraint()
                 ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Category)
                 ->setTable(self::TABLE_Category)->setField('title')->setValues('Testing #1')
        );
    }

    #[Test]
    public function moveContentAndCategoryRelationToDifferentPage(): void
    {
        parent::moveContentAndCategoryRelationToDifferentPage();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/moveContentAndCategoryRelationToDifferentPage.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_TargetPageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();

        self::assertThat(
            $responseSections,
            $this->getRequestSectionStructureHasRecordConstraint()
                ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Category)
                ->setTable(self::TABLE_Category)->setField('title')->setValues('Category C')
        );
    }

    #[Test]
    public function changeContentAndCategorySorting(): void
    {
        parent::changeContentAndCategorySorting();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/changeContentAndCategorySorting.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();

        self::assertThat(
            $responseSections,
            $this->getRequestSectionStructureHasRecordConstraint()
                ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Category)
                ->setTable(self::TABLE_Category)->setField('title')->setValues('Category C')
        );
    }

    #[Test]
    public function copyContentAndCategoryRelation(): void
    {
        parent::copyContentAndCategoryRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/copyContentAndCategoryRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat(
            $responseSections,
            $this->getRequestSectionStructureHasRecordConstraint()
                ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['copiedContentId'])->setRecordField(self::FIELD_Category)
                ->setTable(self::TABLE_Category)->setField('title')->setValues('Category C')
        );
    }

    #[Test]
    public function deleteCategoryRelation(): void
    {
        parent::deleteCategoryRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/deleteCategoryRelation.csv');

        $response = $this->executeFrontendSubRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();

        self::assertThat(
            $responseSections,
            $this->getRequestSectionStructureDoesNotHaveRecordConstraint()
                ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_Category)
                ->setTable(self::TABLE_Category)->setField('title')->setValues('Category C')
        );
    }
}
