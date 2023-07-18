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

namespace TYPO3\CMS\Core\Tests\Functional\Imaging;

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class IconFactoryTest extends FunctionalTestCase
{
    protected IconFactory $subject;
    protected string $notRegisteredIconIdentifier = 'my-super-unregistered-identifier';
    protected string $registeredIconIdentifier = 'actions-close';
    protected string $registeredSpinningIconIdentifier = 'spinning-icon';

    /**
     * Simulate a tt_content record
     */
    protected array $mockRecord = [
        'header' => 'dummy content header',
        'uid' => '1',
        'pid' => '1',
        'image' => '',
        'hidden' => '0',
        'starttime' => '0',
        'endtime' => '0',
        'fe_group' => '',
        'CType' => 'text',
        't3ver_state' => '0',
        't3ver_wsid' => '0',
        'sys_language_uid' => '0',
        'l18n_parent' => '0',
        'subheader' => '',
        'bodytext' => '',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = $this->get(IconFactory::class);
    }

    /**
     * DataProvider for icon sizes
     */
    public static function differentSizesDataProvider(): array
    {
        return [
            ['size ' . Icon::SIZE_DEFAULT => ['input' => Icon::SIZE_DEFAULT, 'expected' => Icon::SIZE_DEFAULT]],
            ['size ' . Icon::SIZE_SMALL => ['input' => Icon::SIZE_SMALL, 'expected' => Icon::SIZE_SMALL]],
            ['size ' . Icon::SIZE_MEDIUM => ['input' => Icon::SIZE_MEDIUM, 'expected' => Icon::SIZE_MEDIUM]],
            ['size ' . Icon::SIZE_LARGE => ['input' => Icon::SIZE_LARGE, 'expected' => Icon::SIZE_LARGE]],
        ];
    }

    /**
     * @test
     */
    public function getIconReturnsIconWithCorrectMarkupWrapperIfRegisteredIconIdentifierIsUsed(): void
    {
        self::assertStringContainsString(
            '<span class="icon-markup">',
            $this->subject->getIcon($this->registeredIconIdentifier)->render()
        );
    }

    /**
     * @test
     */
    public function getIconByIdentifierReturnsIconWithCorrectMarkupIfRegisteredIconIdentifierIsUsed(): void
    {
        self::assertStringContainsString(
            '<span class="t3js-icon icon icon-size-medium icon-state-default icon-actions-close" data-identifier="actions-close">',
            $this->subject->getIcon($this->registeredIconIdentifier)->render()
        );
    }

    /**
     * @test
     * @dataProvider differentSizesDataProvider
     */
    public function getIconByIdentifierAndSizeReturnsIconWithCorrectMarkupIfRegisteredIconIdentifierIsUsed($size): void
    {
        self::assertStringContainsString(
            '<span class="t3js-icon icon icon-size-' . $size['expected'] . ' icon-state-default icon-actions-close" data-identifier="actions-close">',
            $this->subject->getIcon($this->registeredIconIdentifier, $size['input'])->render()
        );
    }

    /**
     * @test
     * @dataProvider differentSizesDataProvider
     */
    public function getIconByIdentifierAndSizeAndWithOverlayReturnsIconWithCorrectOverlayMarkupIfRegisteredIconIdentifierIsUsed($size): void
    {
        self::assertStringContainsString(
            '<span class="icon-overlay icon-overlay-readonly">',
            $this->subject->getIcon($this->registeredIconIdentifier, $size['input'], 'overlay-readonly')->render()
        );
    }

    /**
     * @test
     */
    public function getIconReturnsNotFoundIconWithCorrectMarkupIfUnregisteredIdentifierIsUsed(): void
    {
        self::assertStringContainsString(
            '<span class="t3js-icon icon icon-size-medium icon-state-default icon-default-not-found" data-identifier="default-not-found">',
            $this->subject->getIcon($this->notRegisteredIconIdentifier)->render()
        );
    }

    /**
     * @test
     * @dataProvider differentSizesDataProvider
     */
    public function getIconByIdentifierAndSizeReturnsNotFoundIconWithCorrectMarkupIfUnregisteredIdentifierIsUsed(array $size): void
    {
        self::assertStringContainsString(
            '<span class="t3js-icon icon icon-size-' . $size['expected'] . ' icon-state-default icon-default-not-found" data-identifier="default-not-found">',
            $this->subject->getIcon($this->notRegisteredIconIdentifier, $size['input'])->render()
        );
    }

    /**
     * @test
     */
    public function getIconReturnsCorrectMarkupIfIconIsRegisteredAsSpinningIcon(): void
    {
        $iconRegistry = $this->get(IconRegistry::class);
        $iconRegistry->registerIcon(
            $this->registeredSpinningIconIdentifier,
            SvgIconProvider::class,
            [
                'source' => __DIR__ . '/Fixtures/file.svg',
                'spinning' => true,
            ]
        );
        self::assertStringContainsString(
            '<span class="t3js-icon icon icon-size-medium icon-state-default icon-' . $this->registeredSpinningIconIdentifier . ' icon-spin" data-identifier="spinning-icon">',
            $this->subject->getIcon($this->registeredSpinningIconIdentifier)->render()
        );
    }

    /**
     * @test
     * @dataProvider differentSizesDataProvider
     */
    public function getIconByIdentifierAndSizeAndOverlayReturnsNotFoundIconWithCorrectMarkupIfUnregisteredIdentifierIsUsed(array $size): void
    {
        self::assertStringContainsString(
            '<span class="icon-overlay icon-overlay-readonly">',
            $this->subject->getIcon($this->notRegisteredIconIdentifier, $size['input'], 'overlay-readonly')->render()
        );
    }

    /**
     * @test
     */
    public function getIconThrowsExceptionIfInvalidSizeIsGiven(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->subject->getIcon($this->registeredIconIdentifier, 'foo')->render();
    }

    //
    // Tests for getIconForFileExtension
    //

    /**
     * Tests the return of an icon for a file without extension
     *
     * @test
     */
    public function getIconForFileWithNoFileTypeReturnsDefaultFileIcon(): void
    {
        self::assertStringContainsString(
            '<span class="t3js-icon icon icon-size-medium icon-state-default icon-mimetypes-other-other" data-identifier="mimetypes-other-other">',
            $this->subject->getIconForFileExtension('')->render()
        );
    }

    /**
     * Tests the return of an icon for an unknown file type
     *
     * @test
     */
    public function getIconForFileWithUnknownFileTypeReturnsDefaultFileIcon(): void
    {
        self::assertStringContainsString(
            '<span class="t3js-icon icon icon-size-medium icon-state-default icon-mimetypes-other-other" data-identifier="mimetypes-other-other">',
            $this->subject->getIconForFileExtension('foo')->render()
        );
    }

    /**
     * Tests the return of an icon for a file with extension pdf
     *
     * @test
     */
    public function getIconForFileWithFileTypePdfReturnsPdfIcon(): void
    {
        self::assertStringContainsString(
            '<span class="t3js-icon icon icon-size-medium icon-state-default icon-mimetypes-pdf" data-identifier="mimetypes-pdf">',
            $this->subject->getIconForFileExtension('pdf')->render()
        );
    }

    /**
     * Tests the return of an icon for a file with extension png
     *
     * @test
     */
    public function getIconForFileWithFileTypePngReturnsPngIcon(): void
    {
        self::assertStringContainsString(
            '<span class="t3js-icon icon icon-size-medium icon-state-default icon-mimetypes-media-image" data-identifier="mimetypes-media-image">',
            $this->subject->getIconForFileExtension('png')->render()
        );
    }

    /**
     * @test
     */
    public function getIconForResourceReturnsCorrectMarkupForFileResources(): void
    {
        $resourceMock = $this->createMock(File::class);
        $resourceMock->method('isMissing')->willReturn(false);
        $resourceMock->method('getExtension')->willReturn('pdf');
        $resourceMock->method('getMimeType')->willReturn('');

        self::assertStringContainsString(
            '<span class="t3js-icon icon icon-size-medium icon-state-default icon-mimetypes-pdf" data-identifier="mimetypes-pdf">',
            $this->subject->getIconForResource($resourceMock)->render()
        );
    }

    //////////////////////////////////////////////
    // Tests concerning getIconForResource
    //////////////////////////////////////////////
    /**
     * Tests the returns of no file
     *
     * @test
     */
    public function getIconForResourceWithFileWithoutExtensionTypeReturnsOtherIcon(): void
    {
        $fileObject = $this->getTestSubjectFileObject('');
        $result = $this->subject->getIconForResource($fileObject)->render();
        self::assertStringContainsString('<span class="t3js-icon icon icon-size-medium icon-state-default icon-mimetypes-other-other" data-identifier="mimetypes-other-other">', $result);
    }

    /**
     * Tests the returns of unknown file
     *
     * @test
     */
    public function getIconForResourceWithUnknownFileTypeReturnsOtherIcon(): void
    {
        $fileObject = $this->getTestSubjectFileObject('foo');
        $result = $this->subject->getIconForResource($fileObject)->render();
        self::assertStringContainsString('<span class="t3js-icon icon icon-size-medium icon-state-default icon-mimetypes-other-other" data-identifier="mimetypes-other-other">', $result);
    }

    /**
     * Tests the returns of file pdf
     *
     * @test
     */
    public function getIconForResourceWithPdfReturnsPdfIcon(): void
    {
        $fileObject = $this->getTestSubjectFileObject('pdf');
        $result = $this->subject->getIconForResource($fileObject)->render();
        self::assertStringContainsString('<span class="t3js-icon icon icon-size-medium icon-state-default icon-mimetypes-pdf" data-identifier="mimetypes-pdf">', $result);
    }

    /**
     * Tests the returns of file pdf with known mime-type
     *
     * @test
     */
    public function getIconForResourceWithMimeTypeApplicationPdfReturnsPdfIcon(): void
    {
        $fileObject = $this->getTestSubjectFileObject('pdf', 'application/pdf');
        $result = $this->subject->getIconForResource($fileObject)->render();
        self::assertStringContainsString('<span class="t3js-icon icon icon-size-medium icon-state-default icon-mimetypes-pdf" data-identifier="mimetypes-pdf">', $result);
    }

    /**
     * Tests the returns of file with custom image mime-type
     *
     * @test
     */
    public function getIconForResourceWithCustomImageMimeTypeReturnsImageIcon(): void
    {
        $fileObject = $this->getTestSubjectFileObject('custom', 'image/my-custom-extension');
        $result = $this->subject->getIconForResource($fileObject)->render();
        self::assertStringContainsString('<span class="t3js-icon icon icon-size-medium icon-state-default icon-mimetypes-media-image" data-identifier="mimetypes-media-image">', $result);
    }

    /**
     * Tests the returns of file png
     *
     * @test
     */
    public function getIconForResourceWithPngFileReturnsIcon(): void
    {
        $fileObject = $this->getTestSubjectFileObject('png', 'image/png');
        $result = $this->subject->getIconForResource($fileObject)->render();
        self::assertStringContainsString('<span class="t3js-icon icon icon-size-medium icon-state-default icon-mimetypes-media-image" data-identifier="mimetypes-media-image">', $result);
    }

    /**
     * Tests the returns of normal folder
     *
     * @test
     */
    public function getIconForResourceWithFolderReturnsFolderIcon(): void
    {
        $folderObject = $this->getTestSubjectFolderObject('/test');
        $result = $this->subject->getIconForResource($folderObject)->render();
        self::assertStringContainsString('<span class="t3js-icon icon icon-size-medium icon-state-default icon-apps-filetree-folder-default" data-identifier="apps-filetree-folder-default">', $result);
    }

    /**
     * Tests the returns of open folder
     *
     * @test
     */
    public function getIconForResourceWithOpenFolderReturnsOpenFolderIcon(): void
    {
        $folderObject = $this->getTestSubjectFolderObject('/test');
        $result = $this->subject->getIconForResource($folderObject, Icon::SIZE_MEDIUM, null, ['folder-open' => true])->render();
        self::assertStringContainsString('<span class="t3js-icon icon icon-size-medium icon-state-default icon-apps-filetree-folder-opened" data-identifier="apps-filetree-folder-opened">', $result);
    }

    /**
     * Tests the returns of root folder
     *
     * @test
     */
    public function getIconForResourceWithRootFolderReturnsRootFolderIcon(): void
    {
        $folderObject = $this->getTestSubjectFolderObject('/');
        $result = $this->subject->getIconForResource($folderObject)->render();
        self::assertStringContainsString('<span class="t3js-icon icon icon-size-medium icon-state-default icon-apps-filetree-root" data-identifier="apps-filetree-root">', $result);
    }

    /**
     * Tests the returns of mount root
     *
     * @test
     */
    public function getIconForResourceWithMountRootReturnsMountFolderIcon(): void
    {
        $folderObject = $this->getTestSubjectFolderObject('/mount');
        $result = $this->subject->getIconForResource($folderObject, Icon::SIZE_MEDIUM, null, ['mount-root' => true])->render();
        self::assertStringContainsString('<span class="t3js-icon icon icon-size-medium icon-state-default icon-apps-filetree-mount" data-identifier="apps-filetree-mount">', $result);
    }

    //
    // Test for getIconForRecord
    //

    /**
     * Tests the returns of NULL table + empty array
     *
     * @test
     */
    public function getIconForRecordWithNullTableReturnsMissingIcon(): void
    {
        $GLOBALS['TCA']['']['ctrl'] = [];
        self::assertStringContainsString(
            '<span class="t3js-icon icon icon-size-medium icon-state-default icon-default-not-found" data-identifier="default-not-found">',
            $this->subject->getIconForRecord('', [])->render()
        );
    }

    /**
     * Tests the returns of tt_content + empty record
     *
     * @test
     */
    public function getIconForRecordWithEmptyRecordReturnsNormalIcon(): void
    {
        $GLOBALS['TCA'] = [
            'tt_content' => [
                'ctrl' => [
                    'typeicon_column' => 'CType',
                    'typeicon_classes' => [
                        'default' => 'mimetypes-x-content-text',
                    ],
                ],
            ],
        ];
        $result = $this->subject->getIconForRecord('tt_content', [])->render();
        self::assertStringContainsString('<span class="t3js-icon icon icon-size-medium icon-state-default icon-mimetypes-x-content-text" data-identifier="mimetypes-x-content-text">', $result);
    }

    /**
     * Tests the returns of tt_content + mock record
     *
     * @test
     */
    public function getIconForRecordWithMockRecordReturnsNormalIcon(): void
    {
        $GLOBALS['TCA'] = [
            'tt_content' => [
                'ctrl' => [
                    'typeicon_column' => 'CType',
                    'typeicon_classes' => [
                        'default' => '',
                        'text' => 'mimetypes-x-content-text',
                    ],
                ],
            ],
        ];
        $result = $this->subject->getIconForRecord('tt_content', $this->mockRecord)->render();
        self::assertStringContainsString('<span class="t3js-icon icon icon-size-medium icon-state-default icon-mimetypes-x-content-text" data-identifier="mimetypes-x-content-text">', $result);
    }

    /**
     * Tests the returns of tt_content + mock record of type 'list' (aka plugin)
     *
     * @test
     */
    public function getIconForRecordWithMockRecordOfTypePluginReturnsDefaultPluginIcon(): void
    {
        $GLOBALS['TCA'] = [
            'tt_content' => [
                'ctrl' => [
                    'typeicon_column' => 'CType',
                    'typeicon_classes' => [
                        'default' => '',
                        'list' => 'mimetypes-x-content-plugin',
                    ],
                ],
            ],
        ];
        $mockRecord = $this->mockRecord;
        $mockRecord['CType'] = 'list';
        $result = $this->subject->getIconForRecord('tt_content', $mockRecord)->render();
        self::assertStringContainsString('<span class="t3js-icon icon icon-size-medium icon-state-default icon-mimetypes-x-content-plugin" data-identifier="mimetypes-x-content-plugin">', $result);
    }

    /**
     * Tests the returns of tt_content + mock record of type 'list' (aka plugin) with a dedicated icon for the
     * plugin (registered in ExtensionUtility::registerPlugin)
     *
     * @test
     */
    public function getIconForRecordWithMockRecordOfTypePluginReturnsConfiguredPluginIcon(): void
    {
        $GLOBALS['TCA'] = [
            'tt_content' => [
                'ctrl' => [
                    'typeicon_column' => 'CType',
                    'typeicon_classes' => [
                        'default' => '',
                        'list' => 'mimetypes-x-content-plugin',
                    ],
                ],
                'columns' => [
                    'list_type' => [
                        'config' => [
                            'items' => [
                                [
                                    'label' => 'Blog example plugin',
                                    'value' => 'pi_blogexample',
                                    'icon' => $this->registeredIconIdentifier,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $mockRecord = $this->mockRecord;
        $mockRecord['CType'] = 'list';
        $mockRecord['list_type'] = 'pi_blogexample';
        $result = $this->subject->getIconForRecord('tt_content', $mockRecord)->render();
        self::assertStringContainsString('<span class="t3js-icon icon icon-size-medium icon-state-default icon-actions-close" data-identifier="actions-close">', $result);
    }

    /**
     * Tests the returns of tt_content + mock record with hidden flag
     *
     * @test
     */
    public function getIconForRecordWithMockRecordWithHiddenFlagReturnsNormalIconAndOverlay(): void
    {
        $GLOBALS['TCA'] = [
            'tt_content' => [
                'ctrl' => [
                    'enablecolumns' => [
                        'disabled' => 'hidden',
                    ],
                    'typeicon_column' => 'CType',
                    'typeicon_classes' => [
                        'default' => '',
                        'text' => 'mimetypes-x-content-text',
                    ],
                ],
            ],
        ];
        $mockRecord = $this->mockRecord;
        $mockRecord['hidden'] = '1';
        $result = $this->subject->getIconForRecord('tt_content', $mockRecord)->render();
        self::assertStringContainsString('<span class="t3js-icon icon icon-size-medium icon-state-default icon-mimetypes-x-content-text" data-identifier="mimetypes-x-content-text">', $result);
        self::assertStringContainsString('<span class="icon-overlay icon-overlay-hidden">', $result);
    }

    public static function getIconForRecordDefaultsToBasePageIconForCustomPageTypesIfTheyDontDefineOwnIconsDataProvider(): iterable
    {
        yield 'Custom page without default icon' => [
            'record' => [
                'doktype' => '1337',
                'hidden' => '0',
                'content_from_pid' => '0',
                'nav_hide' => '0',
                'is_siteroot' => '0',
                'module' => '',
            ],
            'expected' => '<span class="t3js-icon icon icon-size-medium icon-state-default icon-apps-pagetree-page-default" data-identifier="apps-pagetree-page-default">',
        ];

        yield 'nav_hide=1' => [
            'record' => [
                'doktype' => '1337',
                'nav_hide' => '1',
                'is_siteroot' => '0',
                'module' => '',
                'content_from_pid' => '0',
            ],
            'expected' => '<span class="t3js-icon icon icon-size-medium icon-state-default icon-apps-pagetree-page-hideinmenu" data-identifier="apps-pagetree-page-hideinmenu">',
        ];

        yield 'is_siteroot=1' => [
            'record' => [
                'doktype' => '1337',
                'nav_hide' => '0',
                'is_siteroot' => '1',
                'module' => '',
                'content_from_pid' => '0',
            ],
            'expected' => '<span class="t3js-icon icon icon-size-medium icon-state-default icon-apps-pagetree-page-domain" data-identifier="apps-pagetree-page-domain">',
        ];

        yield 'module=fe_users' => [
            'record' => [
                'doktype' => '1337',
                'nav_hide' => '0',
                'is_siteroot' => '0',
                'module' => 'fe_users',
                'content_from_pid' => '0',
            ],
            'expected' => '<span class="t3js-icon icon icon-size-medium icon-state-default icon-apps-pagetree-folder-contains-fe_users" data-identifier="apps-pagetree-folder-contains-fe_users">',
        ];

        yield 'content_from_pid=1' => [
            'record' => [
                'doktype' => '1337',
                'nav_hide' => '0',
                'is_siteroot' => '0',
                'module' => '',
                'content_from_pid' => '1',
            ],
            'expected' => '<span class="t3js-icon icon icon-size-medium icon-state-default icon-apps-pagetree-page-content-from-page" data-identifier="apps-pagetree-page-content-from-page">',
        ];

        yield 'content_from_pid=1, nav_hide=1' => [
            'record' => [
                'doktype' => '1337',
                'nav_hide' => '1',
                'is_siteroot' => '0',
                'module' => '',
                'content_from_pid' => '1',
            ],
            'expected' => '<span class="t3js-icon icon icon-size-medium icon-state-default icon-apps-pagetree-page-content-from-page-hideinmenu" data-identifier="apps-pagetree-page-content-from-page-hideinmenu">',
        ];
    }

    /**
     * @test
     * @dataProvider getIconForRecordDefaultsToBasePageIconForCustomPageTypesIfTheyDontDefineOwnIconsDataProvider
     */
    public function getIconForRecordDefaultsToBasePageIconForCustomPageTypesIfTheyDontDefineOwnIcons(array $record, string $expected): void
    {
        $result = $this->subject->getIconForRecord('pages', $record)->render();

        self::assertStringContainsString($expected, $result);
    }

    /**
     * Create file object to use as test subject
     */
    protected function getTestSubjectFileObject(string $extension, string $mimeType = ''): File
    {
        $mockedStorage = $this->createMock(ResourceStorage::class);
        $mockedFile = $this->getMockBuilder(File::class)
            ->setConstructorArgs([['identifier' => '', 'name' => ''], $mockedStorage])
            ->getMock();
        $mockedFile->expects(self::atMost(1))->method('getExtension')->willReturn($extension);
        $mockedFile->expects(self::atLeastOnce())->method('getMimeType')->willReturn($mimeType);
        return $mockedFile;
    }

    /**
     * Create folder object to use as test subject
     */
    protected function getTestSubjectFolderObject(string $identifier): Folder
    {
        $mockedStorage = $this->createMock(ResourceStorage::class);
        $mockedStorage->method('getRootLevelFolder')->willReturn(
            new Folder($mockedStorage, '/', '/')
        );
        $mockedStorage->method('checkFolderActionPermission')->willReturn(true);
        $mockedStorage->method('isBrowsable')->willReturn(true);
        return new Folder($mockedStorage, $identifier, $identifier);
    }
}
