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

namespace TYPO3\CMS\Core\Tests\Unit\Imaging\IconProvider;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Testcase for \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider
 */
final class BitmapIconProviderTest extends UnitTestCase
{
    protected ?BitmapIconProvider $subject;

    /**
     * @var Icon
     */
    protected $icon;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new BitmapIconProvider();
        $this->icon = new Icon();
        $this->icon->setIdentifier('foo');
        $this->icon->setSize(Icon::SIZE_SMALL);
    }

    #[Test]
    public function prepareIconMarkupWithRelativeSourceReturnsInstanceOfIconWithCorrectMarkup(): void
    {
        $this->subject->prepareIconMarkup($this->icon, ['source' => 'fileadmin/foo.png']);
        self::assertEquals('<img src="fileadmin/foo.png" width="16" height="16" alt="" />', $this->icon->getMarkup());
    }

    #[Test]
    public function prepareIconMarkupWithAbsoluteSourceReturnsInstanceOfIconWithCorrectMarkup(): void
    {
        $this->subject->prepareIconMarkup($this->icon, ['source' => Environment::getPublicPath() . '/fileadmin/foo.png']);
        self::assertEquals('<img src="fileadmin/foo.png" width="16" height="16" alt="" />', $this->icon->getMarkup());
    }

    #[Test]
    public function prepareIconMarkupEXTSourceReferenceReturnsInstanceOfIconWithCorrectMarkup(): void
    {
        $this->subject->prepareIconMarkup($this->icon, ['source' => 'EXT:core/Resources/Public/Images/foo.png']);
        self::assertEquals('<img src="typo3/sysext/core/Resources/Public/Images/foo.png" width="16" height="16" alt="" />', $this->icon->getMarkup());
    }
}
