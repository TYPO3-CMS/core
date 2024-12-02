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

namespace TYPO3\CMS\Core\Tests\Unit\Imaging;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Cache\Frontend\NullFrontend;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProviderInterface;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class IconRegistryTest extends UnitTestCase
{
    #[Test]
    public function getDefaultIconIdentifierReturnsTheCorrectDefaultIconIdentifierString(): void
    {
        $result = (new IconRegistry(new NullFrontend('test'), 'BackendIcons'))->getDefaultIconIdentifier();
        self::assertEquals('default-not-found', $result);
    }

    #[Test]
    public function isRegisteredReturnsTrueForRegisteredIcon(): void
    {
        $subject = new IconRegistry(new NullFrontend('test'), 'BackendIcons');
        $result = $subject->isRegistered($subject->getDefaultIconIdentifier());
        self::assertTrue($result);
    }

    #[Test]
    public function isRegisteredReturnsFalseForNotRegisteredIcon(): void
    {
        $result = (new IconRegistry(new NullFrontend('test'), 'BackendIcons'))->isRegistered('my-super-unregistered-identifier');
        self::assertFalse($result);
    }

    #[Test]
    public function registerIconAddNewIconToRegistry(): void
    {
        $unregisteredIcon = 'foo-bar-unregistered';
        $subject = new IconRegistry(new NullFrontend('test'), 'BackendIcons');
        self::assertFalse($subject->isRegistered($unregisteredIcon));
        $subject->registerIcon($unregisteredIcon, BitmapIconProvider::class, [
            'name' => 'pencil',
            'source' => 'EXT:core/Resoureces/Public/Icons/pencil.png',
        ]);
        self::assertTrue($subject->isRegistered($unregisteredIcon));
    }

    #[Test]
    public function registerIconThrowsInvalidArgumentExceptionWithInvalidIconProvider(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1437425803);

        (new IconRegistry(new NullFrontend('test'), 'BackendIcons'))->registerIcon('my-super-unregistered-identifier', GeneralUtility::class);
    }

    #[Test]
    public function getIconConfigurationByIdentifierThrowsExceptionWithUnregisteredIconIdentifier(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1437425804);

        (new IconRegistry(new NullFrontend('test'), 'BackendIcons'))->getIconConfigurationByIdentifier('my-super-unregistered-identifier');
    }

    #[Test]
    public function getIconConfigurationByIdentifierReturnsCorrectConfiguration(): void
    {
        $result = (new IconRegistry(new NullFrontend('test'), 'BackendIcons'))->getIconConfigurationByIdentifier('default-not-found');
        // result must contain at least provider and options array
        self::assertArrayHasKey('provider', $result);
        self::assertArrayHasKey('options', $result);
        // the provider must implement the IconProviderInterface
        self::assertContains(IconProviderInterface::class, class_implements($result['provider']));
    }

    #[Test]
    public function getAllRegisteredIconIdentifiersReturnsArrayWithAllRegisteredIconIdentifiers(): void
    {
        $result = (new IconRegistry(new NullFrontend('test'), 'BackendIcons'))->getAllRegisteredIconIdentifiers();
        self::assertContains('default-not-found', $result);
    }

    #[Test]
    public function getIconIdentifierForFileExtensionReturnsDefaultIconIdentifierForEmptyFileExtension(): void
    {
        $result = (new IconRegistry(new NullFrontend('test'), 'BackendIcons'))->getIconIdentifierForFileExtension('');
        self::assertEquals('mimetypes-other-other', $result);
    }

    #[Test]
    public function getIconIdentifierForFileExtensionReturnsDefaultIconIdentifierForUnknownFileExtension(): void
    {
        $result = (new IconRegistry(new NullFrontend('test'), 'BackendIcons'))->getIconIdentifierForFileExtension('xyz');
        self::assertEquals('mimetypes-other-other', $result);
    }

    #[Test]
    public function getIconIdentifierForFileExtensionReturnsImageIconIdentifierForImageFileExtension(): void
    {
        $result = (new IconRegistry(new NullFrontend('test'), 'BackendIcons'))->getIconIdentifierForFileExtension('jpg');
        self::assertEquals('mimetypes-media-image', $result);
    }

    #[Test]
    public function registerFileExtensionRegisterAnIcon(): void
    {
        $subject = new IconRegistry(new NullFrontend('test'), 'BackendIcons');
        $subject->registerFileExtension('abc', 'xyz');
        $result = $subject->getIconIdentifierForFileExtension('abc');
        self::assertEquals('xyz', $result);
    }

    #[Test]
    public function registerFileExtensionOverwriteAnExistingIcon(): void
    {
        $subject = new IconRegistry(new NullFrontend('test'), 'BackendIcons');
        $subject->registerFileExtension('jpg', 'xyz');
        $result = $subject->getIconIdentifierForFileExtension('jpg');
        self::assertEquals('xyz', $result);
    }

    #[Test]
    public function registerMimeTypeIconRegisterAnIcon(): void
    {
        $subject = new IconRegistry(new NullFrontend('test'), 'BackendIcons');
        $subject->registerMimeTypeIcon('foo/bar', 'mimetype-foo-bar');
        $result = $subject->getIconIdentifierForMimeType('foo/bar');
        self::assertEquals('mimetype-foo-bar', $result);
    }

    #[Test]
    public function registerMimeTypeIconOverwriteAnExistingIcon(): void
    {
        $subject = new IconRegistry(new NullFrontend('test'), 'BackendIcons');
        $subject->registerMimeTypeIcon('video/*', 'mimetype-foo-bar');
        $result = $subject->getIconIdentifierForMimeType('video/*');
        self::assertEquals('mimetype-foo-bar', $result);
    }

    #[Test]
    public function getIconIdentifierForMimeTypeWithUnknownMimeTypeReturnNull(): void
    {
        $result = (new IconRegistry(new NullFrontend('test'), 'BackendIcons'))->getIconIdentifierForMimeType('bar/foo');
        self::assertNull($result);
    }
}
