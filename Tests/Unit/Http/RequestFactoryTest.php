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

namespace TYPO3\CMS\Core\Tests\Unit\Http;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\Client\GuzzleClientFactory;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class RequestFactoryTest extends UnitTestCase
{
    #[Test]
    public function requestHasMethodSet(): void
    {
        $factory = new RequestFactory(new GuzzleClientFactory());
        $request = $factory->createRequest('POST', '/');
        self::assertSame('POST', $request->getMethod());
    }

    #[Test]
    public function requestFactoryHasAWritableEmptyBody(): void
    {
        $factory = new RequestFactory(new GuzzleClientFactory());
        $request = $factory->createRequest('GET', '/');
        $body = $request->getBody();

        self::assertSame('', $body->__toString());
        self::assertSame(0, $body->getSize());
        self::assertTrue($body->isSeekable());

        $body->write('Foo');
        self::assertSame(3, $body->getSize());
        self::assertSame('Foo', $body->__toString());
    }

    #[Test]
    public function raisesExceptionForInvalidMethod(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1436717275);
        $factory = new RequestFactory(new GuzzleClientFactory());
        $factory->createRequest('BOGUS-BODY', '/');
    }
}
