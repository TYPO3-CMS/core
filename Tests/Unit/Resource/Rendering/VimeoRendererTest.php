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

namespace TYPO3\CMS\Core\Tests\Unit\Resource\Rendering;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\VimeoHelper;
use TYPO3\CMS\Core\Resource\Rendering\VimeoRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class VimeoRendererTest
 */
final class VimeoRendererTest extends UnitTestCase
{
    protected VimeoRenderer&MockObject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $vimeoHelper = $this->getAccessibleMock(VimeoHelper::class, ['getOnlineMediaId'], ['vimeo']);
        $vimeoHelper->method('getOnlineMediaId')->willReturn('7331');

        $this->subject = $this->getAccessibleMock(VimeoRenderer::class, ['getOnlineMediaHelper', 'shouldIncludeFrameBorderAttribute']);
        $this->subject->method('shouldIncludeFrameBorderAttribute')->willReturn(false);
        $this->subject->method('getOnlineMediaHelper')->willReturn($vimeoHelper);
    }

    #[Test]
    public function getPriorityReturnsCorrectValue(): void
    {
        self::assertSame(1, $this->subject->getPriority());
    }

    #[Test]
    public function canRenderReturnsTrueOnCorrectFile(): void
    {
        $fileResourceMock1 = $this->createMock(File::class);
        $fileResourceMock1->method('getMimeType')->willReturn('video/vimeo');

        $fileResourceMock2 = $this->createMock(File::class);
        $fileResourceMock2->method('getMimeType')->willReturn('video/unknown');
        $fileResourceMock2->method('getExtension')->willReturn('vimeo');

        self::assertTrue($this->subject->canRender($fileResourceMock1));
        self::assertTrue($this->subject->canRender($fileResourceMock2));
    }

    #[Test]
    public function canRenderReturnsFalseOnCorrectFile(): void
    {
        $fileResourceMock = $this->createMock(File::class);
        $fileResourceMock->method('getMimeType')->willReturn('video/youtube');

        self::assertFalse($this->subject->canRender($fileResourceMock));
    }

    #[Test]
    public function renderOutputIsCorrect(): void
    {
        $fileResourceMock = $this->createMock(File::class);

        self::assertSame(
            '<iframe src="https://player.vimeo.com/video/7331?dnt=1&amp;title=0&amp;byline=0&amp;portrait=0" allowfullscreen width="300" height="200" allow="fullscreen"></iframe>',
            $this->subject->render($fileResourceMock, '300m', '200')
        );
    }

    #[Test]
    public function renderOutputWithLoopIsCorrect(): void
    {
        $fileResourceMock = $this->createMock(File::class);

        self::assertSame(
            '<iframe src="https://player.vimeo.com/video/7331?loop=1&amp;dnt=1&amp;title=0&amp;byline=0&amp;portrait=0" allowfullscreen width="300" height="200" allow="fullscreen"></iframe>',
            $this->subject->render($fileResourceMock, '300m', '200', ['loop' => 1])
        );
    }

    #[Test]
    public function renderOutputWithAutoplayIsCorrect(): void
    {
        $fileResourceMock = $this->createMock(File::class);

        self::assertSame(
            '<iframe src="https://player.vimeo.com/video/7331?autoplay=1&amp;muted=1&amp;dnt=1&amp;title=0&amp;byline=0&amp;portrait=0" allowfullscreen width="300" height="200" allow="autoplay; fullscreen"></iframe>',
            $this->subject->render($fileResourceMock, '300m', '200', ['autoplay' => 1])
        );
    }

    #[Test]
    public function renderOutputWithAutoplayFromReferenceIsCorrect(): void
    {
        $fileResourceMock = $this->createMock(File::class);

        $fileReferenceMock = $this->createMock(FileReference::class);
        $fileReferenceMock->method('getProperty')->willReturn(1);
        $fileReferenceMock->method('getOriginalFile')->willReturn($fileResourceMock);

        self::assertSame(
            '<iframe src="https://player.vimeo.com/video/7331?autoplay=1&amp;muted=1&amp;dnt=1&amp;title=0&amp;byline=0&amp;portrait=0" allowfullscreen width="300" height="200" allow="autoplay; fullscreen"></iframe>',
            $this->subject->render($fileReferenceMock, '300m', '200')
        );
    }

    #[Test]
    public function renderOutputWithAutoplayAndWithoutControlsIsCorrect(): void
    {
        $fileResourceMock = $this->createMock(File::class);

        self::assertSame(
            '<iframe src="https://player.vimeo.com/video/7331?autoplay=1&amp;muted=1&amp;dnt=1&amp;title=0&amp;byline=0&amp;portrait=0" allowfullscreen width="300" height="200" allow="autoplay; fullscreen"></iframe>',
            $this->subject->render($fileResourceMock, '300m', '200', ['autoplay' => 1])
        );
    }

    #[Test]
    public function renderOutputWithAdditionalAttributes(): void
    {
        $fileResourceMock = $this->createMock(File::class);

        self::assertSame(
            '<iframe src="https://player.vimeo.com/video/7331?dnt=1&amp;title=0&amp;byline=0&amp;portrait=0" allowfullscreen foo="bar" custom-play="preload" sanitizetest="&lt;&gt;&quot;&apos;test" width="300" height="200" allow="fullscreen"></iframe>',
            $this->subject->render($fileResourceMock, '300m', '200', ['additionalAttributes' => ['foo' => 'bar', 'custom-play' => 'preload', '<"\'>sanitize^&test' => '<>"\'test']])
        );
    }

    #[Test]
    public function renderOutputWithDataAttributesForCustomization(): void
    {
        $fileResourceMock = $this->createMock(File::class);

        self::assertSame(
            '<iframe src="https://player.vimeo.com/video/7331?dnt=1&amp;title=0&amp;byline=0&amp;portrait=0" allowfullscreen data-player-handler="vimeo" data-custom-playerId="player-123" data-sanitizetest="test" width="300" height="200" allow="fullscreen"></iframe>',
            $this->subject->render($fileResourceMock, '300m', '200', ['data' => ['player-handler' => 'vimeo', 'custom-playerId' => 'player-123', '*sanitize&test"' => 'test']])
        );
    }

    #[Test]
    public function renderOutputWithCombinationOfDataAndAdditionalAttributes(): void
    {
        $fileResourceMock = $this->createMock(File::class);

        self::assertSame(
            '<iframe src="https://player.vimeo.com/video/7331?dnt=1&amp;title=0&amp;byline=0&amp;portrait=0" allowfullscreen foo="bar" custom-play="preload" data-player-handler="vimeo" data-custom-playerId="player-123" width="300" height="200" allow="fullscreen"></iframe>',
            $this->subject->render($fileResourceMock, '300m', '200', ['data' => ['player-handler' => 'vimeo', 'custom-playerId' => 'player-123'], 'additionalAttributes' => ['foo' => 'bar', 'custom-play' => 'preload']])
        );
    }

    #[Test]
    public function renderOutputWithCustomAllowIsCorrect(): void
    {
        $fileResourceMock = $this->createMock(File::class);

        self::assertSame(
            '<iframe src="https://player.vimeo.com/video/7331?dnt=1&amp;title=0&amp;byline=0&amp;portrait=0" allowfullscreen width="300" height="200" allow="foo; bar"></iframe>',
            $this->subject->render($fileResourceMock, '300m', '200', ['allow' => 'foo; bar'])
        );
    }

    #[Test]
    public function renderOutputWithCustomAllowAndAutoplayIsCorrect(): void
    {
        $fileResourceMock = $this->createMock(File::class);

        self::assertSame(
            '<iframe src="https://player.vimeo.com/video/7331?autoplay=1&amp;muted=1&amp;dnt=1&amp;title=0&amp;byline=0&amp;portrait=0" allowfullscreen width="300" height="200" allow="foo; bar"></iframe>',
            $this->subject->render($fileResourceMock, '300m', '200', ['allow' => 'foo; bar', 'autoplay' => 1])
        );
    }

    #[Test]
    public function renderOutputWithPrivateVimeoCodeIsCorrect(): void
    {
        $vimeoHelper = $this->getAccessibleMock(VimeoHelper::class, ['getOnlineMediaId'], ['vimeo']);
        $vimeoHelper->method('getOnlineMediaId')->willReturn('7331/private0123');

        $subject = $this->getAccessibleMock(VimeoRenderer::class, ['getOnlineMediaHelper', 'shouldIncludeFrameBorderAttribute']);
        $subject->method('shouldIncludeFrameBorderAttribute')->willReturn(false);
        $subject->method('getOnlineMediaHelper')->willReturn($vimeoHelper);

        $fileResourceMock = $this->createMock(File::class);

        self::assertSame(
            '<iframe src="https://player.vimeo.com/video/7331?h=private0123&amp;dnt=1&amp;title=0&amp;byline=0&amp;portrait=0" allowfullscreen width="300" height="200" allow="fullscreen"></iframe>',
            $subject->render($fileResourceMock, '300m', '200')
        );
    }

    #[Test]
    public function renderOutputIsEscaped(): void
    {
        $vimeoHelper = $this->getAccessibleMock(VimeoHelper::class, ['getOnlineMediaId'], ['vimeo']);
        $vimeoHelper->method('getOnlineMediaId')->willReturn('7331<script>danger</script>\'"random"quotes;');

        $subject = $this->getAccessibleMock(VimeoRenderer::class, ['getOnlineMediaHelper', 'shouldIncludeFrameBorderAttribute']);
        $subject->method('shouldIncludeFrameBorderAttribute')->willReturn(false);
        $subject->method('getOnlineMediaHelper')->willReturn($vimeoHelper);

        $fileResourceMock = $this->createMock(File::class);

        self::assertSame(
            '<iframe src="https://player.vimeo.com/video/7331&lt;script&gt;danger&lt;?h=script&gt;&apos;&quot;random&quot;quotes;&amp;dnt=1&amp;title=0&amp;byline=0&amp;portrait=0" allowfullscreen width="300" height="200" allow="fullscreen"></iframe>',
            $subject->render($fileResourceMock, '300m', '200')
        );
    }

    #[Test]
    public function renderOutputWithApiIsCorrect(): void
    {
        $fileResourceMock = $this->createMock(File::class);

        self::assertSame(
            '<iframe src="https://player.vimeo.com/video/7331?api=1&amp;dnt=1&amp;title=0&amp;byline=0&amp;portrait=0" allowfullscreen width="300" height="200" allow="fullscreen"></iframe>',
            $this->subject->render($fileResourceMock, '300m', '200', ['api' => 1])
        );
    }

    #[Test]
    public function renderOutputWithEnabledNoCookieIsCorrect(): void
    {
        $fileResourceMock = $this->createMock(File::class);

        self::assertSame(
            '<iframe src="https://player.vimeo.com/video/7331?api=1&amp;dnt=1&amp;title=0&amp;byline=0&amp;portrait=0" allowfullscreen width="300" height="200" allow="fullscreen"></iframe>',
            $this->subject->render($fileResourceMock, '300m', '200', ['api' => 1, 'no-cookie' => 1])
        );
    }

    #[Test]
    public function renderOutputWithDisabledNoCookieIsCorrect(): void
    {
        $fileResourceMock = $this->createMock(File::class);

        self::assertSame(
            '<iframe src="https://player.vimeo.com/video/7331?api=1&amp;title=0&amp;byline=0&amp;portrait=0" allowfullscreen width="300" height="200" allow="fullscreen"></iframe>',
            $this->subject->render($fileResourceMock, '300m', '200', ['api' => 1, 'no-cookie' => 0])
        );
    }
}
