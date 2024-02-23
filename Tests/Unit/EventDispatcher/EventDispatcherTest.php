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

namespace TYPO3\CMS\Core\Tests\Unit\EventDispatcher;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class EventDispatcherTest extends UnitTestCase
{
    protected ListenerProviderInterface&MockObject $listenerProviderMock;
    protected EventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listenerProviderMock = $this->createMock(ListenerProviderInterface::class);
        $this->eventDispatcher = new EventDispatcher(
            $this->listenerProviderMock
        );
    }

    #[Test]
    public function implementsPsrInterface(): void
    {
        self::assertInstanceOf(EventDispatcherInterface::class, $this->eventDispatcher);
    }

    #[DataProvider('callables')]
    #[Test]
    public function dispatchesEvent(callable $callable): void
    {
        $event = new \stdClass();
        $event->invoked = 0;

        $this->listenerProviderMock->method('getListenersForEvent')->with($event)->willReturnCallback(static function (object $event) use ($callable): iterable {
            yield $callable;
        });

        $ret = $this->eventDispatcher->dispatch($event);
        self::assertSame($event, $ret);
        self::assertEquals(1, $event->invoked);
    }

    #[DataProvider('callables')]
    #[Test]
    public function doesNotDispatchStoppedEvent(callable $callable): void
    {
        $event = new class () implements StoppableEventInterface {
            public int $invoked = 0;

            public function isPropagationStopped(): bool
            {
                return true;
            }
        };

        $this->listenerProviderMock->method('getListenersForEvent')->with($event)->willReturnCallback(static function (object $event) use ($callable): iterable {
            yield $callable;
        });

        $ret = $this->eventDispatcher->dispatch($event);
        self::assertSame($event, $ret);
        self::assertEquals(0, $event->invoked);
    }

    #[DataProvider('callables')]
    #[Test]
    public function dispatchesMultipleListeners(callable $callable): void
    {
        $event = new \stdClass();
        $event->invoked = 0;

        $this->listenerProviderMock->method('getListenersForEvent')->with($event)->willReturnCallback(static function (object $event) use ($callable): iterable {
            yield $callable;
            yield $callable;
        });

        $ret = $this->eventDispatcher->dispatch($event);
        self::assertSame($event, $ret);
        self::assertEquals(2, $event->invoked);
    }

    #[DataProvider('callables')]
    #[Test]
    public function stopsOnStoppedEvent(callable $callable): void
    {
        $event = new class () implements StoppableEventInterface {
            public int $invoked = 0;
            public bool $stopped = false;

            public function isPropagationStopped(): bool
            {
                return $this->stopped;
            }
        };

        $this->listenerProviderMock->method('getListenersForEvent')->with($event)->willReturnCallback(static function (object $event) use ($callable): iterable {
            yield $callable;
            yield static function (object $event): void {
                $event->invoked += 1;
                $event->stopped = true;
            };
            yield $callable;
        });

        $ret = $this->eventDispatcher->dispatch($event);
        self::assertSame($event, $ret);
        self::assertEquals(2, $event->invoked);
    }

    #[Test]
    public function listenerExceptionIsPropagated(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionCode(1563270337);

        $event = new \stdClass();

        $this->listenerProviderMock->method('getListenersForEvent')->with($event)->willReturnCallback(static function (object $event): iterable {
            yield static function (object $event): void {
                throw new \BadMethodCallException('some invalid state', 1563270337);
            };
        });

        $this->eventDispatcher->dispatch($event);
    }

    /**
     * Provider for callables.
     * Either an invokable, class/method combination or a closure.
     */
    public static function callables(): array
    {
        return [
            [
                // Invokable
                new class () {
                    public function __invoke(object $event): void
                    {
                        $event->invoked += 1;
                    }
                },
            ],
            [
                // Class + method
                [
                    new class () {
                        public function onEvent(object $event): void
                        {
                            $event->invoked += 1;
                        }
                    },
                    'onEvent',
                ],
            ],
            [
                // Closure
                static function (object $event): void {
                    $event->invoked += 1;
                },
            ],
        ];
    }
}
