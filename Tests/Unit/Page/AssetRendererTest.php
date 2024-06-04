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

namespace TYPO3\CMS\Core\Tests\Unit\Page;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Page\AssetRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class AssetRendererTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;
    protected AssetRenderer $subject;
    protected MockObject&EventDispatcherInterface $eventDispatcher;

    public function setUp(): void
    {
        parent::setUp();
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->subject = new AssetRenderer(null, $eventDispatcher);
        $this->eventDispatcher = $eventDispatcher;
    }

    #[DataProviderExternal(AssetDataProvider::class, 'filesDataProvider')]
    #[Test]
    public function styleSheets(array $files, array $expectedResult, array $expectedMarkup): void
    {
        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
        foreach ($files as $file) {
            [$identifier, $source, $attributes, $options] = $file;
            $assetCollector->addStyleSheet($identifier, $source, $attributes, $options);
        }
        self::assertSame($expectedMarkup['css_no_prio'], $this->subject->renderStyleSheets());
        self::assertSame($expectedMarkup['css_prio'], $this->subject->renderStyleSheets(true));
    }

    #[DataProviderExternal(AssetDataProvider::class, 'filesDataProvider')]
    #[Test]
    public function javaScript(array $files, array $expectedResult, array $expectedMarkup): void
    {
        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
        foreach ($files as $file) {
            [$identifier, $source, $attributes, $options] = $file;
            $assetCollector->addJavaScript($identifier, $source, $attributes, $options);
        }
        self::assertSame($expectedMarkup['js_no_prio'], $this->subject->renderJavaScript());
        self::assertSame($expectedMarkup['js_prio'], $this->subject->renderJavaScript(true));
    }

    #[DataProviderExternal(AssetDataProvider::class, 'inlineDataProvider')]
    #[Test]
    public function inlineJavaScript(array $sources, array $expectedResult, array $expectedMarkup): void
    {
        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
        foreach ($sources as $source) {
            [$identifier, $source, $attributes, $options] = $source;
            $assetCollector->addInlineJavaScript($identifier, $source, $attributes, $options);
        }
        self::assertSame($expectedMarkup['js_no_prio'], $this->subject->renderInlineJavaScript());
        self::assertSame($expectedMarkup['js_prio'], $this->subject->renderInlineJavaScript(true));
    }

    #[DataProviderExternal(AssetDataProvider::class, 'inlineDataProvider')]
    #[Test]
    public function inlineStyleSheets(array $sources, array $expectedResult, array $expectedMarkup): void
    {
        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
        foreach ($sources as $source) {
            [$identifier, $source, $attributes, $options] = $source;
            $assetCollector->addInlineStyleSheet($identifier, $source, $attributes, $options);
        }
        self::assertSame($expectedMarkup['css_no_prio'], $this->subject->renderInlineStyleSheets());
        self::assertSame($expectedMarkup['css_prio'], $this->subject->renderInlineStyleSheets(true));
    }

    #[DataProviderExternal(AssetDataProvider::class, 'renderMethodsAndEventsDataProvider')]
    #[Test]
    public function beforeRenderingEvent(
        string $renderMethodName,
        bool $isInline,
        bool $priority,
        string $eventClassName
    ): void {
        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
        $event = new $eventClassName(
            $assetCollector,
            $isInline,
            $priority
        );

        $this->eventDispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with($event);

        $this->subject->$renderMethodName($priority);
    }
}
