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

namespace TYPO3\CMS\Core\Tests\Unit\Utility;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\NullFrontend;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Context\VisibilityAspect;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class RootlineUtilityTest extends UnitTestCase
{
    protected RootlineUtility&MockObject&AccessibleObjectInterface $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $cacheManager = new CacheManager();
        $cacheManager->registerCache(new NullFrontend('rootline'));
        $cacheManager->registerCache(new NullFrontend('runtime'));
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManager);

        $this->subject = $this->getAccessibleMock(
            RootlineUtility::class,
            ['enrichWithRelationFields', 'resolvePageId'],
            [1, '', new Context()]
        );

        $this->subject->method('resolvePageId')->willReturnArgument(0);
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    /**
     * Tests that $subsetCandidate is completely part of $superset
     * and keys match.
     *
     * See (A ^ B) = A <=> A c B
     * @param array $subsetCandidate
     * @param array $superset
     */
    protected function assertIsSubset(array $subsetCandidate, array $superset): void
    {
        self::assertSame($subsetCandidate, array_intersect_assoc($subsetCandidate, $superset));
    }

    /**
     * @test
     */
    public function isMountedPageWithoutMountPointsReturnsFalse(): void
    {
        $this->subject->__construct(1, '', new Context());
        self::assertFalse($this->subject->isMountedPage());
    }

    /**
     * @test
     */
    public function isMountedPageWithMatchingMountPointParameterReturnsTrue(): void
    {
        $this->subject->__construct(1, '1-99', new Context());
        self::assertTrue($this->subject->isMountedPage());
    }

    /**
     * @test
     */
    public function isMountedPageWithNonMatchingMountPointParameterReturnsFalse(): void
    {
        $this->subject->__construct(1, '99-99', new Context());
        self::assertFalse($this->subject->isMountedPage());
    }

    /**
     * @test
     */
    public function processMountedPageWithNonMountedPageThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1343464100);

        $this->subject->__construct(1, '1-99', new Context());
        $this->subject->_call(
            'processMountedPage',
            ['uid' => 1],
            ['uid' => 99, 'doktype' => PageRepository::DOKTYPE_DEFAULT]
        );
    }

    /**
     * @test
     */
    public function processMountedPageWithMountedPageNotThrowsException(): void
    {
        $this->subject->__construct(1, '1-99', new Context());
        self::assertNotEmpty($this->subject->_call(
            'processMountedPage',
            ['uid' => 1],
            ['uid' => 99, 'doktype' => PageRepository::DOKTYPE_MOUNTPOINT, 'mount_pid' => 1]
        ));
    }

    /**
     * @test
     */
    public function processMountedPageWithMountedPageAddsMountedFromParameter(): void
    {
        $this->subject->__construct(1, '1-99', new Context());
        $result = $this->subject->_call(
            'processMountedPage',
            ['uid' => 1],
            ['uid' => 99, 'doktype' => PageRepository::DOKTYPE_MOUNTPOINT, 'mount_pid' => 1]
        );
        self::assertTrue(isset($result['_MOUNTED_FROM']));
        self::assertSame(1, $result['_MOUNTED_FROM']);
    }

    /**
     * @test
     */
    public function processMountedPageWithMountedPageAddsMountPointParameterToReturnValue(): void
    {
        $this->subject->__construct(1, '1-99', new Context());
        $result = $this->subject->_call(
            'processMountedPage',
            ['uid' => 1],
            ['uid' => 99, 'doktype' => PageRepository::DOKTYPE_MOUNTPOINT, 'mount_pid' => 1]
        );
        self::assertTrue(isset($result['_MP_PARAM']));
        self::assertSame('1-99', $result['_MP_PARAM']);
    }

    /**
     * @test
     */
    public function processMountedPageForMountPageIsOverlayAddsMountOLParameter(): void
    {
        $this->subject->__construct(1, '1-99', new Context());
        $result = $this->subject->_call(
            'processMountedPage',
            ['uid' => 1],
            ['uid' => 99, 'doktype' => PageRepository::DOKTYPE_MOUNTPOINT, 'mount_pid' => 1, 'mount_pid_ol' => 1]
        );
        self::assertTrue(isset($result['_MOUNT_OL']));
        self::assertTrue($result['_MOUNT_OL']);
    }

    /**
     * @test
     */
    public function processMountedPageForMountPageIsOverlayAddsDataInformationAboutMountPage(): void
    {
        $this->subject->__construct(1, '1-99', new Context());
        $result = $this->subject->_call('processMountedPage', ['uid' => 1], [
            'uid' => 99,
            'doktype' => PageRepository::DOKTYPE_MOUNTPOINT,
            'mount_pid' => 1,
            'mount_pid_ol' => 1,
            'pid' => 5,
            'title' => 'TestCase',
        ]);
        self::assertTrue(isset($result['_MOUNT_PAGE']));
        self::assertSame(['uid' => 99, 'pid' => 5, 'title' => 'TestCase'], $result['_MOUNT_PAGE']);
    }

    /**
     * @test
     */
    public function processMountedPageForMountPageWithoutOverlayReplacesMountedPageWithMountPage(): void
    {
        $mountPointPageData = [
            'uid' => 99,
            'doktype' => PageRepository::DOKTYPE_MOUNTPOINT,
            'mount_pid' => 1,
            'mount_pid_ol' => 0,
        ];
        $this->subject->__construct(1, '1-99', new Context());
        $result = $this->subject->_call('processMountedPage', ['uid' => 1], $mountPointPageData);
        $this->assertIsSubset($mountPointPageData, $result);
    }

    /**
     * @test
     */
    public function columnHasRelationToResolveDetectsGroupFieldAsLocal(): void
    {
        self::assertFalse($this->subject->_call('columnHasRelationToResolve', [
            'type' => 'group',
        ]));
    }

    /**
     * @test
     */
    public function columnHasRelationToResolveDetectsGroupFieldWithMMAsRemote2(): void
    {
        self::assertTrue($this->subject->_call('columnHasRelationToResolve', [
            'config' => [
                'type' => 'group',
                'MM' => 'tx_xyz',
            ],
        ]));
    }

    /**
     * @test
     */
    public function columnHasRelationToResolveDetectsInlineFieldAsLocal(): void
    {
        self::assertFalse($this->subject->_call('columnHasRelationToResolve', [
            'config' => [
                'type' => 'inline',
            ],
        ]));
    }

    /**
     * @test
     */
    public function columnHasRelationToResolveDetectsInlineFieldWithForeignKeyAsRemote(): void
    {
        self::assertTrue($this->subject->_call('columnHasRelationToResolve', [
            'config' => [
                'type' => 'inline',
                'foreign_field' => 'xyz',
            ],
        ]));
    }

    /**
     * @test
     */
    public function columnHasRelationToResolveDetectsInlineFieldWithFMMAsRemote(): void
    {
        self::assertTrue($this->subject->_call('columnHasRelationToResolve', [
            'config' => [
                'type' => 'inline',
                'MM' => 'xyz',
            ],
        ]));
    }

    /**
     * @test
     */
    public function columnHasRelationToResolveDetectsSelectFieldAsLocal(): void
    {
        self::assertFalse($this->subject->_call('columnHasRelationToResolve', [
            'config' => [
                'type' => 'select',
            ],
        ]));
    }

    /**
     * @test
     */
    public function columnHasRelationToResolveDetectsSelectFieldWithMMAsRemote(): void
    {
        self::assertTrue($this->subject->_call('columnHasRelationToResolve', [
            'config' => [
                'type' => 'select',
                'MM' => 'xyz',
            ],
        ]));
    }

    /**
     * @test
     */
    public function getCacheIdentifierContainsAllContextParameters(): void
    {
        $this->subject->method('resolvePageId')->willReturn(42);

        $context = new Context();
        $context->setAspect('workspace', new WorkspaceAspect(15));
        $context->setAspect('visibility', new VisibilityAspect());
        $context->setAspect('language', new LanguageAspect(8, 8, LanguageAspect::OVERLAYS_OFF));
        $this->subject->__construct(42, '47-11', $context);
        self::assertSame('42_47-11_8_15_0', $this->subject->getCacheIdentifier());
        $this->subject->__construct(42, '47-11', $context);
        self::assertSame('42_47-11_8_15_0', $this->subject->getCacheIdentifier());

        $context->setAspect('workspace', new WorkspaceAspect(0));
        $this->subject->__construct(42, '47-11', $context);
        self::assertSame('42_47-11_8_0_0', $this->subject->getCacheIdentifier());
    }

    /**
     * @test
     */
    public function getCacheIdentifierReturnsValidIdentifierWithCommasInMountPointParameter(): void
    {
        $this->subject->method('resolvePageId')->willReturn(42);
        $cacheFrontend = new NullFrontend('some-frontend');
        $context = new Context();
        $context->setAspect('workspace', new WorkspaceAspect(15));
        $context->setAspect('language', new LanguageAspect(8, 8, LanguageAspect::OVERLAYS_OFF));
        $this->subject->__construct(42, '47-11,48-12', $context);
        self::assertTrue($cacheFrontend->isValidEntryIdentifier($this->subject->getCacheIdentifier()));
    }
}
