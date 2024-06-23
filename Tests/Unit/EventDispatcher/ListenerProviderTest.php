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
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ListenerProviderTest extends UnitTestCase
{
    protected ContainerInterface&MockObject $containerMock;
    protected ?ListenerProvider $listenerProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->listenerProvider = new ListenerProvider(
            $this->containerMock
        );
    }

    #[Test]
    public function implementsPsrInterface(): void
    {
        self::assertInstanceOf(ListenerProviderInterface::class, $this->listenerProvider);
    }

    #[Test]
    public function addedListenersAreReturnedByGetAllListenerDefinitions(): void
    {
        $this->listenerProvider->addListener('Event\\Name', 'listener1');
        $this->listenerProvider->addListener('Event\\Name', 'listener2', 'methodName');

        self::assertEquals([
            'Event\\Name' => [
                'listener1' => [ 'service' => 'listener1', 'method' => null ],
                'listener2' => [ 'service' => 'listener2', 'method' => 'methodName' ],
            ],
        ], $this->listenerProvider->getAllListenerDefinitions());
    }

    #[Test]
    public function addedListenerCorrectlySetsTheListenerIdentifier(): void
    {
        $this->listenerProvider->addListener(event: 'Event\\Name', service: 'service.name1');
        $this->listenerProvider->addListener(event: 'Event\\Name', service: 'service.name2', identifier: 'listenerIdentifier2');

        self::assertEquals([
            'Event\\Name' => [
                'service.name1' => [ 'service' => 'service.name1', 'method' => null ],
                'listenerIdentifier2' => [ 'service' => 'service.name2', 'method' => null ],
            ],
        ], $this->listenerProvider->getAllListenerDefinitions());
    }

    #[DataProvider('listeners')]
    #[Test]
    public function dispatchesEvent($listener, ?string $method = null): void
    {
        $event = new \stdClass();
        $event->invoked = 0;

        $this->containerMock->method('get')->with('listener')->willReturn($listener);
        $this->listenerProvider->addListener(\stdClass::class, 'listener', $method);

        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            $listener($event);
        }

        self::assertEquals(1, $event->invoked);
    }

    #[DataProvider('listeners')]
    #[Test]
    public function associatesToEventParentClass($listener, ?string $method = null): void
    {
        $extendedEvent = new class () extends \stdClass {
            public int $invoked = 0;
        };

        $this->containerMock->method('get')->with('listener')->willReturn($listener);
        $this->listenerProvider->addListener(\stdClass::class, 'listener', $method);
        foreach ($this->listenerProvider->getListenersForEvent($extendedEvent) as $listener) {
            $listener($extendedEvent);
        }

        self::assertEquals(1, $extendedEvent->invoked);
    }

    #[DataProvider('listeners')]
    #[Test]
    public function associatesToImplementedInterfaces($listener, ?string $method = null): void
    {
        $eventImplementation = new class () implements \IteratorAggregate {
            public int $invoked = 0;

            public function getIterator(): \Traversable
            {
                throw new \BadMethodCallException('Test', 1586942436);
            }
        };

        $this->containerMock->method('get')->with('listener')->willReturn($listener);
        $this->listenerProvider->addListener(\IteratorAggregate::class, 'listener', $method);
        foreach ($this->listenerProvider->getListenersForEvent($eventImplementation) as $listener) {
            $listener($eventImplementation);
        }

        self::assertEquals(1, $eventImplementation->invoked);
    }

    #[Test]
    public function addListenerPreservesOrder(): void
    {
        $this->listenerProvider->addListener(\stdClass::class, 'listener1');
        $this->listenerProvider->addListener(\stdClass::class, 'listener2');

        $event = new \stdClass();
        $event->sequence = '';
        $this->containerMock->method('get')->willReturnOnConsecutiveCalls(
            static function (object $event): void {
                $event->sequence .= 'a';
            },
            static function (object $event): void {
                $event->sequence .= 'b';
            }
        );
        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            $listener($event);
        }

        self::assertEquals('ab', $event->sequence);
    }

    #[Test]
    public function throwsExceptionForInvalidCallable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1549988537);

        $event = new \stdClass();
        $this->containerMock->method('get')->with('listener')->willReturn(new \stdClass());
        $this->listenerProvider->addListener(\stdClass::class, 'listener');
        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            $listener($event);
        }
    }

    /**
     * Provider for event listeners.
     * Either an invokable, class/method combination or a closure.
     */
    public static function listeners(): array
    {
        return [
            [
                // Invokable
                'listener' => new class () {
                    public function __invoke(object $event): void
                    {
                        $event->invoked = 1;
                    }
                },
                'method' => null,
            ],
            [
                // Class + method
                'listener' => new class () {
                    public function onEvent(object $event): void
                    {
                        $event->invoked = 1;
                    }
                },
                'method' => 'onEvent',
            ],
            [
                // Closure
                'listener' => static function (object $event): void {
                    $event->invoked = 1;
                },
                'method' => null,
            ],
        ];
    }
}
