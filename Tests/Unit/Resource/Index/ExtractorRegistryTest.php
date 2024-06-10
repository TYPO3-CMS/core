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

namespace TYPO3\CMS\Core\Tests\Unit\Resource\Index;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Resource\Index\ExtractorInterface;
use TYPO3\CMS\Core\Resource\Index\ExtractorRegistry;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @todo: Use fixture classes instead of mocks for the extractors to simplify the setup.
 */
final class ExtractorRegistryTest extends UnitTestCase
{
    #[Test]
    public function registeredExtractorClassCanBeRetrieved(): void
    {
        $extractorClass = StringUtility::getUniqueId('extractor');
        $extractorObject = $this->getMockBuilder(ExtractorInterface::class)
            ->setMockClassName($extractorClass)
            ->getMock();

        $extractorRegistry = $this->getMockExtractorRegistry([[$extractorClass, $extractorObject]]);

        $extractorRegistry->registerExtractionService($extractorClass);
        self::assertContains($extractorObject, $extractorRegistry->getExtractors());
    }

    #[Test]
    public function registerExtractorClassThrowsExceptionIfClassDoesNotExist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1422705270);

        $className = 'e1f9aa4e1cd3aa7ff05dcdccb117156a';
        $extractorRegistry = $this->getMockExtractorRegistry();
        $extractorRegistry->registerExtractionService($className);
    }

    #[Test]
    public function registerExtractorClassThrowsExceptionIfClassDoesNotImplementRightInterface(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1422705271);

        $className = __CLASS__;
        $extractorRegistry = $this->getMockExtractorRegistry();
        $extractorRegistry->registerExtractionService($className);
    }

    #[Test]
    public function registerExtractorClassWithHighestPriorityIsFirstInResult(): void
    {
        $extractorClass1 = StringUtility::getUniqueId('extractor');
        $extractorObject1 = $this->getMockBuilder(ExtractorInterface::class)
            ->setMockClassName($extractorClass1)
            ->getMock();
        $extractorObject1->method('getExecutionPriority')->willReturn(1);

        $extractorClass2 = StringUtility::getUniqueId('extractor');
        $extractorObject2 = $this->getMockBuilder(ExtractorInterface::class)
            ->setMockClassName($extractorClass2)
            ->getMock();
        $extractorObject2->method('getExecutionPriority')->willReturn(10);

        $extractorClass3 = StringUtility::getUniqueId('extractor');
        $extractorObject3 = $this->getMockBuilder(ExtractorInterface::class)
            ->setMockClassName($extractorClass3)
            ->getMock();
        $extractorObject3->method('getExecutionPriority')->willReturn(2);

        $createdExtractorInstances = [
            [$extractorClass1, $extractorObject1],
            [$extractorClass2, $extractorObject2],
            [$extractorClass3, $extractorObject3],
        ];

        $extractorRegistry = $this->getMockExtractorRegistry($createdExtractorInstances);
        $extractorRegistry->registerExtractionService($extractorClass1);
        $extractorRegistry->registerExtractionService($extractorClass2);
        $extractorRegistry->registerExtractionService($extractorClass3);

        $extractorInstances = $extractorRegistry->getExtractors();

        self::assertInstanceOf($extractorClass2, $extractorInstances[0]);
        self::assertInstanceOf($extractorClass3, $extractorInstances[1]);
        self::assertInstanceOf($extractorClass1, $extractorInstances[2]);
    }

    #[Test]
    public function registeredExtractorClassWithSamePriorityAreAllReturned(): void
    {
        $extractorClass1 = StringUtility::getUniqueId('extractor');
        $extractorObject1 = $this->getMockBuilder(ExtractorInterface::class)
            ->setMockClassName($extractorClass1)
            ->getMock();
        $extractorObject1->method('getExecutionPriority')->willReturn(1);

        $extractorClass2 = StringUtility::getUniqueId('extractor');
        $extractorObject2 = $this->getMockBuilder(ExtractorInterface::class)
            ->setMockClassName($extractorClass2)
            ->getMock();
        $extractorObject2->method('getExecutionPriority')->willReturn(1);

        $createdExtractorInstances = [
            [$extractorClass1, $extractorObject1],
            [$extractorClass2, $extractorObject2],
        ];

        $extractorRegistry = $this->getMockExtractorRegistry($createdExtractorInstances);
        $extractorRegistry->registerExtractionService($extractorClass1);
        $extractorRegistry->registerExtractionService($extractorClass2);

        $extractorInstances = $extractorRegistry->getExtractors();
        self::assertContains($extractorObject1, $extractorInstances);
        self::assertContains($extractorObject2, $extractorInstances);
    }

    #[Test]
    public function registeredExtractorsCanBeFilteredByDriverTypeButNoTyeREstrictionIsTreatedAsCompatible(): void
    {
        $extractorClass1 = StringUtility::getUniqueId('extractor');
        $extractorObject1 = $this->getMockBuilder(ExtractorInterface::class)
            ->setMockClassName($extractorClass1)
            ->getMock();
        $extractorObject1->method('getExecutionPriority')->willReturn(1);
        $extractorObject1->method('getDriverRestrictions')->willReturn([]);

        $extractorClass2 = StringUtility::getUniqueId('extractor');
        $extractorObject2 = $this->getMockBuilder(ExtractorInterface::class)
            ->setMockClassName($extractorClass2)
            ->getMock();
        $extractorObject2->method('getExecutionPriority')->willReturn(1);
        $extractorObject2->method('getDriverRestrictions')->willReturn(['Bla']);

        $createdExtractorInstances = [
            [$extractorClass1, $extractorObject1],
            [$extractorClass2, $extractorObject2],
        ];

        $extractorRegistry = $this->getMockExtractorRegistry($createdExtractorInstances);
        $extractorRegistry->registerExtractionService($extractorClass1);
        $extractorRegistry->registerExtractionService($extractorClass2);

        $extractorInstances = $extractorRegistry->getExtractorsWithDriverSupport('Bla');
        self::assertContains($extractorObject1, $extractorInstances);
        self::assertContains($extractorObject2, $extractorInstances);
    }

    #[Test]
    public function registeredExtractorsCanBeFilteredByDriverType(): void
    {
        $extractorClass1 = StringUtility::getUniqueId('extractor');
        $extractorObject1 = $this->getMockBuilder(ExtractorInterface::class)
            ->setMockClassName($extractorClass1)
            ->getMock();
        $extractorObject1->method('getExecutionPriority')->willReturn(1);
        $extractorObject1->method('getDriverRestrictions')->willReturn(['Foo']);

        $extractorClass2 = StringUtility::getUniqueId('extractor');
        $extractorObject2 = $this->getMockBuilder(ExtractorInterface::class)
            ->setMockClassName($extractorClass2)
            ->getMock();
        $extractorObject2->method('getExecutionPriority')->willReturn(1);
        $extractorObject2->method('getDriverRestrictions')->willReturn(['Bla']);

        $createdExtractorInstances = [
            [$extractorClass1, $extractorObject1],
            [$extractorClass2, $extractorObject2],
        ];

        $extractorRegistry = $this->getMockExtractorRegistry($createdExtractorInstances);
        $extractorRegistry->registerExtractionService($extractorClass1);
        $extractorRegistry->registerExtractionService($extractorClass2);

        $extractorInstances = $extractorRegistry->getExtractorsWithDriverSupport('Bla');
        self::assertNotContains($extractorObject1, $extractorInstances);
        self::assertContains($extractorObject2, $extractorInstances);
    }

    /**
     * Initialize an ExtractorRegistry and mock createExtractorInstance()
     */
    protected function getMockExtractorRegistry(array $createsExtractorInstances = []): ExtractorRegistry&MockObject
    {
        $extractorRegistry = $this->getMockBuilder(ExtractorRegistry::class)
            ->onlyMethods(['createExtractorInstance'])
            ->getMock();

        if (!empty($createsExtractorInstances)) {
            $extractorRegistry
                ->method('createExtractorInstance')
                ->willReturnMap($createsExtractorInstances);
        }

        return $extractorRegistry;
    }
}
