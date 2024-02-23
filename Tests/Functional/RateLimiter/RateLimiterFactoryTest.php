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
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\RateLimiter\RateLimiterFactory;
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
        $userAuth = new class ($loginType) extends AbstractUserAuthentication {
            public function __construct($loginType)
            {
                $this->loginType = $loginType;
            }
        };

        $request = (new ServerRequest('https://example.com', 'POST'));
        $subject = new RateLimiterFactory();
        $rateLimiter = $subject->createLoginRateLimiter($userAuth, $request);
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
        $userAuth = new class ($loginType) extends AbstractUserAuthentication {
            public function __construct($loginType)
            {
                $this->loginType = $loginType;
            }
        };
        $subject = new RateLimiterFactory();
        $rateLimiter = $subject->createLoginRateLimiter($userAuth, $request);
        self::assertTrue($rateLimiter->consume(6)->isAccepted());
    }
}
