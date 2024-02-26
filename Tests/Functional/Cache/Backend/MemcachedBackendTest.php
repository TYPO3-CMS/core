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

namespace TYPO3\CMS\Core\Tests\Functional\Cache\Backend;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Cache\Backend\MemcachedBackend;
use TYPO3\CMS\Core\Cache\Exception;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class MemcachedBackendTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    /**
     * Sets up this test case
     */
    protected function setUp(): void
    {
        if (!extension_loaded('memcache') && !extension_loaded('memcached')) {
            self::markTestSkipped('Neither "memcache" nor "memcached" extension was available');
        }
        if (!getenv('typo3TestingMemcachedHost')) {
            self::markTestSkipped('environment variable "typo3TestingMemcachedHost" must be set to run this test');
        }
        // Note we assume that if that typo3TestingMemcachedHost env is set, we can use that for testing,
        // there is no test to see if the daemon is actually up and running. Tests will fail if env
        // is set but daemon is down.

        parent::setUp();
    }

    /**
     * Initialize MemcacheBackend ($subject)
     */
    protected function initializeSubject(): MemcachedBackend
    {
        // We know this env is set, otherwise setUp() would skip the tests
        $memcachedHost = getenv('typo3TestingMemcachedHost');
        // If typo3TestingMemcachedPort env is set, use it, otherwise fall back to standard port
        $env = getenv('typo3TestingMemcachedPort');
        $memcachedPort = is_string($env) ? (int)$env : 11211;

        $subject = new MemcachedBackend('Testing', [ 'servers' => [$memcachedHost . ':' . $memcachedPort] ]);
        $subject->initializeObject();
        return $subject;
    }

    #[Test]
    public function setThrowsExceptionIfNoFrontEndHasBeenSet(): void
    {
        $subject = $this->initializeSubject();

        $this->expectException(Exception::class);
        $this->expectExceptionCode(1207149215);

        $subject->set(StringUtility::getUniqueId('MyIdentifier'), 'some data');
    }

    #[Test]
    public function initializeObjectThrowsExceptionIfNoMemcacheServerIsConfigured(): void
    {
        $subject = new MemcachedBackend('Testing');
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1213115903);
        $subject->initializeObject();
    }

    #[Test]
    public function itIsPossibleToSetAndCheckExistenceInCache(): void
    {
        $frontendMock = $this->createMock(FrontendInterface::class);
        $frontendMock->method('getIdentifier')->willReturn('pages');

        $subject = $this->initializeSubject();
        $subject->setCache($frontendMock);

        $identifier = StringUtility::getUniqueId('MyIdentifier');
        $subject->set($identifier, 'Some data');
        self::assertTrue($subject->has($identifier));
    }

    #[Test]
    public function itIsPossibleToSetAndGetEntry(): void
    {
        $frontendMock = $this->createMock(FrontendInterface::class);
        $frontendMock->method('getIdentifier')->willReturn('pages');

        $subject = $this->initializeSubject();
        $subject->setCache($frontendMock);

        $data = 'Some data';
        $identifier = StringUtility::getUniqueId('MyIdentifier');
        $subject->set($identifier, $data);
        self::assertEquals($data, $subject->get($identifier));
    }

    #[Test]
    public function getReturnsPreviouslySetDataWithVariousTypes(): void
    {
        $frontendMock = $this->createMock(FrontendInterface::class);
        $frontendMock->method('getIdentifier')->willReturn('pages');

        $subject = $this->initializeSubject();
        $subject->setCache($frontendMock);

        $data = [
            'string' => 'Serialize a string',
            'integer' => 0,
            'anotherIntegerValue' => 123456,
            'float' => 12.34,
            'bool' => true,
            'array' => [
                0 => 'test',
                1 => 'another test',
            ],
        ];

        $subject->set('myIdentifier', $data);
        self::assertSame($data, $subject->get('myIdentifier'));
    }

    /**
     * Check if we can store ~5 MB of data.
     */
    #[Test]
    public function largeDataIsStored(): void
    {
        $frontendMock = $this->createMock(FrontendInterface::class);
        $frontendMock->method('getIdentifier')->willReturn('pages');

        $subject = $this->initializeSubject();
        $subject->setCache($frontendMock);

        $data = str_repeat('abcde', 1024 * 1024);
        $subject->set('tooLargeData', $data);
        self::assertTrue($subject->has('tooLargeData'));
        self::assertEquals($subject->get('tooLargeData'), $data);
    }

    #[Test]
    public function itIsPossibleToRemoveEntryFromCache(): void
    {
        $frontendMock = $this->createMock(FrontendInterface::class);
        $frontendMock->method('getIdentifier')->willReturn('pages');

        $subject = $this->initializeSubject();
        $subject->setCache($frontendMock);

        $data = 'Some data';
        $identifier = StringUtility::getUniqueId('MyIdentifier');
        $subject->set($identifier, $data);
        $subject->remove($identifier);
        self::assertFalse($subject->has($identifier));
    }

    #[Test]
    public function itIsPossibleToOverwriteAnEntryInTheCache(): void
    {
        $frontendMock = $this->createMock(FrontendInterface::class);
        $frontendMock->method('getIdentifier')->willReturn('pages');

        $subject = $this->initializeSubject();
        $subject->setCache($frontendMock);

        $data = 'Some data';
        $identifier = StringUtility::getUniqueId('MyIdentifier');
        $subject->set($identifier, $data);
        $otherData = 'some other data';
        $subject->set($identifier, $otherData);
        self::assertEquals($otherData, $subject->get($identifier));
    }

    #[Test]
    public function findIdentifiersByTagFindsCacheEntriesWithSpecifiedTag(): void
    {
        $frontendMock = $this->createMock(FrontendInterface::class);
        $frontendMock->method('getIdentifier')->willReturn('pages');

        $subject = $this->initializeSubject();
        $subject->setCache($frontendMock);

        $data = 'Some data';
        $identifier = StringUtility::getUniqueId('MyIdentifier');
        $subject->set($identifier, $data, ['UnitTestTag%tag1', 'UnitTestTag%tag2']);
        $retrieved = $subject->findIdentifiersByTag('UnitTestTag%tag1');
        self::assertEquals($identifier, $retrieved[0]);
        $retrieved = $subject->findIdentifiersByTag('UnitTestTag%tag2');
        self::assertEquals($identifier, $retrieved[0]);
    }

    #[Test]
    public function setRemovesTagsFromPreviousSet(): void
    {
        $frontendMock = $this->createMock(FrontendInterface::class);
        $frontendMock->method('getIdentifier')->willReturn('pages');

        $subject = $this->initializeSubject();
        $subject->setCache($frontendMock);

        $data = 'Some data';
        $identifier = StringUtility::getUniqueId('MyIdentifier');
        $subject->set($identifier, $data, ['UnitTestTag%tag1', 'UnitTestTag%tag2']);
        $subject->set($identifier, $data, ['UnitTestTag%tag3']);
        self::assertEquals([], $subject->findIdentifiersByTag('UnitTestTag%tagX'));
    }

    #[Test]
    public function hasReturnsFalseIfTheEntryDoesntExist(): void
    {
        $frontendMock = $this->createMock(FrontendInterface::class);
        $frontendMock->method('getIdentifier')->willReturn('pages');

        $subject = $this->initializeSubject();
        $subject->setCache($frontendMock);

        $identifier = StringUtility::getUniqueId('NonExistingIdentifier');
        self::assertFalse($subject->has($identifier));
    }

    #[Test]
    public function removeReturnsFalseIfTheEntryDoesntExist(): void
    {
        $frontendMock = $this->createMock(FrontendInterface::class);
        $frontendMock->method('getIdentifier')->willReturn('pages');

        $subject = $this->initializeSubject();
        $subject->setCache($frontendMock);

        $identifier = StringUtility::getUniqueId('NonExistingIdentifier');
        self::assertFalse($subject->remove($identifier));
    }

    #[Test]
    public function flushByTagRemovesCacheEntriesWithSpecifiedTag(): void
    {
        $frontendMock = $this->createMock(FrontendInterface::class);
        $frontendMock->method('getIdentifier')->willReturn('pages');

        $subject = $this->initializeSubject();
        $subject->setCache($frontendMock);

        $data = 'some data' . microtime();
        $subject->set('BackendMemcacheTest1', $data, ['UnitTestTag%test', 'UnitTestTag%boring']);
        $subject->set('BackendMemcacheTest2', $data, ['UnitTestTag%test', 'UnitTestTag%special']);
        $subject->set('BackendMemcacheTest3', $data, ['UnitTestTag%test']);
        $subject->flushByTag('UnitTestTag%special');
        self::assertTrue($subject->has('BackendMemcacheTest1'));
        self::assertFalse($subject->has('BackendMemcacheTest2'));
        self::assertTrue($subject->has('BackendMemcacheTest3'));
    }

    #[Test]
    public function flushByTagsRemovesCacheEntriesWithSpecifiedTags(): void
    {
        $frontendMock = $this->createMock(FrontendInterface::class);
        $frontendMock->method('getIdentifier')->willReturn('pages');

        $subject = $this->initializeSubject();
        $subject->setCache($frontendMock);

        $data = 'some data' . microtime();
        $subject->set('BackendMemcacheTest1', $data, ['UnitTestTag%test', 'UnitTestTag%boring']);
        $subject->set('BackendMemcacheTest2', $data, ['UnitTestTag%test', 'UnitTestTag%special']);
        $subject->set('BackendMemcacheTest3', $data, ['UnitTestTag%test']);
        $subject->flushByTags(['UnitTestTag%special', 'UnitTestTag%boring']);
        self::assertFalse($subject->has('BackendMemcacheTest1'));
        self::assertFalse($subject->has('BackendMemcacheTest2'));
        self::assertTrue($subject->has('BackendMemcacheTest3'));
    }

    #[Test]
    public function flushRemovesAllCacheEntries(): void
    {
        $frontendMock = $this->createMock(FrontendInterface::class);
        $frontendMock->method('getIdentifier')->willReturn('pages');

        $subject = $this->initializeSubject();
        $subject->setCache($frontendMock);

        $data = 'some data' . microtime();
        $subject->set('BackendMemcacheTest1', $data);
        $subject->set('BackendMemcacheTest2', $data);
        $subject->set('BackendMemcacheTest3', $data);
        $subject->flush();
        self::assertFalse($subject->has('BackendMemcacheTest1'));
        self::assertFalse($subject->has('BackendMemcacheTest2'));
        self::assertFalse($subject->has('BackendMemcacheTest3'));
    }

    #[Test]
    public function flushRemovesOnlyOwnEntries(): void
    {
        $thisFrontendMock = $this->createMock(FrontendInterface::class);
        $thisFrontendMock->method('getIdentifier')->willReturn('thisCache');
        $thisBackend = $this->initializeSubject();
        $thisBackend->setCache($thisFrontendMock);

        $thatFrontendMock = $this->createMock(FrontendInterface::class);
        $thatFrontendMock->method('getIdentifier')->willReturn('thatCache');
        $thatBackend = $this->initializeSubject();
        $thatBackend->setCache($thatFrontendMock);

        $thisBackend->set('thisEntry', 'Hello');
        $thatBackend->set('thatEntry', 'World!');
        $thatBackend->flush();

        self::assertEquals('Hello', $thisBackend->get('thisEntry'));
        self::assertFalse($thatBackend->has('thatEntry'));
    }
}
