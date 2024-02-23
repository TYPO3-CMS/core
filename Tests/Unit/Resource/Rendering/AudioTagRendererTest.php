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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Rendering\AudioTagRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class AudioTagRendererTest
 */
final class AudioTagRendererTest extends UnitTestCase
{
    #[Test]
    public function getPriorityReturnsCorrectValue(): void
    {
        $audioTagRenderer = new AudioTagRenderer();

        self::assertSame(1, $audioTagRenderer->getPriority());
    }

    #[Test]
    public function canRenderReturnsTrueOnCorrectFile(): void
    {
        $audioTagRenderer = new AudioTagRenderer();

        $fileResourceMock1 = $this->createMock(File::class);
        $fileResourceMock1->method('getMimeType')->willReturn('audio/mpeg');
        $fileResourceMock2 = $this->createMock(File::class);
        $fileResourceMock2->method('getMimeType')->willReturn('audio/wav');
        $fileResourceMock3 = $this->createMock(File::class);
        $fileResourceMock3->method('getMimeType')->willReturn('audio/ogg');

        self::assertTrue($audioTagRenderer->canRender($fileResourceMock1));
        self::assertTrue($audioTagRenderer->canRender($fileResourceMock2));
        self::assertTrue($audioTagRenderer->canRender($fileResourceMock3));
    }

    #[Test]
    public function canRenderReturnsFalseOnCorrectFile(): void
    {
        $audioTagRenderer = new AudioTagRenderer();

        $fileResourceMock = $this->createMock(File::class);
        $fileResourceMock->method('getMimeType')->willReturn('video/mp4');

        self::assertFalse($audioTagRenderer->canRender($fileResourceMock));
    }

    /**
     * Array of configurations
     */
    public static function renderArgumentsDataProvider(): array
    {
        return [
            [
                '//:path/myAudioFile?foo=bar&baz=true',
                [],
                '<audio controls><source src="//:path/myAudioFile?foo=bar&amp;baz=true" type="audio/mpeg"></audio>',
            ],
            [
                '//:path/myAudioFile',
                ['loop' => 1],
                '<audio controls loop><source src="//:path/myAudioFile" type="audio/mpeg"></audio>',
            ],
            [
                '//:path/myAudioFile',
                ['autoplay' => 1],
                '<audio controls autoplay><source src="//:path/myAudioFile" type="audio/mpeg"></audio>',
            ],
            [
                '//:path/myAudioFile',
                ['controls' => 0, 'autoplay' => 1],
                '<audio autoplay><source src="//:path/myAudioFile" type="audio/mpeg"></audio>',
            ],
            [
                '//:path/myAudioFile',
                ['controls' => 1, 'controlsList' => 'nodownload'],
                '<audio controls controlsList="nodownload"><source src="//:path/myAudioFile" type="audio/mpeg"></audio>',
            ],
            [
                '//:path/myAudioFile',
                ['additionalAttributes' => ['muted' => 'muted', 'foo' => 'bar']],
                '<audio muted="muted" foo="bar" controls><source src="//:path/myAudioFile" type="audio/mpeg"></audio>',
            ],
            [
                '//:path/myAudioFile',
                ['data' => ['js-required' => 'yes', 'custom-id' => 'audio-123']],
                '<audio data-js-required="yes" data-custom-id="audio-123" controls><source src="//:path/myAudioFile" type="audio/mpeg"></audio>',
            ]
            ,
            [
                '//:path/myAudioFile',
                ['data' => ['js-required' => 'yes', 'custom-id' => 'audio-123'], 'additionalAttributes' => ['muted' => 'muted', 'foo' => 'bar']],
                '<audio muted="muted" foo="bar" data-js-required="yes" data-custom-id="audio-123" controls><source src="//:path/myAudioFile" type="audio/mpeg"></audio>',
            ],
        ];
    }

    #[DataProvider('renderArgumentsDataProvider')]
    #[Test]
    public function renderOutputIsCorrect(string $url, array $arguments, string $expected): void
    {
        $audioTagRenderer = new AudioTagRenderer();

        $fileResourceMock = $this->createMock(File::class);
        $fileResourceMock->method('getMimeType')->willReturn('audio/mpeg');
        $fileResourceMock->method('getPublicUrl')->willReturn($url);

        self::assertSame(
            $expected,
            $audioTagRenderer->render($fileResourceMock, '300m', '200', $arguments)
        );
    }
}
