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

namespace TYPO3\CMS\Core\Tests\Unit\Resource;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class FileReferenceTest extends UnitTestCase
{
    protected function prepareFixture(array $fileReferenceProperties, array $originalFileProperties): FileReference&MockObject&AccessibleObjectInterface
    {
        $fixture = $this->getAccessibleMock(FileReference::class, null, [], '', false);
        $originalFileMock = $this->createMock(File::class);
        $originalFileMock->method('getProperties')
            ->willReturn(
                $originalFileProperties
            );
        $fixture->_set('originalFile', $originalFileMock);
        $fixture->_set('propertiesOfFileReference', $fileReferenceProperties);

        return $fixture;
    }

    public static function propertiesDataProvider(): array
    {
        return [
            'File properties correctly override file reference properties' => [
                [
                    'title' => null,
                    'description' => 'fileReferenceDescription',
                    'alternative' => '',
                ],
                [
                    'title' => 'fileTitle',
                    'description' => 'fileDescription',
                    'alternative' => 'fileAlternative',
                    'file_only_property' => 'fileOnlyPropertyValue',
                ],
                [
                    'title' => 'fileTitle',
                    'description' => 'fileReferenceDescription',
                    'alternative' => '',
                    'file_only_property' => 'fileOnlyPropertyValue',
                ],
            ],
        ];
    }

    #[DataProvider('propertiesDataProvider')]
    #[Test]
    public function getPropertiesReturnsMergedPropertiesAndRespectsNullValues(array $fileReferenceProperties, array $originalFileProperties, array $expectedMergedProperties): void
    {
        $fixture = $this->prepareFixture($fileReferenceProperties, $originalFileProperties);
        $actual = $fixture->getProperties();
        self::assertSame($expectedMergedProperties, $actual);
    }

    #[DataProvider('propertiesDataProvider')]
    #[Test]
    public function hasPropertyReturnsTrueForAllMergedPropertyKeys(array $fileReferenceProperties, array $originalFileProperties, array $expectedMergedProperties): void
    {
        $fixture = $this->prepareFixture($fileReferenceProperties, $originalFileProperties);
        foreach ($expectedMergedProperties as $key => $_) {
            self::assertTrue($fixture->hasProperty($key));
        }
    }

    #[DataProvider('propertiesDataProvider')]
    #[Test]
    public function getPropertyReturnsAllMergedPropertyKeys(array $fileReferenceProperties, array $originalFileProperties, array $expectedMergedProperties): void
    {
        $fixture = $this->prepareFixture($fileReferenceProperties, $originalFileProperties);
        foreach ($expectedMergedProperties as $key => $expectedValue) {
            self::assertSame($expectedValue, $fixture->getProperty($key));
        }
    }

    #[DataProvider('propertiesDataProvider')]
    #[Test]
    public function getPropertyThrowsExceptionForNotAvailableProperty(array $fileReferenceProperties, array $originalFileProperties): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1314226805);

        $fixture = $this->prepareFixture($fileReferenceProperties, $originalFileProperties);
        $fixture->getProperty(StringUtility::getUniqueId('nothingHere'));
    }

    #[DataProvider('propertiesDataProvider')]
    #[Test]
    public function getPropertyDoesNotThrowExceptionForPropertyOnlyAvailableInOriginalFile(
        array $fileReferenceProperties,
        array $originalFileProperties
    ): void {
        $fixture = $this->prepareFixture($fileReferenceProperties, $originalFileProperties);
        self::assertSame($originalFileProperties['file_only_property'], $fixture->getProperty('file_only_property'));
    }

    #[DataProvider('propertiesDataProvider')]
    #[Test]
    public function getReferencePropertyThrowsExceptionForPropertyOnlyAvailableInOriginalFile(
        array $fileReferenceProperties,
        array $originalFileProperties
    ): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1360684914);

        $fixture = $this->prepareFixture($fileReferenceProperties, $originalFileProperties);
        $fixture->getReferenceProperty('file_only_property');
    }

    #[Test]
    public function getTitleReturnsEmptyStringWhenPropertyValueIsNull(): void
    {
        $fixture = $this->prepareFixture(['title' => null], []);
        self::assertSame('', $fixture->getTitle());
    }

    #[Test]
    public function getAlternativeReturnsEmptyStringWhenPropertyValueIsNull(): void
    {
        $fixture = $this->prepareFixture(['alternative' => null], []);
        self::assertSame('', $fixture->getAlternative());
    }

    #[Test]
    public function getDescriptionReturnsEmptyStringWhenPropertyValueIsNull(): void
    {
        $fixture = $this->prepareFixture(['description' => null], []);
        self::assertSame('', $fixture->getDescription());
    }
}
