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

namespace TYPO3\CMS\Core\Tests\Functional\RateLimiter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\RateLimiter\RateLimit;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\RateLimiter\RateLimiterFactory;
use TYPO3\CMS\Core\RateLimiter\Storage\CachingFrameworkStorage;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class RateLimiterFactoryTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    public static function loginRateLimiterLimitsRequestsDataProvider(): array
    {
        return [
            'backend accepted' => [
                'BE',
                5,
                1,
                true,
            ],
            'backend denied' => [
                'BE',
                5,
                6,
                false,
            ],
            'frontend accepted' => [
                'FE',
                5,
                1,
                true,
            ],
            'frontend denied' => [
                'FE',
                5,
                6,
                false,
            ],
        ];
    }

    #[DataProvider('loginRateLimiterLimitsRequestsDataProvider')]
    #[Test]
    public function loginRateLimiterReturnsExpectedResults(string $loginType, int $loginRateLimit, int $tokens, bool $expected): void
    {
        $GLOBALS['TYPO3_CONF_VARS'][$loginType]['loginRateLimit'] = $loginRateLimit;
        $request = (new ServerRequest('https://example.com', 'POST'));
        $subject = new RateLimiterFactory($this->get(CachingFrameworkStorage::class));
        $rateLimiter = $subject->createLoginRateLimiter($request, $loginType);
        $rateLimit = null;
        for ($i = 0; $i < $tokens; $i++) {
            $rateLimit = $rateLimiter->consume();
        }
        self::assertInstanceOf(RateLimit::class, $rateLimit);
        self::assertEquals($expected, $rateLimit->isAccepted());
    }

    #[Test]
    public function loginRateLimiterRespectsIpExcludeList(): void
    {
        $loginType = 'BE';
        $GLOBALS['TYPO3_CONF_VARS'][$loginType]['loginRateLimit'] = 5;
        $GLOBALS['TYPO3_CONF_VARS'][$loginType]['loginRateLimitIpExcludeList'] = '127.0.0.1';

        $request = (new ServerRequest('https://example.com', 'POST', 'php://input', [], ['REMOTE_ADDR' => '127.0.0.1']));
        $subject = new RateLimiterFactory($this->get(CachingFrameworkStorage::class));
        $rateLimiter = $subject->createLoginRateLimiter($request, $loginType);
        self::assertTrue($rateLimiter->consume(6)->isAccepted());
    }

    #[Test]
    public function createWithConstructorConfigWorks(): void
    {
        $config = [
            'id' => 'test-limiter',
            'policy' => 'sliding_window',
            'limit' => 3,
            'interval' => '1 minute',
        ];
        $subject = new RateLimiterFactory($this->get(CachingFrameworkStorage::class), $config);
        $limiter = $subject->create('test-key');

        self::assertTrue($limiter->consume()->isAccepted());
    }

    #[Test]
    public function createWithoutConfigThrowsLogicException(): void
    {
        $subject = new RateLimiterFactory($this->get(CachingFrameworkStorage::class));

        $this->expectException(\LogicException::class);
        $this->expectExceptionCode(1740000001);
        $subject->create('test-key');
    }

    #[Test]
    public function createLimiterWithExplicitConfigWorks(): void
    {
        $config = [
            'id' => 'explicit-test',
            'policy' => 'sliding_window',
            'limit' => 2,
            'interval' => '1 minute',
        ];
        $subject = new RateLimiterFactory($this->get(CachingFrameworkStorage::class));
        $limiter = $subject->createLimiter($config, 'some-key');

        self::assertTrue($limiter->consume()->isAccepted());
        self::assertTrue($limiter->consume()->isAccepted());
        self::assertFalse($limiter->consume()->isAccepted());
    }

    #[Test]
    public function createLimiterAppliesConfigOverrides(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['rateLimiter']['override-test'] = [
            'limit' => 1,
        ];

        $config = [
            'id' => 'override-test',
            'policy' => 'sliding_window',
            'limit' => 100,
            'interval' => '1 minute',
        ];
        $subject = new RateLimiterFactory($this->get(CachingFrameworkStorage::class));
        $limiter = $subject->createLimiter($config, 'some-key');

        self::assertTrue($limiter->consume()->isAccepted());
        self::assertFalse($limiter->consume()->isAccepted());
    }

    #[Test]
    public function loginLimiterUsesHumanReadableIdAndRespectsOverrides(): void
    {
        $loginType = 'BE';
        $GLOBALS['TYPO3_CONF_VARS'][$loginType]['loginRateLimit'] = 10;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['rateLimiter']['login-be'] = [
            'limit' => 1,
        ];

        $request = new ServerRequest('https://example.com', 'POST', 'php://input', [], ['REMOTE_ADDR' => '192.0.2.99']);
        $subject = new RateLimiterFactory($this->get(CachingFrameworkStorage::class));
        $rateLimiter = $subject->createLoginRateLimiter($request, $loginType);

        self::assertTrue($rateLimiter->consume()->isAccepted());
        self::assertFalse($rateLimiter->consume()->isAccepted());
    }
}
